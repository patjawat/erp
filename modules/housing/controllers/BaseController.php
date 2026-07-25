<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;

abstract class BaseController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['housing.staff', 'housing.admin'],
                    ],
                ],
            ],
        ]);
    }
}
