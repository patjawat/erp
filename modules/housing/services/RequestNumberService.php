<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use Yii;

final class RequestNumberService
{
    public function next(string $prefix = 'HRQ'): string
    {
        return sprintf(
            '%s-%s-%s',
            $prefix,
            date('Ymd'),
            strtoupper(substr(Yii::$app->security->generateRandomString(8), 0, 8))
        );
    }
}
