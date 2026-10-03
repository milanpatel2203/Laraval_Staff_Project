<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    /**
     * Stream a CSV file download with UTF-8 BOM for Microsoft Excel compatibility.
     *
     * @param string $filename
     * @param array $headers
     * @param iterable $rows
     * @return StreamedResponse
     */
    public static function streamDownload(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel to recognize encoding properly
            fputs($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, $headers);

            // Data rows
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
        ]);
    }
}
