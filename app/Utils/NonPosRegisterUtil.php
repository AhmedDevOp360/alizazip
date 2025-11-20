<?php

namespace App\Utils;

use App\Account;
use App\AccountTransaction;
use App\Transaction;
use App\TransactionPayment;
use App\CashRegister;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NonPosRegisterUtil extends Util
{
    /**
     * Returns opening balances for Caja Menor and bank accounts up to (date - 1 day).
     *
     * @param int $businessId
     * @param string $date Y-m-d
     * @param int|null $locationId
     * @return array [
     *   'caja_menor' => float,
     *   'accounts' => [account_id => ['name' => string, 'opening' => float]]
     * ]
     */
    public function getOpeningBalances(int $businessId, string $date, ?int $locationId = null): array
    {
        $asOf = Carbon::parse($date)->startOfDay();

        // 1) Petty cash opening from last closed cash register before the date (POS only)
        $registerQuery = CashRegister::where('business_id', $businessId)
            ->where('status', 'close')
            ->whereNotNull('closed_at')
            // Ignore General (Non-POS) closings when determining opening petty cash
            // ->where(function ($q) {
            //     $q->whereNull('closing_type')
            //       ->orWhere('closing_type', '!=', 'general');
            // })
            ->where('closed_at', '<', $asOf)
            ->orderBy('closed_at', 'desc')
            // Ensure the most recent record when multiple share the same closed_at
            ->orderBy('id', 'desc');

        if (! is_null($locationId) && $this->checkIfColumnExists('cash_registers', 'location_id')) {
            $registerQuery->where('location_id', $locationId);
        }

        $lastClosed = $registerQuery->first();
        if ($lastClosed) {
            $result['caja_menor'] = (float) $lastClosed->closing_amount;
        } else {
            $result['caja_menor'] = 0.0;
        }

        // 2) Bank/other accounts opening (optional)
        $accountsQuery = Account::where('business_id', $businessId)
            ->select('id', 'name', 'account_number', 'note');

        $accounts = $accountsQuery->get();

        $accountIds = $accounts->pluck('id')->all();
        if (! empty($accountIds)) {
            $balances = AccountTransaction::whereIn('account_id', $accountIds)
                ->where('operation_date', '<', $asOf)
                ->select('account_id', DB::raw("SUM(CASE WHEN type='credit' THEN amount ELSE -1*amount END) as balance"))
                ->groupBy('account_id')
                ->pluck('balance', 'account_id');

            foreach ($accounts as $account) {
                $opening = (float) ($balances[$account->id] ?? 0);
                $result['accounts'][$account->id] = [
                    'name' => $account->name,
                    'opening' => $opening,
                ];
            }
        }

        return $result;
    }

    private function checkIfColumnExists(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Returns daily ingresos/egresos and detailed rows for the selected period from backend (non-POS) transactions.
     * Excludes POS register payments by restricting to non-POS sells (direct_sale=1) and other non-sell docs.
     *
     * @param int $businessId
     * @param string $startDate Y-m-d
     * @param string $endDate Y-m-d
     * @param int|null $locationId
     * @return array
     */
    public function getDailyMovements(int $businessId, string $startDate, string $endDate, ?int $locationId = null): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $payments = TransactionPayment::join('transactions as t', 'transaction_payments.transaction_id', '=', 't.id')
            ->leftJoin('contacts as c', 't.contact_id', '=', 'c.id')
            ->where('t.business_id', $businessId)
            ->whereBetween('transaction_payments.created_at', [$start, $end])
            // Include all sells (both POS and direct sale) and non-sell types
            ;

        if (! is_null($locationId)) {
            $payments->where('t.location_id', $locationId);
        }

        $rawPayments = $payments->select(
            'transaction_payments.id',
            'transaction_payments.amount',
            'transaction_payments.method',
            'transaction_payments.created_at',
            't.type as t_type',
            't.ref_no',
            't.invoice_no',
            't.status',
            'c.name as contact_name',
            'c.supplier_business_name'
        )->get();

        $ingresos = [
            'sales' => 0.0,
            'abonos' => 0.0,
            'details' => []
        ];
        $egresos = [
            'gastos' => 0.0,
            'anticipos' => 0.0,
            'pagos' => 0.0,
            'details' => []
        ];

        $byMethodIncome = [];
        $byMethodExpense = [];

        foreach ($rawPayments as $p) {
            $contact = trim(($p->supplier_business_name ? $p->supplier_business_name.', ' : '').(string) $p->contact_name);
            $desc = $p->ref_no ?: $p->invoice_no ?: $p->t_type;
            $codePrefix = '';
            if (! empty($p->ref_no)) {
                $codePrefix = strtoupper(substr($p->ref_no, 0, 2));
            } elseif (! empty($p->invoice_no)) {
                $codePrefix = strtoupper(substr($p->invoice_no, 0, 2));
            }

            $row = [
                'date' => Carbon::parse($p->created_at)->toDateTimeString(),
                'party' => $contact,
                'description' => $desc,
                'amount' => (float) $p->amount,
                'method' => $p->method,
                'type' => $p->t_type,
                'code_prefix' => $codePrefix,
            ];

            // Prefix mapping like the Excel template
            if (in_array($codePrefix, ['FV'])) { // Total Sells
                $ingresos['sales'] += (float) $p->amount;
                $ingresos['details'][] = $row;
                $byMethodIncome[$p->method] = ($byMethodIncome[$p->method] ?? 0) + (float) $p->amount;
                continue;
            }
            if (in_array($codePrefix, ['PY','PC'])) { // Payment Customer / Credits
                $ingresos['abonos'] += (float) $p->amount;
                $ingresos['details'][] = $row;
                $byMethodIncome[$p->method] = ($byMethodIncome[$p->method] ?? 0) + (float) $p->amount;
                continue;
            }
            if (in_array($codePrefix, ['EX'])) { // Expenses
                $egresos['gastos'] += (float) $p->amount;
                $egresos['details'][] = $row;
                $byMethodExpense[$p->method] = ($byMethodExpense[$p->method] ?? 0) + (float) $p->amount;
                continue;
            }
            if (in_array($codePrefix, ['AD'])) { // Advances
                $egresos['anticipos'] += (float) $p->amount;
                $egresos['details'][] = $row;
                $byMethodExpense[$p->method] = ($byMethodExpense[$p->method] ?? 0) + (float) $p->amount;
                continue;
            }
            if (in_array($codePrefix, ['PP'])) { // Purchase Payments
                $egresos['pagos'] += (float) $p->amount;
                $egresos['details'][] = $row;
                $byMethodExpense[$p->method] = ($byMethodExpense[$p->method] ?? 0) + (float) $p->amount;
                continue;
            }

            // Fallback to transaction type when code not present
            switch ($p->t_type) {
                case 'sell':
                    $ingresos['sales'] += (float) $p->amount;
                    $ingresos['details'][] = $row;
                    $byMethodIncome[$p->method] = ($byMethodIncome[$p->method] ?? 0) + (float) $p->amount;
                    break;
                case 'sell_return':
                    $egresos['pagos'] += (float) $p->amount;
                    $egresos['details'][] = $row;
                    $byMethodExpense[$p->method] = ($byMethodExpense[$p->method] ?? 0) + (float) $p->amount;
                    break;
                case 'expense':
                    $egresos['gastos'] += (float) $p->amount;
                    $egresos['details'][] = $row;
                    $byMethodExpense[$p->method] = ($byMethodExpense[$p->method] ?? 0) + (float) $p->amount;
                    break;
                case 'purchase':
                    $egresos['pagos'] += (float) $p->amount;
                    $egresos['details'][] = $row;
                    $byMethodExpense[$p->method] = ($byMethodExpense[$p->method] ?? 0) + (float) $p->amount;
                    break;
                case 'purchase_return':
                    $ingresos['abonos'] += (float) $p->amount;
                    $ingresos['details'][] = $row;
                    $byMethodIncome[$p->method] = ($byMethodIncome[$p->method] ?? 0) + (float) $p->amount;
                    break;
                default:
                    if ($p->t_type === 'opening_balance') {
                        // ignore
                    } else {
                        $ingresos['abonos'] += (float) $p->amount;
                        $ingresos['details'][] = $row;
                        $byMethodIncome[$p->method] = ($byMethodIncome[$p->method] ?? 0) + (float) $p->amount;
                    }
                    break;
            }
        }

        return [
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'by_method' => [
                'income' => $byMethodIncome,
                'expense' => $byMethodExpense,
            ],
        ];
    }
}


