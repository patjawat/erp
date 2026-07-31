<?php

namespace app\modules\helpdesk2\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use app\components\ThaiDateHelper;
use app\components\CategoriseHelper;
use app\modules\hr\models\Employees;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\filemanager\models\Uploads;
use app\modules\inventory\models\StockEvent;
use app\modules\filemanager\components\FileManagerHelper;
use Google\Service\Compute\Help;

/**
 * This is the model class for table "helpdesk".
 *
 * @property int $id
 * @property string|null $repair_number รหัสงานซ่อม
 * @property string|null $device_type_id ประเภทอุปกรณ์
 * @property string|null $asset_number หมายเลขครุภัณฑ์
 * @property string|null $request_repair_date วันที่ต้องการให้ซ่อม
 * @property string|null $repair_result ผลการซ่อม (ซ่อมได้/ซ่อมไม่ได้)
 * @property string|null $repair_type ประเภทการซ่อม
 * @property int $category_id
 * @property int|null $emp_id
 * @property string|null $ref
 * @property string|null $code
 * @property string|null $receive_date วันที่ต้องการให้ซ่อม
 * @property string|null $date_start
 * @property string|null $date_end
 * @property string|null $name ชื่อการเก็บข้อมูล
 * @property string|null $title รายการ
 * @property string|null $data_json การเก็บข้อมูลชนิด JSON
 * @property string|null $status สถานะ
 * @property string|null $rating คะแนน
 * @property int|null $move_out จำหน่าย
 * @property string|null $repair_group หน่วยงานที่ส่งซ่่อม
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 */
class Helpdesk extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public $q;
    public $q_department;
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['repair_number', 'device_type_id', 'asset_number', 'request_repair_date', 'repair_result', 'repair_type', 'emp_id', 'ref', 'code', 'receive_date', 'date_start', 'date_end', 'name', 'title', 'data_json', 'status', 'rating', 'move_out', 'repair_group', 'thai_year', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['category_id'], 'default', 'value' => 0],
            [['request_repair_date', 'receive_date', 'date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'q','q_department'], 'safe'],
            [['category_id', 'emp_id', 'move_out', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [['repair_number', 'device_type_id', 'asset_number', 'repair_result', 'repair_type'], 'string', 'max' => 100],
            [['ref', 'code', 'name', 'title', 'status', 'rating', 'repair_group'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'repair_number' => 'รหัสงานซ่อม',
            'device_type_id' => 'ประเภทอุปกรณ์',
            'asset_number' => 'หมายเลขครุภัณฑ์',
            'request_repair_date' => 'วันที่ต้องการให้ซ่อม',
            'repair_result' => 'ผลการซ่อม (ซ่อมได้/ซ่อมไม่ได้)',
            'repair_type' => 'ประเภทการซ่อม',
            'category_id' => 'Category ID',
            'emp_id' => 'Emp ID',
            'ref' => 'Ref',
            'code' => 'Code',
            'receive_date' => 'วันที่ต้องการให้ซ่อม',
            'date_start' => 'Date Start',
            'date_end' => 'Date End',
            'name' => 'ชื่อการเก็บข้อมูล',
            'title' => 'รายการ',
            'data_json' => 'การเก็บข้อมูลชนิด JSON',
            'status' => 'สถานะ',
            'rating' => 'คะแนน',
            'move_out' => 'จำหน่าย',
            'repair_group' => 'หน่วยงานที่ส่งซ่่อม',
            'thai_year' => 'ปีงบประมาณ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->name !== 'repair') {
            return;
        }
        if (!in_array((int) $this->repair_group, [1, 2, 3], true)) {
            return;
        }
        $isDraft = is_array($this->data_json) && !empty($this->data_json['draft']);
        if ($isDraft) {
            return;
        }

        try {
            if ($insert) {
                \app\modules\telegrambot\components\HelpdeskTelegramNotify::notifyNewRepair($this);
            } elseif (array_key_exists('status', $changedAttributes) && (string) $changedAttributes['status'] !== (string) $this->status) {
                \app\modules\telegrambot\components\HelpdeskTelegramNotify::notifyStatusChanged($this, (string) $changedAttributes['status']);
            }
        } catch (\Throwable $e) {
            \Yii::error('Helpdesk telegram notify failed: ' . $e->getMessage(), __METHOD__);
        }
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


    public static function getRepairTypeList()
    {
        return [
            'internal' => 'ซ่อมภายใน',
            'external' => 'ซ่อมภายนอก',
        ];
    }

    /**
     * ชื่อและรูปแบบสถานะงานซ่อมมาตรฐาน ใช้ร่วมกันทุกหน้าของระบบ
     */
    public static function repairStatusMeta(): array
    {
        return [
            'pending' => [
                'label' => 'รอรับเรื่อง',
                'color' => 'warning',
                'icon' => 'fa-regular fa-clock',
            ],
            'receive' => [
                'label' => 'รับเรื่องแล้ว',
                'color' => 'info',
                'icon' => 'fa-solid fa-user-check',
            ],
            'in_progress' => [
                'label' => 'กำลังดำเนินการ',
                'color' => 'primary',
                'icon' => 'fa-solid fa-gears',
            ],
            'success' => [
                'label' => 'เสร็จสิ้น',
                'color' => 'success',
                'icon' => 'fa-regular fa-circle-check',
            ],
            'cancel' => [
                'label' => 'ยกเลิก',
                'color' => 'danger',
                'icon' => 'fa-solid fa-ban',
            ],
        ];
    }

    /** รองรับข้อมูลเดิมที่เคยบันทึกเป็น Cancel ตัวพิมพ์ใหญ่ */
    public static function normalizeRepairStatus($status): string
    {
        $code = strtolower(trim((string) $status));
        return $code !== '' ? $code : 'pending';
    }

    public static function repairStatusOptions(): array
    {
        return array_map(
            static fn(array $meta): string => $meta['label'],
            static::repairStatusMeta()
        );
    }

    public static function repairStatusLabel($status): string
    {
        $code = static::normalizeRepairStatus($status);
        return static::repairStatusMeta()[$code]['label'] ?? 'ไม่ทราบสถานะ';
    }

    /**
     * ระดับความเร่งด่วนพร้อมสีและไอคอนเชิงความหมาย
     */
    public static function repairUrgencyMeta(): array
    {
        return [
            'low' => [
                'label' => 'ต่ำ',
                'color' => 'secondary',
                'icon' => 'fa-solid fa-arrow-down',
            ],
            'medium' => [
                'label' => 'ปานกลาง',
                'color' => 'info',
                'icon' => 'fa-solid fa-minus',
            ],
            'high' => [
                'label' => 'สูง',
                'color' => 'warning',
                'icon' => 'fa-solid fa-arrow-up',
            ],
            'critical' => [
                'label' => 'วิกฤต',
                'color' => 'danger',
                'icon' => 'fa-solid fa-triangle-exclamation',
            ],
        ];
    }

    /** รองรับรหัสความเร่งด่วนเดิมแบบตัวเลข 1-4 */
    public static function normalizeRepairUrgency($urgency): string
    {
        return match (strtolower(trim((string) $urgency))) {
            '1', 'low' => 'low',
            '2', 'medium' => 'medium',
            '3', 'high' => 'high',
            '4', 'critical' => 'critical',
            default => '',
        };
    }

    public static function repairUrgencyInfo($urgency): array
    {
        $code = static::normalizeRepairUrgency($urgency);
        return static::repairUrgencyMeta()[$code] ?? [
            'label' => 'ไม่ระบุ',
            'color' => 'secondary',
            'icon' => 'fa-regular fa-circle-question',
        ];
    }

    //ผลการซ่อม
    public static function getRepairResultList()
    {
        return [
            'Y' => 'ซ่อมได้',
            'N' => 'ซ่อมไม่ได้',
        ];
    }


    //แสดงรูปภาพประกอบ
    public function getImageRequest()
    {
        $models = Uploads::find()->where(['ref' => $this->ref, 'name' => 'repair_request'])->all();
        $html = '';

        foreach ($models as $model) {
            $imgUrl = FileManagerHelper::getImg($model->id);
            $html .= Html::img($imgUrl, [
                'class' => 'img-thumbnail img-fluid h-auto object-fit-cover',
                'alt' => 'รูปแจ้งซ่อม',
                'loading' => 'lazy',
                'width' => 200,
            ]);
        }

        return $html ?: '<p class="text-body-secondary fst-italic mb-0">ไม่มีรูปภาพประกอบ</p>';
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

    public function getEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    /**
     * ผู้แจ้งซ่อม (lookup จาก `created_by` -> employees.user_id)
     */
    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['user_id' => 'created_by']);
    }
    public function getEmpTeam()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getDeviceType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'device_type_id'])->andOnCondition(['name' => 'device_type']);
    }

    public function getRepairStatus()
    {
        return $this->hasOne(Categorise::class, ['code' => 'status'])->andOnCondition(['name' => 'repair_status']);
    }


    function HelpdeskGenNumber($depCode)
    {
        $prefix = 'REP';
        $yearMonth = (date('y') + 543) . date('m'); // 2507 หรือ 6707
        // $departmentCode = 'IT'; // จากผู้ใช้งาน/แบบฟอร์ม
        $departmentCode = $depCode; // จากผู้ใช้งาน/แบบฟอร์ม

        $count = self::find()
            ->where(['like', 'repair_number', "$prefix-$yearMonth-$departmentCode"])
            ->count();

        $nextNumber = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        return "$prefix-$yearMonth-$departmentCode-$nextNumber";
    }

    public function ResetHelpdeskGenNumber()
    {
        try {

            switch ($this->repair_group) {
                case '1':
                    $depCode = 'GEN';
                    break;
                case '2':
                    $depCode = 'IT';
                    break;
                case '3':
                    $depCode = 'MED';
                    break;
                default:
                    $depCode = '';
                    break;
            }

            $prefix = 'REP';

            // ใช้ created_at แทนวันที่ปัจจุบัน
            $createdAt = new \DateTime($this->created_at); // เช่น 2025-02-02 11:12:00
            $buddhistYear = (int)$createdAt->format('y') + 43; // เช่น ปี ค.ศ. 25 -> 25+43 = 68 (พ.ศ. เฉพาะหลักท้าย)
            $yearMonth = $buddhistYear . $createdAt->format('m'); // 6802

            $departmentCode = $depCode;

            $count = self::find()
                ->where(['like', 'repair_number', "$prefix-$yearMonth-$departmentCode"])
                ->count();

            $nextNumber = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            return "$prefix-$yearMonth-$departmentCode-$nextNumber";
            //code...
        } catch (\Throwable $th) {
            return '';
        }
    }


    // ผู้แจ่งซ่อม
    public function getUserReq()
    {
        try {
            $emp = $this->employee;
            // แสดงเฉพาะรูปในกรอบ (ไม่ใช้ getAvatar ที่แนบชื่อ/วันที่/ตำแหน่งข้างรูป)
            $avatarOnly = Html::img('@web/img/loading.gif', [
                'class' => 'avatar-md lazyload object-fit-cover d-block m-0 rounded-circle border border-secondary border-opacity-25 shadow-sm',
                'alt' => (string) ($emp->fullname ?? 'ผู้แจ้ง'),
                'data' => [
                    'expand' => '-20',
                    'sizes' => 'auto',
                    'src' => $emp->ShowAvatar(),
                ],
            ]);
            return [
                'avatar' => $avatarOnly,
                'fullname' => $emp->fullname,
                'department' => $emp->departmentName(),
            ];
        } catch (\Throwable $th) {

            return [
                'avatar' => '',
                'fullname' => '',
                'department' => '',
            ];
        }
    }

    //แสดงวันเวลาที่แสดง
    public function viewCreated()
    {
        try {
            // ถ้ารับงานแล้ว ใช้ `receive_date` แทน `created_at` เพื่อให้หน้าเมนู/ประวัติ
            // แสดง "วันและเวลาที่รับงาน" ได้ตรง
            $baseDatetime = !empty($this->receive_date) ? (string) $this->receive_date : (string) $this->created_at;
            $datetime = explode(' ', $baseDatetime);
            $date = ThaiDateHelper::formatThaiDate($datetime[0] ?? '');
            $time = !empty($datetime[1]) ? ((string) $datetime[1]) . '.น' : '';
            return [
                'full' => $date . ' ' . $time,
                'date' => $date,
                'time' => $time
            ];
        } catch (\Throwable $th) {
            return [
                'full' => '',
                'date' => '',
                'time' => ''
            ];
        }
    }

    //แสดงวันเวลาที่แก้ไข
        public function viewUpdated()
    {
        try {
            $datetime = explode(' ', $this->updated_at);
            $date = ThaiDateHelper::formatThaiDate($datetime[0]);
            $time = $datetime[1] . '.น';
            return [
                'full' => $date . ' ' . $time,
                'date' => $date,
                'time' => $time
            ];
        } catch (\Throwable $th) {
            return [
                'full' => '',
                'date' => '',
                'time' => ''
            ];
        }
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

    /** RBAC role ของช่างตามแผนกที่รับงานซ่อม */
    public static function repairRoleName($group): ?string
    {
        return match ((string) $group) {
            '1' => 'technician',
            '2' => 'computer',
            '3' => 'medical',
            default => null,
        };
    }

    // ช่างเทคนิค แสดงตามชื่อกลุ่มที่ส่งมา
    public function listTecName()
    {
        return ArrayHelper::map(
            static::TechnicianList($this->repair_group),
            'user_id',
            static fn(Employees $employee): string => trim((string) $employee->fullname)
        );
    }

    /**
     * บุคลากรที่ยังปฏิบัติงานและมีสิทธิ์ในระบบซ่อมของแผนกที่รับงาน
     * repair_group 1=technician, 2=computer, 3=medical
     */
    public static function TechnicianList($group)
    {
        $roleName = static::repairRoleName($group);
        if ($roleName === null) {
            return [];
        }

        return Employees::find()
            ->alias('emp')
            ->innerJoin('auth_assignment auth', 'auth.user_id = emp.user_id')
            ->where([
                'auth.item_name' => $roleName,
                'emp.status' => 1,
            ])
            ->andWhere(['<>', 'emp.user_id', 0])
            ->distinct()
            ->orderBy(['emp.fname' => SORT_ASC, 'emp.lname' => SORT_ASC])
            ->all();
    }

    //รายการอุกรณ์
    public function listDeviceType()
    {
        $list = Categorise::find()->andWhere(['name' => 'device_type'])->all();
        return ArrayHelper::map($list, 'code', 'title');
    }

    //ดึงทรัพสินทร์ที่เป็นครุภัณฑ์
    public function listAsset()
    {
        $list = Asset::find()->where(['asset_group_id' => 4])->all();
        return ArrayHelper::map($list, 'code', 'code');
    }

    /**
     * รายการครุภัณฑ์สำหรับ Select2 picker (รูป + ชื่อ + รหัส + ประเภท)
     * คืนค่า ['items' => [code => label], 'options' => [code => ['data-*' => ...]]]
     *
     * เลี่ยง N+1: ดึง Asset + Uploads ครั้งละชุด แล้ว cache 5 นาที
     */
    public function listAssetForPicker()
    {
        $cacheKey = 'helpdesk2:asset_picker:group:4:v1';
        $cache    = Yii::$app->has('cache') ? Yii::$app->cache : null;
        if ($cache && ($hit = $cache->get($cacheKey)) !== false) {
            return $hit;
        }

        // 1) โหลด asset เฉพาะคอลัมน์ที่ใช้ (asArray = เร็วกว่า hydrate AR)
        $assets = Asset::find()
            ->select(['id', 'code', 'ref', 'data_json'])
            ->where(['asset_group_id' => 4])
            ->asArray()
            ->all();

        if (!$assets) {
            $empty = ['items' => [], 'options' => []];
            if ($cache) { $cache->set($cacheKey, $empty, 300); }
            return $empty;
        }

        // 2) batch โหลด Uploads ทั้งหมดในคิวรีเดียว (index ด้วย ref)
        $refs = array_values(array_unique(array_filter(array_column($assets, 'ref'))));
        $uploadsByRef = [];
        if ($refs) {
            $uploadsByRef = Uploads::find()
                ->select(['id', 'ref'])
                ->where(['ref' => $refs, 'name' => 'asset'])
                ->indexBy('ref')
                ->asArray()
                ->all();
        }

        $placeholder = Yii::getAlias('@web') . '/img/placeholder-img.jpg';
        $items   = [];
        $options = [];

        foreach ($assets as $row) {
            $code = (string) ($row['code'] ?? '');
            if ($code === '') {
                continue;
            }

            $dj = $row['data_json'] ?? null;
            if (is_string($dj)) {
                $dj = json_decode($dj, true) ?: [];
            }
            if (!is_array($dj)) {
                $dj = [];
            }

            $name = (string) ($dj['asset_name'] ?? '');
            if ($name === '') { $name = 'ไม่ระบุชื่อครุภัณฑ์'; }
            $type = (string) ($dj['asset_type_text'] ?? '');

            $upload = $uploadsByRef[$row['ref']] ?? null;
            $img = $upload
                ? \yii\helpers\Url::to(['/filemanager/uploads/show', 'id' => $upload['id']], true)
                : $placeholder;

            $items[$code] = trim($name . ' ' . $code);
            $options[$code] = [
                'data-image' => $img,
                'data-name'  => $name,
                'data-type'  => $type,
                'data-code'  => $code,
            ];
        }

        $result = ['items' => $items, 'options' => $options];
        if ($cache) {
            $cache->set($cacheKey, $result, 300);
        }
        return $result;
    }



    public function ListStatus()
    {
        return static::repairStatusOptions();
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
        $list = Categorise::find()->andWhere(['name' => 'helpdesk_urgency'])->all();
        return ArrayHelper::map($list, 'code', function ($model) {
            return $model->title . ' - ' . $model->data_json['description'];
        });
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

    /** ช่องทางซ่อม: ส่งซ่อมภายนอก / ซ่อมภายใน (ซ่อมเอง) */
    public static function listRepairChannel()
    {
        return [
            'internal' => 'ซ่อมภายใน (ซ่อมเอง)',
            'external' => 'ส่งซ่อมภายนอก',
        ];
    }

    /** แสดงข้อความช่องทางซ่อมจาก data_json[repair_channel] */
    public function viewRepairChannelLabel()
    {
        $code = (string) ($this->data_json['repair_channel'] ?? '');
        $list = static::listRepairChannel();
        return $list[$code] ?? '-';
    }

    /** เป็นส่งซ่อมภายนอกหรือไม่ */
    public function isExternalRepair()
    {
        return (string) ($this->data_json['repair_channel'] ?? '') === 'external';
    }

    /** แสดงรายละเอียดส่งซ่อมภายนอก */
    public function getExternalRepairDetailHtml()
    {
        if (!$this->isExternalRepair()) {
            return '';
        }
        $d = $this->data_json;
        $rows = [];
        if (!empty($d['external_vendor'])) {
            $rows[] = ['label' => 'ร้าน/ผู้รับซ่อม', 'value' => $d['external_vendor']];
        }
        if (!empty($d['external_contact'])) {
            $rows[] = ['label' => 'ผู้ติดต่อ / เบอร์โทร', 'value' => $d['external_contact']];
        }
        if (!empty($d['external_items'])) {
            $rows[] = ['label' => 'รายการที่ส่งซ่อม', 'value' => $d['external_items']];
        }
        if (!empty($d['external_location'])) {
            $rows[] = ['label' => 'สถานที่/ที่อยู่', 'value' => $d['external_location']];
        }
        if (!empty($d['external_notes'])) {
            $rows[] = ['label' => 'หมายเหตุ/ขั้นตอน', 'value' => $d['external_notes']];
        }
        if (empty($rows)) {
            return '<p class="text-muted small mb-0">ยังไม่กรอกรายละเอียดส่งซ่อมภายนอก</p>';
        }
        $html = '<ul class="list-unstyled mb-0 small">';
        foreach ($rows as $r) {
            $html .= '<li class="mb-2"><span class="text-muted">' . Html::encode($r['label']) . ':</span> ';
            $html .= '<span class="fw-medium">' . nl2br(Html::encode($r['value'])) . '</span></li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /** แสดงรายการไฟล์บิล/ใบเสร็จ (ส่งซ่อมภายนอก) */
    public function getExternalRepairBillsHtml()
    {
        if (!$this->ref) {
            return '<p class="text-muted small mb-0">ไม่มีบิลแนบ</p>';
        }
        $models = Uploads::find()->where(['ref' => $this->ref, 'name' => 'external_repair_bill'])->all();
        if (empty($models)) {
            return '<p class="text-muted small mb-0">ไม่มีบิลแนบ</p>';
        }
        $html = '<ul class="list-unstyled mb-0 small">';
        foreach ($models as $m) {
            $url = Url::to(['/filemanager/uploads/show', 'id' => $m->id], true);
            $label = Html::encode($m->real_filename ?: 'ไฟล์แนบ');
            $html .= '<li class="mb-2">';
            $html .= '<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">';
            $html .= '<div><i class="bi bi-file-earmark-pdf me-1 text-danger"></i>' . $label . '</div>';
            $html .= '<div class="d-flex gap-1">';
            $html .= '<a href="' . Html::encode($url) . '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">เปิดดู</a>';
            $html .= '<a href="' . Html::encode($url) . '" download="' . $label . '" class="btn btn-sm btn-outline-secondary">ดาวน์โหลด</a>';
            $html .= '</div></div></li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /** จำนวนไฟล์บิล/ใบเสร็จที่แนบ (ส่งซ่อมภายนอก) */
    public function getExternalRepairBillsCount(): int
    {
        if (!$this->ref) {
            return 0;
        }
        return (int) Uploads::find()->where(['ref' => $this->ref, 'name' => 'external_repair_bill'])->count();
    }

    /** แสดงรูปภาพงานซ่อม (ช่างแนบ) */
    public function getRepairWorkPhotosHtml()
    {
        if (!$this->ref) {
            return '<p class="text-muted small mb-0">ไม่มีรูปภาพงานซ่อม</p>';
        }
        $models = Uploads::find()->where(['ref' => $this->ref, 'name' => 'repair_work_photo'])->all();
        if (empty($models)) {
            return '<p class="text-muted small mb-0">ไม่มีรูปภาพงานซ่อม</p>';
        }
        $html = '';
        foreach ($models as $m) {
            $imgUrl = FileManagerHelper::getImg($m->id);
            $viewUrl = Url::to(['/filemanager/uploads/show', 'id' => $m->id], true);
            $html .= '<a href="' . Html::encode($viewUrl) . '" target="_blank" rel="noopener" class="d-inline-block me-2 mb-2">';
            $html .= Html::img($imgUrl, [
                'class' => 'img-thumbnail',
                'style' => 'max-width: 120px; max-height: 120px; object-fit: cover;',
                'alt' => 'รูปงานซ่อม',
            ]);
            $html .= '</a>';
        }
        return $html;
    }

    // ผู้ร่วมดำเนินการ
    public function StackTeam()
    {
        try {
            $data = '';
            $data .= '<div class="avatar-stack">';
            foreach (HelpdeskDetail::find()->where(['name' => 'repair_team', 'helpdesk_id' => $this->id])->all() as $key => $item) {
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
        return static::repairStatusOptions();
    }

    //  ภาพทีม
    public function avatarStack()
    {
        try {
            $data = '';
            $data .= '<div class="avatar-stack">';
            $join = $this->data_json['join'] ?? [];
            if (!is_iterable($join)) {
                return '';
            }
            foreach ($join as $key => $avatar) {
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
    public function viewUrgent()
    {
        $rawCode = is_array($this->data_json ?? null) ? ($this->data_json['urgency'] ?? '') : '';
        $code = static::normalizeRepairUrgency($rawCode);
        $info = static::repairUrgencyInfo($rawCode);
        $model = $code !== ''
            ? Categorise::findOne(['name' => 'helpdesk_urgency', 'code' => $code])
            : null;
        $title = trim((string) ($model->title ?? $info['label']));

        $content = Html::tag('i', '', [
            'class' => $info['icon'] . ' me-1',
            'aria-hidden' => 'true',
        ]) . Html::encode($title);

        return [
            'title' => $title,
            'description' => $code !== '' ? $title . ' - ' . $code : $title,
            'view' => Html::tag('span', $content, [
                'class' => 'badge bg-' . $info['color'] . '-subtle text-' . $info['color']
                    . '-emphasis border border-' . $info['color'] . '-subtle rounded-pill fw-medium px-2 py-1',
            ]),
            'color' => $info['color'],
            'icon' => $info['icon'],
        ];
    }

    public function viewStatus()
    {
        $code = static::normalizeRepairStatus($this->status);
        $meta = static::repairStatusMeta()[$code] ?? [
            'label' => 'ไม่ทราบสถานะ',
            'color' => 'secondary',
            'icon' => 'fa-regular fa-circle-question',
        ];

        $content = Html::tag('i', '', [
            'class' => $meta['icon'] . ' me-1',
            'aria-hidden' => 'true',
        ]) . Html::encode($meta['label']);

        return Html::tag('span', $content, [
            'class' => 'badge bg-' . $meta['color'] . '-subtle text-' . $meta['color']
                . '-emphasis border border-' . $meta['color'] . '-subtle rounded-pill fw-medium px-2 py-1',
        ]);
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

    //แสดงผลวันที่รับเรื่อง
    public function viewReceiveDate()
    {
        return \Yii::$app->thaiFormatter->asDate($this->receive_date, 'long');
    }



    public function viewCreateDateTime()
    {
        $baseDatetime = !empty($this->receive_date) ? (string) $this->receive_date : (string) $this->created_at;
        return Yii::$app->thaiDate->toThaiDate($baseDatetime, true, false);
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
    //แสดง timeline สถานะงานซ่อมล่าสุด
    public function viewServiceRecordInfo()
    {
        try {
            $model = HelpdeskDetail::find()
                ->where(['helpdesk_id' => $this->id])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();

            if ($model === null) {
                return '';
            }

            return '<div class="alert alert-info">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="bi bi-info-circle-fill fs-3"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading">' . Html::encode($model->status) . '</h5>
                        <p class="mb-0">' . Html::encode($model->title) . '</p>
                    </div>
                </div>
            </div>';
        } catch (\Throwable $th) {
            return '';
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

    // ผู้รับงาน
    public function viewTechRevice()
    {
        // update emp_id จาก helpdesk_detail
        // $sql = "UPDATE  `helpdesk_detail` d 
        // LEFT JOIN employees e ON e.user_id = d.created_by SET d.emp_id = e.id 
        // WHERE name = 'service_record';";

        try {
            $helpdeskDetail = HelpdeskDetail::find()
                ->where(['helpdesk_id' => $this->id, 'name' => 'service_record'])
                ->orderBy(['id' => SORT_ASC])
                ->one();
            $emp = Employees::findOne(['id' => $helpdeskDetail->emp_id]);
            return $emp;
        } catch (\Throwable $th) {
            return '';
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
            FROM helpdesk_detail h
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
                $where[] = ['h.thai_year' => $this->thai_year];
                $where[] = ['h.repair_group' => $this->repair_group];

                if ($this->thai_year) {
                    $date = AppHelper::BudgetYearRange($this->thai_year);
                    // เงื่อนไขนี้จะทำงานร่วมกับ query หลักได้ทันที
                    $where[] = ['between', 'h.created_at', $date['start'], $date['end']];
                }

                $selectColumns = ['h.thai_year'];
                
                // วนลูปสร้าง SUM(CASE...) สำหรับเดือน 1-12 เพื่อลดความยาวของโค้ด
                // เรียงเดือนตามปีงบประมาณไทย (เริ่มเดือน 10 จบเดือน 9)
                $months = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
                $statuses = ['pending' => '', 'cancel' => '_cancel', 'success' => '_success'];

                foreach ($months as $m) {
                    foreach ($statuses as $status => $suffix) {
                        $alias = "m{$m}{$suffix}";
                        // ใช้ SUM(CASE...) แทน Subquery เพื่อให้ Filter ของ Query หลักใช้งานได้
                        $selectColumns[] = new Expression("SUM(CASE WHEN MONTH(h.created_at) = $m AND h.status = '$status' THEN 1 ELSE 0 END) AS $alias");
                    }
                }

                return Helpdesk::find()
                    ->alias('h')
                    ->select($selectColumns)
                    ->where($where)
                    ->groupBy(['h.thai_year'])
                    ->asArray()
                    ->one();
            }

}
