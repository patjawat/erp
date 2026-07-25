<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use Yii;

final class DocumentNumberService
{
    public function temporary(string $prefix): string
    {
        return sprintf('%s-%s-%s', $prefix, date('Ymd'), strtoupper(substr(Yii::$app->security->generateRandomString(8), 0, 8)));
    }

    public function receiptNumber(int $id, ?int $year = null): string
    {
        $buddhistYear = ($year ?: (int)date('Y')) + 543;
        return sprintf('HR-%d-%06d', $buddhistYear, $id);
    }
}
