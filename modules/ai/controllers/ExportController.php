<?php

declare(strict_types=1);

namespace app\modules\ai\controllers;

use app\modules\ai\security\PermissionChecker;
use app\modules\ai\services\AuditLogger;
use app\modules\ai\services\AiExportService;
use Throwable;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

class ExportController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'excel' => ['POST'],
                    'download' => ['GET'],
                ],
            ],
        ];
    }

    public function actionExcel(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $payload = $this->requestPayload();

        try {
            $result = (new AiExportService())->exportExcel($payload);
            return [
                'success' => true,
                'data' => $result->toArray(),
            ];
        } catch (Throwable $exception) {
            (new AuditLogger())->log([
                'dataset_code' => $payload['dataset'] ?? $payload['dataset_code'] ?? null,
                'tool_name' => 'export_excel',
                'action' => 'export_excel',
                'status' => 'error',
                'error_message' => $exception->getMessage(),
            ]);
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    public function actionDownload(string $file): Response
    {
        (new PermissionChecker())->requirePermission('ai.export.excel');

        $basePath = FileHelper::normalizePath(Yii::getAlias('@runtime/ai-exports'));
        $userBasePath = FileHelper::normalizePath($basePath . DIRECTORY_SEPARATOR . (string) Yii::$app->user->id);
        $targetPath = FileHelper::normalizePath($basePath . DIRECTORY_SEPARATOR . ltrim($file, '/\\'));

        if (!str_starts_with($targetPath, $userBasePath . DIRECTORY_SEPARATOR) || !is_file($targetPath)) {
            throw new BadRequestHttpException('Export file was not found.');
        }

        return Yii::$app->response->sendFile($targetPath, basename($targetPath));
    }

    /**
     * @return array<string, mixed>
     */
    private function requestPayload(): array
    {
        $body = json_decode(Yii::$app->request->rawBody, true);
        if (!is_array($body)) {
            $body = [];
        }

        return array_merge(Yii::$app->request->post(), $body);
    }
}
