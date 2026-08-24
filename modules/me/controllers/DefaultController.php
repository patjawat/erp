<?php

namespace app\modules\me\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\appreciation\models\Appreciation;
use app\modules\appreciation\models\AppreciationProgramYear;
use app\modules\appreciation\models\AppreciationRedemption;
use app\modules\appreciation\services\AppreciationPointService;
use app\modules\dms\models\Documents;
use app\modules\attendance\models\CheckinRecord;
use app\modules\helpdesk\models\HelpdeskSearch;
use app\modules\hr\models\Employees;
use app\modules\hr\models\ProbationCase;
use app\modules\hr\models\ProbationEvaluation;
use app\modules\hr\models\ProbationRound;
use app\modules\leave\models\Leave;
use app\modules\leave\models\LeaveSearch;
use app\modules\serviceProfile\models\ServiceProfile;
use app\modules\serviceProfile\services\InboxService;
use app\modules\usermanager\models\User;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Default controller for the `me` module
 */
class DefaultController extends Controller
{
    public function actionIndex()
    {
        // clear session คลัง
        foreach (['sub-warehouse', 'main-warehouse', 'asset_type'] as $key) {
            Yii::$app->session->remove($key);
        }

        $model = UserHelper::GetEmployee();

        if ($model === null) {
            return $this->render('warning');
        }

        $info = $model->getInfo();
    //  return $model;
        if (!$model->hasPersonnelPositionConfigured()) {
            return $this->render('warning');
        }
        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget(),
            'emp_id' => $model->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        //เช็คจากการตั้งค่าใน Employees ถ้าเป็น รพ.สต.
        if ($model->branch == 'BRANCH') {
            return $this->redirect(['/inventory-v2/sub-stock/dashboard']);
        }

        $todayCheckinCount = 0;
        $appreciationReceivedCount = 0;
        $appreciationStatus = ['year'=>null,'earned'=>0,'used'=>0,'balance'=>0,'levelName'=>'เริ่มต้น','levelColor'=>'#2563eb','nextLevelName'=>null,'pointsToNext'=>0,'progress'=>0,'rewardsCount'=>0];
        $unreadDocumentCount = 0;
        $probationCaseCount = 0;
        $probationTaskCount = 0;
        $serviceProfileCurrent = null;
        $serviceProfileDraft = null;
        $serviceProfileActionCount = 0;
        try {
            $empId = (int) $model->id;
            $depId = (int) ($model->department ?? 0);

            // ให้ชุดเงื่อนไขตรงกับหน้า /me/documents/show-home
            $employeeNames = "'comment_emp', 'tags', 'employee_tag', 'employee', 'req_approve'";
            $departmentNames = "'comment_dept', 'department'";

            $query = Documents::find()
                ->leftJoin(
                    ['te' => 'documents_detail'],
                    "te.document_id = documents.id AND te.name IN ({$employeeNames}) AND te.to_id = :empId",
                    [':empId' => (string) $empId]
                )
                ->leftJoin(
                    ['td' => 'documents_detail'],
                    "td.document_id = documents.id AND td.name IN ({$departmentNames}) AND td.to_id = :depId",
                    [':depId' => (string) $depId]
                )
                ->leftJoin(
                    ['tr' => 'documents_detail'],
                    'tr.document_id = documents.id AND tr.name = :readName AND tr.to_id = :empIdRead',
                    [':readName' => 'read', ':empIdRead' => (string) $empId]
                )
                ->andWhere([
                    'or',
                    ['not', ['te.id' => null]],
                    ['not', ['td.id' => null]],
                ])
                ->andWhere(['tr.id' => null]);

            $unreadDocumentCount = (int) $query->count('DISTINCT [[documents]].[[id]]');
        } catch (\Throwable $e) {
            $unreadDocumentCount = 0;
        }
        if ($model && $model->id) {
            try {
                $serviceProfileCurrent = ServiceProfile::findCurrent('department', (int) $model->department);
                $serviceProfileDraft = ServiceProfile::find()
                    ->where(['owner_type' => 'department', 'owner_id' => (int) $model->department])
                    ->andWhere(['status' => [ServiceProfile::STATUS_DRAFT, ServiceProfile::STATUS_RETURNED, ServiceProfile::STATUS_REVIEW_PENDING, ServiceProfile::STATUS_APPROVAL_PENDING, ServiceProfile::STATUS_ACKNOWLEDGEMENT_PENDING]])
                    ->orderBy(['fiscal_year' => SORT_DESC, 'revision_no' => SORT_DESC])
                    ->one();
                $serviceProfileActionCount = (new InboxService())->count($model);
            } catch (\Throwable $e) {
                $serviceProfileCurrent = null;
                $serviceProfileDraft = null;
                $serviceProfileActionCount = 0;
            }
            try {
                $probationCaseCount = (int) ProbationCase::find()->where(['or',
                    ['employee_id' => $model->id],
                    ['supervisor_employee_id' => $model->id],
                    ['group_head_employee_id' => $model->id],
                    ['director_employee_id' => $model->id],
                ])->count();
                $probationTaskCount = (int) ProbationEvaluation::find()
                    ->where(['evaluator_employee_id' => $model->id, 'status' => 'open'])
                    ->count();
                $probationTaskCount += (int) ProbationCase::find()
                    ->where(['final_recommender_employee_id' => $model->id, 'status' => 'waiting_decision'])
                    ->count();
                $probationTaskCount += (int) ProbationRound::find()->alias('r')
                    ->innerJoin(['c' => ProbationCase::tableName()], 'c.id = r.case_id')
                    ->where(['c.director_employee_id' => $model->id, 'r.status' => 'waiting_acknowledgement'])
                    ->count();
            } catch (\Throwable $e) {
                $probationCaseCount = 0;
                $probationTaskCount = 0;
            }
            try {
                $todayCheckinCount = CheckinRecord::find()
                    ->andWhere(['emp_id' => $model->id])
                    ->andWhere(['>=', 'checkin_at', date('Y-m-d 00:00:00')])
                    ->andWhere(['<=', 'checkin_at', date('Y-m-d 23:59:59')])
                    ->count();
            } catch (\Throwable $e) {
                $todayCheckinCount = 0;
            }
            try {
                $programYear=AppreciationProgramYear::active();
                $summary=AppreciationPointService::summary($model->id,$programYear);
                $appreciationReceivedQuery=Appreciation::find()->andWhere(['to_emp_id'=>$model->id]);
                if($programYear)$appreciationReceivedQuery->andWhere(['between','created_at',$programYear->start_at.' 00:00:00',$programYear->end_at.' 23:59:59']);
                $appreciationReceivedCount=(int)$appreciationReceivedQuery->count();
                $currentMin=$summary['level']?(int)$summary['level']->min_points:0;
                $nextMin=$summary['nextLevel']?(int)$summary['nextLevel']->min_points:null;
                $progress=$nextMin!==null && $nextMin>$currentMin ? max(0,min(100,round((($summary['earned']-$currentMin)/($nextMin-$currentMin))*100))) : ($summary['earned']>0?100:0);
                $rewardsCount=$programYear?(int)AppreciationRedemption::find()->where(['emp_id'=>$model->id,'program_year_id'=>$programYear->id,'status'=>[AppreciationRedemption::STATUS_APPROVED,AppreciationRedemption::STATUS_DELIVERED]])->count():0;
                $levelColor=$summary['level'] && preg_match('/^#[0-9a-fA-F]{6}$/',(string)$summary['level']->color)?$summary['level']->color:'#2563eb';
                $appreciationStatus=['year'=>$programYear,'earned'=>(int)$summary['earned'],'used'=>(int)$summary['used'],'balance'=>(int)$summary['balance'],'levelName'=>$summary['level']?$summary['level']->name:'เริ่มต้น','levelColor'=>$levelColor,'nextLevelName'=>$summary['nextLevel']?$summary['nextLevel']->name:null,'pointsToNext'=>$nextMin!==null?max(0,$nextMin-(int)$summary['earned']):0,'progress'=>$progress,'rewardsCount'=>$rewardsCount];
            } catch (\Throwable $e) {
                $appreciationReceivedCount = 0;
            }
        }

        return $this->render('index', [
            'model' => $model ? $model : new Employees(),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'todayCheckinCount' => $todayCheckinCount,
            'appreciationReceivedCount' => $appreciationReceivedCount,
            'appreciationStatus' => $appreciationStatus,
            'unreadDocumentCount' => $unreadDocumentCount,
            'probationCaseCount' => $probationCaseCount,
            'probationTaskCount' => $probationTaskCount,
            'serviceProfileCurrent' => $serviceProfileCurrent,
            'serviceProfileDraft' => $serviceProfileDraft,
            'serviceProfileActionCount' => $serviceProfileActionCount,
        ]);
    }


    public function actionWarning()
    {
        return $this->render('warning');
    }

    public function actionV2()
    {
        $model = UserHelper::GetEmployee();

        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget(),
            'emp_id' => $model->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);


        return $this->render('me_v2', [
            'model' => $model ? $model : new Employees(),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionV3()
    {
        $model = UserHelper::GetEmployee();

        $searchModel = new LeaveSearch([
            'thai_year' => AppHelper::YearBudget(),
            'emp_id' => $model->id
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);


        return $this->render('me_v3', [
            'model' => $model ? $model : new Employees(),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }




    public function actionTeam()
    {
        return $this->render('team_work', []);
    }

    /**
     * หน้าโปรไฟล์ของฉัน (/me/default/profile หรือ /me/profile ถ้ามี rule)
     */
    public function actionProfile()
    {
        $model = UserHelper::GetEmployee();
        if ($model === null) {
            return $this->render('warning');
        }
        return $this->render('profile', [
            'model' => $model,
        ]);
    }

    /**
     * ตั้งค่าบัญชีเข้าใช้งานระบบ (เปลี่ยนชื่อเข้าใช้ / รหัสผ่าน) บันทึกแล้ว redirect ไป /me/profile
     */
    public function actionAccount()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/me/profile']);
        }
        $model = User::findOne(Yii::$app->user->id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบบัญชี');
        }
        $model->password = '';
        $model->confirm_password = '';

        $post = Yii::$app->request->post('User', []);
        $newPassword = trim($post['password'] ?? '');
        $newConfirm = trim($post['confirm_password'] ?? '');

        if ($model->load(Yii::$app->request->post())) {
            if ($newPassword === '') {
                $model->password = $model->password_hash;
                $model->confirm_password = $model->password_hash;
            }
            if ($model->validate()) {
                if ($newPassword !== '') {
                    $model->setPassword($newPassword);
                }
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'บันทึกการตั้งค่าบัญชีเรียบร้อย');
                    return $this->redirect(['/me/profile']);
                }
            } else {
                if ($newPassword === '') {
                    $model->password = '';
                    $model->confirm_password = '';
                }
            }
        }

        return $this->render('account', [
            'model' => $model,
        ]);
    }

    public function actionRepairMe()
    {
        $userId = Yii::$app->user->id;
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'repair', 'created_by' => $userId]);
        $dataProvider->query->andFilterWhere(['in', 'status', [1, 2, 3]]);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'summary' => $dataProvider->getTotalCount(),
            'content' => $this->renderAjax('repair_me', [
                'dataProvider' => $dataProvider,
            ])
        ];
    }

    public function actionRepair()
    {
        $userId = Yii::$app->user->id;
        $searchModel = new HelpdeskSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andFilterWhere(['name' => 'repair', 'created_by' => $userId]);
        // $dataProvider->query->andFilterWhere(['in', 'status', [1, 2, 3]]);
        $dataProvider->pagination->pageSize = 4;
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'summary' => $dataProvider->getTotalCount(),
            'content' => $this->renderAjax('activity', [
                'dataProvider' => $dataProvider,
            ])
        ];
    }

    public function actionTest()
    {
        $model = new Leave();
        $model->emp_id = 123;   
        return $this->render('test', [
            'model' => $model,
        ]);
    }
}
