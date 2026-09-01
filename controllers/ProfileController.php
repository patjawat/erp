<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;
use app\models\Amphure;
use app\models\District;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use yii\bootstrap5\ActiveForm;
use app\modules\hr\models\Employees;
use app\modules\usermanager\models\User;
use app\modules\hr\models\EmployeeDetail;
use app\modules\hr\models\EmployeeDetailSearch;
use app\modules\hr\models\EmployeeTrainingPlan;
use app\modules\hr\models\IdpCycle;
use app\modules\hr\models\IdpPlan;
use app\modules\hr\models\ProbationCase;
use app\modules\housing\services\HousingContextService;
use app\modules\housing\models\Building;
use app\modules\housing\models\Unit;
use app\modules\housing\models\Handover;
use app\modules\housing\models\Occupancy;
use app\modules\finance\services\PayrollPeriodService;
use app\modules\finance\services\PayrollRunService;
use app\components\SiteHelper;
use yii\db\Query;
use yii\web\ForbiddenHttpException;

class ProfileController extends \yii\web\Controller

{

        //เชื่อม line กับ user ที่ลงทะเบียนไว้แล้ว
        public function actionLineConnect(){

            if (Yii::$app->request->post()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $lineID =  Yii::$app->request->post('line_id');
                $user = User::findOne(Yii::$app->user->id);
                if(!$user){
                    return [
                        'status' => 'error'
                    ];
                }
                if($user){
                    $user->line_id = $lineID;
                    $user->save(false);
                    return [
                        'status' => 'success'
                    ];
                }
                
            }
            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                'title' => 'title',
                'content' => $this->renderAjax('line_connect')
            ];
        }else{
            return $this->render('line_connect');
        }
    
        }
        
    /**
     * เชื่อมบัญชี Telegram เพื่อรับแจ้งเตือนงาน
     *
     * เดิมผูกได้ทางเดียวคือเปิด Mini App จากในแอป Telegram ซึ่งคนส่วนใหญ่ไม่รู้วิธี
     * หน้านี้ให้ลิงก์เชิญกดจากมือถือได้เลย หรือสแกน QR ถ้านั่งอยู่หน้าคอม
     */
    public function actionTelegramConnect()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/auth/login']);
        }

        $user = User::findOne((int) Yii::$app->user->id);
        $token = \app\modules\telegrambot\services\TelegramLinkService::issueToken((int) Yii::$app->user->id);

        $params = [
            'user' => $user,
            'linked' => $user && trim((string) $user->telegram_id) !== '',
            'deepLink' => $token ? \app\modules\telegrambot\services\TelegramLinkService::deepLink($token) : null,
            'botUsername' => \app\modules\telegrambot\services\TelegramLinkService::botUsername(),
            'ttlMinutes' => (int) ceil(\app\modules\telegrambot\services\TelegramLinkService::TOKEN_TTL / 60),
        ];

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'เชื่อมต่อ Telegram',
                'content' => $this->renderAjax('telegram_connect', $params),
            ];
        }
        return $this->render('telegram_connect', $params);
    }

    /** ให้หน้าเชื่อมต่อถามสถานะเป็นระยะ จะได้รู้ทันทีที่ผูกสำเร็จโดยไม่ต้องรีเฟรช */
    public function actionTelegramStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            return ['linked' => false];
        }
        $user = User::findOne((int) Yii::$app->user->id);
        return ['linked' => $user && trim((string) $user->telegram_id) !== ''];
    }

    public function actionTelegramUnlink()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest || !$this->request->isPost) {
            return ['status' => 'error', 'message' => 'ไม่สามารถดำเนินการได้'];
        }
        $ok = \app\modules\telegrambot\services\TelegramLinkService::unbind((int) Yii::$app->user->id);
        return [
            'status' => $ok ? 'success' : 'error',
            'message' => $ok ? 'ยกเลิกการเชื่อมต่อแล้ว' : 'ยกเลิกไม่สำเร็จ',
        ];
    }

    public function actionIndex()
    {
        $name = $this->request->get('name');
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($model && in_array($name, ['health', 'occupational_health'], true)) {
            return $this->redirect([
                '/health/health-screen/index',
                'HealthScreenSearch[emp_id]' => (int) $model->id,
            ]);
        }
        $trainingPlans = [];
        $idpCycle = null;
        $idpPlan = null;
        $probationCases = [];
        $probationActionCount = 0;
        $dataProvider = null;
        $housingContext = null;
        $housingVacancies = [];
        $payrollRows = [];
        $housingActionCount = $model ? (int)Handover::find()
            ->joinWith('occupancy')
            ->where([
                'housing_occupancy.emp_id' => $model->id,
                'housing_handover.status' => Handover::STATUS_DRAFT,
            ])
            ->andWhere(['is not', 'housing_handover.handed_over_signed_at', null])
            ->andWhere(['housing_handover.received_signed_at' => null])
            ->count() : 0;
        if ($model && $name === 'payroll') {
            $payrollRows = (new Query())->select(['pe.*', 'p.period_code', 'p.period_type', 'p.date_start', 'p.date_end', 'p.pay_date'])
                ->from(['pe' => '{{%payroll_period_employee}}'])
                ->innerJoin(['p' => '{{%payroll_period}}'], 'p.id = pe.payroll_period_id')
                ->where(['pe.employee_id' => (int) $model->id, 'p.status' => 'calculated'])
                ->orderBy(['p.period_code' => SORT_DESC, 'p.period_type' => SORT_ASC])->all();
            foreach ($payrollRows as &$payrollRow) {
                $payrollRow['employee_snapshot'] = PayrollPeriodService::decodeSnapshot($payrollRow['employee_snapshot']);
            }
            unset($payrollRow);
        } elseif ($model && $name === 'training_roadmap') {
            $trainingPlans = EmployeeTrainingPlan::find()
                ->where(['emp_id' => $model->id])
                ->with(['roadmap.phases.activities', 'results'])
                ->orderBy(['id' => SORT_DESC])
                ->all();
        } elseif ($model && $name === 'idp') {
            $idpCycle = IdpCycle::current();
            $idpPlan = $idpCycle
                ? IdpPlan::find()->where(['cycle_id' => $idpCycle->id, 'emp_id' => $model->id])
                    ->with(['cycle', 'employee', 'supervisor', 'goals.activities'])->one()
                : null;
        } elseif ($model && $name === 'housing') {
            $housingContext = (new HousingContextService())->forUser((int)Yii::$app->user->id, [
                'tab' => $this->request->get('housing_tab', 'overview'),
                'expenseYear' => $this->request->get('expense_year'),
                'maintenanceStatus' => $this->request->get('maintenance_status', 'all'),
                'maintenanceYear' => $this->request->get('maintenance_year'),
            ]);
            if ($housingContext['mode'] === 'applicant') {
                $buildings = Building::find()
                    ->with(['units.floor', 'units.rooms'])
                    ->where(['housing_building.status' => Building::STATUS_ACTIVE])
                    ->orderBy(['housing_building.sort_order' => SORT_ASC, 'housing_building.name' => SORT_ASC])
                    ->all();
                foreach ($buildings as $building) {
                    if ($building->building_type === Building::TYPE_HOUSE && !$building->units) {
                        $unit = null;
                        $room = null;
                        $housingVacancies[] = compact('building', 'unit', 'room');
                        continue;
                    }
                    foreach ($building->units as $unit) {
                        if ($unit->rooms) {
                            foreach ($unit->rooms as $room) {
                                if ($room->status === Unit::STATUS_VACANT) {
                                    $housingVacancies[] = compact('building', 'unit', 'room');
                                }
                            }
                        } elseif ($unit->status === Unit::STATUS_VACANT) {
                            $room = null;
                            $housingVacancies[] = compact('building', 'unit', 'room');
                        }
                    }
                }
            }
        } elseif ($model && $name === 'performance_appraisal') {
            $probationCases = ProbationCase::find()
                ->with(['employee', 'template', 'rounds.evaluations.evaluator', 'rounds.acknowledgement', 'decision', 'acknowledgement'])
                ->where(['or',
                    ['employee_id' => $model->id],
                    ['supervisor_employee_id' => $model->id],
                    ['group_head_employee_id' => $model->id],
                    ['director_employee_id' => $model->id],
                ])
                ->orderBy(['updated_at' => SORT_DESC, 'id' => SORT_DESC])
                ->all();
            foreach ($probationCases as $case) {
                foreach ($case->rounds as $round) foreach ($round->evaluations as $evaluation) {
                    if ((int)$evaluation->evaluator_employee_id === (int)$model->id && $evaluation->status === 'open') $probationActionCount++;
                }
                if ((int)$case->final_recommender_employee_id === (int)$model->id && $case->status === 'waiting_decision') $probationActionCount++;
                foreach ($case->rounds as $round) {
                    if ((int)$case->director_employee_id === (int)$model->id && $round->status === 'waiting_acknowledgement') $probationActionCount++;
                }
            }
        } elseif ($model && $name) {
            $searchModel = new EmployeeDetailSearch();
            $dataProvider = $searchModel->search($this->request->queryParams);
            $dataProvider->query
                ->where(['emp_id' => $model->id, 'name' => $name])
                ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.date_start') desc"))
                ->addOrderBy(['id' => SORT_DESC]);
            $dataProvider->pagination->pageSize = 8;
        }
        // if($model){
        return $this->render('@app/modules/hr/views/employees/view', [
            'model' => $model ? $model : new Employees(),
            'name' => $name,
            'trainingPlans' => $trainingPlans,
            'idpCycle' => $idpCycle,
            'idpPlan' => $idpPlan,
            'probationCases' => $probationCases,
            'probationActionCount' => $probationActionCount,
            'dataProvider' => $dataProvider,
            'housingContext' => $housingContext,
            'housingVacancies' => $housingVacancies,
            'housingActionCount' => $housingActionCount,
            'payrollRows' => $payrollRows,
        ]);
        // }else{
        //     return $this->renderContent('<h1 class="text-center">ไม่พบข้อมูลพนักงาน</h1>');
        // }
    }

    public function actionPayrollSlip($id)
    {
        if (Yii::$app->user->isGuest) throw new ForbiddenHttpException('กรุณาเข้าสู่ระบบ');
        $employee = Employees::find()->where(['user_id' => (int) Yii::$app->user->id])->one();
        if (!$employee) throw new ForbiddenHttpException('ไม่พบบุคลากรที่เชื่อมกับบัญชีผู้ใช้');
        $row = (new Query())->select(['pe.*', 'p.period_code', 'p.period_type', 'p.date_start', 'p.date_end', 'p.pay_date', 'p.created_at AS period_created_at', 'p.created_by AS period_created_by'])
            ->from(['pe' => '{{%payroll_period_employee}}'])->innerJoin(['p' => '{{%payroll_period}}'], 'p.id = pe.payroll_period_id')
            ->where(['pe.id' => (int) $id, 'pe.employee_id' => (int) $employee->id, 'p.status' => 'calculated'])->one();
        if (!$row) throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดูสลิปนี้');
        $row['employee_snapshot'] = PayrollPeriodService::decodeSnapshot($row['employee_snapshot']);
        $row['calculation_snapshot'] = PayrollPeriodService::decodeSnapshot($row['calculation_snapshot']);
        $issuer = $row['period_created_by'] ? User::findOne((int) $row['period_created_by']) : null;
        $params = ['row' => $row, 'types' => PayrollRunService::TYPES, 'organization' => SiteHelper::getInfo(), 'issuerName' => trim((string) ($issuer?->employee?->fullname ?? $issuer?->username ?? ''))];
        if (Yii::$app->request->isAjax) return $this->renderAjax('@app/modules/finance/views/payroll/_payslip_content', $params);
        return $this->render('payroll-slip', $params);
    }
    public function actionIndex2()
    {
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        // if($model){
        return $this->render('index2', [
            'model' => $model,
        ]);

    }

    public function actionSetting()
    {
        $emp = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

        $id = Yii::$app->user->id;
        $model = User::findOne($id);
        $model->getRoleByUser();
        $model->password = $model->password_hash;
        $model->confirm_password = $model->password_hash;
        $oldPass = $model->password_hash;
        $model->fullname = $model->employee->fullname;
        $model->phone = $model->employee->phone;

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if ($oldPass !== $model->password) {
                    $model->setPassword($model->password);
                }
                if ($model->save()) {
                    $model->assignment();
                    $emp->email = $model->email;
                    $emp->phone = $model->phone;
                    $emp->save(false);
                    return $this->asJson(['success' => true, 'url' => Url::to(['/profile'])]);
                }

            }
            $result = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getInputId($model, $attribute)] = $errors;
            }
    
            return $this->asJson(['validation' => $result]);

            //     return [
            //         'status' => 'success',
            //         'url' => Url::to(['/profile'])

            //     ];
            //  }else{
            //      return $this->redirect(['/profile']);

            //  }
        }

        return $this->render('setting', [
            'model' => $model,
        ]);

    }

    public function actionHrd()
    {
        return $this->render('hrd');

    }

    public function actionFormGeneral()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        $amphure = isset($model->province) ? ArrayHelper::map($this->getAmphure($model->province), 'id', 'name') : null;
        $district = isset($model->amphure) ? ArrayHelper::map($this->getDistrict($model->amphure), 'id', 'name') : null;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            } else {

                return ActiveForm::validate($model);
            }

        } else {
            return [
                'title' => 'ข้อมูลทั่วไป',
                'content' => $this->renderAjax('_form_general', [
                    'model' => $model,
                    'amphure' => $amphure,
                    'district' => $district,
                ]),
            ];

        }
    }

    public function actionFormPersonal()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // $model = UserHelper::GetEmployee();
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($this->request->isPost) {
            $post = $this->request->post('Employees');
            $model->data_json = ArrayHelper::merge($model->data_json, $post['data_json']);
            if ($model->save(false)) {

                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            }
        } else {

            return [
                'title' => '<h4>ข้อมูลส่วนตัว</h4>',
                'content' => $this->renderAjax('_form_personal', [
                    'model' => $model,
                ]),
            ];
        }
    }

    public function actionFormFamily()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // $model = UserHelper::GetEmployee();
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($this->request->isPost) {
            $post = $this->request->post('Employees');
            $model->data_json = ArrayHelper::merge($model->data_json, $post['data_json']);
            if ($model->save(false)) {

                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            }
        } else {

            return [
                'title' => '<h4>ครอบครัว</h4>',
                'content' => $this->renderAjax('_form_family', [
                    'model' => $model,
                ]),
            ];
        }
    }

    public function actionFormAddress()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // $model = UserHelper::GetEmployee();
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($this->request->isPost) {
            $post = $this->request->post('Employees');
            $model->data_json = ArrayHelper::merge($model->data_json, $post['data_json']);
            if ($model->save(false)) {

                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            }
        } else {

            return [
                'title' => '<h4>ข้อมูลที่อยู่</h4>',
                'content' => $this->renderAjax('_form_address', [
                    'model' => $model,
                ]),
            ];
        }
    }

    public function actionFormChangname()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // $model = UserHelper::GetEmployee();
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($this->request->isPost) {
            $post = $this->request->post('Employees');
            $model->data_json = ArrayHelper::merge($model->data_json, $post['data_json']);
            if ($model->save(false)) {

                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            }
        } else {

            return [
                'title' => '<h4>เพิ่มประวัติการเปลี่ยนชื่อ</h4>',
                'content' => $this->renderAjax('_form_changname', [
                    'model' => $model,
                ]),
            ];
        }
    }

    public function actionFormExperience()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // $model = UserHelper::GetEmployee();
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        if ($this->request->isPost) {
            $post = $this->request->post('Employees');
            $model->data_json = ArrayHelper::merge($model->data_json, $post['data_json']);
            if ($model->save(false)) {

                return [
                    'status' => 'success',
                    'data' => $model,
                ];
            }
        } else {

            return [
                'title' => '<h4>เพิ่มประสบการณ์</h4>',
                'content' => $this->renderAjax('_form_experience', [
                    'model' => $model,
                ]),
            ];
        }
    }

    public function actionFormEducation()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // $model = UserHelper::GetEmployee();
        $model = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

        if ($model->load(Yii::$app->request->post()) && $model->save(false)) {

            return [
                'status' => 'success',
                'data' => $model,
            ];
        } else {

            return [
                'title' => 'เพิ่มข้อมูลการศึกษา',
                'content' => $this->renderAjax('_form_education', [
                    'model' => $model,
                ]),
            ];
        }
    }

    protected function getAmphure($id)
    {
        $datas = Amphure::find()->where(['province_id' => $id])->all();
        return $this->MapData($datas, 'id', 'name_th');
    }

    protected function getDistrict($id)
    {
        $datas = District::find()->where(['amphure_id' => $id])->all();
        return $this->MapData($datas, 'id', 'name_th');
    }

    protected function MapData($datas, $fieldId, $fieldName)
    {
        $obj = [];
        foreach ($datas as $key => $value) {
            array_push($obj, ['id' => $value->{$fieldId}, 'name' => $value->{$fieldName}]);
        }
        return $obj;
    }

    public function actionUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $fileName = 'avatar';
        $uploadPath = Yii::getAlias('@webroot') . '/files';

        if (isset($_FILES[$fileName])) {
            $file = \yii\web\UploadedFile::getInstanceByName($fileName);
            $data = \yii\web\UploadedFile::getInstanceByName($fileName);

            // return $file;

            //Print file data
            //print_r($file);

            $model = UserHelper::GetEmployee();
            $fileName = $file->baseName . '.' . $file->extension;
            $realFileName = md5($file->baseName . time()) . '.' . $file->extension;
            $savePath = Yii::getAlias('@webroot') . '/avatar/' . $realFileName;

            if ($file->saveAs($savePath)) {
                $model = UserHelper::GetEmployee();

                // if (!empty($model->avatar)) {
                @unlink(Yii::getAlias('@webroot') . '/avatar/' . $model->avatar);
                // }

                $model->avatar = $realFileName;
                $model->save(false);
                return '/avatar/' . $model->avatar;
            }
        }

        return false;
    }

public function actionHistory(){

    Yii::$app->response->format = Response::FORMAT_JSON;
    $sql = "select CAST(JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.date_start')) as DATE) as date_start, year(CAST(JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.date_start')) as DATE)) as date_year, CAST(JSON_UNQUOTE(JSON_EXTRACT(data_json,'$.salary')) as UNSIGNED) as salary FROM employee_detail WHERE name = 'position' AND emp_id = 8 GROUP BY year(date_start) ORDER BY year(date_start) DESC;";
    $querys =  Yii::$app->db->createCommand($sql)->queryAll();

    $employeesDetail = EmployeeDetail::find()->where(['emp_id'=>8,'name' => 'position'])->all();
    $data = [];
    $categories = [];
    foreach($querys as $model){
        $data[] = $model['salary'];
        $categories[] = $model['date_year'];
    }

    foreach($employeesDetail as $model){
        // $data[] = $model->data_json['salary'];
        // $categories[] = Yii::$app->thaiFormatter->asDate($model->data_json['date_start'],'medium');
    }


    return [
        'name' => '',
        'data' => $data,
        'categories' => $categories
    ];
}

}
