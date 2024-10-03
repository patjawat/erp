<?php

namespace app\modules\hr\controllers;

use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\hr\models\EmployeeDetailSearch;
use app\modules\hr\models\Employees;
use app\modules\hr\models\EmployeesSearch;
use app\modules\hr\models\Organization;
use app\modules\hr\models\UploadCsv;
use ruskid\csvimporter\CSVImporter;
use ruskid\csvimporter\CSVReader;
use ruskid\csvimporter\MultipleImportStrategy;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\validators\DateValidator;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use Yii;

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
        $searchModel = new EmployeesSearch();
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
}
