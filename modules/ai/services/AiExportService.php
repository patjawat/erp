<?php

declare(strict_types=1);

namespace app\modules\ai\services;

use app\modules\ai\security\PermissionChecker;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;

class AiExportService
{
    public function __construct(
        private ?DatasetRegistry $datasetRegistry = null,
        private ?QueryGateway $queryGateway = null,
        private ?AiExcelExporter $excelExporter = null,
        private ?AuditLogger $auditLogger = null,
        private ?PermissionChecker $permissionChecker = null
    ) {
        $this->datasetRegistry = $datasetRegistry ?: new DatasetRegistry();
        $this->queryGateway = $queryGateway ?: new QueryGateway($this->datasetRegistry);
        $this->excelExporter = $excelExporter ?: new AiExcelExporter();
        $this->auditLogger = $auditLogger ?: new AuditLogger();
        $this->permissionChecker = $permissionChecker ?: new PermissionChecker();
    }

    /**
     * @param array<string, mixed> $request
     */
    public function exportExcel(array $request, ?string $conversationId = null, ?string $provider = null, ?string $toolName = 'export_excel'): AiExportResult
    {
        $startedAt = microtime(true);
        $datasetCode = (string) ($request['dataset'] ?? $request['dataset_code'] ?? '');
        $this->permissionChecker->requirePermission('ai.export.excel');
        $dataset = $this->datasetRegistry->get($datasetCode);

        if (!$dataset->isExportable) {
            throw new BadRequestHttpException("Dataset '{$datasetCode}' is not exportable.");
        }

        $queryResult = $this->queryGateway->run($request, $conversationId, $provider, $toolName);
        $preferredName = isset($request['file_name']) ? (string) $request['file_name'] : null;
        $exportResult = $this->excelExporter->export($queryResult, $preferredName);
        $downloadUrl = $this->downloadUrl($exportResult->filePath);

        $exportResult = new AiExportResult(
            $exportResult->filePath,
            $exportResult->fileName,
            $exportResult->datasetCode,
            $exportResult->rowCount,
            $exportResult->durationMs,
            $downloadUrl
        );

        $this->auditLogger->log([
            'conversation_id' => $conversationId,
            'provider' => $provider,
            'dataset_code' => $datasetCode,
            'tool_name' => $toolName,
            'action' => 'export_excel',
            'status' => 'success',
            'row_count' => $exportResult->rowCount,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'request' => ['dataset' => $datasetCode, 'fields' => $request['fields'] ?? null],
            'response' => $exportResult->toArray(),
        ]);

        return $exportResult;
    }

    private function downloadUrl(string $filePath): ?string
    {
        if (!isset(Yii::$app->request) || !isset(Yii::$app->urlManager)) {
            return null;
        }

        $basePath = FileHelper::normalizePath(Yii::getAlias('@runtime/ai-exports'));
        $targetPath = FileHelper::normalizePath($filePath);
        $relativePath = ltrim(substr($targetPath, strlen($basePath)), DIRECTORY_SEPARATOR);

        return Url::to(['/ai/export/download', 'file' => str_replace(DIRECTORY_SEPARATOR, '/', $relativePath)], true);
    }
}
