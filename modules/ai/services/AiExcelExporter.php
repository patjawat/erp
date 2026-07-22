<?php

declare(strict_types=1);

namespace app\modules\ai\services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\helpers\FileHelper;

class AiExcelExporter
{
    public function export(QueryResult $result, ?string $preferredName = null): AiExportResult
    {
        $startedAt = microtime(true);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($this->safeSheetTitle($result->dataset->code), 0, 31));

        foreach ($result->fields as $index => $field) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $result->labels[$field] ?? $field);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getStyle($column . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $rowNumber = 2;
        foreach ($result->rows as $row) {
            foreach ($result->fields as $index => $field) {
                $column = Coordinate::stringFromColumnIndex($index + 1);
                $sheet->setCellValue($column . $rowNumber, $row[$field] ?? null);
            }
            $rowNumber++;
        }

        foreach ($result->fields as $index => $field) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index + 1))->setAutoSize(true);
        }

        $userSegment = isset(Yii::$app->user) && !Yii::$app->user->isGuest ? (string) Yii::$app->user->id : 'system';
        $directory = Yii::getAlias('@runtime/ai-exports/' . $this->safeFileName($userSegment) . '/' . date('Ymd'));
        FileHelper::createDirectory($directory);

        $fileName = $this->safeFileName($preferredName ?: $result->dataset->code . '_' . date('Ymd_His')) . '.xlsx';
        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;
        (new Xlsx($spreadsheet))->save($filePath);

        return new AiExportResult(
            $filePath,
            $fileName,
            $result->dataset->code,
            $result->rowCount(),
            (int) round((microtime(true) - $startedAt) * 1000)
        );
    }

    private function safeFileName(string $name): string
    {
        $name = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $name) ?: 'ai_export';
        return trim($name, '_') ?: 'ai_export';
    }

    private function safeSheetTitle(string $title): string
    {
        return preg_replace('/[\\\\\\/\\?\\*\\[\\]:]+/', '_', $title) ?: 'AI Export';
    }
}
