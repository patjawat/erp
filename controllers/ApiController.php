<?php

namespace app\controllers;

use Yii;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * Public, read-only application metadata endpoints.
 */
class ApiController extends Controller
{
    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::class,
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['Content-Type'],
                    'Access-Control-Max-Age' => 3600,
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'version' => ['GET', 'HEAD'],
                ],
            ],
        ];
    }

    /**
     * Returns the version of the application serving this request.
     */
    public function actionVersion()
    {
        $displayVersion = (string) Yii::$app->version;
        $version = preg_replace('/^v(?=\d)/i', '', $displayVersion) ?? $displayVersion;

        Yii::$app->response->headers->set(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, max-age=0'
        );

        return $this->asJson([
            'schema_version' => 1,
            'version' => $version,
            'display_version' => $displayVersion,
        ]);
    }
}
