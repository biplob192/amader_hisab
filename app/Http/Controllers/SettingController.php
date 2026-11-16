<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class SettingController extends Controller
{
    private const BACKUP_DIRECTORY_KEY = 'database_backup_directory';

    public function index(): View
    {
        $backupDirectory = Setting::query()->where('key', self::BACKUP_DIRECTORY_KEY)->value('value');

        return view('settings.index', [
            'backupDirectory' => $backupDirectory,
            'backups' => $this->recentBackups($backupDirectory),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'backup_directory' => ['required', 'string', 'max:1000'],
        ]);

        $directory = realpath($validated['backup_directory']);

        if ($directory === false || ! is_dir($directory) || ! is_writable($directory)) {
            return back()->withInput()->withErrors([
                'backup_directory' => 'Choose an existing folder that the application can write to.',
            ]);
        }

        Setting::query()->updateOrCreate(
            ['key' => self::BACKUP_DIRECTORY_KEY],
            ['value' => $directory],
        );

        return to_route('settings.index')->with('status', 'Backup folder saved.');
    }

    public function openBackupFolder(): JsonResponse
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return response()->json(['message' => 'Opening the backup folder in Explorer is available only on Windows.'], 422);
        }

        $directory = Setting::query()->where('key', self::BACKUP_DIRECTORY_KEY)->value('value');
        $realDirectory = is_string($directory) ? realpath($directory) : false;

        if ($realDirectory === false || ! is_dir($realDirectory)) {
            return response()->json(['message' => 'Save an existing backup folder before opening it in Explorer.'], 422);
        }

        $windowsDirectory = getenv('WINDIR');
        $explorer = is_string($windowsDirectory) && $windowsDirectory !== ''
            ? $windowsDirectory.DIRECTORY_SEPARATOR.'explorer.exe'
            : 'explorer.exe';

        try {
            $process = proc_open([$explorer, $realDirectory], [
                0 => ['file', 'NUL', 'r'],
                1 => ['file', 'NUL', 'w'],
                2 => ['file', 'NUL', 'w'],
            ], $pipes, null, null, ['bypass_shell' => true]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Windows Explorer could not be opened.'], 500);
        }

        if (! is_resource($process)) {
            return response()->json(['message' => 'Windows Explorer could not be opened.'], 500);
        }

        proc_close($process);

        return response()->json(['message' => 'Backup folder opened in Windows Explorer.']);
    }

    public function backup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'backup_name' => ['nullable', 'string', 'max:120'],
        ]);

        $directory = Setting::query()->where('key', self::BACKUP_DIRECTORY_KEY)->value('value');
        $realDirectory = is_string($directory) ? realpath($directory) : false;

        if ($realDirectory === false || ! is_dir($realDirectory) || ! is_writable($realDirectory)) {
            return to_route('settings.index')->with('error', 'Save an existing writable backup folder before creating a backup.');
        }

        $readableName = Str::of($validated['backup_name'] ?: 'Database backup')
            ->replaceMatches('/[^\pL\pN _-]+/u', '')
            ->trim()
            ->replaceMatches('/\s+/', '-')
            ->value();
        $readableName = $readableName !== '' ? $readableName : 'Database-backup';
        $connection = config('database.default');
        $connectionConfig = config("database.connections.{$connection}");
        $extension = ($connectionConfig['driver'] ?? null) === 'sqlite' ? 'sqlite' : 'sql';
        $filename = 'AmaderHisab-'.$readableName.'-'.now()->format('Y-m-d_H-i-s-u').'.'.$extension;
        $backupPath = $realDirectory.DIRECTORY_SEPARATOR.$filename;

        try {
            $this->writeDatabaseBackup($connectionConfig, $backupPath);
        } catch (Throwable $exception) {
            report($exception);

            return to_route('settings.index')->with('error', $exception instanceof RuntimeException
                ? $exception->getMessage()
                : 'The database backup could not be created. Check the application log for details.');
        }

        return to_route('settings.index')->with('status', "Database backup created: {$filename}");
    }

    /**
     * @param  array<string, mixed>  $connectionConfig
     */
    private function writeDatabaseBackup(array $connectionConfig, string $backupPath): void
    {
        $driver = $connectionConfig['driver'] ?? null;

        if ($driver === 'sqlite') {
            $databasePath = $connectionConfig['database'] ?? null;

            if (! is_string($databasePath) || $databasePath === ':memory:' || str_starts_with($databasePath, 'file:')) {
                throw new RuntimeException('File backups require a SQLite database stored on disk.');
            }

            $pdo = DB::connection()->getPdo();

            $pdo->exec('VACUUM INTO '.$pdo->quote($backupPath));

            return;
        }

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('Manual backups currently support MySQL, MariaDB, and file-based SQLite databases.');
        }

        if (! function_exists('proc_open')) {
            throw new RuntimeException('The PHP proc_open function is required to create MySQL backups.');
        }

        $binary = $this->findDumpUtility($driver);
        $currentEnvironment = getenv();
        $environment = is_array($currentEnvironment)
            ? array_filter($currentEnvironment, fn (string $value, string $key): bool => in_array(strtolower($key), ['path', 'systemroot', 'windir', 'temp', 'tmp'], true), ARRAY_FILTER_USE_BOTH)
            : [];
        $environment['MYSQL_PWD'] = (string) ($connectionConfig['password'] ?? '');
        $databaseName = (string) ($connectionConfig['database'] ?? '');

        if ($databaseName === '') {
            throw new RuntimeException('Set a database name in the active database connection before creating a backup.');
        }

        $arguments = [
            $binary,
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--host='.(string) ($connectionConfig['host'] ?? '127.0.0.1'),
            '--port='.(string) ($connectionConfig['port'] ?? 3306),
            '--user='.(string) ($connectionConfig['username'] ?? ''),
            $databaseName,
        ];
        $output = fopen($backupPath, 'xb');

        if ($output === false) {
            throw new RuntimeException('The backup file could not be created in the selected folder.');
        }

        try {
            $process = proc_open($arguments, [
                0 => ['pipe', 'r'],
                1 => $output,
                2 => ['pipe', 'w'],
            ], $pipes, null, $environment);
        } catch (Throwable $exception) {
            fclose($output);
            unlink($backupPath);

            throw new RuntimeException('The database dump utility could not be started. Check that the MySQL or MariaDB client is installed.', previous: $exception);
        }

        if (! is_resource($process)) {
            fclose($output);
            unlink($backupPath);
            throw new RuntimeException('The database dump utility could not be started. Install MySQL or MariaDB command-line tools and make sure they are on PATH.');
        }

        fclose($pipes[0]);
        $errorOutput = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        fclose($output);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            unlink($backupPath);
            $message = trim((string) $errorOutput);
            throw new RuntimeException($message !== '' ? 'Database backup failed: '.$message : 'Database backup failed. Check that the database dump utility is installed and configured.');
        }

        clearstatcache(true, $backupPath);

        if (filesize($backupPath) === false || filesize($backupPath) === 0) {
            unlink($backupPath);
            throw new RuntimeException('The database dump utility returned an empty backup file.');
        }
    }

    private function findDumpUtility(string $driver): string
    {
        $extension = PHP_OS_FAMILY === 'Windows' ? '.exe' : '';
        $binaryNames = $driver === 'mariadb'
            ? ['mariadb-dump'.$extension, 'mysqldump'.$extension]
            : ['mysqldump'.$extension, 'mariadb-dump'.$extension];
        $candidates = [];
        $pathEnvironment = getenv('PATH');

        foreach (explode(PATH_SEPARATOR, is_string($pathEnvironment) ? $pathEnvironment : '') as $directory) {
            if ($directory === '') {
                continue;
            }

            foreach ($binaryNames as $binaryName) {
                $candidate = rtrim($directory, '/\\').DIRECTORY_SEPARATOR.$binaryName;

                if (is_file($candidate)) {
                    $candidates[] = realpath($candidate) ?: $candidate;
                }
            }
        }

        $laragonBinDirectory = dirname(base_path(), 2).DIRECTORY_SEPARATOR.'bin';

        foreach (['mysql', 'mariadb'] as $databaseDirectory) {
            foreach ($binaryNames as $binaryName) {
                $pattern = $laragonBinDirectory.DIRECTORY_SEPARATOR.$databaseDirectory.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.$binaryName;

                foreach (glob($pattern) ?: [] as $candidate) {
                    if (is_file($candidate)) {
                        $candidates[] = realpath($candidate) ?: $candidate;
                    }
                }
            }
        }

        $candidates = array_values(array_unique($candidates));

        if ($candidates === []) {
            throw new RuntimeException('Could not find the MySQL dump utility. Add mysqldump to PATH or install the MySQL command-line tools.');
        }

        $serverVersion = (string) DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
        preg_match_all('/(\d+)\.(\d+)/', $serverVersion, $versionMatches, PREG_SET_ORDER);
        $serverVersionParts = $versionMatches === [] ? null : end($versionMatches);

        if (is_array($serverVersionParts)) {
            foreach ($candidates as $candidate) {
                if (preg_match('/[\\\\\/](?:mysql|mariadb)-(\d+)\.(\d+)/i', $candidate, $candidateVersion)
                    && $candidateVersion[1] === $serverVersionParts[1]
                    && $candidateVersion[2] === $serverVersionParts[2]) {
                    return $candidate;
                }
            }
        }

        return $candidates[0];
    }

    /**
     * @return array<int, array{name: string, size: int, created_at: int}>
     */
    private function recentBackups(mixed $directory): array
    {
        if (! is_string($directory) || ! is_dir($directory) || ! is_readable($directory)) {
            return [];
        }

        $files = [];

        foreach (scandir($directory) ?: [] as $filename) {
            if (! str_starts_with($filename, 'AmaderHisab-') || ! preg_match('/\.(sql|sqlite)$/i', $filename)) {
                continue;
            }

            $path = $directory.DIRECTORY_SEPARATOR.$filename;

            if (! is_file($path)) {
                continue;
            }

            $files[] = [
                'name' => $filename,
                'size' => (int) filesize($path),
                'created_at' => (int) filemtime($path),
            ];
        }

        usort($files, fn (array $left, array $right): int => $right['created_at'] <=> $left['created_at']);

        return array_slice($files, 0, 10);
    }
}
