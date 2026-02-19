<?php

namespace App\Services\Community;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

/**
 * قراءة ملفات مجموعات البيانات (Excel / CSV) لعرض معاينة في الصفحة.
 */
class DatasetFileReaderService
{
    /** أقصى عدد صفوف للمعاينة في الواجهة */
    public const PREVIEW_MAX_ROWS = 500;

    /**
     * قراءة الملف وإرجاع مصفوفة صفوف (أول صف = عناوين إن وُجدت).
     *
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    public function readPreview(string $fullPath): array
    {
        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            return ['headers' => [], 'rows' => []];
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        try {
            if ($extension === 'csv') {
                return $this->readCsv($fullPath);
            }
            if (in_array($extension, ['xlsx', 'xls'], true)) {
                return $this->readSpreadsheet($fullPath);
            }
        } catch (\Throwable $e) {
            report($e);
            return ['headers' => [], 'rows' => []];
        }

        return ['headers' => [], 'rows' => []];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    private function readCsv(string $path): array
    {
        $reader = new Csv();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $this->normalizeRows($rows);
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    private function readSpreadsheet(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $this->normalizeRows($rows);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    private function normalizeRows(array $rows): array
    {
        $rows = array_values($rows);
        $rows = array_slice($rows, 0, self::PREVIEW_MAX_ROWS + 1);

        $headers = [];
        $dataRows = [];

        foreach ($rows as $index => $row) {
            $row = is_array($row) ? array_values($row) : [];
            $row = array_map(function ($cell) {
                if (is_null($cell) || $cell === '') {
                    return '';
                }
                return trim((string) $cell);
            }, $row);

            if ($index === 0) {
                $colCount = count($row);
                $headers = $row;
                foreach ($headers as $i => $h) {
                    if ($h === '') {
                        $headers[$i] = 'العمود ' . ((int) $i + 1);
                    }
                }
                if (empty(array_filter($headers))) {
                    $headers = array_map(fn ($i) => 'العمود ' . ($i + 1), range(0, $colCount - 1));
                }
                continue;
            }

            $dataRows[] = $row;
        }

        if (empty($headers) && !empty($dataRows)) {
            $first = $dataRows[0];
            $headers = array_map(fn ($i) => 'العمود ' . ($i + 1), range(0, count($first) - 1));
        }

        return ['headers' => $headers, 'rows' => $dataRows];
    }
}
