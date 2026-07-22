<?php

declare(strict_types=1);

namespace app\modules\ai\services;

final class AiExportResult
{
    public function __construct(
        public readonly string $filePath,
        public readonly string $fileName,
        public readonly string $datasetCode,
        public readonly int $rowCount,
        public readonly int $durationMs,
        public readonly ?string $downloadUrl = null
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'file_name' => $this->fileName,
            'dataset' => $this->datasetCode,
            'row_count' => $this->rowCount,
            'duration_ms' => $this->durationMs,
            'download_url' => $this->downloadUrl,
        ];
    }
}
