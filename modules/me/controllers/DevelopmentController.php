<?php

namespace app\modules\me\controllers;

use Yii;
use DateTime;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\Processor;
use app\components\SiteHelper;
use app\components\UserHelper;
use yii\helpers\BaseFileHelper;
use app\components\ThaiDateHelper;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\Development;
use app\modules\hr\models\DevelopmentDetail;
use app\modules\hr\models\DevelopmentSearch;
use app\modules\hr\models\DevelopmentDetailSearch;

/**
 * DevelopmentController implements the CRUD actions for Development model.
 */
class DevelopmentController extends Controller
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
     * Lists all Development models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $lastDay = (new DateTime(date('Y-m-d')))->modify('last day of this month')->format('Y-m-d');
        $searchModel = new DevelopmentSearch([
            'thai_year' => AppHelper::YearBudget(),
            'date_start' => AppHelper::convertToThai(date('Y-m') . '-01'),
            'date_end' => AppHelper::convertToThai($lastDay),
            'status' => ['Pending']
        ]);
        // $searchModel = new DevelopmentDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('developmentDetail');
        $dataProvider->query->andFilterWhere(['development_detail.emp_id' => $me->id]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $searchModel->q],
        ]);
        // if ($searchModel->thai_year !== '' && $searchModel->thai_year !== null) {
        //     $searchModel->date_start = AppHelper::convertToThai(($searchModel->thai_year - 544) . '-10-01');
        //     $searchModel->date_end = AppHelper::convertToThai(($searchModel->thai_year - 543) . '-09-30');
        // }

        try {
            $dateStart = AppHelper::convertToGregorian($searchModel->date_start);
            $dateEnd = AppHelper::convertToGregorian($searchModel->date_end);
            // $dataProvider->query->andFilterWhere(['>=', 'date_start', $dateStart])->andFilterWhere(['<=', 'date_end', $dateEnd]);
        } catch (\Throwable $th) {
        }
        $dataProvider->query->groupBy('development_detail.id');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Development model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        if($this->request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/hr/views/development/view', [
                    'model' => $this->findModel($id),
                ]),
            ];
        }else{
            return $this->render('@app/modules/hr/views/development/view', [
                'model' => $this->findModel($id),
            ]);
        }
    }

    /**
     * Creates a new Development model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Development([
            'thai_year' => AppHelper::YearBudget()
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $me = UserHelper::GetEmployee();
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->emp_id = $me->id;
                try {
                    $model->date_start = $model->date_start ? AppHelper::convertToGregorian($model->date_start) : null;
                    $model->date_end = $model->date_end ? AppHelper::convertToGregorian($model->date_end) : null;
                    $model->vehicle_date_start = $model->vehicle_date_start ? AppHelper::convertToGregorian($model->vehicle_date_start) : null;
                    $model->vehicle_date_end = $model->vehicle_date_end ? AppHelper::convertToGregorian($model->vehicle_date_end) : null;
                } catch (\Throwable $th) {
                }
                if ($model->save(false)) {
                    
                    $addMember = new DevelopmentDetail();
                    $addMember->development_id = $model->id;
                    $addMember->name = 'member';
                    $addMember->emp_id = $me->id;
                    $addMember->save(false);
                    $model->createApprove();
                }

                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => false,
                    'errors' => $model->getErrors(),
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/hr/views/development/_form', ['model' => $model]),
            ];
        } else {
            return $this->render('@app/modules/hr/views/development/_form', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Development model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        try {
            $model->date_start = AppHelper::convertToThai($model->date_start);
            $model->date_end = AppHelper::convertToThai($model->date_end);
            $model->vehicle_date_start = AppHelper::convertToThai($model->vehicle_date_start);
            $model->vehicle_date_end = AppHelper::convertToThai($model->vehicle_date_end);
        } catch (\Throwable $th) {
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                try {
                    $model->date_start = $model->date_start ? AppHelper::convertToGregorian($model->date_start) : null;
                    $model->date_end = $model->date_end ? AppHelper::convertToGregorian($model->date_end) : null;
                    $model->vehicle_date_start = $model->vehicle_date_start ? AppHelper::convertToGregorian($model->vehicle_date_start) : null;
                    $model->vehicle_date_end = $model->vehicle_date_end ? AppHelper::convertToGregorian($model->vehicle_date_end) : null;
                } catch (\Throwable $th) {
                }
                $model->save();

                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => false,
                    'errors' => $model->getErrors(),
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/hr/views/development/_form', ['model' => $model]),
            ];
        } else {
            return $this->render('@app/modules/hr/views/development/_form', [
                'model' => $model,
            ]);
        }
    }


    //การตอบรับเป็นวิทบาการ
    public function actionResponseDev($id)
    {
        $model = $this->findModel($id);
        $oldData = $model->data_json;

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->data_json = ArrayHelper::merge($oldData, $model->data_json);
                $model->save(false);
                 return [
            'status' => 'success',
        ];
            }
        }
         if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/hr/views/development/_form_response_dev', ['model' => $model]),
            ];
        }
       
    }
    /**
     * Deletes an existing Development model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCancel($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $model->status = 'Cancel';
        $model->save(false);
        return [
            'success' => 'success',
        ];
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Development model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Development the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Development::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    // แบบฟอร์มเดินทางไปราชการ
    public function actionFormOfficial($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        $title = 'แบบฟอร์มเดินทางไปราชการ';
        $result_name = $title . '-' . $model->id . '.docx';
        $word_name = 'แบบฟอร์มเดินทางไปราชการ.docx';

        @unlink(Yii::getAlias('@webroot') . '/msword/results/development/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/development/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้

        // return $model->checkerName(1)['employee']->signature();
        $dateStart = Yii::$app->thaiFormatter->asDate($model->date_start, 'long');
        $dateEnd = Yii::$app->thaiFormatter->asDate($model->date_end, 'long');

        $templateProcessor->setValue('org_fullname', $this->GetInfo()['org_fullname']);
        $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);
        $templateProcessor->setValue('doc_number', $this->GetInfo()['doc_number']);
        $templateProcessor->setValue('governor', $this->GetInfo()['governor']);
        $templateProcessor->setValue('fullname', $model->createdByEmp?->fullname ?? '-');
        $templateProcessor->setValue('position', $model->createdByEmp?->positionName());
        $templateProcessor->setValue('topic', $model->topic);
        $templateProcessor->setValue('member', $model->memberText()['count'] > 1 ? 'พร้อมด้วย ' . $model->memberText()['text'] : '');
        $templateProcessor->setValue('location', $model->data_json['location'] ?? '-');
        $templateProcessor->setValue('distance', $model->data_json['distance'] ?? '-');
        $templateProcessor->setValue('doc_date', ThaiDateHelper::formatThaiDate(date('Y-m-d')));
        $templateProcessor->setValue('dev_date', ThaiDateHelper::formatThaiDateRange($model->date_start, $model->date_end));
        $templateProcessor->setValue('date_go', ThaiDateHelper::formatThaiDate($model->vehicle_date_start));
        $templateProcessor->setValue('date_back', ThaiDateHelper::formatThaiDate($model->vehicle_date_end));
        $templateProcessor->setValue('v_type', ($model->vehicleType?->title ?? '-') . ' ทะเบียน ' . ($model->data_json['license_plate'] ?? '-'));
        $countDays = (new DateTime($model->date_end))->diff(new DateTime($model->date_start))->days + 1;
        $templateProcessor->setValue('count_days', $countDays);

               //ผู้ขออนุญาต
        try {
            $templateProcessor->setImg('emp_sign', ['src' => $model->createdByEmp->signature(), 'size' => [150, 50]]);
        } catch (\Throwable $th) {
            $templateProcessor->setValue('emp_sign', '.......................................');
        }

        $signToFullname = $model->assignedTo?->fullname ?? '-';
        $signToPosition = $model->assignedTo?->positionName() ?? '-';
        $templateProcessor->setValue('sign_to', $signToFullname . ' ตำแหน่ง ' . $signToPosition . ' ปฏิบัติงานแทน');
        $templateProcessor->setValue('sign_to_name', $signToFullname);
        $templateProcessor->setValue('sign_to_position', $signToPosition);
        // ลสยมือผู้ปฏิบัติงานแทน
        try {
            $templateProcessor->setImg('sign_to_sign', ['src' => $model->assignedTo->signature(), 'size' => [150, 50]]);
        } catch (\Throwable $th) {
            $templateProcessor->setValue('sign_to_sign', '........................................');
        }

        $templateProcessor->setValue('org_position', 'ผู้อำนวยการ' . $this->GetInfo()['company_name']);
        $templateProcessor->setValue('director', $this->GetInfo()['director_fullname']);
        $templateProcessor->setValue('createDate', Yii::$app->thaiFormatter->asDate($model->created_at, 'long'));

        if ($model->status == 'Approve') {
            $status = 'อนุมัติ';
        } else if ($model->status == 'Reject') {
            $status = 'ไม่อนุมัติ';
        } else {
            $status = 'รอการอนุมัติ';
        }
        $templateProcessor->setValue('status', $status);

        // ผู้อำนวยการ
        $dicrectorType = ($this->GetInfo()['director_type'] == 'รักษาการแทนผู้อำนวยการ' ? 'รักษาการแทนผู้อำนวยการ' : '');
        $templateProcessor->setValue('direc_fullname', $this->GetInfo()['director_fullname']);
        $templateProcessor->setValue('direc_position', $this->GetInfo()['director_position'] . $dicrectorType);
        try {
            $templateProcessor->setImg('direc_sign', ['src' => $this->GetInfo()['director']->signature(), 'size' => [150, 60]]);  // ลายมือผู้ตรวจสอบ
        } catch (\Throwable $th) {
            $templateProcessor->setValue('direc_sign', '...........................................');
        }

        $filePath = Yii::getAlias('@webroot') . '/msword/results/development/' . $result_name;
        $templateProcessor->saveAs($filePath);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        if (file_exists($filePath)) {
            return $this->Show($result_name);
            // return Yii::$app->response->sendFile($filePath);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }

        return $this->redirect('https://docs.google.com/viewerng/viewer?url=' . Url::base('https') . '/msword/results/leave/' . $result_name);
        // return $this->Show($result_name);
    }

    // ใบขออนุญาตเดินทางไปราชการ
    public function actionPermitRequest($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        $title = 'แบบฟอร์มขออนุญาต';
        $result_name = $title . '-' . $model->id . '.docx';
        $word_name = $title . '.docx';

        @unlink(Yii::getAlias('@webroot') . '/msword/results/development/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/development/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้

        // return $model->checkerName(1)['employee']->signature();
        $dateStart = Yii::$app->thaiFormatter->asDate($model->date_start, 'long');
        $dateEnd = Yii::$app->thaiFormatter->asDate($model->date_end, 'long');

        $templateProcessor->setValue('org_fullname', $this->GetInfo()['org_fullname']);
        $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);
        $templateProcessor->setValue('address', $this->GetInfo()['address']);
        $templateProcessor->setValue('phone', $this->GetInfo()['phone']);
        $templateProcessor->setValue('doc_number', $this->GetInfo()['doc_number']);
        $templateProcessor->setValue('document_number', $model->document?->doc_number ?? '-');
        $templateProcessor->setValue('doc_date', ThaiDateHelper::formatThaiDate(date('Y-m-d')));
        $templateProcessor->setValue('dev_date', ThaiDateHelper::formatThaiDateRange($model->date_start, $model->date_end));
        $templateProcessor->setValue('location', $model->data_json['location'] ?? '-');
        $templateProcessor->setValue('governor', $this->GetInfo()['governor']);
        $templateProcessor->setValue('fullname', $model->createdByEmp?->fullname ?? '-');
        $templateProcessor->setValue('position', $model->createdByEmp?->positionName() ?? '-');
        $templateProcessor->setValue('department', $model->createdByEmp?->departmentName() ?? '-');
        $templateProcessor->setValue('topic', $model->topic);
        // ผู้อำนวยการ
        $dicrectorType = ($this->GetInfo()['director_type'] == 'รักษาการแทนผู้อำนวยการ' ? 'รักษาการแทนผู้อำนวยการ' : '');
        $templateProcessor->setValue('direc_fullname', $this->GetInfo()['director_fullname']);
        $templateProcessor->setValue('direc_position', $this->GetInfo()['director_position'] . $dicrectorType);
       try {
            $templateProcessor->setImg('direc_sign', ['src' => $this->GetInfo()['director']->signature(), 'size' => [150, 60]]);  // ลายมือผู้ตรวจสอบ
        } catch (\Throwable $th) {
            $templateProcessor->setValue('direc_sign', '...........................................');
        }

        if ($model->status == 'Approve') {
            $status = 'อนุมัติ';
        } else if ($model->status == 'Reject') {
            $status = 'ไม่อนุมัติ';
        } else {
            $status = 'รอการอนุมัติ';
        }
        $templateProcessor->setValue('status', $status);

        $filePath = Yii::getAlias('@webroot') . '/msword/results/development/' . $result_name;
        $templateProcessor->saveAs($filePath);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        if (file_exists($filePath)) {
            return $this->Show($result_name);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }
        return $this->redirect('https://docs.google.com/viewerng/viewer?url=' . Url::base('https') . '/msword/results/leave/' . $result_name);
    }

    // ใบตอบรับเป็นวิทยากร
    public function actionFormAcademic($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        $title = 'แบบฟอร์มตอบรับวิทยากร';
        $result_name = $title . '-' . $model->id . '.docx';
        $word_name = $title . '.docx';

        @unlink(Yii::getAlias('@webroot') . '/msword/results/development/' . $result_name);
        $templateProcessor = new Processor(Yii::getAlias('@webroot') . '/msword/development/' . $word_name);  // เลือกไฟล์ template ที่เราสร้างไว้

        $templateProcessor->setValue('org_fullname', $this->GetInfo()['org_fullname']);
        $templateProcessor->setValue('org_name', $this->GetInfo()['company_name']);
        $templateProcessor->setValue('address', $this->GetInfo()['address']);
        $templateProcessor->setValue('phone', $this->GetInfo()['phone']);
        $templateProcessor->setValue('doc_number', $this->GetInfo()['doc_number']);
        $templateProcessor->setValue('document_number', $model->document?->doc_number ?? '-');
        $templateProcessor->setValue('document_date', ThaiDateHelper::formatThaiDate($model->document?->doc_date) ?? '-');
        $templateProcessor->setValue('doc_date', ThaiDateHelper::formatThaiDate(date('Y-m-d')));
        $templateProcessor->setValue('dev_date', ThaiDateHelper::formatThaiDateRange($model->date_start, $model->date_end));
        $templateProcessor->setValue('location', $model->data_json['location'] ?? '-');
        $templateProcessor->setValue('governor', $this->GetInfo()['governor']);
        $templateProcessor->setValue('fullname', $model->createdByEmp?->fullname ?? '-');
        $templateProcessor->setValue('position', $model->createdByEmp?->positionName() ?? '-');
        $templateProcessor->setValue('department', $model->createdByEmp?->departmentName() ?? '-');
        $templateProcessor->setValue('topic', $model->topic);

            // ลสยมือผู้ปฏิบัติงานแทน
        try {
            $templateProcessor->setImg('emp_sign', ['src' => $model->createdByEmp->signature(), 'size' => [150, 50]]);
        } catch (\Throwable $th) {
            $templateProcessor->setValue('emp_sign', '........................................');
        }
        
        // ผู้อำนวยการ
        $dicrectorType = ($this->GetInfo()['director_type'] == 'รักษาการแทนผู้อำนวยการ' ? 'รักษาการแทนผู้อำนวยการ' : '');
        $templateProcessor->setValue('direc_fullname', $this->GetInfo()['director_fullname']);
        $templateProcessor->setValue('direc_position', $this->GetInfo()['director_position'] . $dicrectorType);
       try {
            $templateProcessor->setImg('direc_sign', ['src' => $this->GetInfo()['director']->signature(), 'size' => [150, 60]]);  // ลายมือผู้ตรวจสอบ
        } catch (\Throwable $th) {
            $templateProcessor->setValue('direc_sign', '...........................................');
        }

        $filePath = Yii::getAlias('@webroot') . '/msword/results/development/' . $result_name;
        $templateProcessor->saveAs($filePath);  // สั่งให้บันทึกข้อมูลลงไฟล์ใหม่
        if (file_exists($filePath)) {
            return $this->Show($result_name);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }
        return $this->redirect('https://docs.google.com/viewerng/viewer?url=' . Url::base('https') . '/msword/results/leave/' . $result_name);
    }

    // ดึงค่ากน่วยงาน
    protected function GetInfo()
    {
        $info = SiteHelper::getInfo();
        return [
            'org_fullname' => $info['company_name'] . ' ' . $info['address'],  // ที่อยู่
            'company_name' => $info['company_name'],  // ชื่อหน่วยงาน
            'phone' => $info['phone'],  // ชื่อหน่วยงาน
            'doc_number' => $info['doc_number'],  // ชื่อหน่วยงาน
            'governor' => 'ผู้ว่าราชการจังหวัด' . $info['province'],  // ผุ้ว่าราชการ
            'leader_fullname' => $info['leader_fullname'],  //
            'leader_position' => $info['leader_position'],  //
            'address' => $info['address'],  // ที่อยู่
            'phone' => $info['phone'],  // โทรศัพท์
            'province' => $info['province'],  // ที่อยู่
            'director' => $info['director'],
            'director_name' => $info['director_name'],  // ชื่อผู้บริหาร ผอ.
            'director_fullname' => SiteHelper::viewDirector()['fullname'],  // ชื่อผู้บริหาร ผอ.
            'director_position' => $info['director_position'],  // ตำแหน่งของ ผอ.
            'director' => $info['director'],  // ตำแหน่งของ ผอ.
            'director_type' => $info['director_type']  // ประเภทตำแหน่งของ ผอ.
        ];
    }

    public static function CreateDir($folderName)
    {
        $downloadPath = Yii::getAlias('@app') . '/web/downloads';
        if ($downloadPath != null) {
            BaseFileHelper::createDirectory($downloadPath, 0777);
        }

        if ($folderName != null) {
            $basePath = Yii::getAlias('@app') . '/web/msword/results/development';
            BaseFileHelper::createDirectory($basePath . $folderName, 0777);
        }
        return;
    }

    private function Show($filename)
    {
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'title' => Html::a('<i class="fa-solid fa-cloud-arrow-down"></i> ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/msword/results/development/' . $filename), ['class' => 'btn btn-primary text-center mb-3', 'target' => '_blank', 'onclick' => 'return closeModal()']),
                'content' => $this->renderAjax('show', ['filename' => $filename]),
            ];
        } else {
            echo '<p>';
            echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . $filename), ['class' => 'btn btn-info']);  // สร้าง link download
            echo '</p>';
            // echo '<iframe src="https://view.officeapps.live.com/op/embed.aspx?src='.Url::to(Yii::getAlias('@web').'/msword/temp/asset_result.docx', true).'&embedded=true"  style="position: absolute;width:99%; height: 90%;border: none;"></iframe>';
            echo '<iframe src="https://docs.google.com/viewerng/viewer?url=' . Url::to(Yii::getAlias('@web') . $filename, true) . '&embedded=true"  style="position: absolute;width:100%; height: 100%;border: none;"></iframe>';
        }
    }
}
