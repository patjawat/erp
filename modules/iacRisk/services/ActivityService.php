<?php

namespace app\modules\iacRisk\services;

use app\modules\iacRisk\models\Activity;
use Yii;

class ActivityService
{
    public function log(array $attributes): void
    {
        $row = new Activity($attributes + [
            'ip_address' => Yii::$app->request->userIP,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id,
        ]);
        if (!$row->save()) throw new \RuntimeException(implode(' ', $row->getFirstErrors()));
    }
}
