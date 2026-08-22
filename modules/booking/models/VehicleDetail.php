<?php

namespace app\modules\booking\models;

use Yii;
use yii\helpers\Url;
use yii\db\Expression;
use yii\bootstrap5\Html;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\am\models\Asset;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use app\modules\booking\models\Vehicle;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * This is the model class for table "vehicle_detail".
 *
 * @property int $id
 * @property int $vehicle_id ID ของรถยนต์
 * @property string|null $ref
 * @property float|null $mileage_start เลขไมล์รถก่อนออกเดินทาง
 * @property float|null $mileage_end เลขไมล์หลังเดินทาง
 * @property float|null $distance_km ระยะทาง กม.
 * @property float|null $oil_price น้ำมันที่เติม
 * @property float|null $oil_liter ปริมาณน้ำมัน
 * @property string|null $license_plate ทะเบียนยานพาหนะ
 * @property string|null $status สถานะ
 * @property string|null $date_start เริ่มวันที่
 * @property string|null $time_start เริ่มเวลา
 * @property string|null $date_end ถึงวันที่
 * @property string|null $time_end ถึงเวลา
 * @property string|null $driver_id พนักงานขับ
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class VehicleDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $q;
    public $emp_id;
    public $thai_year;
    public $date_filter;
    public $location;
    public static function tableName()
    {
        return 'vehicle_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vehicle_id'], 'required'],
            [['vehicle_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['mileage_start', 'mileage_end', 'distance_km', 'oil_price', 'oil_liter'], 'number'],
            [['date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at', 'q', 'emp_id', 'thai_year', 'date_filter', 'location'], 'safe'],
            [['ref', 'license_plate', 'status', 'time_start', 'time_end', 'driver_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vehicle_id' => 'ID ของรถยนต์',
            'ref' => 'Ref',
            'mileage_start' => 'เลขไมล์รถก่อนออกเดินทาง',
            'mileage_end' => 'เลขไมล์หลังเดินทาง',
            'distance_km' => 'ระยะทาง กม.',
            'oil_price' => 'น้ำมันที่เติม',
            'oil_liter' => 'ปริมาณน้ำมัน',
            'license_plate' => 'ทะเบียนยานพาหนะ',
            'status' => 'สถานะ',
            'date_start' => 'เริ่มวันที่',
            'time_start' => 'เริ่มเวลา',
            'date_end' => 'ถึงวันที่',
            'time_end' => 'ถึงเวลา',
            'driver_id' => 'พนักงานขับ',
            'data_json' => 'ยานพาหนะ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }

    public function getVehicle()
    {
        return $this->hasOne(Vehicle::class, ['id' => 'vehicle_id']);
    }

    public function getCar()
    {
        return $this->hasOne(Asset::class, ['license_plate' => 'license_plate']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getDriver()
    {
        return $this->hasOne(Employees::class, ['id' => 'driver_id']);
    }


    // สถานะ
    public function getVehicleDetailStatus()
    {
        return $this->hasOne(Categorise::class, ['code' => 'status'])->andOnCondition(['name' => 'vehicle_detail_status']);
    }

    public function showDate()
    {
        return ThaiDateHelper::formatThaiDate($this->date_start);
    }

    public function Upload()
    {
        $ref = $this->ref;
        $name = 'vehicle_bill';
        return FileManagerHelper::FileUpload($ref, $name);
    }

    public function showDriver($msg = null)
    {
        try {
            $emp = Employees::findOne(['id' => $this->driver_id]);
            // $msg = $emp->departmentName();
            return [
                'avatar' => $emp->getAvatar(false, $msg),
                'fullname' => $emp->fullname,
                'photo' => $emp->showAvatar(),
            ];
        } catch (\Throwable $th) {
            return [
                'avatar' => '',
                'fullname' => '',
                'photo' => \Yii::getAlias('@web') . '/img/placeholder_cid.png'
            ];
        }
    }


 public function viewTime()
{
    $timeStart = substr((string)($this->time_start ?? ''), 0, 5);
    $timeEnd = substr((string)($this->time_end ?? ''), 0, 5);
    $fulltime = $timeStart . ' - ' . $timeEnd;

    return [
        'start' => $timeStart,
        'end' => $timeEnd,
        'full' => $fulltime . ' น.'
    ];
}

    // แสดงหน่วยงานภานนอก
    public function ListOrg()
    {
        $model = Categorise::find()
            ->where(['name' => 'document_org'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }

    public function ListThaiYear()
    {
        $model = Vehicle::find()
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

    // แสดงรายการาถานะ
    public function ListStatus()
    {
        $model = Categorise::find()
            ->where(['name' => 'vehicle_detail_status'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }

    public function viewStatus()
    {
        $statusName = $this->vehicleDetailStatus?->title ?? null;
        return $this->getStatus($this->status, $statusName);
    }

    public  function getStatus($status, $statusName = null)
    {
        $data = AppHelper::viewStatus($status, $statusName);
        return [
            'title' => $data['title'],
            'color' => $data['color'],
            'view' => $data['view'],
            'icon' => $data['icon']
        ];
    }

    /* ------------------------------------------------------------------
     * แบบประเมินความพึงพอใจการใช้รถ (ใช้ taxonomy categorise name = 'rating'
     * ชุดเดียวกับงานซ่อม 1=ควรปรับปรุง ... 5=ดีมาก)
     * เก็บใน data_json:
     *   survey_token   = คีย์สำหรับลิงก์ประเมิน (ส่งทาง Telegram)
     *   survey_sent_at = เวลาที่ส่งลิงก์
     *   satisfaction   = ['score','comment','at','by']
     * ------------------------------------------------------------------ */

    public const STATUS_SUCCESS = 'Success';

    /** ค่า data_json ที่ปลอดภัยเสมอเป็น array */
    public function dataJson(): array
    {
        if (is_array($this->data_json)) {
            return $this->data_json;
        }
        if (is_string($this->data_json) && $this->data_json !== '') {
            return json_decode($this->data_json, true) ?: [];
        }
        return [];
    }

    /** คีย์ลิงก์ประเมิน — สร้างและบันทึกให้อัตโนมัติถ้ายังไม่มี */
    public function ensureSurveyToken(): string
    {
        $data = $this->dataJson();
        $token = trim((string) ($data['survey_token'] ?? ''));
        if ($token !== '') {
            return $token;
        }
        $token = Yii::$app->security->generateRandomString(32);
        $data['survey_token'] = $token;
        $this->data_json = $data;
        $this->save(false, ['data_json']);

        return $token;
    }

    public function surveyToken(): string
    {
        return trim((string) ($this->dataJson()['survey_token'] ?? ''));
    }

    /** ลิงก์แบบประเมิน (absolute) สำหรับส่งให้ผู้ขอ */
    public function surveyUrl(): string
    {
        return Url::to(['/booking/vehicle-survey/index', 'token' => $this->ensureSurveyToken()], true);
    }

    /** ผลการประเมินที่บันทึกไว้ */
    public function satisfaction(): array
    {
        $row = $this->dataJson()['satisfaction'] ?? [];
        return is_array($row) ? $row : [];
    }

    public function satisfactionScore(): int
    {
        return (int) ($this->satisfaction()['score'] ?? 0);
    }

    public function isSurveyed(): bool
    {
        return $this->satisfactionScore() > 0;
    }

    /** ประเมินได้เมื่อภารกิจเสร็จสิ้นแล้วและยังไม่เคยประเมิน */
    public function canSurvey(): bool
    {
        return (string) $this->status === self::STATUS_SUCCESS && !$this->isSurveyed();
    }

    public function saveSatisfaction(int $score, string $comment, $byEmpId = null): bool
    {
        if ($score < 1 || $score > 5) {
            return false;
        }
        $data = $this->dataJson();
        $data['satisfaction'] = [
            'score' => $score,
            'comment' => $comment,
            'at' => date('Y-m-d H:i:s'),
            'by' => $byEmpId !== null && $byEmpId !== '' ? (string) $byEmpId : null,
        ];
        $this->data_json = $data;

        return (bool) $this->save(false, ['data_json']);
    }

    public function markSurveySent(): void
    {
        $data = $this->dataJson();
        $data['survey_sent_at'] = date('Y-m-d H:i:s');
        $this->data_json = $data;
        $this->save(false, ['data_json']);
    }

    /** คำอธิบายคะแนนตาม taxonomy 'rating' */
    public static function ratingTitle(int $score): string
    {
        if ($score < 1) {
            return '';
        }
        $cat = Categorise::findOne(['name' => 'rating', 'code' => (string) $score]);

        return (string) ($cat->title ?? '');
    }

    public static function listRating(): array
    {
        $rows = Categorise::find()
            ->where(['name' => 'rating'])
            ->orderBy(['code' => SORT_ASC])
            ->asArray()
            ->all();

        return ArrayHelper::map($rows, 'code', 'title');
    }

    /** แสดงดาว + คำอธิบาย (ใช้ในตารางฝั่งเจ้าหน้าที่) */
    public function viewSatisfaction(): string
    {
        $score = $this->satisfactionScore();
        if ($score < 1) {
            return '';
        }
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            $stars .= '<i class="bi bi-star' . ($i <= $score ? '-fill' : '') . '"></i>';
        }

        return '<span class="text-warning">' . $stars . '</span> <span class="text-muted">'
            . Html::encode(self::ratingTitle($score)) . '</span>';
    }

    public static function findBySurveyToken(string $token): ?self
    {
        $token = trim($token);
        if ($token === '' || strlen($token) < 16) {
            return null;
        }

        return self::find()
            ->where(new Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.survey_token')) = :token", [':token' => $token]))
            ->andWhere(['deleted_at' => null])
            ->one();
    }
}
