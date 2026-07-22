<?php

declare(strict_types=1);

namespace app\modules\ai\controllers;

use app\modules\ai\services\QueryGateway;
use Throwable;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class QueryController extends Controller
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
                    'run' => ['POST'],
                ],
            ],
        ];
    }

    public function actionRun(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $payload = $this->requestPayload();

        try {
            $result = (new QueryGateway())->run($payload);
            return [
                'success' => true,
                'data' => $result->toArray(),
            ];
        } catch (Throwable $exception) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'error' => $exception->getMessage(),
            ];
        }
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
