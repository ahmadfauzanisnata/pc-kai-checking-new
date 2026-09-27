<?php

namespace App\Services;

use App\Models\Inspection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReferenceNumberService
{
    protected static array $romanMonths = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
    ];

    public static function generate(string|Carbon|null $date = null): string
    {
        $targetDate = $date ? Carbon::parse($date) : now();
        $year = $targetDate->year;
        $month = $targetDate->month;
        $romanMonth = self::$romanMonths[$month] ?? 'I';

        return DB::transaction(function () use ($year, $romanMonth) {
            // Lock recent inspections for the year to prevent race conditions
            $latestInspection = Inspection::whereYear('document_date', $year)
                ->where('reference_number', 'like', "%/TI/PC-CHECK/%/{$year}")
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            $nextSequence = 1;
            if ($latestInspection && preg_match('/^(\d+)\/TI\/PC-CHECK\//', $latestInspection->reference_number, $matches)) {
                $nextSequence = intval($matches[1]) + 1;
            }

            // Ensure unique sequence
            do {
                $seqFormatted = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
                $referenceNumber = "{$seqFormatted}/TI/PC-CHECK/{$romanMonth}/{$year}";
                $exists = Inspection::where('reference_number', $referenceNumber)->exists();
                if ($exists) {
                    $nextSequence++;
                }
            } while ($exists);

            return $referenceNumber;
        });
    }
}
