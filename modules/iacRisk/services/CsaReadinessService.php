<?php

namespace app\modules\iacRisk\services;

use app\modules\iacRisk\models\Csa;
use app\modules\iacRisk\models\CsaRisk;

class CsaReadinessService
{
    public function inspect(Csa $csa): array
    {
        $errors=[];$steps=$csa->steps;
        if(!$steps)$errors[]='ต้องมีอย่างน้อย 1 ขั้นตอนการปฏิบัติงาน';
        foreach($steps as $index=>$step){
            $label='ขั้นตอนที่ '.($index+1);
            if($step->has_risk&&!$step->risks)$errors[]=$label.' ระบุว่ามีความเสี่ยงแต่ยังไม่มีรายการความเสี่ยง';
            foreach($step->risks as $riskIndex=>$risk){
                $riskLabel=$label.' ความเสี่ยงที่ '.($riskIndex+1);
                if(!$risk->controls)$errors[]=$riskLabel.' ต้องระบุการควบคุมภายในที่มีอยู่';
                if($risk->adequacy===CsaRisk::ADEQUACY_NOT_ASSESSED)$errors[]=$riskLabel.' ต้องประเมินความเพียงพอของการควบคุม';
                if($risk->adequacy===CsaRisk::ADEQUACY_INADEQUATE){
                    if(trim((string)$risk->residual_risk)==='')$errors[]=$riskLabel.' ต้องระบุความเสี่ยงคงเหลือ';
                    if(!$risk->plans)$errors[]=$riskLabel.' ต้องมีวิธีแก้ไข ผู้รับผิดชอบ และกำหนดเสร็จ';
                }
            }
        }
        return ['ready'=>$errors===[],'errors'=>$errors,'step_count'=>count($steps),'risk_count'=>array_sum(array_map(static fn($step)=>count($step->risks),$steps))];
    }
}
