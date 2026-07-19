<?php
namespace app\modules\appreciation\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\db\Query;
use app\modules\appreciation\models\AppreciationChallenge;
use app\modules\appreciation\models\AppreciationLevel;
use app\modules\appreciation\models\AppreciationProgramYear;
use app\modules\appreciation\models\AppreciationRedemption;
use app\modules\appreciation\models\AppreciationReward;
use app\modules\appreciation\models\AppreciationValue;
use app\modules\appreciation\models\AppreciationActivity;
use app\modules\appreciation\models\AppreciationParticipation;
use app\modules\appreciation\models\Appreciation;
use app\modules\hr\models\Employees;
use app\components\AppHelper;

class AdminController extends Controller
{
    public function behaviors() { return ['access' => ['class' => AccessControl::class, 'rules' => [['allow'=>true, 'roles'=>['admin','hr']]]], 'verbs'=>['class'=>VerbFilter::class,'actions'=>['save'=>['post'],'redemption'=>['post'],'participation'=>['post']]]]; }
    public function actionIndex()
    {
        $editType=(string)Yii::$app->request->get('edit_type',''); $editId=(int)Yii::$app->request->get('edit_id',0); $editModel=null;
        $modelClasses=$this->modelClasses();
        if($editId && isset($modelClasses[$editType]))$editModel=$modelClasses[$editType]::findOne($editId);
        $activeYear=AppreciationProgramYear::active();
        $participations=AppreciationParticipation::find()->with('activity')->orderBy(['registered_at'=>SORT_DESC])->limit(100)->all();
        $empIds=array_values(array_unique(array_merge(array_column($participations,'emp_id'),array_column(AppreciationRedemption::find()->select('emp_id')->asArray()->all(),'emp_id'))));
        $employees=$empIds?Employees::find()->where(['id'=>$empIds])->indexBy('id')->all():[];
        $dashboard=['activities'=>0,'participants'=>0,'completed'=>0,'points'=>0,'participationRate'=>0];
        $scoreRows=[];
        $valueColumns=[];
        $analyticsRows=[];
        $participantAnalytics=['total'=>0,'departments'=>[],'positions'=>[],'ages'=>[],'statuses'=>[]];
        if($activeYear){
            $dashboard['activities']=(int)AppreciationActivity::find()->where(['program_year_id'=>$activeYear->id])->count();
            $dashboard['participants']=(int)AppreciationParticipation::find()->where(['program_year_id'=>$activeYear->id])->select('emp_id')->distinct()->count();
            $dashboard['completed']=(int)AppreciationParticipation::find()->where(['program_year_id'=>$activeYear->id,'status'=>AppreciationParticipation::STATUS_COMPLETED])->count();
            $dashboard['points']=(int)AppreciationParticipation::find()->where(['program_year_id'=>$activeYear->id,'status'=>AppreciationParticipation::STATUS_COMPLETED])->sum('points_awarded');
            $employeeTotal=(int)Employees::find()->count(); $dashboard['participationRate']=$employeeTotal?round($dashboard['participants']*100/$employeeTotal,1):0;
            $thanks=Appreciation::find()->select(['to_emp_id','points'=>'SUM(points_given)'])->where(['between','created_at',$activeYear->start_at.' 00:00:00',$activeYear->end_at.' 23:59:59'])->groupBy('to_emp_id')->asArray()->all();
            $activityScores=AppreciationParticipation::find()->select(['emp_id','points'=>'SUM(points_awarded)'])->where(['program_year_id'=>$activeYear->id,'status'=>AppreciationParticipation::STATUS_COMPLETED])->groupBy('emp_id')->asArray()->all();
            foreach($thanks as $row){$id=(int)$row['to_emp_id'];$scoreRows[$id]=['emp_id'=>$id,'thanks'=>(int)$row['points'],'activities'=>0];}
            foreach($activityScores as $row){$id=(int)$row['emp_id'];if(!isset($scoreRows[$id]))$scoreRows[$id]=['emp_id'=>$id,'thanks'=>0,'activities'=>0];$scoreRows[$id]['activities']=(int)$row['points'];}
            foreach($scoreRows as &$row)$row['total']=$row['thanks']+$row['activities']; unset($row); usort($scoreRows,fn($a,$b)=>$b['total']<=>$a['total']);
            $scoreEmpIds=array_column($scoreRows,'emp_id'); if($scoreEmpIds)$employees+=Employees::find()->where(['id'=>$scoreEmpIds])->indexBy('id')->all();

            $valueDefinitions=AppreciationValue::find()->orderBy(['sort_order'=>SORT_ASC,'name'=>SORT_ASC])->all();
            $valueMap=[];
            foreach($valueDefinitions as $value){
                $coreCode=$value->core_value_code ?: $value->code;
                $coreName=$value->core_value_name ?: $value->name;
                $valueMap[$value->code]=['code'=>$coreCode,'name'=>$coreName];
                if(!isset($valueColumns[$coreCode]))$valueColumns[$coreCode]=['code'=>$coreCode,'name'=>$coreName];
            }

            $valueScores=Appreciation::find()->select([
                'to_emp_id','badge_type','core_value_code_snapshot','core_value_name_snapshot',
                'department_name_snapshot'=>'MAX(department_name_snapshot)',
                'position_name_snapshot'=>'MAX(position_name_snapshot)',
                'position_group_name_snapshot'=>'MAX(position_group_name_snapshot)',
                'age_band_snapshot'=>'MAX(age_band_snapshot)',
                'points'=>'SUM(points_given)','thanks_count'=>'COUNT(*)',
            ])->where(['between','created_at',$activeYear->start_at.' 00:00:00',$activeYear->end_at.' 23:59:59'])
              ->groupBy(['to_emp_id','badge_type','core_value_code_snapshot','core_value_name_snapshot'])->asArray()->all();

            foreach($valueScores as $row){
                $empId=(int)$row['to_emp_id'];
                $mapped=$valueMap[$row['badge_type']]??null;
                $code=$row['core_value_code_snapshot'] ?: ($mapped['code']??$row['badge_type']?:'other');
                $name=$row['core_value_name_snapshot'] ?: ($mapped['name']??$row['badge_type']?:'อื่น ๆ');
                if(!isset($valueColumns[$code]))$valueColumns[$code]=['code'=>$code,'name'=>$name];
                if(!isset($analyticsRows[$empId]))$analyticsRows[$empId]=[
                    'emp_id'=>$empId,'values'=>[],'thanks'=>0,'thanks_count'=>0,'activities'=>0,'total'=>0,
                    'department_name'=>$row['department_name_snapshot'],'position_name'=>$row['position_name_snapshot'],
                    'position_group_name'=>$row['position_group_name_snapshot'],'age_band'=>$row['age_band_snapshot'],
                ];
                $analyticsRows[$empId]['values'][$code]=($analyticsRows[$empId]['values'][$code]??0)+(int)$row['points'];
                $analyticsRows[$empId]['thanks']+=(int)$row['points'];
                $analyticsRows[$empId]['thanks_count']+=(int)$row['thanks_count'];
            }

            foreach($activityScores as $row){
                $empId=(int)$row['emp_id'];
                if(!isset($analyticsRows[$empId]))$analyticsRows[$empId]=['emp_id'=>$empId,'values'=>[],'thanks'=>0,'thanks_count'=>0,'activities'=>0,'total'=>0,'department_name'=>null,'position_name'=>null,'position_group_name'=>null,'age_band'=>null];
                $analyticsRows[$empId]['activities']=(int)$row['points'];
            }
            foreach($analyticsRows as &$row)$row['total']=$row['thanks']+$row['activities']; unset($row);
            usort($analyticsRows,fn($a,$b)=>$b['total']<=>$a['total']);
            $analyticsEmpIds=array_column($analyticsRows,'emp_id');
            if($analyticsEmpIds)$employees+=Employees::find()->where(['id'=>$analyticsEmpIds])->indexBy('id')->all();

            $participationTable=AppreciationParticipation::tableName();
            $participantAnalytics['total']=(int)AppreciationParticipation::find()->where(['program_year_id'=>$activeYear->id])->select('emp_id')->distinct()->count();
            foreach(['departments'=>'department_name_snapshot','positions'=>'position_group_name_snapshot','ages'=>'age_band_snapshot','statuses'=>'status'] as $key=>$column){
                $participantAnalytics[$key]=(new Query())->select(['label'=>$column,'count'=>'COUNT(DISTINCT emp_id)'])
                    ->from($participationTable)->where(['program_year_id'=>$activeYear->id])->groupBy($column)->orderBy(['count'=>SORT_DESC])->all();
            }
        }
        return $this->render('index', [
            'years'=>AppreciationProgramYear::find()->orderBy(['year'=>SORT_DESC])->all(),
            'values'=>AppreciationValue::find()->orderBy(['sort_order'=>SORT_ASC])->all(),
            'levels'=>AppreciationLevel::find()->with('programYear')->orderBy(['program_year_id'=>SORT_DESC,'min_points'=>SORT_ASC])->all(),
            'rewards'=>AppreciationReward::find()->with('programYear')->orderBy(['program_year_id'=>SORT_DESC,'points_cost'=>SORT_ASC])->all(),
            'challenges'=>AppreciationChallenge::find()->orderBy(['start_at'=>SORT_DESC])->all(),
            'redemptions'=>AppreciationRedemption::find()->with('reward')->orderBy(['requested_at'=>SORT_DESC])->limit(100)->all(),
            'activities'=>AppreciationActivity::find()->with('programYear')->orderBy(['start_at'=>SORT_DESC])->all(),
            'participations'=>$participations, 'employees'=>$employees, 'activeYear'=>$activeYear, 'dashboard'=>$dashboard,
            'scoreRows'=>$scoreRows, 'editType'=>$editType, 'editModel'=>$editModel,
            'valueColumns'=>array_values($valueColumns), 'analyticsRows'=>$analyticsRows, 'participantAnalytics'=>$participantAnalytics,
        ]);
    }
    public function actionSave($type, $id = null)
    {
        $classes=$this->modelClasses();
        if (!isset($classes[$type])) throw new NotFoundHttpException();
        $class=$classes[$type]; $model=$id ? $class::findOne((int)$id) : new $class();
        if (!$model) throw new NotFoundHttpException();
        $loaded=$model->load(Yii::$app->request->post());
        if($loaded && in_array($type,['year','challenge','activity'],true)){
            foreach(['start_at','end_at'] as $attribute){
                $converted=AppHelper::convertToGregorian($model->$attribute);
                if($converted)$model->$attribute=$type==='activity'?$converted.($attribute==='start_at'?' 00:00:00':' 23:59:59'):$converted;
            }
        }
        $savedImagePath=null;
        try {
            if($loaded && in_array($type,['activity','reward'],true)){
                $model->imageFile=UploadedFile::getInstance($model,'imageFile');
            }
            $valid=$loaded && $model->validate();
            if($valid && in_array($type,['activity','reward'],true) && $model->imageFile){
                $uploadDir=Yii::getAlias('@webroot/uploads/appreciation/'.$type);
                FileHelper::createDirectory($uploadDir,0755,true);
                $fileName=Yii::$app->security->generateRandomString(24).'.'.strtolower($model->imageFile->extension);
                $savedImagePath=$uploadDir.DIRECTORY_SEPARATOR.$fileName;
                if($model->imageFile->saveAs($savedImagePath)){
                    $model->image_url='/uploads/appreciation/'.$type.'/'.$fileName;
                }else{
                    $model->addError('imageFile','ไม่สามารถบันทึกภาพได้ กรุณาลองใหม่');
                    $valid=false;
                }
            }
            if ($valid && $model->save(false)) {
                Yii::$app->session->setFlash('success','บันทึกการตั้งค่าแล้ว');
            } else {
                if($savedImagePath && is_file($savedImagePath))@unlink($savedImagePath);
                $message = $model->getFirstErrors() ? implode(' ', $model->getFirstErrors()) : 'กรุณาตรวจสอบข้อมูลให้ครบถ้วน';
                Yii::$app->session->setFlash('error','บันทึกไม่สำเร็จ: ' . $message);
            }
        } catch (\Throwable $e) {
            if($savedImagePath && is_file($savedImagePath))@unlink($savedImagePath);
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error','บันทึกไม่สำเร็จ ระบบได้บันทึกรายละเอียดข้อผิดพลาดไว้แล้ว');
        }
        $tabs=['year'=>'years','value'=>'values','level'=>'levels','reward'=>'rewards','challenge'=>'challenges','activity'=>'activities'];
        return $this->redirect(['index','#'=>$tabs[$type]??'years']);
    }
    public function actionRedemption($id, $status)
    {
        $model=AppreciationRedemption::findOne((int)$id);
        if (!$model || !isset(AppreciationRedemption::statusLabels()[$status])) throw new NotFoundHttpException();
        $old=$model->status; $model->status=$status; $model->processed_at=date('Y-m-d H:i:s'); $model->processed_by=Yii::$app->user->id;
        if ($model->save() && $status===AppreciationRedemption::STATUS_REJECTED && $old!==AppreciationRedemption::STATUS_REJECTED) AppreciationReward::updateAllCounters(['stock_qty'=>1], ['id'=>$model->reward_id]);
        Yii::$app->session->setFlash('success','อัปเดตสถานะคำขอแล้ว'); return $this->redirect(['index','#'=>'redemptions']);
    }
    public function actionParticipation($id,$status)
    {
        $model=AppreciationParticipation::findOne((int)$id);
        if(!$model || !in_array($status,[AppreciationParticipation::STATUS_COMPLETED,AppreciationParticipation::STATUS_REJECTED],true))throw new NotFoundHttpException();
        $model->status=$status; $model->reviewed_at=date('Y-m-d H:i:s'); $model->reviewed_by=Yii::$app->user->id;
        if($status===AppreciationParticipation::STATUS_COMPLETED){$model->points_awarded=$model->activity?$model->activity->points:0; $model->completed_at=date('Y-m-d H:i:s');} else {$model->points_awarded=0;}
        $model->save(); Yii::$app->session->setFlash('success','อัปเดตผลการเข้าร่วมแล้ว'); return $this->redirect(['index','#'=>'participations']);
    }
    private function modelClasses(){ return ['year'=>AppreciationProgramYear::class,'value'=>AppreciationValue::class,'level'=>AppreciationLevel::class,'reward'=>AppreciationReward::class,'challenge'=>AppreciationChallenge::class,'activity'=>AppreciationActivity::class]; }
}
