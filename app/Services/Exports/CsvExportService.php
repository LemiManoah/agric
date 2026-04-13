<?php

namespace App\Services\Exports;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    /**
     * @param  array<int, array<string, mixed>>|\Illuminate\Support\Collection<int, array<string, mixed>>  $rows
     */
    public function streamDownload(string $filename, array|Collection $rows, ?array $headers = null): StreamedResponse
    {
        $normalizedRows = $rows instanceof Collection ? $rows->values()->all() : array_values($rows);
        $headerRow = $headers ?? array_keys($normalizedRows[0] ?? []);

        return response()->streamDownload(function () use ($headerRow, $normalizedRows): void {
            $output = fopen('php://output', 'w');

            if ($headerRow !== []) {
                fputcsv($output, $headerRow);
            }

            foreach ($normalizedRows as $row) {
                $orderedRow = [];

                foreach ($headerRow as $header) {
                    $orderedRow[] = $row[$header] ?? null;
                }

                fputcsv($output, $orderedRow === [] ? array_values($row) : $orderedRow);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
