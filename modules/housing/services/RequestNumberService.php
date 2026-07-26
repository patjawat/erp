<?php

declare(strict_types=1);

namespace app\modules\housing\services;

use app\modules\housing\models\HousingRequest;

final class RequestNumberService
{
    public function next(string $prefix = 'HOM'): string
    {
        $thaiYear = (int)date('Y') + 543;
        $year = substr((string)$thaiYear, -2);
        $numberPrefix = $prefix . '-' . $year . '-';
        $last = HousingRequest::find()
            ->select('request_no')
            ->where(['like', 'request_no', $numberPrefix . '%', false])
            ->orderBy(['id' => SORT_DESC])
            ->scalar();
        $sequence = $last ? ((int)substr((string)$last, -4) + 1) : 1;

        return $numberPrefix . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }
}
