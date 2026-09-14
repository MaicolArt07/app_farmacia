<?php

namespace App\Exports;

use App\Exports\Sheets\DailyBookCashMovementsSheet;
use App\Exports\Sheets\DailyBookPurchasesSheet;
use App\Exports\Sheets\DailyBookSalesSheet;
use App\Exports\Sheets\DailyBookSummarySheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DailyBookExport implements WithMultipleSheets
{
    public function __construct(private string $from, private string $to)
    {
    }

    public function sheets(): array
    {
        return [
            new DailyBookSummarySheet($this->from, $this->to),
            new DailyBookSalesSheet($this->from, $this->to),
            new DailyBookPurchasesSheet($this->from, $this->to),
            new DailyBookCashMovementsSheet($this->from, $this->to),
        ];
    }
}
