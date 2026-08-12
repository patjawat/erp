<?php

namespace app\modules\pm\services;

use Yii;
use app\modules\pm\models\{StrategyImportBatch,StrategyGoal,StrategyIndicator,StrategyIndicatorYear,StrategyIssue,StrategyMeasure,StrategyMission,StrategyProgram,StrategySuccessFactor};

class StrategyImportCommitService
{
    public function commit(StrategyImportBatch $batch): array
    {
        if($batch->status!=='staged') throw new \DomainException('ชุดนำเข้านี้ถูกดำเนินการแล้ว');
        if(!$batch->plan->isEditable()) throw new \DomainException('ชุดแผนไม่ได้อยู่ในสถานะฉบับร่าง');
        $tx=Yii::$app->db->beginTransaction(); $context=[]; $count=0;
        try{
            foreach($batch->rows as $row){$this->commitRow($batch,$row,$context);$row->status='imported';$row->save(false,['status']);$count++;}
            $batch->status='imported';$batch->save(false,['status','updated_at','updated_by']);$tx->commit();
            return ['rows'=>$count,'goals'=>count($context['goals']??[])];
        }catch(\Throwable $e){$tx->rollBack();throw $e;}
    }

    private function commitRow(StrategyImportBatch $batch,$row,array &$ctx):void
    {
        $plan=$batch->plan;$p=$row->payload();$mk=$p['mission'];$ik=$p['issue'];$gk=$p['goal'];
        $mission=StrategyMission::findOne(['plan_id'=>$plan->id,'code'=>$mk['code']])?:new StrategyMission(['plan_id'=>$plan->id,'code'=>$mk['code']?:'M'.(count($plan->missions)+1),'name'=>$mk['name'],'is_active'=>1]);if($mission->isNewRecord)$mission->save(false);
        $issue=StrategyIssue::findOne(['mission_id'=>$mission->id,'code'=>$ik['code']])?:new StrategyIssue(['mission_id'=>$mission->id,'code'=>$ik['code']?:$mission->code.'.S1','name'=>$ik['name'],'is_active'=>1]);if($issue->isNewRecord)$issue->save(false);
        $goalCode=$gk['code']?:$issue->code.'.G0';$goalName=$gk['name']?:'ยังไม่ระบุเป้าประสงค์ในไฟล์ต้นฉบับ';$goalKey=$issue->id.'|'.$goalCode;
        if(!isset($ctx['goals'][$goalKey])){$goal=StrategyGoal::findOne(['issue_id'=>$issue->id,'code'=>$goalCode])?:new StrategyGoal(['issue_id'=>$issue->id,'code'=>$goalCode,'name'=>$goalName,'is_active'=>1]);if($goal->isNewRecord)$goal->save(false);$ctx['goals'][$goalKey]=$goal;}$goal=$ctx['goals'][$goalKey];
        $indicator=null;$code=trim($p['indicator']['code']);if($code!==''){$indicator=StrategyIndicator::findOne(['plan_id'=>$plan->id,'code'=>$code])?:new StrategyIndicator(['plan_id'=>$plan->id,'goal_id'=>$goal->id,'code'=>$code,'name'=>$p['indicator']['name'],'level'=>'hospital','is_active'=>1]);if($indicator->isNewRecord)$indicator->save(false);$ctx['indicator']=$indicator;}elseif(isset($ctx['indicator']))$indicator=$ctx['indicator'];
        if(trim($p['rca'])!=='')foreach(preg_split('/\r?\n/u',$p['rca']) as $text)if(trim($text)!==''){$f=new StrategySuccessFactor(['goal_id'=>$goal->id,'name'=>trim($text),'factor_type'=>'rca','is_active'=>1]);$f->save(false);}
        if(trim($p['secondary'])!==''&&preg_match('/^(HOS\.\d+(?:\.\d+)+)\s*[-–:]?\s*(.*)$/us',trim($p['secondary']),$match)){$secCode=$match[1];$secName=trim($match[2])?:trim($p['secondary']);$sec=StrategyIndicator::findOne(['plan_id'=>$plan->id,'code'=>$secCode])?:new StrategyIndicator(['plan_id'=>$plan->id,'goal_id'=>$goal->id,'parent_id'=>$indicator?->id,'code'=>$secCode,'name'=>$secName,'level'=>'hospital','is_active'=>1]);if($sec->isNewRecord)$sec->save(false);}
        foreach($p['annual'] as $year=>$annual){$measure=null;if(trim($annual['measure'])!==''){$measure=new StrategyMeasure(['goal_id'=>$goal->id,'fiscal_year'=>(int)$year,'name'=>$annual['measure'],'is_active'=>1]);$measure->save(false);}if(trim($annual['program'])!==''){$program=new StrategyProgram(['plan_id'=>$plan->id,'measure_id'=>$measure?->id,'fiscal_year'=>(int)$year,'name'=>$annual['program'],'owner_text'=>$annual['owner']?:null,'is_active'=>1]);$program->save(false);}}
        if($indicator)foreach($p['values'] as $year=>$value){if($year==='baseline'||($value['target']===null&&$value['actual']===null))continue;$v=StrategyIndicatorYear::findOne(['indicator_id'=>$indicator->id,'fiscal_year'=>(int)$year])?:new StrategyIndicatorYear(['indicator_id'=>$indicator->id,'fiscal_year'=>(int)$year]);$v->baseline_value=$p['values']['baseline'];$v->target_value=$value['target'];$v->actual_value=$value['actual'];$v->save(false);}
    }
}
