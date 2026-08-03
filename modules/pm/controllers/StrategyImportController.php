<?php

namespace app\modules\pm\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use app\modules\pm\models\{StrategyPlan,StrategyMission,StrategyIssue,StrategyGoal,StrategyIndicator,StrategyIndicatorValue,StrategySuccessFactor,StrategyMeasure,StrategyProgram,StrategyImportBatch,StrategyImportRow};
use app\modules\pm\services\StrategySpreadsheetParser;

class StrategyImportController extends Controller
{
    public function behaviors():array{return ['access'=>['class'=>AccessControl::class,'rules'=>[['allow'=>true,'roles'=>['pmStrategyManage']]]],'verbs'=>['class'=>VerbFilter::class,'actions'=>['commit'=>['POST'],'cancel'=>['POST']]]];}

    public function actionUpload(int $planId)
    {
        $plan=$this->plan($planId);
        if(Yii::$app->request->isPost){
            $file=UploadedFile::getInstanceByName('strategy_file');
            if(!$file || !in_array(strtolower($file->extension),['xlsx','xls'],true)){Yii::$app->session->setFlash('error','กรุณาเลือกไฟล์ Excel .xlsx หรือ .xls');}
            else{
                $base=tempnam(sys_get_temp_dir(),'pm_strategy_'); $temp=$base.'.'.$file->extension; @unlink($base);
                if($file->saveAs($temp)){
                    try{$parsed=(new StrategySpreadsheetParser())->parse($temp);$batch=$this->stage($plan,$file->name,$parsed);return $this->redirect(['preview','id'=>$batch->id]);}
                    catch(\Throwable $e){Yii::error($e,__METHOD__);Yii::$app->session->setFlash('error','อ่านไฟล์ไม่สำเร็จ: '.$e->getMessage());}
                    finally{if(is_file($temp))@unlink($temp);}
                }
            }
        }
        return $this->render('upload',['plan'=>$plan]);
    }

    public function actionTemplate()
    {
        $path=Yii::getAlias('@app/modules/pm/resources/pm-strategy-template-2568-2572.xlsx');
        if(!is_file($path))throw new NotFoundHttpException('ไม่พบไฟล์ Template');
        return Yii::$app->response->sendFile($path,'pm-strategy-template-2568-2572.xlsx',['inline'=>false]);
    }

    public function actionPreview(int $id){$batch=$this->batch($id);return $this->render('preview',['batch'=>$batch,'rows'=>$batch->rows]);}

    public function actionCommit(int $id)
    {
        $batch=$this->batch($id); $plan=$batch->plan;
        if(!$plan->isEditable()||$batch->status!=='staged')throw new \yii\web\ForbiddenHttpException('ชุดข้อมูลนี้ไม่สามารถนำเข้าได้');
        try{(new \app\modules\pm\services\StrategyImportCommitService())->commit($batch);Yii::$app->session->setFlash('success','นำเข้าข้อมูลยุทธศาสตร์เรียบร้อยแล้ว');return $this->redirect(['/pm/strategy-plan/view','id'=>$plan->id]);}
        catch(\Throwable $e){Yii::error($e,__METHOD__);Yii::$app->session->setFlash('error','นำเข้าไม่สำเร็จ: '.$e->getMessage());return $this->redirect(['preview','id'=>$id]);}
    }

    public function actionCancel(int $id){$batch=$this->batch($id);if($batch->status==='staged'){$batch->status='cancelled';$batch->save(false,['status','updated_at','updated_by']);}return $this->redirect(['/pm/strategy-plan/view','id'=>$batch->plan_id]);}

    private function stage(StrategyPlan $plan,string $name,array $parsed):StrategyImportBatch
    {
        $tx=Yii::$app->db->beginTransaction();$batch=new StrategyImportBatch(['plan_id'=>$plan->id,'original_name'=>$name,'status'=>'staged','total_rows'=>count($parsed['rows']),'valid_rows'=>count(array_filter($parsed['rows'],fn($r)=>$r['status']==='valid')),'error_rows'=>count(array_filter($parsed['rows'],fn($r)=>$r['status']!=='valid')),'summary_json'=>$parsed['summary']]);$batch->save(false);
        foreach($parsed['rows'] as $r){$row=new StrategyImportRow(['batch_id'=>$batch->id,'sheet_name'=>$r['sheet_name'],'row_no'=>$r['row_no'],'status'=>$r['status'],'payload_json'=>$r['payload'],'errors_json'=>$r['errors']]);$row->save(false);}$tx->commit();return $batch;
    }

    private function commitRow(StrategyPlan $plan,StrategyImportRow $row,array &$ctx):void
    {
        $p=$row->payload();$mk=$p['mission'];$ik=$p['issue'];$gk=$p['goal'];
        $mission=StrategyMission::findOne(['plan_id'=>$plan->id,'code'=>$mk['code']])?:new StrategyMission(['plan_id'=>$plan->id,'code'=>$mk['code']?:'M'.(count($plan->missions)+1),'name'=>$mk['name'],'is_active'=>1]);if($mission->isNewRecord)$mission->save(false);
        $issue=StrategyIssue::findOne(['mission_id'=>$mission->id,'code'=>$ik['code']])?:new StrategyIssue(['mission_id'=>$mission->id,'code'=>$ik['code']?:$mission->code.'.S1','name'=>$ik['name'],'is_active'=>1]);if($issue->isNewRecord)$issue->save(false);
        $goalCode=$gk['code']?:$issue->code.'.G0';$goalName=$gk['name']?:'ยังไม่ระบุเป้าประสงค์ในไฟล์ต้นฉบับ';
        $goalKey=$issue->id.'|'.$goalCode;if(!isset($ctx['goals'][$goalKey])){$goal=StrategyGoal::findOne(['issue_id'=>$issue->id,'code'=>$goalCode])?:new StrategyGoal(['issue_id'=>$issue->id,'code'=>$goalCode,'name'=>$goalName,'is_active'=>1]);if($goal->isNewRecord)$goal->save(false);$ctx['goals'][$goalKey]=$goal;}$goal=$ctx['goals'][$goalKey];
        $indicator=null;$code=trim($p['indicator']['code']);if($code!==''){$indicator=StrategyIndicator::findOne(['plan_id'=>$plan->id,'code'=>$code])?:new StrategyIndicator(['plan_id'=>$plan->id,'goal_id'=>$goal->id,'code'=>$code,'name'=>$p['indicator']['name'],'level'=>'hospital','is_active'=>1]);if($indicator->isNewRecord)$indicator->save(false);$ctx['indicator']=$indicator;}
        elseif(isset($ctx['indicator']))$indicator=$ctx['indicator'];
        if(trim($p['rca'])!=='')foreach(preg_split('/\r?\n/u',$p['rca']) as $text)if(trim($text)!==''){$f=new StrategySuccessFactor(['goal_id'=>$goal->id,'name'=>trim($text),'factor_type'=>'rca','is_active'=>1]);$f->save(false);}
        if(trim($p['secondary'])!==''){$parts=preg_split('/\s+/',trim($p['secondary']),2);$secCode=$parts[0];if(str_starts_with($secCode,'HOS.')){$sec=StrategyIndicator::findOne(['plan_id'=>$plan->id,'code'=>$secCode])?:new StrategyIndicator(['plan_id'=>$plan->id,'goal_id'=>$goal->id,'parent_id'=>$indicator?->id,'code'=>$secCode,'name'=>$parts[1]??$p['secondary'],'level'=>'hospital','is_active'=>1]);if($sec->isNewRecord)$sec->save(false);}}
        foreach($p['annual'] as $year=>$annual){$measure=null;if(trim($annual['measure'])!==''){$measure=new StrategyMeasure(['goal_id'=>$goal->id,'fiscal_year'=>(int)$year,'name'=>$annual['measure'],'is_active'=>1]);$measure->save(false);}if(trim($annual['program'])!==''){$program=new StrategyProgram(['plan_id'=>$plan->id,'measure_id'=>$measure?->id,'fiscal_year'=>(int)$year,'name'=>$annual['program'],'owner_text'=>$annual['owner']?:null,'is_active'=>1]);$program->save(false);}}
        if($indicator){foreach($p['values'] as $year=>$value){if($year==='baseline')continue;if($value['target']===null&&$value['actual']===null)continue;$v=StrategyIndicatorValue::findOne(['indicator_id'=>$indicator->id,'fiscal_year'=>(int)$year])?:new StrategyIndicatorValue(['indicator_id'=>$indicator->id,'fiscal_year'=>(int)$year]);$v->baseline_value=$p['values']['baseline'];$v->target_value=$value['target'];$v->actual_value=$value['actual'];$v->save(false);}}
    }
    private function plan(int $id):StrategyPlan{return StrategyPlan::findOne($id)?:throw new NotFoundHttpException('ไม่พบชุดแผน');}
    private function batch(int $id):StrategyImportBatch{return StrategyImportBatch::findOne($id)?:throw new NotFoundHttpException('ไม่พบชุดนำเข้า');}
}
