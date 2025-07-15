<?php

namespace app\modules\hr\models;

use Yii;
use yii\helpers\Html;
use yii\db\Expression;
use app\models\Categorise;
use app\components\LineMsg;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\dms\models\Documents;
use app\modules\approve\models\Approve;
use app\modules\usermanager\models\User;
use app\modules\hr\models\DevelopmentDetail;

/**
 * This is the model class for table "development".
 *
 * @property int $id
 * @property int|null $document_id ตามหนังสือ
 * @property string $topic หัวข้อ
 * @property string $status สถานะ
 * @property string $date_start วันที่เริ่ม
 * @property string|null $time_start เริ่มเวลา
 * @property string $date_end ถึงวันที่
 * @property string|null $time_end ถึงเวลา
 * @property string|null $vehicle_type_id ยานพาหนะ
 * @property string $vehicle_date_start วันออกเดินทาง
 * @property string $vehicle_date_end วันกลับ
 * @property string|null $driver_id พนักงานขับ
 * @property string $leader_id หัวหน้าฝ่าย
 * @property int $assigned_to มอบหมายงานให้
 * @property string $emp_id ผู้ขอ
 * @property string|null $data_json JSON
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Development extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $q;

    public $q_department;
    public $date_filter;

    public static function tableName()
    {
        return 'development';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['document_id', 'time_start', 'time_end', 'vehicle_type_id', 'driver_id', 'data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['document_id', 'assigned_to', 'created_by', 'updated_by', 'deleted_by', 'thai_year'], 'integer'],
            [['topic', 'status', 'date_start', 'date_end', 'vehicle_date_end', 'leader_id', 'assigned_to', 'emp_id', 'thai_year', 'leader_group_id'], 'required'],
            [['development_type_id', 'date_start', 'date_end', 'vehicle_date_start', 'vehicle_date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at', 'q', 'q_department', 'response_status','date_filter'], 'safe'],
            [['topic', 'status', 'time_start', 'time_end', 'vehicle_type_id', 'driver_id', 'leader_id', 'emp_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'thai_year' => 'ปีงบประมาณ',
            'document_id' => 'ตามหนังสือ',
            'response_status' => 'ตอบรับเป็นวิทยากร',
            'topic' => 'หัวข้อ',
            'status' => 'สถานะ',
            'date_start' => 'วันที่เริ่ม',
            'time_start' => 'เริ่มเวลา',
            'date_end' => 'ถึงวันที่',
            'time_end' => 'ถึงเวลา',
            'vehicle_type_id' => 'ยานพาหนะ',
            'vehicle_date_start' => 'วันออกเดินทาง',
            'vehicle_date_end' => 'วันกลับ',
            'driver_id' => 'พนักงานขับ',
            'leader_id' => 'หัวหน้าฝ่าย',
            'leader_group_id' => 'หัวหน้ากลุ่มงาน',
            'assigned_to' => 'มอบหมายงานให้',
            'emp_id' => 'ผู้ขอ',
            'data_json' => 'JSON',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => ['updated_at'],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function getDevelopmentType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'development_type_id'])->andOnCondition(['name' => 'development_type']);
    }

    public function getDevelopmentDetail()
    {
        return $this->hasMany(DevelopmentDetail::class, ['development_id' => 'id']);
    }

    public function getDocument()
    {
        return $this->hasOne(Documents::class, ['id' => 'document_id']);
    }

    public function getExpenses()
    {
        return $this->hasMany(DevelopmentDetail::class, ['development_id' => 'id'])->andOnCondition(['name' => 'expense_type']);
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getCreatedByEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getVehicleType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'vehicle_type_id']);
    }


        public function groupYear()
    {
        $year = self::find()
            ->andWhere(['IS NOT', 'thai_year', null])
            ->groupBy(['thai_year'])
            ->orderBy(['thai_year' => SORT_DESC])
            ->all();
        return ArrayHelper::map($year, 'thai_year',function ($model) {
            return 'ปีงบประมาณ '.$model->thai_year;
        });
    }

// สรุปข้อมูลการอบรม/ประชุม/ดูงาน
    public function getSummary()
    {
        return [
            'listSummaryMonth' => $this->listSummaryMonth(),
            'activityType' => $this->activityType(),
            'monthlyTrend' => $this->monthlyTrend(),
        ];
    }
    
    // แนวโน้มการอบรม/ประชุม/ดูงานรายเดือน
    public function monthlyTrend()
    {
        $list = $this->listSummaryMonth();
        $series = [];
        foreach ($list as $item) {
            $series[] = [
                'name' => $item['title'],
                'data' => [
                    (int)$item['m1'],
                    (int)$item['m2'],
                    (int)$item['m3'],
                    (int)$item['m4'],
                    (int)$item['m5'],
                    (int)$item['m6'],
                    (int)$item['m7'],
                    (int)$item['m8'],
                    (int)$item['m9'],
                    (int)$item['m10'],
                    (int)$item['m11'],
                    (int)$item['m12']
                ]
            ];
        }
        return [
            'series' => $series,
            'categories' => [
                'ม.ค.',
                'ก.พ.',
                'มี.ค.',
                'เม.ย.',
                'พ.ค.',
                'มิ.ย.',
                'ก.ค.',
                'ส.ค.',
                'ก.ย.',
                'ต.ค.',
                'พ.ย.',
                'ธ.ค.'
            ]
        ];
    }
    // จำนวนการอบรม/ประชุม/ดูงานทั้งหมด
    public function activityType()
    {
        $sql = "SELECT 
            c.code,
            c.title,
            count(d.id) as total
            FROM categorise c
            LEFT JOIN development d 
                ON d.development_type_id = c.code AND d.thai_year = :thai_year
            WHERE c.name = 'development_type'
            GROUP BY c.code, c.title;";
        $data = Yii::$app->db->createCommand($sql)->bindValue(':thai_year', $this->thai_year)->queryAll();

        $series = [];
        foreach ($data as $item) {
            $series[] = (int)$item['total'];
        }

        return ['series' => $series,'labels' => array_column($data, 'title')];
    }
    
    public function listSummaryMonth()
    {

        $sql = "
                SELECT 
                    c.code,
                    c.title,
                    COUNT(CASE WHEN MONTH(d.date_start) = 1 THEN 1 END) AS m1,
                    COUNT(CASE WHEN MONTH(d.date_start) = 2 THEN 1 END) AS m2,
                    COUNT(CASE WHEN MONTH(d.date_start) = 3 THEN 1 END) AS m3,
                    COUNT(CASE WHEN MONTH(d.date_start) = 4 THEN 1 END) AS m4,
                    COUNT(CASE WHEN MONTH(d.date_start) = 5 THEN 1 END) AS m5,
                    COUNT(CASE WHEN MONTH(d.date_start) = 6 THEN 1 END) AS m6,
                    COUNT(CASE WHEN MONTH(d.date_start) = 7 THEN 1 END) AS m7,
                    COUNT(CASE WHEN MONTH(d.date_start) = 8 THEN 1 END) AS m8,
                    COUNT(CASE WHEN MONTH(d.date_start) = 9 THEN 1 END) AS m9,
                    COUNT(CASE WHEN MONTH(d.date_start) = 10 THEN 1 END) AS m10,
                    COUNT(CASE WHEN MONTH(d.date_start) = 11 THEN 1 END) AS m11,
                    COUNT(CASE WHEN MONTH(d.date_start) = 12 THEN 1 END) AS m12
                FROM categorise c
                LEFT JOIN development d 
                    ON d.development_type_id = c.code AND d.thai_year = :thaiYear
                WHERE c.name = 'development_type'
                GROUP BY c.code, c.title
                ORDER BY c.code
                ";

        $command = Yii::$app->db->createCommand($sql);
        $command->bindValue(':thaiYear', $this->thai_year);

        $data = $command->queryAll();
        return $data;
    }

// เปรียบเทียบข้อมูลการพัฒนารายปี
    public  function getYearlyDevelopmentSummary()
{

      // ดึงจำนวนบุคลากรทั้งหมด
    $totalEmployees = Employees::find()
        ->where(['status' => 1])
        ->count();
        
    $sql = "
        SELECT 
            thai_year,
            total_price,
            total_count,
            unique_emp_count,

            -- ความต่างจำนวนครั้ง
            IFNULL(total_count - LAG(total_count) OVER (ORDER BY thai_year), 0) AS count_difference,

            -- สถานะ (ดูจากจำนวนครั้ง)
            CASE
                WHEN LAG(total_count) OVER (ORDER BY thai_year) IS NULL THEN 'N/A'
                WHEN total_count > LAG(total_count) OVER (ORDER BY thai_year) THEN 'เพิ่มขึ้น'
                WHEN total_count < LAG(total_count) OVER (ORDER BY thai_year) THEN 'ลดลง'
                ELSE 'เท่าเดิม'
            END AS count_status,

            -- % เปลี่ยนจำนวนครั้ง
            CASE
                WHEN LAG(total_count) OVER (ORDER BY thai_year) IS NULL THEN 0
                WHEN LAG(total_count) OVER (ORDER BY thai_year) = 0 THEN 0
                ELSE ROUND(
                    ((total_count - LAG(total_count) OVER (ORDER BY thai_year)) / LAG(total_count) OVER (ORDER BY thai_year)) * 100,
                    2
                )
            END AS count_percent_change,

            -- % บุคลากรที่เข้าร่วมกิจกรรม
            ROUND((unique_emp_count / :totalEmployees) * 100, 2) AS emp_percent

        FROM (
            SELECT 
                d.thai_year, 
                SUM(t.price) AS total_price,
                COUNT(t.id) AS total_count,
                COUNT(DISTINCT d.emp_id) AS unique_emp_count
            FROM development d
            LEFT JOIN development_detail t ON t.development_id = d.id
            WHERE t.price IS NOT NULL
            AND d.thai_year IN (:last_year, :current_year)
            GROUP BY d.thai_year
        ) AS yearly;
            ";
              

    $lastYear = $this->thai_year - 1;
    $currentYear = $this->thai_year;
    $data =  Yii::$app->db->createCommand($sql)
    ->bindValue(':last_year', $lastYear)
    ->bindValue(':current_year', $currentYear)
    ->bindValue(':totalEmployees', $totalEmployees)
    ->queryAll();
    return [
        'total_count' => $data[1]['total_count'] ?? 0,
        'total_price' => $data[1]['total_price'] ?? 0,
        'emp_count' => $data[1]['unique_emp_count'] ?? 0,
        'emp_percent' => $data[1]['emp_percent'] ?? 0,
        'price_percent_change' => $data[1]['price_percent_change'] ?? 0,
        'count_percent_change' => $data[1]['count_percent_change'] ?? 0,
        'year' => $data[1]['thai_year'] ?? 0,
        'price_status' => (isset($data[1]['count_status']) 
            ? ($data[1]['count_status'] == 'เพิ่มขึ้น' 
                ? '<span class="text-success"><i class="fa-solid fa-caret-up"></i> เพิ่มขึ้น</span>' 
                : '<span class="text-danger"><i class="fa-solid fa-caret-down"></i> ลดลง</span>') 
            : '-'),
    
        
    ];
}



    public function listApprove()
    {
        return Approve::find()->where(['name' => 'development', 'from_id' => $this->id])->orderBy(['level' => SORT_ASC])->all();
    }

    // แสดงวันที่สร้าง
    public function viewCreated()
    {
        // return Yii::$app->thaiFormatter->asDate($this->created_at, 'long');
        return Yii::$app->thaiDate->toThaiDate($this->created_at, true, false);
    }

    // ส่ง Msg เมื่อผ่านการอนุมัติ

    public function MsgApprove()
    {
        $message = $this->topic . 'ได้รับการอนุมัติแล้ว';
        $lineId = $this->createdByEmp->user->line_id;
        LineMsg::sendMsg($lineId, $message);
    }

    public function getLeader()
    /**
     * Gets query for [[Leader]].
     *
     * @return yii\db\ActiveQuery
     */
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getAssignedTo()
    {
        return $this->hasOne(Employees::class, ['id' => 'assigned_to']);
    }

    public function VehicleTypeName()
    {
        $model = Categorise::find()
            ->where(['name' => 'vehicle_type'])
            ->andWhere(['code' => $this->data_json['vehicle_type']])
            ->one();
        return $model ? $model->title : '-';
    }

    // สร้างการอนุมัติ
    public function createApprove()
    {
        // หัวหน้างาน
        $developmentStep1Check = Approve::findOne(['from_id' => $this->id, 'level' => 1, 'name' => 'development']);
        try {
            if (!$developmentStep1Check) {
                $developmentStep1 = $developmentStep1Check ? $developmentStep1Check : new Approve();
                $developmentStep1->from_id = $this->id;
                $developmentStep1->name = 'development';
                $developmentStep1->emp_id = $this->leader_id;
                $developmentStep1->title = 'เห็นชอบ';
                $developmentStep1->data_json = ['label' => 'เห็นชอบ'];
                $developmentStep1->level = 1;
                $developmentStep1->status = 'Pending';
                $developmentStep1->save(false);
                // try {
                // ส่ง msg ให้ Approve
                $toUserId = $developmentStep1->employee->user->line_id;
                $msg = 'ขออนุมัติ';
                $msg .= "\n" . 'หัวข้อ : ' . $this->topic;
                $msg .= "\n" . 'วันที่ : ' . ThaiDateHelper::formatThaiDate($this->date_start, 'long', 'short');
                $msg .= "\n" . 'ถึงวันที่ : ' . ThaiDateHelper::formatThaiDate($this->date_end, 'long', 'short');
                $msg .= "\n" . 'ผู้ขอ : ' . $this->createdByEmp->fullname;
                LineMsg::sendMsg($toUserId, $msg);
                // } catch (\Throwable $th) {

                // }
            }
        } catch (\Throwable $th) {
        }

        try {
            // หัวหน้ากลุ่มงานเห็นชอบ
            $developmentStep2Check = Approve::findOne(['from_id' => $this->id, 'level' => 2, 'name' => 'development']);
            if (!$developmentStep2Check) {
                $developmentStep2 = $developmentStep2Check ? $developmentStep2Check : new Approve();
                $developmentStep2->from_id = $this->id;
                $developmentStep2->name = 'development';
                $developmentStep2->emp_id = $this->leader_group_id;
                $developmentStep2->title = 'เห็นชอบ';
                $developmentStep2->data_json = ['label' => 'เห็นชอบ'];
                $developmentStep2->level = 2;
                $developmentStep2->status = 'None';
                $developmentStep2->save(false);
            }
        } catch (\Throwable $th) {
        }

        try {
            // ผู้ตรวจสอบ
            $developmentStep3Check = Approve::findOne(['from_id' => $this->id, 'level' => 3, 'name' => 'development']);
            if (!$developmentStep3Check) {
                $developmentStep3 = $developmentStep3Check ? $developmentStep3Check : new Approve();
                $developmentStep3->from_id = $this->id;
                $developmentStep3->name = 'development';
                $developmentStep3->title = 'ตรวจสอบ';
                // $developmentStep3->emp_id = $this->data_json['approve_3'];
                $developmentStep3->data_json = ['label' => 'ผ่าน'];
                $developmentStep3->level = 3;
                $developmentStep3->status = 'None';
                $developmentStep3->save(false);
            }
        } catch (\Throwable $th) {
        }

        // ผู้อำนวยการอนุมัติ
        $director = SiteHelper::viewDirector();
        $developmentStep4Check = Approve::findOne(['from_id' => $this->id, 'level' => 4, 'name' => 'development']);
        // try {
        if (!$developmentStep4Check) {
            $developmentStep4 = $developmentStep4Check ? $developmentStep4Check : new Approve();
            $developmentStep4->from_id = $this->id;
            $developmentStep4->name = 'development';
            $developmentStep4->emp_id = $director['id'];
            $developmentStep4->title = 'อนุมัติ';
            $developmentStep4->data_json = ['label' => 'อนุมัติ'];
            $developmentStep4->level = 4;
            $developmentStep4->status = 'None';
            $developmentStep4->save(false);
        }
        // code...
        // } catch (\Throwable $th) {
        // }
    }

    //  ภาพทีมคณะกรรมการ
    public function StackMember()
    {
        // try {
        $data = '';
        $data .= '<div class="avatar-stack">';
        foreach (DevelopmentDetail::find()->where(['name' => 'member', 'development_id' => $this->id])->all() as $key => $item) {
            $emp = Employees::findOne(['id' => $item->emp_id]);
            if ($emp) {
                $data .= Html::a(Html::img($emp->ShowAvatar(), ['class' => 'avatar-sm rounded-circle shadow']), ['/me/development-detail/update', 'id' => $item->id, 'title' => '<i class="bi bi-person-circle"></i> กรรมการตรวจรับเข้าคลัง'], ['class' => 'open-modal', 'data' => [
                    'size' => 'model-md',
                    'bs-toggle' => 'tooltip',
                    'bs-placement' => 'top',
                    'bs-title' => $emp->fullname
                ]]);
            }
        }
        $data .= '</div>';

        return $data;
        // } catch (\Throwable $th) {
        // }
    }

    //  แสดงรายชื่อคณะเดินทาง
    public function memberText()
    {
        $data = [];
        foreach (DevelopmentDetail::find()->where(['name' => 'member', 'development_id' => $this->id])->all() as $key => $item) {
            $emp = Employees::findOne(['id' => $item->emp_id]);
            if ($emp) {
                $data[] = $emp->fullname;
            }
        }
        return [
            'data' => $data,
            'count' => count($data),
            'text' => implode(',', $data)
        ];
    }

    // วันที่เอกสาร
    public function showDateRange()
    {
        return ThaiDateHelper::formatThaiDateRange($this->date_start, $this->date_end, 'long', 'short');
    }

    // วันที่ออกเดินทาง
    public function showVehicleDateRange()
    {
        return ThaiDateHelper::formatThaiDateRange($this->vehicle_date_start, $this->vehicle_date_end, 'long', 'short');
    }

    public function ListVehicleType()
    {
        $model = Categorise::find()
            ->where(['name' => 'vehicle_type'])
            ->andWhere(['!=', 'code', 'ambulance'])
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }

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

    public function listStatus()
    {
        return [
            'Pending' => 'รออนุมัติ',
            'Pass' => 'ตรวจสอบผ่าน',
            'Approve' => 'อนุมัติ',
            'Reject' => 'ไม่อนุมัติ',
            'Cancel' => 'ยกเลิก',
            'Complete' => 'เสร็จสิ้น',
        ];
    }

    // การตอบรับเป็นวิทยากร
    public function viewResponseStatus()
    {
        switch ($this->response_status) {
            case 'Accept':
                return [
                    'title' => 'ตอบรับ',
                    'color' => 'success',
                    'view' => '<span class="badge bg-success text-white"><i class="fa-solid fa-circle-check"></i> ตอบรับ</span>'
                ];
                break;
            case 'Reject':
                return [
                    'title' => 'ไม่ตอบรับ',
                    'color' => 'danger',
                    'view' => '<span class="badge bg-danger">ไม่ตอบรับ</span>'
                ];
                break;
            case 'None':
                return [
                    'title' => 'ยังไม่ตอบรับ',
                    'color' => 'warning',
                    'view' => '<span class="badge bg-warning"><i class="fa-regular fa-hourglass-half"></i> ยังไม่ตอบรับ</span>'
                ];
                break;
            default:
                return [
                    'title' => 'ยังไม่ตอบรับ',
                    'color' => 'warning',
                    'view' => '<span class="badge bg-warning"><i class="fa-regular fa-hourglass-half"></i> ยังไม่ตอบรับ</span>'
                ];
        }
    }

    public function viewStatus()
    {
        return $this->getStatus($this->status);
    }

    public function getStatus($status)
    {
        $dateStart = AppHelper::convertToGregorian($this->date_start);
        $dateEnd = AppHelper::convertToGregorian($this->date_end);
        $title = '';
        $color = '';
        $view = '';
        $count = self::find()
            // ->andFilterWhere(['vehicle_type_id' => $this->vehicle_type_id])
            ->andFilterWhere(['status' => $status])
            ->andFilterWhere(['>=', 'date_start', $dateStart])
            ->andFilterWhere(['<=', 'date_end', $dateEnd])
            ->count();
        $total = self::find()->count();
        $data = AppHelper::viewStatus($status);
        $percent = $total > 0 ? ($count / $total * 100) : 0;

        return [
            'total' => $total,
            'count' => $count,
            'percent' => $percent,
            'title' => $data['title'],
            'color' => $data['color'],
            'view' => $data['view']
        ];
    }
}
