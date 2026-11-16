@extends('layouts.authenticated')
@section('title', 'Settings')
@section('page-content')
    <header class="dashboard-header">
        <div><span class="eyebrow login-eyebrow">FAMILY FINANCES</span><h1>Settings</h1><p>Choose where database backups are stored.</p></div>
    </header>
    @if (session('status'))<div hidden data-swal-toast="success">{{ session('status') }}</div>@endif
    @if (session('error'))<div hidden data-swal-toast="error">{{ session('error') }}</div>@endif

    <section class="settings-card">
        <div class="settings-card-heading"><span class="settings-icon" aria-hidden="true">&#128193;</span><div><h2>Backup folder</h2><p>Choose an existing folder that this application can write to.</p></div></div>
        <form method="POST" action="{{ route('settings.update') }}" class="settings-form">
            @csrf @method('PUT')
            <div class="form-field"><label for="backup_directory">Folder path</label><input class="text-input" id="backup_directory" name="backup_directory" type="text" value="{{ old('backup_directory', $backupDirectory) }}" placeholder="C:\\Users\\you\\Documents\\AmaderHisab Backups" autocomplete="off" required>@error('backup_directory')<span class="field-error">{{ $message }}</span>@enderror</div>
            <button class="primary-action" type="submit">Save folder</button>
        </form>
        <p class="settings-help">Enter or paste the full folder path. The folder must already exist.</p>
        <form method="POST" action="{{ route('settings.open-backup-folder') }}" class="settings-open-folder-form" data-loading-form>
            @csrf
            <button class="primary-action" type="submit" data-loading-label="Opening folder..." @disabled(! $backupDirectory || ! is_dir($backupDirectory) || PHP_OS_FAMILY !== 'Windows')><span class="button-spinner" aria-hidden="true"></span><span class="button-label">Open in Windows Explorer</span> <span aria-hidden="true">&#8599;</span></button>
        </form>
    </section>

    <section class="settings-card">
        <div class="settings-card-heading"><span class="settings-icon settings-backup-icon" aria-hidden="true">&#8681;</span><div><h2>Manual database backup</h2><p>Create a backup of the current database in the saved folder.</p></div></div>
        <form method="POST" action="{{ route('settings.backup') }}" class="settings-form settings-backup-form">
            @csrf
            <div class="form-field"><label for="backup_name">Backup name <span class="optional-label">OPTIONAL</span></label><input class="text-input" id="backup_name" name="backup_name" type="text" maxlength="120" value="{{ old('backup_name') }}" placeholder="e.g. Before monthly updates">@error('backup_name')<span class="field-error">{{ $message }}</span>@enderror</div>
            <button class="primary-action" type="submit" @disabled(! $backupDirectory)>Create backup <span aria-hidden="true">&#8594;</span></button>
        </form>
        <p class="settings-help">The current time is added to the filename. Backups include the database schema and data.</p>
    </section>

    <section class="activity-card settings-backup-list">
        <div class="activity-heading"><div><h2>Recent backups</h2><p>Latest 10 backup files in the selected folder</p></div><span class="activity-count">{{ count($backups) }} files</span></div>
        @if (count($backups) > 0)
            <div class="table-scroll"><table class="expense-table"><thead><tr><th>BACKUP FILE</th><th>CREATED</th><th class="align-right">SIZE</th></tr></thead><tbody>
                @foreach ($backups as $backup)
                    <tr><td><strong>{{ $backup['name'] }}</strong></td><td>{{ \Illuminate\Support\Carbon::createFromTimestamp($backup['created_at'])->format('M j, Y · g:i A') }}</td><td class="align-right">{{ $backup['size'] >= 1048576 ? number_format($backup['size'] / 1048576, 1).' MB' : number_format($backup['size'] / 1024, 1).' KB' }}</td></tr>
                @endforeach
            </tbody></table></div>
        @else
            <div class="settings-empty">No backups yet. Choose a folder, then create your first manual backup.</div>
        @endif
    </section>
@endsection
