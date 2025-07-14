<?php

namespace app\modules\helpdesk\models;

use Yii;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use app\components\CategoriseHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\filemanager\models\Uploads;
use app\modules\inventory\models\StockEvent;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * This is the model class for table "helpdesk".
 *
 * @property int         $id
 * @property string|null $ref
 * @property string|null $code
 * @property string|null $date_start
 * @property string|null $date_end
 * @property string|null $name       ชื่อการเก็บข้อมูล
 * @property string|null $title      รายการ
 * @property string|null $data_json  การเก็บข้อมูลชนิด JSON
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null    $created_by ผู้สร้าง
 * @property int|null    $updated_by ผู้แก้ไข
 */
class Helpdesk extends Yii\db\ActiveRecord
{
    public $q;
    public $asset_name;
    public $date_between;
    public $urgency;
    public $asset_type_name;
    public $auth_item;
    public $date_filter;

    public static function tableName()
    {
        return 'helpdesk';
    }

    public function rules()
    {
        return [
            [['emp_id', 'category_id', 'date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'status', 'rating', 'repair_group', 'move_out', 'thai_year', 'q', 'date_between', 'urgency', 'auth_item','date_filter'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['ref', 'code', 'name', 'title'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'Ref',
            'code' => 'Code',
            'date_start' => 'Date Start',
            'date_end' => 'Date End',
            'name' => 'Name',
            'title' => 'Title',
            'thai_year' => 'ปีงบประมาณ',
            'move_out' => 'จำหน่าย',
            'data_json' => 'Data Json',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    public function beforeSave($insert)
    {
        $this->thai_year = AppHelper::YearBudget();

        return parent::beforeSave($insert);
    }

    public function UpdateStockYear() {}

    public function Upload($name)
    {
        return FileManagerHelper::FileUpload($this->ref, $name);
    }

    public function afterFind()
    {
        try {
            $this->asset_name = isset($this->asset->data_json['asset_name']) ? $this->asset->data_json['asset_name'] : null;
            $this->asset_type_name = isset($this->asset->data_json['asset_type_text']) ? $this->asset->data_json['asset_type_text'] : null;
        } catch (\Throwable $th) {
        }

        parent::afterFind();
    }

    // แสดงรูปภาพ
    public function ShowImg()
    {
        try {
            $model = Uploads::find()->where(['ref' => $this->ref, 'name' => 'repair'])->one();
            if ($model) {
                return FileManagerHelper::getImg($model->id);
            } else {
                $filepath = Yii::getAlias('@webroot') . '/img/placeholder-img.jpg';
                $type = pathinfo($filepath, PATHINFO_EXTENSION);
                $data = file_get_contents($filepath);
                return $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        } catch (\Throwable $th) {
            $filepath = Yii::getAlias('@webroot') . '/img/placeholder-img.jpg';
            $type = pathinfo($filepath, PATHINFO_EXTENSION);
            $data = file_get_contents($filepath);
            return $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    }

    // relation
    // Relationships
    public function getAsset()
    {
        return $this->hasOne(Asset::class, ['code' => 'code']);
    }

    public function getStockEvent()
    {
        return $this->hasOne(StockEvent::class, ['helpdesk_id' => 'id']);
    }

    public function getEmpTeam()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    // ผู้แจ่งซ่อม
    public function getUserReq($msg = null)
    {
        return UserHelper::getMe($msg);
        // try {
        //     $employee = Employees::find()->where(['user_id' => $this->created_by])->one();

        //     return [
        //         'avatar' => $employee->getAvatar(false),
        //         'department' => $employee->departmentName()
        //     ];
        // } catch (\Throwable $th) {
        //     return null;
        // }
    }

    // ประเภทของงานซ่อม
    public function RepairType(): array
{
    if (empty($this->data_json['send_type'])) {
        return [
            'title' => 'ไม่พบข้อมูล',
            'image' => '',
        ];
    }

    $imgSrc = '';
    if ($this->data_json['send_type'] === 'asset') {
        $imgSrc = isset($this->asset) && method_exists($this->asset, 'showImg') ? $this->asset->showImg()['image'] : '';
    } else {
        $imgSrc = method_exists($this, 'showImg') ? $this->showImg() : '';
    }

    return match ($this->data_json['send_type']) {
        'general' => [
            'title' => 'ซ่อมทั่วไป',
            'image' => Html::img($imgSrc, [
                'class' => 'avatar avatar-lg rounded-3',
            ])
        ],
        'asset' => [
            'title' => 'ซ่อมครุภัณฑ์',
            'image' => Html::img($imgSrc, [
                'class' => 'avatar rounded-3',
            ])
        ],
        default => [
            'title' => 'ไม่พบประเภทการซ่อม',
            'image' => '',
        ]
    };
}

    

    // ช่างเทคนิค แสดงตามชื่อกลุ่มที่ส่งมา
    public function listTecName()
    {
        $item_name = '';
        // ซ่อมบำรุง
        if ($this->repair_group == 1) {
            $item_name = 'technician';
            // 2 คือศูนย์คอมพิวเตอร์
        } elseif ($this->repair_group == 2) {
            $item_name = 'computer';
            // 3 คือศูนย์เครื่องมือแพทย์
        } elseif ($this->repair_group == 3) {
            $item_name = 'medical';
        } else {
            $item_name = 'technician';
        }

        $sql = "SELECT concat(emp.fname,' ',emp.lname) as fullname,emp.user_id FROM employees emp
        INNER JOIN user ON user.id = emp.user_id
        INNER JOIN auth_assignment auth ON auth.user_id = user.id
        where auth.item_name = :item_name";
        $querys = \Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue(':item_name', $item_name)
            ->queryAll();

        return ArrayHelper::map($querys, 'user_id', 'fullname');
    }


    // ช่างเทคนิค แสดงตามชื่อกลุ่มที่ส่งมา
    public static function TechnicianList($group)
    {
        $item_name = '';
        // ซ่อมบำรุง
        if ($group == 1) {
            $item_name = 'technician';
            // 2 คือศูนย์คอมพิวเตอร์
        } elseif ($group == 2) {
            $item_name = 'computer';
            // 3 คือศูนย์เครื่องมือแพทย์
        } elseif ($group == 3) {
            $item_name = 'medical';
        } else {
            $item_name = 'technician';
        }

        $employees = Employees::find()
        ->alias('emp')
        ->innerJoin('user', 'user.id = emp.user_id')
        ->innerJoin('auth_assignment auth', 'auth.user_id = user.id')
        ->where(['auth.item_name' => $item_name])
        ->all();
        return  $employees;

    }


    public function ListStatus()
    {
        $list = Categorise::find()
            ->andWhere(['name' => 'repair_status'])
            ->andWhere(['IN', 'code', [3, 4, 5]])
            ->all();
        return ArrayHelper::map($list, 'code', 'title');
    }
    // แสดงปีงบประมานทั้งหมด
    public function ListThaiYear()
    {
        $model = self::find()
            ->select('thai_year')
            ->groupBy('thai_year')
            ->orderBy(['thai_year' => SORT_DESC])
            ->asArray()
            ->all();

        $year = AppHelper::YearBudget();
        $isYear = [['thai_year' => $year]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
        // รวมข้อมูล
        $model = ArrayHelper::merge($isYear, $model);
        return ArrayHelper::map($model, 'thai_year', 'thai_year');
    }

    // ความเร่งด่วน
    public static function listUrgency()
    {
        return ArrayHelper::map(CategoriseHelper::Categorise('urgency'), 'code', 'title');
    }

    // การใช้คะแนน
    public static function listRating()
    {
        return ArrayHelper::map(CategoriseHelper::Categorise('rating'), 'code', 'title');
    }

    // หน่วยงานที่ส่งซ่อม
    public static function listRepairGroup()
    {
        return ArrayHelper::map(CategoriseHelper::Categorise('repair_group'), 'code', 'title');
    }

    // ผู้ร่วมดำเนินการ
    public function StackTeam()
    {
        try {
            $data = '';
            $data .= '<div class="avatar-stack">';
            foreach (Helpdesk::find()->where(['name' => 'repair_team', 'category_id' => $this->id])->all() as $key => $item) {
                $emp = Employees::findOne(['id' => $item->emp_id]);
                $data .= Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']);

                $data .= '</a>';
            }
            $data .= '</div>';
            return $data;
        } catch (\Throwable $th) {
        }
    }

    // หน่วยงานที่ส่งซ่อม
    public function viewRepairGroup()
    {
        $model = Categorise::findOne(['name' => 'repair_group', 'code' => $this->repair_group]);
        if ($model) {
            return $model->title;
        } else {
            return null;
        }
    }

    // สถานะงานซ่อม
    public static function listRepairStatus()
    {
        $model = Categorise::find()
            ->where(['name' => 'repair_status'])
            ->andWhere(['<>', 'code', 5])
            ->all();

        return ArrayHelper::map($model, 'code', 'title');
    }

    //  ภาพทีม
    public function avatarStack()
    {
        try {
            $data = '';
            $data .= '<div class="avatar-stack">';
            foreach ($this->data_json['join'] as $key => $avatar) {
                $emp = Employees::findOne(['user_id' => $avatar]);
                $data .= '<a href="javascript: void(0);" class="me-1" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-title="' . $emp->fullname . '">';
                $data .= Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']);
                $data .= '</a>';
            }
            $data .= '</div>';

            return $data;
        } catch (\Throwable $th) {
        }
    }

    public function showAvatarCreate()
    {
        try {
            $emp = Employees::findOne(['user_id' => $this->created_by]);

            // return Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']);
            return $emp->getAvatar(false, $emp->departmentName());
            // return $emp->getAvatar(false, $this->viewCreateDateTime());
            // code...
        } catch (\Throwable $th) {
            // throw $th;
        }
    }

    // แสดงสถานะ
    public function viewStatus()
    {
        try {
        if (isset($this->data_json['urgency'])) {
            $model = Categorise::findOne(['name' => 'repair_status', 'code' => $this->status]);

            if ($model->code == 1) {
                return '<span class="badge rounded-pill bg-danger-subtle"><i class="fa-solid fa-triangle-exclamation"></i> ' . $model->title . '</span>';
            }
            if ($model->code == 2) {
                return '<span class="badge rounded-pill bg-warning-subtle"><i class="fa-solid fa-user-check"></i> ' . $model->title . '</span>';
            }
            if ($model->code == 3) {
                return '<span class="badge rounded-pill bg-primary-subtle"><i class="fa-solid fa-person-digging text-primary"></i> ' . $model->title . '</span>';
            }
            if ($model->code == 4) {
                return '<span class="badge rounded-pill bg-success-subtle"><i class="fa-regular fa-circle-check text-success"></i> ' . $model->title . '</span>';
            }
            if ($model->code == 5) {
                return '<span class="badge rounded-pill bg-success-subtle"><i class="fa-solid fa-circle-minus text-danger"></i> ' . $model->title . '</span>';
            }
        }
                    //code...
                } catch (\Throwable $th) {
                   return 'ไม่ระบุ';
                }
    }

    public function UrgencyName()
    {
        $model = Categorise::findOne(['name' => 'urgency', 'code' => $this->data_json['urgency']]);
        if ($model) {
            return $model->title;
        }
    }

    // แสดงความเร่งด่วน
    public function viewUrgency()
    {
        try {
            if (isset($this->data_json['urgency'])) {
                $model = Categorise::findOne(['name' => 'urgency', 'code' => $this->data_json['urgency']]);
                if ($model->code == 1) {
                    return '<span class="badge text-bg-light fs-13"><i class="fa-solid fa-circle-exclamation"></i> ' . $model->title . '</span>';
                }
                if ($model->code == 2) {
                    return '<span class="badge text-bg-primary fs-13"><i class="fa-solid fa-circle-exclamation"></i> ' . $model->title . '</span>';
                }
                if ($model->code == 3) {
                    return '<span class="badge text-bg-warning fs-13"><i class="fa-solid fa-circle-exclamation"></i> ' . $model->title . '</span>';
                }
                if ($model->code == 4) {
                    return '<span class="badge text-bg-danger fs-13"><i class="fa-solid fa-circle-exclamation"></i> ' . $model->title . '</span>';
                }
            }
        } catch (\Throwable $th) {
            return null;
        }
    }

    // แสดงผู้ส่งซ่อม
    public function viewCreateUser()
    {
        $employee = Employees::find()->where(['user_id' => $this->created_by])->one();
        $msg = $employee->departmentName() . ' โทร ' . $employee->phone;
        return $employee->getAvatar(false, $msg);
    }

    // แสดงวันที่ส่งซ่อม
    public function viewCreateDate()
    {
        return \Yii::$app->thaiFormatter->asDate($this->created_at, 'long');
    }

    public function viewCreateDateTime()
    {
        return Yii::$app->thaiDate->toThaiDate($this->created_at, true, false);
    }

    // แสดงวันที่รับเรื่อง
    public function viewAccetpTime()
    {
        return \Yii::$app->thaiFormatter->asDateTime($this->data_json['accept_time'], 'medium');
        try {
        } catch (\Throwable $th) {
            // throw $th;
        }
    }

    // แสดงวันที่รับเรื่อง
    public function viewStartJob()
    {
        try {
            return \Yii::$app->thaiFormatter->asDateTime($this->data_json['start_job'], 'medium');
        } catch (\Throwable $th) {
            // throw $th;
        }
    }

    // แสดงวันที่แล้วสร็จ
    public function viewEndJob()
    {
        try {
            return \Yii::$app->thaiFormatter->asDateTime($this->data_json['end_job'], 'medium');
        } catch (\Throwable $th) {
        }
    }

    // วันที่แสดงความคิดเห็น
    public function viewCommentDate()
    {
        try {
            return \Yii::$app->thaiFormatter->asDateTime($this->data_json['comment_date'], 'medium');
        } catch (\Throwable $th) {
        }
    }

    public function listUserJob()
    {
        $sql = "SELECT 
    x3.*, 
    ROUND(((x3.rating_user / x3.total_user) * 100), 0) AS p
FROM (
    SELECT 
        x1.*,
        (
            SELECT COUNT(h.id)
            FROM helpdesk h
            WHERE 
                h.name = 'repair_team'
             	AND h.emp_id = x1.emp_id

        ) AS rating_user
    FROM (
        SELECT 
            DISTINCT e.user_id, 
            e.id AS emp_id,
            CONCAT(e.fname, ' ', e.lname) AS fullname,
            (
                SELECT COUNT(DISTINCT e2.id)
                FROM employees e2
                INNER JOIN auth_assignment a2 ON a2.user_id = e2.user_id
            ) AS total_user
        FROM employees e
        INNER JOIN auth_assignment a ON a.user_id = e.user_id
        WHERE a.item_name = :auth_item
    ) AS x1
    GROUP BY x1.user_id
) AS x3 ORDER BY p DESC;
                    ";             
        return Yii::$app
            ->db
            ->createCommand($sql)
            // ->bindValue(':repair_group', $this->repair_group)
            ->bindValue(':auth_item', $this->auth_item)
            ->queryAll();
    }

    // ผลรวมสถานะ
    // public function SummaryStatus($id)
    // {
    //     return self::find()->where(['status' => $id, 'repair_group' => $this->repair_group])->andWhere(['thai_year' => $this->thai_year])->count();
    // }
    // ผลรวมสถานะ
    public function SummaryStatus($status_id)
    {
        $total = Helpdesk::find()->where(['repair_group' => $this->repair_group, 'thai_year' => $this->thai_year])->count();
        $count_status = Helpdesk::find()->where(['status' => $status_id, 'repair_group' => $this->repair_group, 'thai_year' => $this->thai_year])->count();
        $progress_bar = ($count_status > 0) ? round(($count_status / $total) * 100, 2) : 0;
        return [
            'total' => $total,
            'count_status' => self::find()->where(['status' => $status_id, 'repair_group' => $this->repair_group])->andWhere(['thai_year' => $this->thai_year])->count(),
            'progress_bar' => $progress_bar
        ];
    }

    public function SummaryOfYear()
    {
        $where = ['and'];
        $where[] = ['thai_year' => $this->thai_year];  // ใช้กรองถ้าค่ามี
        $where[] = ['repair_group' => $this->repair_group];  // ใช้กรองถ้าค่ามี
        $query = Helpdesk::find()
            ->alias('h')
            ->select([
                'thai_year',
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 10 AND status NOT IN (4,5) AND thai_year = h.thai_year AND repair_group = h.repair_group) as m10'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 10 AND status = 5 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m10_cancel'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 10 AND status = 4 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m10_success'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 11 AND status NOT IN (4,5) AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m11'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 11 AND status = 5 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m11_cancel'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 11 AND status = 4 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m11_success'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 12 AND status NOT IN (4,5) AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m12'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 12 AND status = 5 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m12_cancel'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 12 AND status = 4 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m12_success'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 1 AND status NOT IN (4,5) AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m1'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 1 AND status = 5 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m1_cancel'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 1 AND status = 4 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m1_success'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 2 AND status NOT IN (4,5) AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m2'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 2 AND status = 5 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m2_cancel'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 2 AND status = 4 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m_success'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 3 AND status NOT IN (4,5) AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m3'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 3 AND status = 5 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m3_cancel'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 3 AND status = 4 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m3_success'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 4 AND status NOT IN (4,5) AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m4'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 4 AND status = 5 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m4_cancel'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 4 AND status = 4 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m4_success'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 5 AND status NOT IN (4,5) AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m5'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 5 AND status = 5 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m5_cancel'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 5 AND status = 4 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m5_success'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 6 AND status NOT IN (4,5) AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m6'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 6 AND status = 5 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m6_cancel'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 6 AND status = 4 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m6_success'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 7 AND status NOT IN (4,5) AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m7'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 7 AND status = 5 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m7_cancel'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 7 AND status = 4 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m7_success'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 8 AND status NOT IN (4,5) AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m8'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 8 AND status = 5 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m8_cancel'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 8 AND status = 4 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m8_success'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 9 AND status NOT IN (4,5) AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m9'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 9 AND status = 5 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m9_cancel'),
                new Expression('(SELECT IFNULL(CONVERT(count(id), UNSIGNED),0) FROM helpdesk WHERE  MONTH(created_at) = 9 AND status = 4 AND thai_year = h.thai_year  AND repair_group = h.repair_group) as m9_success'),
            ])
            ->where($where)
            ->groupBy(['thai_year'])
            ->asArray()
            ->one();

        return $query;
    }
}
