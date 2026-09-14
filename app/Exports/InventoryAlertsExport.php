<?php

namespace App\Exports;

use App\Exports\Sheets\ExpiredLotsSheet;
use App\Exports\Sheets\LowStockSheet;
use App\Exports\Sheets\NearExpirationLotsSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InventoryAlertsExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new LowStockSheet(),
            new ExpiredLotsSheet(),
            new NearExpirationLotsSheet(),
        ];
    }
}
