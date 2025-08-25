<?php

namespace app\modules\booking\controllers;

use Yii;
use yii\web\Response;
use setasign\Fpdi\Fpdi;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use Imagine\Filter\Basic\Save;
use app\models\CategoriseSearch;
use yii\web\NotFoundHttpException;

/**
 * LeaveController implements the CRUD actions for Categorise model.
 */
class VehicleFormLayoutController extends Controller
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


    public function actionOrigin()
    {
        return $this->render('origin');
    }
    public function actionIndex()
    {
        $model = Categorise::find()->where(['name' => 'vehicle_layout_form'])->one();
        if (!$model) {
            $model = new Categorise(['name' => 'vehicle_layout_form']);
            $model->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $model->data_json = $this->getDefautlayout();
            $model->save();
        }
        // $model->data_json =  $this->getDefautlayout();

        return $this->render('index', ['model' => $model]);
    }

    public function getDefautlayout()
    {
        $fontFamily = "TH Sarabun New";
        $fontWeight = "bold";
        $fontSize = 20;
        

        return [
            [
                'field' => 'director',
                'x' => 100,
                'y' => 100,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'date',
                'x' => 526,
                'y' => 150,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'fullname',
                'x' => 275,
                'y' => 278,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'fullname_',
                'x' => 458,
                'y' => 526,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'position',
                'x' => 275,
                'y' => 278,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'department',
                'x' => 264,
                'y' => 309,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'phone',
                'x' => 622,
                'y' => 310,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'location',
                'x' => 273,
                'y' => 340,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'passenger',
                'x' => 659,
                'y' => 340,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],

            [
                'field' => 'reason',
                "x" => 146,
                "y" => 370,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'date_start',
                "x" => 232,
                "y" => 397,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'date_end',
                "x" => 233,
                "y" => 426,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'time_start',
                "x" => 709,
                "y" => 402,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'time_end',
                "x" => 711,
                "y" => 431,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'vehicle_type',
                "x" => 373,
                "y" => 681,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'license_plate',
                "x" => 619,
                "y" => 681,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'driver_name',
                "x" => 159,
                "y" => 714,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'driver_name_',
                "x" => 525,
                "y" => 1102,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'driver_leader_name',
                "x" => 482,
                "y" => 836,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'leader_name',
                "x" => 469,
                "y" => 618,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'mileage_start',
                'x' => 254,
                'y' => 1065,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'mileage_end',
                'x' => 254,
                'y' => 1104,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight
            ],
            [
                'field' => 'emp_signature',
                'x' => 444,
                'y' => 462,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight,
                "scale" =>  0.1
            ],
            [
                'field' => 'driver_signature',
                'x' => 435,
                'y' => 560,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight,
                "scale" =>  0.1
            ],
            [
                'field' => 'leader_signature',
                'x' => 435,
                'y' => 560,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight,
                "scale" =>  0.1
            ],
            [
                'field' => 'director_signature',
                'x' => 482,
                'y' => 1026,
                'fontSize' => $fontSize,
                "fontFamily" => $fontFamily,
                "fontWeight" => $fontWeight,
                "scale" =>  0.1
            ]

        ];
    }

    public function actionGetLayout($formName)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $layout = Categorise::find()->where(['name' => $formName])->one();

        if (!$layout) {
            return $this->getDefautlayout();
        } else {
            $data = [];
            foreach ($layout->data_json as $obj) {
                $data[] = [
                    'field' => $obj['field'],
                    'x' => (int)$obj['x'],
                    'y' => (int)$obj['y'],
                    'fontSize' => $obj['fontSize'] ?? 20,
                    'fontFamily' => $obj['fontFamily'] ?? 'TH Sarabun New',
                    'fontWeight' => $obj['fontWeight'] ?? 'bold',
                    'scale' => isset($obj['scale']) ? (float)$obj['scale'] : 0.1,
                ];
            }
            return $data;
        }

        // return $layout->data_json;
    }

    public function actionSaveLayout()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data = Yii::$app->request->post();

        $formName = $data['name'] ?? 'vehicle_layout_form';
        $layoutData = $data['layout'];
        $layout = Categorise::findOne(['name' => $formName]);
        if (!$layout) {
            $layout = new Categorise();
            $layout->name = $formName;
            $layout->created_at = date('Y-m-d H:i:s');
        }
        $layout->data_json = $layoutData;
        // $layout->updated_at = date('Y-m-d H:i:s');
        $layout->save();

        return ['success' => true];
    }




    /**
     * Displays a single Categorise model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Categorise model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Categorise(['name' => 'leave_form']);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->code = $model->NextId();
                $model->save();
                return [
                    'status' => 'success',
                ];
            }
        } else {
            $model->loadDefaultValues();
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

    /**
     * Updates an existing Categorise model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Categorise model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }


     public function actionExport($formName)
    {
        // ดึง layout จาก DB
        $layout = Categorise::find()->where(['name' => $formName])->one();
        if (!$layout) {
            throw new \yii\web\NotFoundHttpException("Layout not found");
        }

        $fields = json_decode($layout->data_json, true);

        // โหลด PDF template
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile(Yii::getAlias('@webroot/files/sample.pdf'));
        $tplId = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplId);

        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplId);

        // ใส่ข้อมูลจริง (สมมุติว่ามาจาก DB หรือฟอร์ม)
        $data = [
            'fullname' => 'นายสมชาย ใจดี',
            'address'  => '123/45 ถ.ประชาอุทิศ กรุงเทพฯ',
            'tel'      => '081-2345678',
        ];

        foreach ($fields as $obj) {
            $text = $data[$obj['field']] ?? '';

            $pdf->SetFont(
                $obj['fontFamily'] ?? 'Helvetica',
                ($obj['fontWeight'] ?? 'bold') === 'bold' ? 'B' : '',
                $obj['fontSize'] ?? 14
            );

            $pdf->SetXY((int)$obj['x'], (int)$obj['y']);
            $pdf->Write(10, $text);
        }

        // บันทึกเป็นไฟล์
        $filePath = Yii::getAlias('@webroot/files/output.pdf');
        $pdf->Output($filePath, 'F');

        return Yii::$app->response->sendFile($filePath);
    }
    

    /**
     * Finds the Categorise model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Categorise the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Categorise::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
