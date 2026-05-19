<?php

namespace app\modules\hr\controllers;

use yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\hr\models\EmployeeDetail;
use app\modules\hr\models\EmployeePosition;
use app\modules\hr\models\EmployeePositionGroup;
use app\modules\hr\models\EmployeeType;
use app\modules\hr\models\EmployeeDetailSearch;

/**
 * EmployeeDetailController implements the CRUD actions for EmployeeDetail model.
 */
class EmployeeDetailController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all EmployeeDetail models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new EmployeeDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    
    /**
     * Displays a single EmployeeDetail model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {

        $name = $this->request->get('name');
        $title = $this->request->get('title');
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $title,
                'content' => $this->renderAjax('./views/' . $name, [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('./views/' . $name, [
                'model' => $model,
            ]);
        }

    }

    /**
     * Creates a new EmployeeDetail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $id = $this->request->get('id');
        $emp_id = $this->request->get('emp_id');
        $name = $this->request->get('name');

        // ดึงตำแหน่งล่าสุกมา ปิดไว้ก่อนยังไม่ได้ใช้
        if($name == "position"){
            $last = EmployeeDetail::find()->where(['emp_id' => $emp_id])
            ->orderBy(
                new \yii\db\Expression("JSON_EXTRACT(data_json, '$.date_start') desc")
            )->one();
        }else{
            $last=false;
        }

        $model = new EmployeeDetail([
            'emp_id' => $emp_id,
            'name' => $name,
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                if (is_string($model->data_json)) {
                    $model->data_json = json_decode($model->data_json, true) ?: [];
                }
                if ($name === 'position') {
                    $model->data_json = $this->enrichPositionDataJson($model->data_json);
                }
                $array2 = [
                    'date_start' => $this->normalizeDateForSave($model->data_json['date_start'] ?? null),
                    'date_end' => $this->normalizeDateForSave($model->data_json['date_end'] ?? null),
                ];
                if ($name === 'benefit') {
                    $array2 = ArrayHelper::merge($array2, [
                        'receive_date' => $this->normalizeDateForSave($model->data_json['receive_date'] ?? null),
                        'start_date' => $this->normalizeDateForSave($model->data_json['start_date'] ?? null),
                        'end_date' => $this->normalizeDateForSave($model->data_json['end_date'] ?? null),
                    ]);
                }

                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->data_json = ArrayHelper::merge($model->data_json, $array2);
                $model->save(false);

                //update ประวติทั่วไป
                // if($name == 'posision' && $model->data_json['update_status'] == "2")
                if ($name == 'position') {
                    $this->UpdatePosition($model);
                }

                if ($model->name == 'education') {
                    $this->UpdateEducation($model);
                }

                return [
                    'status' => 'success',
                    'data' => $model,
                    'container' => '#hr-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
            $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionCreateHealth()
    {
  
    $id = $this->request->get('id');
        $emp_id = $this->request->get('emp_id');
        $name = $this->request->get('name');
        // ดึงตำแหน่งล่าสุกมา ปิดไว้ก่อนยังไม่ได้ใช้
        if($name == "position"){
            $last = EmployeeDetail::find()->where(['emp_id' => $emp_id])
            ->orderBy(
                new \yii\db\Expression("JSON_EXTRACT(data_json, '$.date_start') desc")
            )->one();
        }else{
            $last=false;
        }

        $model = new EmployeeDetail([
            'emp_id' => $emp_id,
            'name' => $name,
        ]);
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                Yii::$app->response->format = Response::FORMAT_JSON;
                if($model->save(false)){
                    return $this->redirect('view_health',['model' => $model]);
                }

                return [
                    'status' => 'success',
                    'data' => $model,
                    'container' => '#hr-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
            $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('health', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('health', [
                'model' => $model,
            ]);
        }
    }

    
    /**
     * Updates an existing EmployeeDetail model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (is_string($model->data_json)) {
            $model->data_json = json_decode($model->data_json, true) ?: [];
        }
        if ($model->name === 'position') {
            $model->data_json = $this->enrichPositionDataJson($model->data_json);
        }
        $arrayUpdate = [
            'date_start' => $this->normalizeDateForView($model->data_json['date_start'] ?? null),
            'date_end' => $this->normalizeDateForView($model->data_json['date_end'] ?? null),
        ];
        if ($model->name === 'benefit') {
            $arrayUpdate = ArrayHelper::merge($arrayUpdate, [
                'receive_date' => $this->normalizeDateForView($model->data_json['receive_date'] ?? null),
                'start_date' => $this->normalizeDateForView($model->data_json['start_date'] ?? null),
                'end_date' => $this->normalizeDateForView($model->data_json['end_date'] ?? null),
            ]);
        }
        $model->data_json = ArrayHelper::merge($model->data_json, $arrayUpdate);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if (is_string($model->data_json)) {
                $model->data_json = json_decode($model->data_json, true) ?: [];
            }
            if ($model->name === 'position') {
                $model->data_json = $this->enrichPositionDataJson($model->data_json);
            }
            $array2 = [
                'date_start' => $this->normalizeDateForSave($model->data_json['date_start'] ?? null),
                'date_end' => $this->normalizeDateForSave($model->data_json['date_end'] ?? null),
            ];
            if ($model->name === 'benefit') {
                $array2 = ArrayHelper::merge($array2, [
                    'receive_date' => $this->normalizeDateForSave($model->data_json['receive_date'] ?? null),
                    'start_date' => $this->normalizeDateForSave($model->data_json['start_date'] ?? null),
                    'end_date' => $this->normalizeDateForSave($model->data_json['end_date'] ?? null),
                ]);
            }

            $model->data_json = ArrayHelper::merge($model->data_json, $array2);
            Yii::$app->response->format = Response::FORMAT_JSON;
            //return $model;
            $model->save();
            //update ประวติทั่วไป

            if ($model->name == 'position') {
                $this->UpdatePosition($model);
            }

            if ($model->name == 'education') {
                $this->UpdateEducation($model);
            }

            return [
                'status' => 'success',
                'data' => $model,
                'container' => '#hr-container',
            ];
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function UpdatePosition($data)
    {
        try {
            $emp = Employees::findOne($data->emp_id);
            if (is_string($emp->data_json)) {
                $emp->data_json = json_decode($emp->data_json, true) ?: [];
            }

            $model = EmployeeDetail::find()
                ->where(['name' => 'position', 'emp_id' => $data->emp_id])
                ->orderBy(new \yii\db\Expression("JSON_EXTRACT(data_json, '$.date_start') desc"))
                ->one();

            if ($model) {
                $positionData = $this->enrichPositionDataJson(is_array($model->data_json) ? $model->data_json : []);
                $departmentName = Organization::findOne($positionData['department'] ?? null);
                $positionTypeText = $positionData['employee_type_text'] ?? '';
                $expertise = trim((string) ($positionData['expertise'] ?? ''));

                $emp->employee_type_id = $positionData['employee_type_id'] ?? null;
                $emp->employee_position_group_id = $positionData['employee_position_group_id'] ?? null;
                $emp->employee_position_id = $positionData['employee_position_id'] ?? null;
                $emp->position_level = $positionTypeText !== 'ข้าราชการ' ? null : ($positionData['position_level'] ?? null);
                $emp->expertise = $expertise !== '' ? $expertise : $emp->expertise;
                $emp->status = $positionData['status'] ?? $emp->status;
                $emp->position_number = $positionData['position_number'] ?? $emp->position_number;
                $emp->salary = $positionData['salary'] ?? $emp->salary;
                $emp->department = $positionData['department'] ?? $emp->department;

                foreach ($this->positionLegacyDataKeys() as $legacyKey) {
                    unset($emp->data_json[$legacyKey]);
                }

                $array2 = [
                    'employee_type_id' => $positionData['employee_type_id'] ?? null,
                    'employee_type_text' => $positionData['employee_type_text'] ?? '',
                    'employee_position_group_id' => $positionData['employee_position_group_id'] ?? null,
                    'employee_position_group_text' => $positionData['employee_position_group_text'] ?? '',
                    'employee_position_id' => $positionData['employee_position_id'] ?? null,
                    'employee_position_text' => $positionData['employee_position_text'] ?? '',
                    'expertise' => $positionData['expertise'] ?? '',
                    'position_level_text' => $positionData['position_level_text'] ?? '',
                    'status_text' => $positionData['status_text'] ?? '',
                    'department_text' => $departmentName ? $departmentName->name : '',
                ];
                $emp->data_json = ArrayHelper::merge($emp->data_json, $array2);
            } else {
                $emp->employee_type_id = null;
                $emp->employee_position_group_id = null;
                $emp->employee_position_id = null;
                $emp->position_level = null;
                $emp->expertise = null;
                foreach ($this->positionLegacyDataKeys() as $legacyKey) {
                    unset($emp->data_json[$legacyKey]);
                }
                $array2 = [
                    'employee_type_id' => null,
                    'employee_type_text' => '',
                    'employee_position_group_id' => null,
                    'employee_position_group_text' => '',
                    'employee_position_id' => null,
                    'employee_position_text' => '',
                    'expertise' => '',
                    'position_level_text' => '',
                    'status_text' => '',
                    'department_text' => '',
                ];
                $emp->data_json = ArrayHelper::merge($emp->data_json, $array2);
            }

            return $emp->save(false);
        } catch (\Throwable $th) {
            return throw $th;
        }
    }

    private function enrichPositionDataJson(array $dataJson): array
    {
        $legacySource = $dataJson;

        $type = $this->resolveEmployeeTypeModel($legacySource);
        $group = $this->resolveEmployeePositionGroupModel($legacySource);
        $position = $this->resolveEmployeePositionModel($legacySource);

        if (!$group && $position && $position->employeePositionGroup) {
            $group = $position->employeePositionGroup;
        }

        $dataJson = array_diff_key($dataJson, array_flip($this->positionLegacyDataKeys()));

        return ArrayHelper::merge($dataJson, [
            'employee_type_id' => $type?->id ?? ($legacySource['employee_type_id'] ?? null),
            'employee_type_text' => $type?->title ?? ($legacySource['employee_type_text'] ?? ''),
            'employee_position_group_id' => $group?->id ?? ($legacySource['employee_position_group_id'] ?? null),
            'employee_position_group_text' => $group?->title ?? ($legacySource['employee_position_group_text'] ?? ''),
            'employee_position_id' => $position?->id ?? ($legacySource['employee_position_id'] ?? null),
            'employee_position_text' => $position?->title ?? ($legacySource['employee_position_text'] ?? ''),
            'expertise' => trim((string) ($legacySource['expertise'] ?? '')),
        ]);
    }

    private function resolveEmployeeTypeModel(array $dataJson): ?EmployeeType
    {
        $employeeTypeId = (int) ($dataJson['employee_type_id'] ?? 0);
        if ($employeeTypeId > 0) {
            return EmployeeType::findOne($employeeTypeId);
        }

        $legacyCode = $this->extractLegacyTypeCode($dataJson['position_type'] ?? null);
        if ($legacyCode !== null) {
            foreach (EmployeeType::find()->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all() as $type) {
                if (in_array($legacyCode, $type->legacyCodes(), true)) {
                    return $type;
                }
            }
        }

        $legacyTitle = trim((string) ($dataJson['position_type_text'] ?? $dataJson['employee_type_text'] ?? ($dataJson['position_type'] ?? '')));
        if ($legacyTitle === '') {
            return null;
        }

        foreach (EmployeeType::find()->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all() as $type) {
            if (strcasecmp(trim((string) $type->title), $legacyTitle) === 0) {
                return $type;
            }
        }

        return null;
    }

    private function resolveEmployeePositionGroupModel(array $dataJson): ?EmployeePositionGroup
    {
        $groupId = (int) ($dataJson['employee_position_group_id'] ?? 0);
        if ($groupId > 0) {
            return EmployeePositionGroup::findOne($groupId);
        }

        $legacyCode = $this->normalizeLegacyCode($dataJson['position_group'] ?? null);
        if ($legacyCode !== null) {
            $group = EmployeePositionGroup::find()
                ->where(['legacy_code' => $legacyCode])
                ->one();
            if ($group) {
                return $group;
            }
        }

        $legacyTitle = trim((string) ($dataJson['position_group_text'] ?? $dataJson['employee_position_group_text'] ?? ($dataJson['position_group'] ?? '')));
        if ($legacyTitle === '') {
            return null;
        }

        foreach (EmployeePositionGroup::find()->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all() as $group) {
            if (strcasecmp(trim((string) $group->title), $legacyTitle) === 0) {
                return $group;
            }
        }

        return null;
    }

    private function resolveEmployeePositionModel(array $dataJson): ?EmployeePosition
    {
        $positionId = (int) ($dataJson['employee_position_id'] ?? 0);
        if ($positionId > 0) {
            return EmployeePosition::find()
                ->with(['employeeType', 'employeePositionGroup'])
                ->where(['id' => $positionId])
                ->one();
        }

        $legacyCode = $this->normalizeLegacyCode($dataJson['position_name'] ?? null);
        if ($legacyCode !== null) {
            $position = EmployeePosition::find()
                ->with(['employeeType', 'employeePositionGroup'])
                ->where(['legacy_code' => $legacyCode])
                ->one();
            if ($position) {
                return $position;
            }
        }

        $legacyTitle = trim((string) ($dataJson['position_name_text'] ?? $dataJson['employee_position_text'] ?? ($dataJson['position_name'] ?? '')));
        if ($legacyTitle === '') {
            return null;
        }

        foreach (EmployeePosition::find()->with(['employeeType', 'employeePositionGroup'])->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all() as $position) {
            if (strcasecmp(trim((string) $position->title), $legacyTitle) === 0) {
                return $position;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function positionLegacyDataKeys(): array
    {
        return [
            'position_name',
            'position_name_text',
            'position_group',
            'position_group_text',
            'position_type',
            'position_type_text',
        ];
    }

    private function extractLegacyTypeCode($value): ?string
    {
        $value = $this->normalizeLegacyCode($value);
        if ($value === null) {
            return null;
        }

        if (preg_match('/^(PT\d+)/i', $value, $matches)) {
            return strtoupper($matches[1]);
        }

        return null;
    }

    private function normalizeLegacyCode($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '' || $value === '-') {
            return null;
        }

        return $value;
    }

    public function UpdateEducation($data)
    {
        try {

            $emp = Employees::findOne($data->emp_id);

            $model = EmployeeDetail::find()->where(['name' => 'education', 'emp_id' => $data->emp_id])
                ->orderBy(
                    new \yii\db\Expression("JSON_EXTRACT(data_json, '$.date_start') desc")
                )->one();

            if (isset($model->data_json['education'])) {
                $emp->education = $model->data_json['education'];
            }

            if (isset($position['position_group'])) {
                $emp->position_group = $position['position_group_id'];
            }

            return $emp->save(false);
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
    }



    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new EmployeeDetail();
        
        
        if ($this->request->isPost && $model->load($this->request->post()))
         { 
        $requiredName = "ต้องระบุ"; 
            //ตรวจสอบตำแหน่ง
        if($model->name == "position"){
            $dataJson = $model->data_json;
            if (is_string($dataJson)) {
                $dataJson = json_decode($dataJson, true) ?: [];
            }
            if (!is_array($dataJson)) {
                $dataJson = [];
            }
            $model->data_json = $this->enrichPositionDataJson($dataJson);

            $typeValue = $model->data_json['employee_type_id'] ?? '';
            $groupValue = $model->data_json['employee_position_group_id'] ?? '';
            $positionValue = $model->data_json['employee_position_id'] ?? '';
            $model->data_json['statuslist'] == "" ? $model->addError('data_json[statuslist]',$requiredName) : null;
            $typeValue == "" ? $model->addError('data_json[employee_type_id]',$requiredName) : null;
            $groupValue == "" ? $model->addError('data_json[employee_position_group_id]',$requiredName) : null;
            $positionValue == "" ? $model->addError('data_json[employee_position_id]',$requiredName) : null;
            $model->data_json['position_number'] == "" ? $model->addError('data_json[position_number]',$requiredName) : null;
            // $model->data_json['department'] == "" ? $model->addError('data_json[department]',$requiredName) : null;

            $positionTypeText = $model->data_json['employee_type_text'] ?? $model->data_json['position_type_text'] ?? '';
            if($positionTypeText == 'ข้าราชการ')
            {
                $model->data_json['position_level'] == "" ? $model->addError('data_json[position_level]','ข้าราชการ'.$requiredName) : null;
            }
            $model->data_json['status'] == "" ? $model->addError('data_json[status]', 'สถานะต้องไม่ว่าง') : null;
            $model->data_json['date_start'] == "__/__/____" ? $model->addError('data_json[date_start]',$requiredName) : null;
        }
        
        // ตรวจสอบการศึกษา
        if($model->name == "education")
        {
            $model->data_json['education'] == "" ? $model->addError('data_json[education]',$requiredName) : null;
            $model->data_json['major'] == "" ? $model->addError('data_json[major]',$requiredName) : null;
            $model->data_json['institute'] == "" ? $model->addError('data_json[institute]',$requiredName) : null;
            $model->data_json['date_start'] == "__/__/____" ? $model->addError('data_json[date_start]',$requiredName) : null;
            $model->data_json['date_end'] == "__/__/____" ? $model->addError('data_json[date_end]',$requiredName) : null;
        
        }

        //ตรวจสอบรางวัลเชิดชูเกียรติ
        if($model->name == "award")
        {
            $model->data_json['award_name'] == "" ? $model->addError('data_json[award_name]', 'ชื่อรางวัลต้องไม่ว่าง') : null;
            $model->data_json['award_company_name'] == "" ? $model->addError('data_json[award_company_name]',$requiredName) : null;
            $model->data_json['date_start'] == "__/__/____" ? $model->addError('data_json[date_start]',$requiredName) : null;
            $model->data_json['date_end'] == "__/__/____" ? $model->addError('data_json[date_end]',$requiredName) : null;
        
        }

          //ตรวจสอบประวัติครอบครัว
          if($model->name == "family")
          {
              $model->data_json['prefix'] == "" ? $model->addError('data_json[prefix]',$requiredName) : null;
              $model->data_json['fname'] == "" ? $model->addError('data_json[fname]',$requiredName) : null;
              $model->data_json['lname'] == "" ? $model->addError('data_json[lname]',$requiredName) : null;
              $model->data_json['family_relation'] == "" ? $model->addError('data_json[family_relation]',$requiredName) : null;
              $model->data_json['status'] == "" ? $model->addError('data_json[status]',$requiredName) : null;
              $model->data_json['status'] == "" ? $model->addError('data_json[status]',$requiredName) : null;
          }
          
          
           //ครื่องราชอิสริยาภรณ์
    if ($model->name == "insignia") {
        $data = $model->data_json;
        $requiredMsg = "{attribute} ไม่สามารถว่างได้";

        // 1. ตรวจสอบชื่อชั้นตรา
        if (empty($data['name'])) {
            $model->addError('data_json[name]', str_replace('{attribute}', 'ชั้นตราเครื่องราชฯ', $requiredMsg));
        }

        // 2. ตรวจสอบปี พ.ศ. (ต้องเป็นตัวเลข 4 หลัก)
        if (empty($data['thai_year'])) {
            $model->addError('data_json[thai_year]', str_replace('{attribute}', 'ปี พ.ศ.', $requiredMsg));
        } elseif (!preg_match('/^[0-9]{4}$/', $data['thai_year'])) {
            $model->addError('data_json[thai_year]', 'ปี พ.ศ. ต้องเป็นตัวเลข 4 หลัก');
        }

        // 3. ตรวจสอบข้อมูลราชกิจจาฯ (เล่ม/ตอน/หน้า)
        if (empty($data['gazette_book'])) {
            $model->addError('data_json[gazette_book]', str_replace('{attribute}', 'เล่มที่', $requiredMsg));
        }
        if (empty($data['gazette_section'])) {
            $model->addError('data_json[gazette_section]', str_replace('{attribute}', 'ตอนที่', $requiredMsg));
        }
        if (empty($data['gazette_page'])) {
            $model->addError('data_json[gazette_page]', str_replace('{attribute}', 'หน้าที่', $requiredMsg));
        }

        // 4. ตรวจสอบวันที่ (ถ้ามีการกรอก ต้องอยู่ในรูปแบบ Date ที่ถูกต้อง)
        if (empty($data['gazette_date'])) {
            $model->addError('data_json[gazette_date]', str_replace('{attribute}', 'วันที่ประกาศ', $requiredMsg));
        }
        
        // 5. ตรวจสอบสถานะการส่งคืน
        if (empty($data['return_status'])) {
            $model->addError('data_json[return_status]', 'กรุณาเลือกสถานะการส่งคืน');
        }
    }

             //ประวัติการเปลี่ยนชื่อ
             if($model->name == "rename")
             {
                 $model->data_json['rename_type'] == "" ? $model->addError('data_json[rename_type]', $requiredName) : null;
                 $model->data_json['date_start'] == "__/__/____" ? $model->addError('data_json[date_start]',$requiredName) : null;
             }

             foreach ($model->getErrors() as $attribute => $errors) {
                 $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
                }
                if (!empty($result)) {
                    return $this->asJson($result);
                }
            }
    }

    private function normalizeDateForView($value)
    {
        if (!is_string($value) || $value === '' || $value === '__/__/____') {
            return $value;
        }

        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
            return $value;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return AppHelper::convertToThai($value) ?: $value;
        }

        return $value;
    }

    private function normalizeDateForSave($value)
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || $value === '__/__/____') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
            return $value;
        }

        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
            return AppHelper::convertToGregorian($value) ?: $value;
        }

        return $value;
    }


    /**
     * Deletes an existing EmployeeDetail model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $item = $this->findModel($id);
        $this->findModel($id)->delete();

        if ($item->name == 'position') {
            $this->UpdatePosition($item);
        }

        if ($item->name == 'education') {
            $this->UpdateEducation($item);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'status' => 'success',
            'data' => $item,
            'container' => '#hr-container',
        ];
    }

    /**
     * Finds the EmployeeDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return EmployeeDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = EmployeeDetail::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionDemo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = EmployeeDetail::find()->where(['emp_id' => 5])
            ->orderBy(
                new \yii\db\Expression("JSON_EXTRACT(data_json, '$.date_start') desc")
            )->one();

        return $model;

    }
}
