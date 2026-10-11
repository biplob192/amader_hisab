<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Expenses for {{ $month->format('F Y') }}</title>
    <style>
        @page { margin: 32px 38px; }
        body { color: #26342c; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { color: #194634; font-size: 20px; margin: 0 0 5px; }
        .subtitle { color: #68756c; margin: 0 0 20px; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #194634; color: #fff; font-size: 10px; text-align: left; }
        th, td { border-bottom: 1px solid #e3e9e4; padding: 9px 8px; }
        td.amount, th.amount { text-align: right; }
        tfoot td { border-top: 2px solid #194634; border-bottom: 0; font-weight: bold; }
        .empty { color: #68756c; padding: 16px 8px; text-align: center; }
    </style>
</head>
<body>
    <h1>Monthly expenses</h1>
    <p class="subtitle">{{ $month->format('F Y') }}</p>
    <table>
        <thead>
            <tr><th>Date</th><th>Category / note</th><th>Paid by</th><th class="amount">Amount</th></tr>
        </thead>
        <tbody>
            @forelse ($expenses as $expense)
                <tr>
                    <td>{{ $expense->spent_on->format('M j, Y') }}</td>
                    <td>{{ $expense->notes ?: $expense->category->name }}</td>
                    <td>{{ $expense->paid_by }}</td>
                    <td class="amount">{{ number_format($expense->net_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td class="empty" colspan="4">No expenses recorded for this month.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="3">Total</td><td class="amount">{{ number_format($total, 2) }}</td></tr>
        </tfoot>
    </table>
</body>
</html>
