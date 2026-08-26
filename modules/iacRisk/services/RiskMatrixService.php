<?php

namespace app\modules\iacRisk\services;

class RiskMatrixService
{
    public static function levelOptions(): array
    {
        return ['low'=>'ต่ำ','moderate'=>'ปานกลาง','high'=>'สูง','very_high'=>'สูงมาก'];
    }

    public static function evaluate(?int $likelihood,?int $impact): ?array
    {
        if(!$likelihood||!$impact||$likelihood<1||$likelihood>5||$impact<1||$impact>5)return null;
        $score=$likelihood*$impact;
        if($score<=3)return ['score'=>$score,'code'=>'low','label'=>'ต่ำ','badge'=>'bg-success-subtle text-success-emphasis'];
        if($score<=9)return ['score'=>$score,'code'=>'moderate','label'=>'ปานกลาง','badge'=>'bg-warning-subtle text-warning-emphasis'];
        if($score<=16)return ['score'=>$score,'code'=>'high','label'=>'สูง','badge'=>'bg-warning text-dark'];
        return ['score'=>$score,'code'=>'very_high','label'=>'สูงมาก','badge'=>'bg-danger text-white'];
    }
}
