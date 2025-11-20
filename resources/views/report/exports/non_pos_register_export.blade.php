<table>
    <tr>
        <td>Filter By Date</td>
        <td>{{ $start }} to {{ $end }}</td>
    </tr>
    <tr><td>Date:</td><td>{{ \Carbon\Carbon::parse($end)->format('d F Y') }}</td></tr>
    <tr><td></td></tr>
    <tr><td><strong>Cash [ C ]</strong></td><td>{{ @num_format($opening['caja_menor'] ?? 0) }}</td></tr>
    <tr><td></td></tr>
    <tr><td colspan="2"><strong>Total Sells +</strong></td></tr>
    @foreach(($movements['ingresos']['details'] ?? []) as $r)
        @if($r['code_prefix'] === 'FV')
        <tr>
            <td>{{ $r['description'] }}</td>
            <td>{{ @num_format($r['amount']) }}</td>
        </tr>
        @endif
    @endforeach
    <tr><td><strong>Total Income</strong></td><td>{{ @num_format(($totals['ingresos'] ?? 0)) }}</td></tr>

    <tr><td></td></tr>
    <tr><td colspan="2"><strong>Total Expenses +</strong></td></tr>
    @foreach(($movements['egresos']['details'] ?? []) as $r)
        @if($r['code_prefix'] === 'EX')
        <tr>
            <td>{{ $r['description'] }}</td>
            <td>{{ @num_format($r['amount']) }}</td>
        </tr>
        @endif
    @endforeach

    <tr><td colspan="2"><strong>Total Advance +</strong></td></tr>
    @foreach(($movements['egresos']['details'] ?? []) as $r)
        @if($r['code_prefix'] === 'AD')
        <tr>
            <td>{{ $r['description'] }}</td>
            <td>{{ @num_format($r['amount']) }}</td>
        </tr>
        @endif
    @endforeach

    <tr><td colspan="2"><strong>Total Payment Purchases +</strong></td></tr>
    @foreach(($movements['egresos']['details'] ?? []) as $r)
        @if($r['code_prefix'] === 'PP')
        <tr>
            <td>{{ $r['description'] }}</td>
            <td>{{ @num_format($r['amount']) }}</td>
        </tr>
        @endif
    @endforeach

    <tr><td><strong>Total Disbursements</strong></td><td>{{ @num_format(($totals['egresos'] ?? 0)) }}</td></tr>

    <tr><td></td></tr>
    <tr><td><strong>Final Cash</strong></td><td>{{ @num_format(($totals['final_caja_menor'] ?? 0)) }}</td></tr>
</table>


