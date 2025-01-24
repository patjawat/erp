<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\LineNotify;
use app\components\SiteHelper;
use ruskid\csvimporter\CSVReader;
use yii\validators\DateValidator;
use yii\web\NotFoundHttpException;
use ruskid\csvimporter\CSVImporter;
use app\modules\hr\models\Employees;
use app\modules\hr\models\UploadCsv;
use app\modules\hr\models\Organization;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use app\modules\hr\models\EmployeesSearch;
use PhpOffice\PhpSpreadsheet\Style\Border;
use app\modules\inventory\models\Warehouse;
use app\modules\inventory\models\StockEvent;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use app\modules\inventory\models\StockSummary;
use ruskid\csvimporter\MultipleImportStrategy;
use app\modules\hr\models\EmployeeDetailSearch;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * EmployeesController implements the CRUD actions for Employees model.
 */
class EmployeesController extends Controller
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
     * Lists all Employees models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new EmployeesSearch([
            'branch' => 'MAIN'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $notStatusParam = $this->request->get('not-status');

        if (isset($searchModel->user_register) && $searchModel->user_register == 0) {
            $dataProvider->query->andWhere(['user_id' => 0]);
        }
        if (isset($searchModel->user_register) && $searchModel->user_register == 1) {
            $dataProvider->query->andWhere(['!=','user_id',0]);
        }

        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $searchModel->q],
            ['like', 'email', $searchModel->q],
            ['like', 'fname', $searchModel->q],
            ['like', 'lname', $searchModel->q],
        ]);

        $dataProvider->query->andWhere(['NOT', ['id' => 1]]);

        // ค้นหาคามกลุ่มโครงสร้าง
        $org1 = Organization::findOne($searchModel->q_department);
        // ถ้ามีกลุ่มย่อย
        if (isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
            FROM tree t1
            JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
            WHERE t2.name = :name;';
            $querys = Yii::$app
                ->db
                ->createCommand($sql)
                ->bindValue(':name', $org1->name)
                ->queryAll();
            $arrDepartment = [];
            foreach ($querys as $tree) {
                $arrDepartment[] = $tree['id'];
            }
            // Yii::$app->response->format = Response::FORMAT_JSON;
            // $dataDepartment =  ArrayHelper::merge($arrDepartment,$org1->lft);
            // $arrDepartment;
            // $arrDepartment[]  = $org1->lft;

            if (count($arrDepartment) > 0) {
                $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
            } else {
                $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
            }
        } else {
            $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        }
        // จบการค้นหา

        if (!$searchModel->status && $searchModel->all_status == 0) {
            $dataProvider->query->andWhere(['status' => 1]);
        }

        if (!$searchModel->status && $searchModel->all_status == 0) {
            if ($notStatusParam) {
                $dataProvider->query->andWhere(['is', 'status', new \yii\db\Expression('null')]);
            } else {
                // $dataProvider->query->andWhere(['NOT', ['status' => [5, 7, 8]]]);
            }
        }

        $dataProvider->query->andWhere(new \yii\db\Expression("CONCAT(fname,' ', lname) LIKE :term", [':term' => '%' . $searchModel->fullname . '%']));

        // ค้นหาตามอายุ
        if ($searchModel->range1 && !$searchModel->range2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('TIMESTAMPDIFF(YEAR, birthday, NOW()) = ' . $searchModel->range1));
        }
        // ค้นหาระหว่างช่วงอายุ
        if ($searchModel->range1 && $searchModel->range2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('TIMESTAMPDIFF(YEAR, birthday, NOW()) BETWEEN ' . $searchModel->range1 . ' AND ' . $searchModel->range2));
        }

        $dataProvider->pagination->pageSize = 16;

  

        $sql = 'SELECT count(id) as total  FROM `employees` WHERE `status` IS NULL';
        $notStatus = Yii::$app->db->createCommand($sql)->queryScalar();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'notStatus' => $notStatus

        ]);
    }

    public function actionSendMsg($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $userId = $model->user->line_id;
        // return LineNotify::sendPushMessage($lineId,'Hello');
        
        
        $result = Yii::$app->lineNotify->sendLeave($userId);
        return $result;
    }
    /**
     * Displays a single Employees model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $name = $this->request->get('name');
        $model = $this->findModel($id);

        $searchModel = new EmployeeDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['emp_id' => $model->id, 'name' => $name]);
        $dataProvider->query->orderBy(
            new \yii\db\Expression("JSON_EXTRACT(data_json, '\$.date_start') desc")
        );
        $dataProvider->pagination->pageSize = 8;

        return $this->render('view', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'name' => $name,
        ]);
    }

    /**
     * Creates a new Employees model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Employees([
            'user_id' => 0,
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // return $this->redirect(['view', 'id' => $model->id]);
                if ($model->save()) {
                    return [
                        'status' => 'success',
                        'container' => '#hr-container',
                    ];
                } else {
                    return false;
                }
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAjax) {
            return [
                'title' => '<i class="fa-regular fa-pen-to-square"></i> สร้างใหม่',
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

    public function actionUploadBasicDoc($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        return [
            'title' => '<i class="fa-solid fa-file-circle-check"></i>  แบบสารสนเทศเบื้องต้น',
            'content' => $this->renderAjax('upload_basic_doc', ['model' => $model]),
        ];
    }

    /**
     * Updates an existing Employees model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model_old_data_json = $model->data_json;
        $model->join_date = AppHelper::DateFormDb($model->join_date);
        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->join_date) {
                $model->join_date = AppHelper::DateToDb($model->join_date);
            }

            $model->data_json = ArrayHelper::merge($model_old_data_json, $model->data_json);
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข',
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ]),
            ];
        } else {
            $model->cid = AppHelper::cidFormat($model->cid);
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Employees model.
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

    /**
     * Finds the Employees model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Employees the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Employees::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionEducationCreate($id)
    {
        $model = $this->findModel($id);
        // $education = $model->data_json['education'];

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                // return $model->data_json['education'];
                $stack = ['orange', 'banana'];
                array_push($model->education, 'apple', 'raspberry');
                return $stack;
                // return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'การศึกษา',
                'content' => $this->renderAjax('_form_education', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('_form_education', [
                'model' => $model,
            ]);
        }
    }

    public function actionImportCsv()
    {
        $model = new UploadCsv();
        $basePath = Yii::getAlias('@app/web/import-csv/');
        AppHelper::CreateDir($basePath);

        $error = [];
        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            $model->file->saveAs($basePath . $model->file->name);

            $importer = new CSVImporter();
            $filename = $basePath . $model->file->name;
            $importer->setData(new CSVReader([
                'filename' => $filename,
                'tableName' => Employees::tableName(),
                'fgetcsvOptions' => [
                    'delimiter' => ';',
                ],
            ]));
            for ($x = 1; $x <= count($importer->getData()); $x++) {
                $data_check_error = $importer->getData()[$x][0];
                $data_check_error = explode(',', $data_check_error);
                if (!empty($data_check_error[0])) {
                    if (count($data_check_error) != 10) {
                        array_push($error, 'ข้อมูลไม่ครบถ้วน');
                    }
                    if (null != Employees::findOne(['cid' => $data_check_error[0]])) {
                        array_push($error, '[ ' . $x . ' ]' . ' CID ' . $data_check_error[0] . ' อยู่ในระบบแล้ว');
                    }
                    if (strlen($data_check_error[0]) != 13) {
                        array_push($error, '[ ' . $x . ' ]' . ' CID ' . $data_check_error[0] . ' ไม่ถูกต้อง (ต้องมี 13 หลัก)');
                    }
                    $validator = new DateValidator();
                    $validator->format = 'yyyy-MM-dd';
                    if ($validator->validate($data_check_error[5])) {
                    } else {
                        array_push($error, '[ ' . $x . ' ]' . ' วันเกิด ' . $data_check_error[5] . ' ไม่ถูกต้อง (1980-01-01)');
                    }
                    if (strlen($data_check_error[6]) == 9 and $data_check_error[6][0] != 0) {
                    } elseif (strlen($data_check_error[6]) != 10) {
                        array_push($error, '[ ' . $x . ' ]' . ' เบอร์โทรศัพท์ ' . $data_check_error[6] . ' ไม่ถูกต้อง (ต้องมี 10 หลัก)');
                    }
                }
            }
            if (empty($error)) {
                $numberRowsAffected = $importer->import(new MultipleImportStrategy([
                    'tableName' => Employees::tableName(),  // change your model names accordingly
                    'configs' => [
                        [
                            'attribute' => 'user_id',
                            'value' => function ($data) {
                                return 0;
                            },
                        ],
                        [
                            'attribute' => 'cid',
                            'allowEmptyValues' => false,
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[0];
                            },
                        ],
                        [
                            'attribute' => 'gender',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[1];
                            },
                        ],
                        [
                            'attribute' => 'prefix',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[2];
                            },
                        ],
                        [
                            'attribute' => 'fname',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[3];
                            },
                        ],
                        [
                            'attribute' => 'lname',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[4];
                            },
                        ],
                        [
                            'attribute' => 'birthday',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[5];
                            },
                        ],
                        [
                            'attribute' => 'phone',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[6];
                            },
                        ],
                        [
                            'attribute' => 'email',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[7];
                            },
                        ],
                        [
                            'attribute' => 'address',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[8];
                            },
                        ],
                        [
                            'attribute' => 'zipcode',
                            'value' => function ($data) {
                                $data = explode(',', $data[0]);
                                return $data[9];
                            },
                        ],
                        [
                            'attribute' => 'position_name',
                            'value' => function ($data) {
                                return 0;
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'value' => function ($data) {
                                return 0;
                            },
                        ],
                    ],
                ]));
                unlink($filename);
                Yii::$app->session->setFlash('data', [
                    'status' => true,
                    'error' => $error,
                ]);
                return $this->redirect(['import-status']);
            } else {
                unlink($filename);
                Yii::$app->session->setFlash('data', [
                    'status' => false,
                    'error' => $error,
                ]);
                return $this->redirect(['import-status']);
            }

            // return var_dump($importer->getData());
        } else {
            return $this->render('import_csv',
                ['model' => $model,
                    'error' => $error,
                    'success' => false]);
        }
    }

    public function actionImportStatus()
    {
        $data = Yii::$app->session->getFlash('data', []);
        $status = isset($data['status']) ? $data['status'] : false;
        $error = isset($data['error']) ? $data['error'] : [];
        return $this->render('import-status', [
            'status' => $status,
            'error' => $error
        ]);
    }

    public function actionExportExcel()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $searchModel = new EmployeesSearch([
            'branch' => 'MAIN'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $notStatusParam = $this->request->get('not-status');

        if (isset($searchModel->user_register) && $searchModel->user_register == 0) {
            $dataProvider->query->andWhere(['user_id' => 0]);
        }
        if (isset($searchModel->user_register) && $searchModel->user_register == 1) {
            $dataProvider->query->andWhere(['!=','user_id',0]);
        }

        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'cid', $searchModel->q],
            ['like', 'email', $searchModel->q],
            ['like', 'fname', $searchModel->q],
            ['like', 'lname', $searchModel->q],
        ]);

        $dataProvider->query->andWhere(['NOT', ['id' => 1]]);

        // ค้นหาคามกลุ่มโครงสร้าง
        $org1 = Organization::findOne($searchModel->q_department);
        // ถ้ามีกลุ่มย่อย
        if (isset($org1) && $org1->lvl == 1) {
            $sql = 'SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.name, t1.icon
            FROM tree t1
            JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
            WHERE t2.name = :name;';
            $querys = Yii::$app
                ->db
                ->createCommand($sql)
                ->bindValue(':name', $org1->name)
                ->queryAll();
            $arrDepartment = [];
            foreach ($querys as $tree) {
                $arrDepartment[] = $tree['id'];
            }
            // Yii::$app->response->format = Response::FORMAT_JSON;
            // $dataDepartment =  ArrayHelper::merge($arrDepartment,$org1->lft);
            // $arrDepartment;
            // $arrDepartment[]  = $org1->lft;

            if (count($arrDepartment) > 0) {
                $dataProvider->query->andWhere(['in', 'department', $arrDepartment]);
            } else {
                $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
            }
        } else {
            $dataProvider->query->andFilterWhere(['department' => $searchModel->q_department]);
        }
        // จบการค้นหา

        if (!$searchModel->status && $searchModel->all_status == 0) {
            $dataProvider->query->andWhere(['status' => 1]);
        }

        if (!$searchModel->status && $searchModel->all_status == 0) {
            if ($notStatusParam) {
                $dataProvider->query->andWhere(['is', 'status', new \yii\db\Expression('null')]);
            } else {
                // $dataProvider->query->andWhere(['NOT', ['status' => [5, 7, 8]]]);
            }
        }

        $dataProvider->query->andWhere(new \yii\db\Expression("CONCAT(fname,' ', lname) LIKE :term", [':term' => '%' . $searchModel->fullname . '%']));

        // ค้นหาตามอายุ
        if ($searchModel->range1 && !$searchModel->range2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('TIMESTAMPDIFF(YEAR, birthday, NOW()) = ' . $searchModel->range1));
        }
        // ค้นหาระหว่างช่วงอายุ
        if ($searchModel->range1 && $searchModel->range2) {
            $dataProvider->query->andWhere(new \yii\db\Expression('TIMESTAMPDIFF(YEAR, birthday, NOW()) BETWEEN ' . $searchModel->range1 . ' AND ' . $searchModel->range2));
        }

        $dataProvider->pagination->pageSize = 30000;

  

        $sql = 'SELECT count(id) as total  FROM `employees` WHERE `status` IS NULL';
        $notStatus = Yii::$app->db->createCommand($sql)->queryScalar();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(8);
        $sheet->getColumnDimension('D')->setWidth(9);
        $sheet->getColumnDimension('E')->setWidth(13);
        $sheet->getColumnDimension('F')->setWidth(13);
        $sheet->getColumnDimension('G')->setWidth(13);
        $sheet->getColumnDimension('H')->setWidth(8);
        $sheet->getColumnDimension('I')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(50);
        $sheet->getColumnDimension('K')->setWidth(13);
        $sheet->getColumnDimension('L')->setWidth(13);
        $sheet->getColumnDimension('M')->setWidth(18);
        $sheet->getColumnDimension('N')->setWidth(13);
        $sheet->getColumnDimension('O')->setWidth(13);
        $sheet->getColumnDimension('P')->setWidth(15);
        $sheet->getColumnDimension('Q')->setWidth(13);
        $sheet->getColumnDimension('R')->setWidth(25);
        $sheet->getColumnDimension('S')->setWidth(13);
        $sheet->getColumnDimension('T')->setWidth(13);
        $sheet->getColumnDimension('U')->setWidth(25);
        $sheet->getColumnDimension('V')->setWidth(18);
        $sheet->getColumnDimension('W')->setWidth(17);
        $sheet->getColumnDimension('X')->setWidth(18);
        $sheet->getColumnDimension('Y')->setWidth(18);
        $sheet->getColumnDimension('Z')->setWidth(13);
        
        $setHeader = 'A1:Z1';
        $sheet->getStyle($setHeader)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($setHeader)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($setHeader)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($setHeader)->getBorders()->getAllBorders()->setColor(new Color(Color::COLOR_BLACK));
        $sheet->getStyle($setHeader)->getFill()->getStartColor()->setRGB('8DB4E2');
        $sheet->getStyle('A1:Z1')->getFont()->setBold(true)->setItalic(false);
        // ตั้งชื่อแผ่นงาน
        $sheet->setTitle('ข้อมูลบุคลากร');
        $sheet->setCellValue('A1', 'ลำดับ');
        $sheet->setCellValue('B1', 'เลขบัตรประชาชน');
        $sheet->setCellValue('C1', 'เพศ');
        $sheet->setCellValue('D1', 'คำนำหน้า');
        $sheet->setCellValue('E1', 'ชื่อ');
        $sheet->setCellValue('F1', 'นามสกุล');
        $sheet->setCellValue('G1', 'วันเกิด');
        $sheet->setCellValue('H1', 'อายุ');
        $sheet->setCellValue('I1', 'Email');
        $sheet->setCellValue('J1', 'ที่อยู่');
        $sheet->setCellValue('K1', 'รหัสไปรษณีย์');
        $sheet->setCellValue('L1', 'วันที่เริ่มงาน');
        $sheet->setCellValue('M1', 'อายุราชการ');
        $sheet->setCellValue('N1', 'เกษียณ');
        $sheet->setCellValue('O1', 'คงเหลือ/ปี');
        $sheet->setCellValue('P1', 'หมายเลขโทรศัพท์');
        $sheet->setCellValue('Q1', 'สถานะ');
        $sheet->setCellValue('R1', 'ตำแหน่งปัจจุบัน');
        $sheet->setCellValue('S1', 'วันที่แต่งตั้ง');
        $sheet->setCellValue('T1', 'เลขตำแหน่ง');
        $sheet->setCellValue('U1', 'ประเภท');
        $sheet->setCellValue('V1', 'ระดับตำแหน่ง');
        $sheet->setCellValue('W1', 'ความเชี่ยวชาญ');
        $sheet->setCellValue('X1', 'ประเภท/กลุ่มงาน');
        $sheet->setCellValue('Y1', 'เงินเดือน');
        $StartRowSheet = 2;
        // $dataItems = $this->findModelItem($params);
        foreach ($dataProvider->getModels() as $key => $value) {
            $numRow = $StartRowSheet++;
            // $a[] = ['B' => 'B'.$StartRow++];
            $sheet->setCellValue('A' . $numRow, $numRow);
            $sheet->setCellValue('B' . $numRow, $value->cid);
            $sheet->getStyle('B' . $numRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
            $sheet->setCellValue('C' . $numRow, $value->gender);
            $sheet->getStyle('C' . $numRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->setCellValue('D' . $numRow, $value->prefix);
            $sheet->setCellValue('E' . $numRow, $value->fname);
            $sheet->setCellValue('F' . $numRow, $value->lname);
            $sheet->setCellValue('G' . $numRow, Yii::$app->thaiFormatter->asDate(AppHelper::DateToDb($value->birthday), 'php:d/m/Y'));
            $sheet->setCellValue('H' . $numRow, $value->age_y);
            $sheet->setCellValue('I' . $numRow, $value->email);
            $sheet->setCellValue('J' . $numRow, $value->address);
            $sheet->setCellValue('K' . $numRow, $value->zipcode);
            $sheet->setCellValue('L' . $numRow, Yii::$app->thaiFormatter->asDate($value->joinDate(), 'php:d/m/Y'));
            $sheet->setCellValue('M' . $numRow, $value->workLife()['full']);
            $sheet->setCellValue('N' . $numRow, Yii::$app->thaiFormatter->asDate($value->year60(), 'php:d/m/Y'));
            $sheet->setCellValue('O' . $numRow, $value->leftYear60());
            $sheet->setCellValue('P' . $numRow, $value->phone);
            $sheet->setCellValue('Q' . $numRow, $value->statusName());
            $sheet->setCellValue('R' . $numRow, $value->positionName(['icon' => false]));
            $sheet->setCellValue('S' . $numRow, Yii::$app->thaiFormatter->asDate($value->nowPosition()['date_start'], 'php:d/m/Y'));
            $sheet->setCellValue('T' . $numRow, $value->nowPosition()['position_number']);
            $sheet->setCellValue('U' . $numRow, $value->positionTypeName());
            $sheet->setCellValue('V' . $numRow, $value->positionLevelName());
            $sheet->setCellValue('W' . $numRow, $value->expertiseName());
            $sheet->setCellValue('X' . $numRow, $value->positionGroupName());
            $sheet->setCellValue('Y' . $numRow, $value->salary ? number_format($value->salary, 2) : '');
        }

        $writer = new Xlsx($spreadsheet);
        $filePath = Yii::getAlias('@webroot') . '/downloads/บุคลากร.xlsx';
        $writer->save($filePath);  // สร้าง excel

        // if (file_exists($output_file)) {  // ตรวจสอบว่ามีไฟล์ หรือมีการสร้างไฟล์ แล้วหรือไม่
        //     echo Html::a('ดาวน์โหลดเอกสาร', Url::to(Yii::getAlias('@web') . '/myData.xlsx'), ['class' => 'btn btn-info', 'target' => '_blank']);  // สร้าง link download
        // }

        if (file_exists($filePath)) {
            return Yii::$app->response->sendFile($filePath);
        } else {
            throw new \yii\web\NotFoundHttpException('The file does not exist.');
        }
    }
}
