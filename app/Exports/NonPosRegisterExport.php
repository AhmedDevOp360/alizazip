<?php

namespace App\Exports;

use App\Utils\NonPosRegisterUtil;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class NonPosRegisterExport implements FromView, WithTitle
{
    protected int $businessId;
    protected string $startDate;
    protected string $endDate;
    protected ?int $locationId;

    public function __construct(int $businessId, string $startDate, string $endDate, ?int $locationId = null)
    {
        $this->businessId = $businessId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->locationId = $locationId;
    }

    public function title(): string
    {
        return 'Non-POS Closing';
    }

    public function view(): View
    {
        /** @var NonPosRegisterUtil $util */
        $util = app(NonPosRegisterUtil::class);

        $openings = $util->getOpeningBalances($this->businessId, $this->startDate, $this->locationId);
        $movements = $util->getDailyMovements($this->businessId, $this->startDate, $this->endDate, $this->locationId);

        $opening_caja = (float) ($openings['caja_menor'] ?? 0);
        $ingresos_total = (float) ($movements['ingresos']['sales'] + $movements['ingresos']['abonos']);
        $egresos_total = (float) ($movements['egresos']['gastos'] + $movements['egresos']['anticipos'] + $movements['egresos']['pagos']);
        $final_caja = $opening_caja + $ingresos_total - $egresos_total;

        return view('report.exports.non_pos_register_export', [
            'start' => $this->startDate,
            'end' => $this->endDate,
            'opening' => $openings,
            'movements' => $movements,
            'totals' => [
                'ingresos' => $ingresos_total,
                'egresos' => $egresos_total,
                'final_caja_menor' => $final_caja,
            ],
        ]);
    }
}


