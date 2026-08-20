<?php

namespace app\modules\booking\models;

use Yii;
use DateTime;
use yii\helpers\Url;
use yii\db\Expression;
use yii\bootstrap5\Html;
use app\models\Categorise;
use app\components\LineMsg;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\am\models\Asset;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Development;
use app\modules\hr\models\DevelopmentDetail;
use app\modules\leave\models\Leave;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentTags;
use app\modules\booking\models\BookingDetail;
use app\modules\booking\models\VehicleDetail;
use app\modules\booking\components\VehicleTelegramNotify;

/**
 * This is the model class for table "vehicle".
 *
 * @property int $id
 * @property string|null $ref
 * @property string $code รหัส
 * @property int $thai_year ปีงบประมาณ
 * @property string $vehicle_type_id ประเภทของรถ general หรือ ambulance
 * @property int $go_type ประเภทการเดินทาง 1 = ไปกลับ, 2 = ค้างคืน
 * @property float|null $oil_price น้ำมันที่เติม
 * @property float|null $oil_liter ปริมาณน้ำมัน
 * @property int|null $document_id ตามหนังสือ
 * @property int|null $owner_id ผู้ดูแลห้องประชุม
 * @property string $urgent ความเร่งด่วน
 * @property string|null $license_plate ทะเบียนยานพาหนะ
 * @property string $location สถานที่ไป
 * @property string $reason เหตุผล
 * @property string $status สถานะ
 * @property string $date_start เริ่มวันที่
 * @property string $time_start เริ่มเวลา
 * @property string $date_end ถึงวันที่
 * @property string $time_end ถึงเวลา
 * @property string|null $driver_id พนักงานขับ
 * @property string $leader_id หัวหน้างานรับรอง
 * @property string $emp_id ผู้ขอ
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class Vehicle extends \yii\db\ActiveRecord
{
    public $q;
    public $q_department;
    public $date_filter;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vehicle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'thai_year', 'vehicle_type_id', 'go_type', 'urgent', 'location', 'reason', 'status', 'date_start', 'time_start', 'date_end', 'time_end', 'leader_id', 'emp_id'], 'required', 'message' => 'ต้องระบุ'],
            [['thai_year', 'go_type', 'document_id', 'owner_id', 'development_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['oil_price', 'oil_liter'], 'number'],
            [['date_start', 'date_end', 'data_json', 'created_at', 'updated_at', 'deleted_at', 'q', 'q_department', 'refer_type', 'date_filter', 'is_shared'], 'safe'],
            [['ref', 'code', 'vehicle_type_id', 'urgent', 'license_plate', 'location', 'reason', 'status', 'time_start', 'time_end', 'driver_id', 'leader_id', 'emp_id'], 'string', 'max' => 255],
            ['is_shared', 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'Ref',
            'code' => 'รหัส',
            'is_shared' => 'จัดสรรร่วม',
            'thai_year' => 'ปีงบประมาณ',
            'vehicle_type_id' => 'ประเภทของรถ general หรือ ambulance',
            'refer_type' => 'ประเภทของการ refer รถพยาบาล refer,ems,normal',
            'go_type' => 'ประเภทการเดินทาง 1 = ไปกลับ, 2 = ค้างคืน',
            'oil_price' => 'น้ำมันที่เติม',
            'oil_liter' => 'ปริมาณน้ำมัน',
            'document_id' => 'ตามหนังสือ',
            'development_id' => 'ใบขออนุญาตไปราชการต้นเรื่อง',
            'owner_id' => 'ผู้ดูแลห้องประชุม',
            'urgent' => 'ความเร่งด่วน',
            'license_plate' => 'ทะเบียนยานพาหนะ',
            'location' => 'สถานที่ไป',
            'reason' => 'เหตุผล',
            'status' => 'สถานะ',
            'date_start' => 'เริ่มวันที่',
            'time_start' => 'เริ่มเวลา',
            'date_end' => 'ถึงวันที่',
            'time_end' => 'ถึงเวลา',
            'driver_id' => 'พนักงานขับ',
            'leader_id' => 'หัวหน้างานรับรอง',
            'emp_id' => 'ผู้ขอ',
            'data_json' => 'ยานพาหนะ',
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

    // สถานที่ไห
    public function getLocationOrg()
    {
        return $this->hasOne(Categorise::class, ['code' => 'location'])->andOnCondition(['name' => 'document_org']);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getLeader()
    {
        return $this->hasOne(Employees::class, ['id' => 'leader_id']);
    }

    public function getDriver()
    {
        return $this->hasOne(Employees::class, ['id' => 'driver_id']);
    }


    // ประเภทของการจองรถ
    public function getCarType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'vehicle_type_id']);
    }

    public function getReferType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'refer_type'])->andOnCondition(['name' => 'refer_type']);
    }

    // สถานะ
    public function getVehicleStatus()
    {
        return $this->hasOne(Categorise::class, ['code' => 'status'])->andOnCondition(['name' => 'vehicle_status']);
    }

    public function getvehicleDetails()
    {
        return $this->hasMany(VehicleDetail::class, ['vehicle_id' => 'id']);
    }


    public function getTemplate()
    {
        // ชื่อแบบฟอร์มที่ใช้สำหรับการจัดเก็บ layout
        $formName = 'vehicle_layout_form';
        $template = Categorise::find()->where(['name' => $formName])->one();
        if ($template) {
            return  Url::to(['/dms/documents/show', 'ref' => $template->ref]);
        } else {
            return false;
        }
    }
    // ผู้ขอบริการ
    public function userRequest()
    {
        try {
        $emp = $this->employee;
        $createDate = $this->viewCreated()['full'] !== '' ?  $this->viewCreated()['full'] : 'ไม่ระบุ';
        return [
            'photo' => $emp->showAvatar(),
            'avatar' => $emp->getAvatar(false,$emp->departmentName()),
            'fullname' => $emp->fullname,
            'department' => $emp->departmentName(),
            'signature' => $emp->getInfo()['signature'],

        ];
        } catch (\Throwable $th) {

            return [
                'photo' => '@web/img/placeholder-img.jpg',
                'avatar' => '',
                'fullname' => '',
                'department' => '',
            ];
        }

    }

    /**
     * ผู้ร่วมเดินทาง (companions) จาก data_json['companions'].
     * ค่าที่เป็นตัวเลข = บุคลากร (resolve จากทะเบียน), ค่าอื่น = บุคคลภายนอก (free text).
     *
     * คืน single source ที่ทุกหน้า (view/show/print) และ PDF payload ใช้ร่วมกัน:
     *   items          = [['employee' => Employees|null, 'name' => string, 'is_staff' => bool], ...]
     *   names          = "ชื่อ 1, ชื่อ 2, ..."      (แนวนอน คั่น comma — PDF/legacy)
     *   names_vertical = "ชื่อ 1\nชื่อ 2\n..."        (แนวตั้ง 1 คนต่อบรรทัด)
     *   names_numbered = "1. ชื่อ 1\n2. ชื่อ 2\n..."  (แนวตั้ง มีเลขนำ)
     *   indexed        = [1 => 'ชื่อ 1', 2 => 'ชื่อ 2', ...] (วางทีละช่องตามเลขในเทมเพลต)
     *   count          = จำนวนคน
     */
    public function companions()
    {
        $raw = is_array($this->data_json)
            ? ($this->data_json['companions'] ?? [])
            : ((json_decode((string) $this->data_json, true) ?: [])['companions'] ?? []);

        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }
        $raw = is_array($raw)
            ? array_values(array_filter(array_map(static fn ($v) => trim((string) $v), $raw), static fn ($v) => $v !== ''))
            : [];

        $empIds = array_values(array_map('intval', array_filter($raw, 'ctype_digit')));
        $employees = $empIds
            ? Employees::find()->where(['id' => $empIds])->indexBy('id')->all()
            : [];

        $items = [];
        $names = [];
        $numbered = [];
        $indexed = [];
        foreach ($raw as $entry) {
            $emp = (ctype_digit($entry) && isset($employees[(int) $entry])) ? $employees[(int) $entry] : null;
            $name = $emp ? $emp->fullname() : $entry;
            $items[] = ['employee' => $emp, 'name' => $name, 'is_staff' => $emp !== null];
            $names[] = $name;
            $position = count($names);
            $numbered[] = $position . '. ' . $name;
            $indexed[$position] = $name;
        }

        return [
            'items' => $items,
            'names' => implode(', ', $names),
            'names_vertical' => implode("\n", $names),
            'names_numbered' => implode("\n", $numbered),
            'indexed' => $indexed,
            'count' => count($items),
        ];
    }

    //แสดงวันเวลาที่แสดง
    public function viewCreated()
    {
        try {
            $datetime = explode(' ', $this->created_at);
            $date = ThaiDateHelper::formatThaiDate($datetime[0]);
            $time =  substr($datetime[1], 0, 5) . '.น';
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


    // กรณีแสดงช่วงวันที่
    public function showDateRange()
    {
        return ThaiDateHelper::formatThaiDateRange($this->date_start, $this->date_end);  // แสดงผล: 1 - 3 ม.ค. 2568
    }

    // แสดงราชชื่อพนักงานขับรถ
    public function listDriver()
    {
        $arrDrivers = Employees::find()
            ->from('employees e')
            ->leftJoin('auth_assignment a', 'e.user_id = a.user_id')
            ->where(['a.item_name' => 'driver'])
            ->all();
        return ArrayHelper::map($arrDrivers, 'id', function ($model) {
            return $model->fullname;
        });
    }

    /**
     * สถานะพนักงานขับรถในวันที่ระบุ — ใครลา / ไปประชุม-อบรม-ไปราชการ ในวันนั้น
     *
     * @param string $date วันที่ในรูปแบบ Y-m-d
     * @return array ['date','total','absent','available','items' => [['emp_id','fullname','photo','position','entries' => [['type','label','icon','color','detail','date_range','date_short','status']]]]]
     */
    public static function driverStatusByDate($date)
    {
        $drivers = Employees::find()
            ->select('e.*')
            ->from('employees e')
            ->leftJoin('auth_assignment a', 'e.user_id = a.user_id')
            ->where(['a.item_name' => 'driver'])
            ->all();

        $driverIds = ArrayHelper::getColumn($drivers, 'id');
        $driverById = ArrayHelper::index($drivers, 'id');
        $items = [];

        // ช่วงวันที่แบบสั้น (ตัดปีออก) เพื่อให้แสดงได้ในแถวเดียว
        $shortRange = function ($start, $end) {
            $range = ThaiDateHelper::formatThaiDateRange($start, $end);
            return trim(preg_replace('/\s+\d{4}$/u', '', $range));
        };

        // รวมรายการของ พขร. แต่ละคน (คนเดียวอาจทั้งลาและไปอบรมคาบเกี่ยวกันได้)
        $addEntry = function ($empId, $entry) use (&$items, $driverById) {
            $empId = (int) $empId;
            if (!isset($driverById[$empId])) {
                return;
            }
            if (!isset($items[$empId])) {
                $emp = $driverById[$empId];
                $items[$empId] = [
                    'emp_id' => $empId,
                    'fullname' => $emp->fullname,
                    'photo' => $emp->showAvatar(),
                    'position' => $emp->positionName(),
                    'entries' => [],
                ];
            }

            // รายละเอียดที่ซ้ำกับชื่อประเภท (เช่น ลาพักผ่อน/ลาพักผ่อน) ไม่ต้องเก็บ
            $detail = trim((string) $entry['detail']);
            $entry['detail'] = ($detail === '' || $detail === trim((string) $entry['label'])) ? '' : $detail;

            // กันรายการซ้ำ เช่น ถูกใส่ชื่อไว้หลายบรรทัดในเรื่องเดียวกัน
            $key = $entry['type'] . '|' . $entry['label'] . '|' . $entry['date_range'];
            $items[$empId]['entries'][$key] = $entry;
        };

        if (!empty($driverIds)) {
            // 1) การลา
            $leaves = Leave::find()
                ->where(['emp_id' => $driverIds, 'deleted_at' => null])
                ->andWhere(['<=', 'date_start', $date])
                ->andWhere(['>=', 'date_end', $date])
                ->andWhere(['NOT IN', 'status', ['Cancel', 'Reject']])
                ->all();

            foreach ($leaves as $leave) {
                $typeJson = is_array($leave->leaveType?->data_json) ? $leave->leaveType->data_json : [];
                $addEntry($leave->emp_id, [
                    'type' => 'leave',
                    'label' => $leave->leaveType?->title ?? 'ลา',
                    'icon' => $typeJson['icon'] ?? '<i class="bi bi-calendar-x"></i>',
                    'color' => $typeJson['color'] ?? '#f8d7da',
                    'detail' => $leave->reason,
                    'date_range' => ThaiDateHelper::formatThaiDateRange($leave->date_start, $leave->date_end),
                    'date_short' => $shortRange($leave->date_start, $leave->date_end),
                    'status' => $leave->status,
                ]);
            }

            // 2) ไปประชุม/อบรม/ไปราชการ — เป็นผู้ขอเอง หรือถูกใส่ชื่อเป็นผู้ร่วมเดินทาง
            $memberOf = (new \yii\db\Query())
                ->select('development_id')
                ->from('development_detail')
                ->where(['name' => 'member', 'emp_id' => $driverIds]);

            $developments = Development::find()
                ->where(['deleted_at' => null])
                ->andWhere(['<=', 'date_start', $date])
                ->andWhere(['>=', 'date_end', $date])
                ->andWhere(['NOT IN', 'status', ['Cancel', 'Reject']])
                ->andWhere(['OR', ['emp_id' => $driverIds], ['id' => $memberOf]])
                ->all();

            foreach ($developments as $development) {
                $empIds = ArrayHelper::getColumn(
                    DevelopmentDetail::find()
                        ->where(['development_id' => $development->id, 'name' => 'member'])
                        ->all(),
                    'emp_id'
                );
                $empIds[] = $development->emp_id;

                $typeTitle = $development->developmentType?->title;

                foreach (array_unique($empIds) as $empId) {
                    $addEntry($empId, [
                        'type' => 'development',
                        'label' => 'อบรม/ไปราชการ',
                        'icon' => '<i class="bi bi-mortarboard"></i>',
                        'color' => '#cfe2f3',
                        'detail' => trim(($typeTitle ? $typeTitle . ': ' : '') . $development->topic),
                        'date_range' => ThaiDateHelper::formatThaiDateRange($development->date_start, $development->date_end),
                        'date_short' => $shortRange($development->date_start, $development->date_end),
                        'status' => $development->status,
                    ]);
                }
            }
        }

        // เรียงตามชื่อเพื่อให้ลำดับคงที่ทุกครั้งที่โหลด
        $items = array_values(array_map(function ($item) {
            $item['entries'] = array_values($item['entries']);
            return $item;
        }, $items));
        usort($items, fn($a, $b) => strcmp((string) $a['fullname'], (string) $b['fullname']));

        return [
            'date' => $date,
            'total' => count($drivers),
            'absent' => count($items),
            'available' => max(0, count($drivers) - count($items)),
            'items' => $items,
        ];
    }

    public function listReferType()
    {
        $list = Categorise::find()
            ->where(['name' => 'refer_type'])
            ->asArray()
            ->all();
        return ArrayHelper::map($list, 'code', 'title');
    }

    public function listDriverDetails()
    {
        return BookingDetail::find()->where(['name' => 'driver_detail', 'booking_id' => $this->id])->all();
    }

    public function sendMessage($msg = null)
    {
        $telegramMessage = $msg !== null ? $msg : VehicleTelegramNotify::buildDefaultBookingMessage($this);
        VehicleTelegramNotify::broadcastToVehicleRole($telegramMessage);
        VehicleTelegramNotify::sendVehicleChannel($telegramMessage);
        if ($msg === null) {
            VehicleTelegramNotify::notifyDriverIfSelected($this);
        }

        $lineMessage = ($msg !== null ? $msg : '') . $this->reason . 'วันเวลา ' . Yii::$app->thaiFormatter->asDate($this->date_start, 'medium') . ' เวลา' . $this->time_end . ' - ' . $this->time_end;

        try {

            $data = [];
            foreach ($this->listMembers as $item) {
                if (isset($item->employee->user->line_id)) {
                    $lineId = $item->employee->user->line_id;
                    LineMsg::sendMsg($lineId, $lineMessage);
                }
            }
            // return $data;
            //code...
        } catch (\Throwable $th) {
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

    public function showStartTime()
    {
        try {
            $time = $this->time_start;
            $formattedTime = (new DateTime($time))->format('H:i');
            return $formattedTime;
        } catch (\Throwable $th) {
            return false;
        }
    }



    //แสดง icon ถ้าเป็นเวลาปัจจุบัน

    public function showEndTime()
    {
        try {
            $time = $this->time_end;
            $formattedTime = (new DateTime($time))->format('H:i');
            return $formattedTime;
        } catch (\Throwable $th) {
            return false;
        }
    }

    // พนักงานขับที่ร้องขอ
    public function reqDriver()
    {
        try {
            return Employees::findOne($this->data_json['req_driver_id']);
        } catch (\Throwable $th) {
            return false;
        }
    }

    // section Relationships
    public function getCar()
    {
        return $this->hasOne(Asset::class, ['license_plate' => 'license_plate']);
    }

    // thai_year
    public function groupYear()
    {
        $year = self::find()
            ->andWhere(['IS NOT', 'thai_year', null])
            ->groupBy(['thai_year'])
            ->orderBy(['thai_year' => SORT_DESC])
            ->all();
        return ArrayHelper::map($year, 'thai_year', 'thai_year');
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

    public function viewStatus()
    {
        $statusName = $this->vehicleStatus?->title ?? null;
        return $this->getStatus($this->status, $statusName);
    }

    public  function getStatus($status, $statusName = null)
    {
        $count = self::find()
            ->andFilterWhere(['vehicle_type_id' => $this->vehicle_type_id])
            ->andWhere(['status' => $status])->count();
        $total = self::find()->count();
        $data = AppHelper::viewStatus($status, $statusName);
        $percent = $total > 0 ? ($count / $total * 100) : 0;

        return [
            'total' => $total,
            'count' => $count,
            'percent' => $percent,
            'title' => $data['title'],
            'color' => $data['color'],
            'view' => $data['view'],
            'icon' => $data['icon']
        ];
    }


    public function viewCarType()
    {
        try {
            $title = $this->carType?->title ?? '-';
            if ($this->vehicle_type_id == 'ambulance') {
                return $title . ' (<code>' . $this->referType->title . '</code>)';
            } else {
                return $title;
            }
        } catch (\Throwable $th) {
            // throw $th;
        }
    }

    // แสดงรายการาถานะ
    public function ListStatus()
    {
        $model = Categorise::find()
            ->where(['name' => 'vehicle_status'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
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

    // แสดงรายการประเภทการเดินทาง
    public function viewGoType()
    {
        if ($this->go_type == 1) {
            return 'ไปกลับ';
        } else {
            return 'ค้างคืน';
        }
    }

    public function listUrgent()
    {
        $model = Categorise::find()
            ->where(['name' => 'urgent'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }

    public function viewUrgent()
    {
        return $this->urgent;
    }

    //การจัดสรรร่วม Stock
    public function isShareStack()
    {
        $datas = self::find()
            ->andWhere([
                'is_shared' => 1,
                'date_start' => $this->date_start,
                'location' => $this->location, // แก้ตรงนี้ด้วยนะ 'lcoation' เป็น 'location'
            ])
            ->all();

if (count($datas) >= 1) {
    try {
        $data = '';
        $data .= '<div class="avatar-stack">';
        $targetEmpId = $this->emp_id; // หรือใช้ชื่อให้ชัดเจน

        foreach ($datas as $key => $item) {
            if ($item->emp_id != $targetEmpId) {
                continue; // ข้ามถ้าไม่ใช่ emp_id ที่ต้องการ
            }

            $emp = Employees::findOne(['id' => $item->emp_id]);
            if ($emp) {
                $data .= Html::img('@web/img/loading.gif', [
                    'class' => 'avatar-sm rounded-circle shadow lazyload',
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' => $emp->showAvatar()
                    ]
                ]);
            }
        }

        $data .= '</div>';
        return $data;
    } catch (\Throwable $th) {
        // อาจ log error หรือ throw ใหม่
    }
}

    }
    //  ภาพทีมคณะกรรมการ
    public function StackDriver()
    {
        try {
            $data = '';
            $data .= '<div class="avatar-stack">';
            foreach (VehicleDetail::find()->where(['vehicle_id' => $this->id])->all() as $key => $item) {
                $emp = Employees::findOne(['id' => $item->driver_id]);
                $data .= Html::a(Html::img('@web/img/loading.gif', [
                    'class' => 'avatar-sm rounded-circle shadow lazyload',
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' => $emp->showAvatar()
                    ]
                ]),['/booking/vehicle/work-update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> บันทึกภาระกิจการใช้รถยนต์'], ['class' => 'open-modal', 'data' => ['size' => 'modal-lg']]);
            }
            $data .= '</div>';
            return $data;
        } catch (\Throwable $th) {
        }
    }

    public function listDocument()
    {
        $me = UserHelper::GetEmployee();
        $document = DocumentTags::find()->where(['tag_id' => 1, 'name' => 'comment'])->all();
        return ArrayHelper::map($document, 'id', function ($model) {
            return $model->document->topic;
        });
    }

    // แสดงรายการทะยานพาหนะ
    public function ListCarItems()
    {
        $items = Asset::find()->andWhere([
            'AND',
            ['IS NOT', 'license_plate', null],
            ['<>', 'license_plate', ''],
            ['<>', 'license_plate', ' ']
        ])->all();
        return ArrayHelper::map($items, 'license_plate', 'license_plate');
    }

    public function Approve()
    {
        $emp = UserHelper::GetEmployee();
        $department_id = $emp->department;
        $sql = "SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, 
                    t1.id,
                    t1.name as t1name,

                    t1.data_json->>'\$.leader1' as t1_leader,
                    t1.data_json->>'\$.leader1_fullname' as t1_leader_fullname,
                    t2.id,t2.name as t2name,

                    t2.data_json->>'\$.leader1' as t2_leader,
                    t2.data_json->>'\$.leader1_fullname' as t2_leader_fullname,
                    t3.id,t3.name as t3name,
                    t3.data_json->>'\$.leader1' as t3_leader,
                    t3.data_json->>'\$.leader1_fullname' as t3_leader_fullname
                    FROM tree t1
                    JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
                    JOIN tree t3 ON t2.lft BETWEEN t3.lft AND t3.rgt AND t2.lvl = t3.lvl + 1
                    WHERE t1.id  = :id";
        $query = Yii::$app
            ->db
            ->createCommand($sql)
            ->bindValue('id', $department_id)
            ->queryOne();
        if ($query) {
            $leader = Employees::find()->where(['id' => $query['t1_leader']])->one();
            $leaderGroup = Employees::find()->where(['id' => $query['t2_leader']])->one();
            $director = Employees::find()->where(['id' => $query['t3_leader']])->one();

            return [
                'approve_1' => (isset($query['t1_leader']) && $leader) ? [
                    'id' => $query['t1_leader'],
                    'avatar' => $leader ? $leader->getAvatar(false) : '',
                    'fullname' => $leader ? $leader->fullname : '',
                    'position' => $leader ? $leader->positionName() : '',
                    'signature' => $leader ? $leader->signature() : '',
                    'title' => 'หัวหน้างาน'
                ] : [],
                'approve_2' => ($leaderGroup) ? [
                    'id' => $query['t2_leader'],
                    'avatar' => $leaderGroup ? $leaderGroup->getAvatar(false) : '',
                    'fullname' => $leaderGroup ? $leaderGroup->fullname : '',
                    'position' => $leaderGroup ? $leaderGroup->positionName() : '',
                    'signature' => $leaderGroup ? $leaderGroup->signature() : '',
                    'title' => 'หัวหน้ากลุ่มงาน'
                ] : [],
                'approve_3' => ($director) ? [
                    'id' => $query['t3_leader'],
                    'avatar' => $director ? $director->getAvatar(false) : '',
                    'fullname' => $director ? $director->fullname : '',
                    'position' => $director ? $director->positionName() : '',
                    'signature' => $director ? $director->signature() : '',
                    'title' => 'ผู้อำนวยการ'
                ] : []
            ];
        } else {
            // ถ้าเป็นหัวหน้าลาเอง
            $leader = Employees::find()->where(['id' => $emp->id])->one();
            if ($leader) {
                return [
                    'approve_1' => [
                        'id' => $leader->id,
                        'avatar' => $leader->getAvatar(false),
                        'fullname' => $leader->fullname,
                        'position' => $leader->positionName(),
                        'title' => 'หัวหน้างาน'
                    ],
                    'approve_2' => [
                        'id' => $leader->id,
                        'fullname' => $leader->fullname,
                        'position' => $leader->positionName(),
                        'title' => 'หัวหน้ากลุ่มงาน'
                    ],
                    'approve_3' => [
                        'id' => $leader->id,
                        'fullname' => $leader->fullname,
                        'position' => $leader->positionName(),
                        'title' => 'ผู้อำนวยการ'
                    ]
                ];
            } else {
                return [
                    'approve_1' => [],
                    'approve_2' => [],
                    'approve_3' => []
                ];
            }
        }
    }


    // รายงานแยกตามเดือน
    public function getChartSummary($name)
    {
        // return $name;
        $arr =  Vehicle::find()
            ->select([
                'thai_year',
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 1 THEN 1 END) AS m1'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 2 THEN 1 END) AS m2'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 3 THEN 1 END) AS m3'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 4 THEN 1 END) AS m4'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 5 THEN 1 END) AS m5'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 6 THEN 1 END) AS m6'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 7 THEN 1 END) AS m7'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 8 THEN 1 END) AS m8'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 9 THEN 1 END) AS m9'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 10 THEN 1 END) AS m10'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 11 THEN 1 END) AS m11'),
                new Expression('COUNT(CASE WHEN MONTH(d.date_start) = 12 THEN 1 END) AS m12'),
            ])
            ->leftJoin('vehicle_detail d', 'd.vehicle_id = vehicle.id')
            // ->where(['thai_year' => $this->thai_year, 'vehicle_type_id' => $name])
            ->where(['vehicle_type_id' => $name])
            ->andWhere(['IN', 'd.status', ['Approve']])
            ->andFilterWhere(['thai_year' => $this->thai_year])
            ->groupBy('thai_year')
            ->asArray()
            ->one();
        // กำหนดค่าเริ่มต้น 0 ให้เดือนที่ไม่มีข้อมูล
        for ($i = 1; $i <= 12; $i++) {
            $key = 'm' . $i;
            if (!isset($arr[$key])) {
                $arr[$key] = 0;
            }
        }
        return $arr;
    }
    // รายงานแยกตามเดือน
    public function getChartSummaryAmbulance($ambulanceType)
    {
        // return $name;
        $arr =  Vehicle::find()
            ->select([
                'thai_year',
                new Expression('COUNT(CASE WHEN MONTH(date_start) = 1 THEN 1 END) AS m1'),
                new Expression('COUNT(CASE WHEN MONTH(date_start) = 2 THEN 1 END) AS m2'),
                new Expression('COUNT(CASE WHEN MONTH(date_start) = 3 THEN 1 END) AS m3'),
                new Expression('COUNT(CASE WHEN MONTH(date_start) = 4 THEN 1 END) AS m4'),
                new Expression('COUNT(CASE WHEN MONTH(date_start) = 5 THEN 1 END) AS m5'),
                new Expression('COUNT(CASE WHEN MONTH(date_start) = 6 THEN 1 END) AS m6'),
                new Expression('COUNT(CASE WHEN MONTH(date_start) = 7 THEN 1 END) AS m7'),
                new Expression('COUNT(CASE WHEN MONTH(date_start) = 8 THEN 1 END) AS m8'),
                new Expression('COUNT(CASE WHEN MONTH(date_start) = 9 THEN 1 END) AS m9'),
                new Expression('COUNT(CASE WHEN MONTH(date_start) = 10 THEN 1 END) AS m10'),
                new Expression('COUNT(CASE WHEN MONTH(date_start) = 11 THEN 1 END) AS m11'),
                new Expression('COUNT(CASE WHEN MONTH(date_start) = 12 THEN 1 END) AS m12'),
            ])
            //  ->leftJoin('vehicle_detail d', 'd.vehicle_id = vehicle.id')
            // ->where(['thai_year' => $this->thai_year, 'vehicle_type_id' => $name])

            ->andWhere(['vehicle_type_id' => 'ambulance'])
            ->andWhere(['refer_type' => $ambulanceType])
            //  ->where(['vehicle_type_id' => 'ambulance'])
            //  ->andWhere(['IN','d.status',['Approve']])
            ->andFilterWhere(['thai_year' => $this->thai_year])

            ->groupBy('thai_year')
            ->asArray()
            ->one();
        // กำหนดค่าเริ่มต้น 0 ให้เดือนที่ไม่มีข้อมูล
        for ($i = 1; $i <= 12; $i++) {
            $key = 'm' . $i;
            if (!isset($arr[$key])) {
                $arr[$key] = 0;
            }
        }
        return $arr;
    }

    /** หน่วยงานที่ขอใช้รถมากที่สุด (มาก → น้อย) */
    public function departmentSummary($limit = 8)
    {
        $query = Vehicle::find()
            ->select(['name' => 'd.name', 'total' => new Expression('COUNT(v.id)')])
            ->from(['v' => Vehicle::tableName()])
            ->leftJoin(['e' => Employees::tableName()], 'e.id = v.emp_id')
            ->leftJoin(['d' => Organization::tableName()], 'd.id = e.department')
            ->where(['IN', 'v.status', self::ACTIVE_STATUSES])
            ->andWhere(['IS NOT', 'd.name', null])
            ->andWhere(['<>', 'd.name', ''])
            ->andFilterWhere(['v.thai_year' => $this->thai_year])
            ->andFilterWhere(['v.vehicle_type_id' => $this->vehicle_type_id])
            ->groupBy(['d.id', 'd.name'])
            ->orderBy(['total' => SORT_DESC]);

        if ($limit !== null) {
            $query->limit((int) $limit);
        }

        return $query->asArray()->all();
    }

    /** รถที่ถูกขอใช้บ่อยที่สุดในปีงบที่เลือก (มาก → น้อย) */
    public function carSummary($limit = 8)
    {
        $query = Vehicle::find()
            ->select([
                'license_plate',
                new Expression('COUNT(id) AS total'),
            ])
            ->where(['IN', 'status', self::ACTIVE_STATUSES])
            ->andWhere(['IS NOT', 'license_plate', null])
            ->andWhere(['<>', 'license_plate', ''])
            ->andFilterWhere(['thai_year' => $this->thai_year])
            ->andFilterWhere(['vehicle_type_id' => $this->vehicle_type_id])
            ->groupBy('license_plate')
            ->orderBy(['total' => SORT_DESC]);

        if ($limit !== null) {
            $query->limit((int) $limit);
        }

        return $query->asArray()->all();
    }

    /** คำขอ/การจัดสรรที่ถือว่ามีผล (ไม่ใช่รออนุมัติ ไม่ใช่ยกเลิก) */
    public const ACTIVE_STATUSES = ['Approve', 'Pass', 'Success'];

    /** สถานะรายวันที่นับเป็น "งาน" ของพนักงานขับรถ */
    public const DRIVER_WORK_STATUSES = self::ACTIVE_STATUSES;

    /**
     * เพดานระยะทางต่อการจัดสรร 1 วันที่ถือว่าสมเหตุสมผล (กม.)
     *
     * ที่ต้องมีเพราะผู้ใช้บางรายกรอกเลขไมล์ลงช่องระยะทาง ทำให้ยอดรวมเพี้ยนหลักแสน
     * รายการที่เกินเพดานจะไม่ถูกนำไปรวมยอด แต่จะถูกนับไว้ให้เจ้าหน้าที่ตามแก้
     */
    public const DISTANCE_SANITY_MAX = 2000;

    /** ลำดับเดือนตามปีงบประมาณ ต.ค. → ก.ย. */
    public const FISCAL_MONTHS = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

    public const FISCAL_MONTH_LABELS = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];

    /**
     * สรุปปริมาณงานพนักงานขับรถ (เรียงจากมากไปน้อย)
     *
     * นับจาก vehicle_detail.driver_id (การจัดสรรรายวัน) ไม่ใช่ vehicle.driver_id
     * เพราะการจัดสรร พขร. จริงเกิดที่ระดับรายวัน — vehicle.driver_id แทบไม่ถูกเซ็ต
     * 1 แถวรายวัน = 1 งาน คำขอที่ค้างคืนหลายวันจึงนับตามจำนวนวันที่จัดสรร
     */
    public function driverSummary($limit = null)
    {
        $query = (new \yii\db\Query())
            ->select([
                'driver_id' => 'd.driver_id',
                'fullname' => new Expression("TRIM(CONCAT(COALESCE(e.prefix, ''), COALESCE(e.fname, ''), ' ', COALESCE(e.lname, '')))"),
                'total' => new Expression('COUNT(d.id)'),
            ])
            ->from(['d' => VehicleDetail::tableName()])
            ->innerJoin(['v' => self::tableName()], 'v.id = d.vehicle_id')
            ->leftJoin(['e' => Employees::tableName()], 'e.id = d.driver_id')
            ->where(['d.deleted_at' => null])
            ->andWhere(['IN', 'd.status', self::DRIVER_WORK_STATUSES])
            ->andWhere(['IS NOT', 'd.driver_id', null])
            ->andWhere(['<>', 'd.driver_id', ''])
            ->andFilterWhere(['v.thai_year' => $this->thai_year])
            ->andFilterWhere(['v.vehicle_type_id' => $this->vehicle_type_id])
            ->groupBy(['d.driver_id', 'e.prefix', 'e.fname', 'e.lname'])
            ->orderBy(['total' => SORT_DESC]);

        if ($limit !== null) {
            $query->limit((int) $limit);
        }

        return $query->all();
    }

    /* ------------------------------------------------------------------
     * ข้อมูลสำหรับหน้า Dashboard ยานพาหนะ
     * ------------------------------------------------------------------ */

    /**
     * ตัวเลขสรุปหัวหน้า Dashboard — สถานะคำขอ + ปริมาณการเดินทางที่บันทึกจริง
     *
     * @return array{pending:int, allocated:int, success:int, cancelled:int, total:int,
     *               distance:float, oil_price:float, trips:int, logged:int, unlogged:int}
     */
    public function dashboardSummary(): array
    {
        $statusRows = (new \yii\db\Query())
            ->select(['status', 'total' => new Expression('COUNT(*)')])
            ->from(self::tableName())
            ->where(['deleted_at' => null])
            ->andFilterWhere(['thai_year' => $this->thai_year])
            ->andFilterWhere(['vehicle_type_id' => $this->vehicle_type_id])
            ->groupBy('status')
            ->all();

        $byStatus = [];
        foreach ($statusRows as $row) {
            $byStatus[(string) $row['status']] = (int) $row['total'];
        }

        // ปริมาณจริงมาจากการจัดสรรรายวัน ไม่ใช่หัวคำขอ
        $loggedExpr = '(COALESCE(d.distance_km, 0) > 0 OR COALESCE(d.oil_price, 0) > 0 OR COALESCE(d.oil_liter, 0) > 0)';
        $sane = 'COALESCE(d.distance_km, 0) BETWEEN 0 AND ' . (int) self::DISTANCE_SANITY_MAX;
        $trip = (new \yii\db\Query())
            ->select([
                'trips' => new Expression('COUNT(*)'),
                'distance' => new Expression("COALESCE(SUM(CASE WHEN {$sane} THEN d.distance_km ELSE 0 END), 0)"),
                'distance_anomalies' => new Expression("SUM(CASE WHEN {$sane} THEN 0 ELSE 1 END)"),
                'oil_price' => new Expression('COALESCE(SUM(d.oil_price), 0)'),
                'logged' => new Expression("SUM(CASE WHEN {$loggedExpr} THEN 1 ELSE 0 END)"),
            ])
            ->from(['d' => VehicleDetail::tableName()])
            ->innerJoin(['v' => self::tableName()], 'v.id = d.vehicle_id')
            ->where(['d.deleted_at' => null])
            ->andWhere(['IN', 'd.status', self::ACTIVE_STATUSES])
            ->andFilterWhere(['v.thai_year' => $this->thai_year])
            ->andFilterWhere(['v.vehicle_type_id' => $this->vehicle_type_id])
            ->one();

        $trips = (int) ($trip['trips'] ?? 0);
        $logged = (int) ($trip['logged'] ?? 0);

        return [
            'pending' => (int) ($byStatus['Pending'] ?? 0),
            'allocated' => (int) ($byStatus['Pass'] ?? 0) + (int) ($byStatus['Approve'] ?? 0),
            'success' => (int) ($byStatus['Success'] ?? 0),
            'cancelled' => (int) ($byStatus['Cancel'] ?? 0),
            'total' => array_sum($byStatus),
            'distance' => (float) ($trip['distance'] ?? 0),
            'distance_anomalies' => (int) ($trip['distance_anomalies'] ?? 0),
            'oil_price' => (float) ($trip['oil_price'] ?? 0),
            'trips' => $trips,
            'logged' => $logged,
            'unlogged' => max(0, $trips - $logged),
        ];
    }

    /** จัดผลลัพธ์ที่ group ตามเดือนปฏิทินให้เรียงตามปีงบ ต.ค. → ก.ย. */
    private static function toFiscalSeries(array $rowsByMonth): array
    {
        $series = [];
        foreach (self::FISCAL_MONTHS as $m) {
            $series[] = (float) ($rowsByMonth[$m] ?? 0);
        }

        return $series;
    }

    /**
     * จำนวนการใช้รถรายเดือน แยกตามประเภทรถ และแยกตามประเภทภารกิจของรถฉุกเฉิน
     *
     * นับจาก vehicle_detail (การจัดสรรรายวัน) ด้วยสถานะที่ใช้งานจริง
     * ของเดิมกรอง d.status = 'Approve' อย่างเดียว ซึ่งเลิกใช้ไปแล้ว กราฟจึงเป็นศูนย์ทั้งปี
     */
    public function monthlyUsageSummary(): array
    {
        $base = fn() => (new \yii\db\Query())
            ->from(['d' => VehicleDetail::tableName()])
            ->innerJoin(['v' => self::tableName()], 'v.id = d.vehicle_id')
            ->where(['d.deleted_at' => null])
            ->andWhere(['IN', 'd.status', self::ACTIVE_STATUSES])
            ->andWhere(['IS NOT', 'd.date_start', null])
            ->andFilterWhere(['v.thai_year' => $this->thai_year]);

        $byType = [];
        $rows = $base()
            ->select([
                'type' => 'v.vehicle_type_id',
                'm' => new Expression('MONTH(d.date_start)'),
                'total' => new Expression('COUNT(*)'),
            ])
            ->groupBy(['v.vehicle_type_id', new Expression('MONTH(d.date_start)')])
            ->all();
        foreach ($rows as $row) {
            $byType[(string) $row['type']][(int) $row['m']] = (int) $row['total'];
        }

        $byRefer = [];
        $referRows = $base()
            ->select([
                'refer' => new Expression('UPPER(v.refer_type)'),
                'm' => new Expression('MONTH(d.date_start)'),
                'total' => new Expression('COUNT(*)'),
            ])
            ->andWhere(['v.vehicle_type_id' => 'ambulance'])
            ->groupBy([new Expression('UPPER(v.refer_type)'), new Expression('MONTH(d.date_start)')])
            ->all();
        foreach ($referRows as $row) {
            $byRefer[(string) $row['refer']][(int) $row['m']] = (int) $row['total'];
        }

        $ambulance = self::toFiscalSeries($byType['ambulance'] ?? []);
        $refer = self::toFiscalSeries($byRefer['REFER'] ?? []);
        $ems = self::toFiscalSeries($byRefer['EMS'] ?? []);
        $normal = self::toFiscalSeries($byRefer['NORMAL'] ?? []);

        // บางคำขอไม่ได้ระบุ refer_type — กันไม่ให้ยอดกราฟแยกประเภทหายไปจากยอดรวม
        $other = [];
        foreach ($ambulance as $i => $total) {
            $other[] = max(0, $total - ($refer[$i] + $ems[$i] + $normal[$i]));
        }

        return [
            'labels' => self::FISCAL_MONTH_LABELS,
            'official' => self::toFiscalSeries($byType['official'] ?? []),
            'personal' => self::toFiscalSeries($byType['personal'] ?? []),
            'ambulance' => $ambulance,
            'refer' => $refer,
            'ems' => $ems,
            'normal' => $normal,
            'other' => $other,
        ];
    }

    /** ค่าน้ำมันและระยะทางรายเดือนตามปีงบ */
    public function monthlyCostSummary(): array
    {
        $rows = (new \yii\db\Query())
            ->select([
                'm' => new Expression('MONTH(d.date_start)'),
                'oil_price' => new Expression('COALESCE(SUM(d.oil_price), 0)'),
                'distance' => new Expression(
                    'COALESCE(SUM(CASE WHEN COALESCE(d.distance_km, 0) BETWEEN 0 AND '
                    . (int) self::DISTANCE_SANITY_MAX . ' THEN d.distance_km ELSE 0 END), 0)'
                ),
            ])
            ->from(['d' => VehicleDetail::tableName()])
            ->innerJoin(['v' => self::tableName()], 'v.id = d.vehicle_id')
            ->where(['d.deleted_at' => null])
            ->andWhere(['IN', 'd.status', self::ACTIVE_STATUSES])
            ->andWhere(['IS NOT', 'd.date_start', null])
            ->andFilterWhere(['v.thai_year' => $this->thai_year])
            ->andFilterWhere(['v.vehicle_type_id' => $this->vehicle_type_id])
            ->groupBy([new Expression('MONTH(d.date_start)')])
            ->all();

        $oil = [];
        $distance = [];
        foreach ($rows as $row) {
            $oil[(int) $row['m']] = (float) $row['oil_price'];
            $distance[(int) $row['m']] = (float) $row['distance'];
        }

        return [
            'labels' => self::FISCAL_MONTH_LABELS,
            'oil_price' => self::toFiscalSeries($oil),
            'distance' => self::toFiscalSeries($distance),
        ];
    }

    /** คำขอที่ยังรอจัดสรร เรียงตามวันเดินทางที่ใกล้ที่สุด */
    public function pendingRequests($limit = 6)
    {
        return Vehicle::find()
            ->where(['status' => 'Pending', 'deleted_at' => null])
            ->andFilterWhere(['thai_year' => $this->thai_year])
            ->andFilterWhere(['vehicle_type_id' => $this->vehicle_type_id])
            ->with(['employee', 'locationOrg'])
            ->orderBy(['date_start' => SORT_ASC, 'time_start' => SORT_ASC])
            ->limit((int) $limit)
            ->all();
    }

    /* ------------------------------------------------------------------
     * ผลประเมินความพึงพอใจการใช้รถ
     * คะแนนเก็บใน vehicle_detail.data_json.satisfaction.score (taxonomy 'rating' 1-5)
     * ------------------------------------------------------------------ */

    private const SATISFACTION_SCORE_SQL = "CAST(JSON_UNQUOTE(JSON_EXTRACT(d.data_json, '$.satisfaction.score')) AS UNSIGNED)";

    /** ฐานคิวรีภารกิจที่เสร็จสิ้น กรองตามปีงบ/ประเภทรถที่เลือกอยู่ */
    private function satisfactionBaseQuery(): \yii\db\Query
    {
        return (new \yii\db\Query())
            ->from(['d' => VehicleDetail::tableName()])
            ->innerJoin(['v' => self::tableName()], 'v.id = d.vehicle_id')
            ->where(['d.deleted_at' => null])
            ->andWhere(['d.status' => 'Success'])
            ->andFilterWhere(['v.thai_year' => $this->thai_year])
            ->andFilterWhere(['v.vehicle_type_id' => $this->vehicle_type_id]);
    }

    /** เฉพาะรายการที่ผู้ขอประเมินแล้ว */
    private function satisfactionRatedQuery(): \yii\db\Query
    {
        return $this->satisfactionBaseQuery()
            ->andWhere(new Expression(self::SATISFACTION_SCORE_SQL . ' BETWEEN 1 AND 5'));
    }

    /**
     * สรุปภาพรวม: คะแนนเฉลี่ย จำนวนผู้ประเมิน อัตราการตอบกลับ และการกระจายรายระดับ
     *
     * @return array{avg:float, total:int, success:int, rate:int, items:array}
     */
    public function satisfactionSummary(): array
    {
        $scoreExp = new Expression(self::SATISFACTION_SCORE_SQL);

        $success = (int) $this->satisfactionBaseQuery()->count();
        $rows = $this->satisfactionRatedQuery()
            ->select(['score' => $scoreExp, 'total' => new Expression('COUNT(*)')])
            ->groupBy([$scoreExp])
            ->all();

        $counts = [];
        $total = 0;
        $sum = 0;
        foreach ($rows as $row) {
            $score = (int) $row['score'];
            $count = (int) $row['total'];
            $counts[$score] = $count;
            $total += $count;
            $sum += $score * $count;
        }

        $items = [];
        foreach (VehicleDetail::listRating() as $code => $title) {
            $code = (int) $code;
            $count = $counts[$code] ?? 0;
            $items[$code] = [
                'code' => $code,
                'title' => $title,
                'count' => $count,
                'p' => $total > 0 ? (int) round(($count / $total) * 100) : 0,
            ];
        }
        krsort($items);

        return [
            'avg' => $total > 0 ? round($sum / $total, 2) : 0.0,
            'total' => $total,
            'success' => $success,
            'rate' => $success > 0 ? (int) round(($total / $success) * 100) : 0,
            'items' => array_values($items),
        ];
    }

    /** คะแนนเฉลี่ยรายพนักงานขับรถ (เรียงจากคะแนนสูงสุด) */
    public function satisfactionDriverSummary($limit = 10, $minCount = 1): array
    {
        $query = $this->satisfactionRatedQuery()
            ->select([
                'driver_id' => 'd.driver_id',
                'fullname' => new Expression("TRIM(CONCAT(COALESCE(e.prefix, ''), COALESCE(e.fname, ''), ' ', COALESCE(e.lname, '')))"),
                'total' => new Expression('COUNT(*)'),
                'avg_score' => new Expression('AVG(' . self::SATISFACTION_SCORE_SQL . ')'),
            ])
            ->leftJoin(['e' => Employees::tableName()], 'e.id = d.driver_id')
            ->andWhere(['IS NOT', 'd.driver_id', null])
            ->andWhere(['<>', 'd.driver_id', ''])
            ->groupBy(['d.driver_id', 'e.prefix', 'e.fname', 'e.lname'])
            ->having(['>=', new Expression('COUNT(*)'), (int) $minCount])
            ->orderBy(['avg_score' => SORT_DESC, 'total' => SORT_DESC]);

        if ($limit !== null) {
            $query->limit((int) $limit);
        }

        return $query->all();
    }

    /** ข้อเสนอแนะล่าสุดจากผู้ขอ */
    public function satisfactionComments($limit = 5): array
    {
        $commentExp = "JSON_UNQUOTE(JSON_EXTRACT(d.data_json, '$.satisfaction.comment'))";
        $atExp = "JSON_UNQUOTE(JSON_EXTRACT(d.data_json, '$.satisfaction.at'))";

        return $this->satisfactionRatedQuery()
            ->select([
                'code' => 'v.code',
                'reason' => 'v.reason',
                'license_plate' => 'd.license_plate',
                'score' => new Expression(self::SATISFACTION_SCORE_SQL),
                'comment' => new Expression($commentExp),
                'rated_at' => new Expression($atExp),
                'driver_name' => new Expression("TRIM(CONCAT(COALESCE(e.prefix, ''), COALESCE(e.fname, ''), ' ', COALESCE(e.lname, '')))"),
            ])
            ->leftJoin(['e' => Employees::tableName()], 'e.id = d.driver_id')
            ->andWhere(['<>', new Expression($commentExp), ''])
            ->andWhere(['IS NOT', new Expression($commentExp), null])
            ->orderBy(new Expression($atExp . ' DESC'))
            ->limit((int) $limit)
            ->all();
    }

    // รายงานค่าใช้จ่ายรถ
    public function getPriceSummary()
    {
        // return $name;
        $arr = Vehicle::find()
            ->select([
                'thai_year',
                new Expression('SUM(CASE WHEN MONTH(d.date_start) = 1 THEN d.oil_price ELSE 0 END) AS m1'),
                new Expression('SUM(CASE WHEN MONTH(d.date_start) = 2 THEN d.oil_price ELSE 0 END) AS m2'),
                new Expression('SUM(CASE WHEN MONTH(d.date_start) = 3 THEN d.oil_price ELSE 0 END) AS m3'),
                new Expression('SUM(CASE WHEN MONTH(d.date_start) = 4 THEN d.oil_price ELSE 0 END) AS m4'),
                new Expression('SUM(CASE WHEN MONTH(d.date_start) = 5 THEN d.oil_price ELSE 0 END) AS m5'),
                new Expression('SUM(CASE WHEN MONTH(d.date_start) = 6 THEN d.oil_price ELSE 0 END) AS m6'),
                new Expression('SUM(CASE WHEN MONTH(d.date_start) = 7 THEN d.oil_price ELSE 0 END) AS m7'),
                new Expression('SUM(CASE WHEN MONTH(d.date_start) = 8 THEN d.oil_price ELSE 0 END) AS m8'),
                new Expression('SUM(CASE WHEN MONTH(d.date_start) = 9 THEN d.oil_price ELSE 0 END) AS m9'),
                new Expression('SUM(CASE WHEN MONTH(d.date_start) = 10 THEN d.oil_price ELSE 0 END) AS m10'),
                new Expression('SUM(CASE WHEN MONTH(d.date_start) = 11 THEN d.oil_price ELSE 0 END) AS m11'),
                new Expression('SUM(CASE WHEN MONTH(d.date_start) = 12 THEN d.oil_price ELSE 0 END) AS m12'),
            ])
            ->leftJoin('vehicle_detail d', 'd.vehicle_id = vehicle.id')
            ->andWhere(['NOT IN', 'vehicle.status', ['Pending', 'Cancel']])
            ->andFilterWhere(['vehicle.thai_year' => $this->thai_year])
            ->groupBy('thai_year')
            ->asArray()
            ->one();

        // กำหนดค่าเริ่มต้น 0 ให้เดือนที่ไม่มีข้อมูล
        for ($i = 1; $i <= 12; $i++) {
            $key = 'm' . $i;
            if (!isset($arr[$key])) {
                $arr[$key] = 0;
            }
        }
        return $arr;
    }


    public function sendMessageTelegram()
    {
        //ส่งการแจ้งเตือนทาง Telegram

        $message = <<<MSG
                        📌 <b>{$this->reason}</b>\n
                        🧑‍💼 <b>ผู้ขอ:</b> นายสมชาย ใจดี\n
                        📍 <b>สถานที่:</b> ศาลากลางจังหวัด\n
                        📅 <b>วันที่:</b> 1 มิ.ย. 2567\n
                        🕒 <b>เวลา:</b> 08:30 - 16:00 น.\n
                        🚗 <b>ประเภทรถ:</b> รถตู้\n
                        // 🔗 <a href="https://your-app.com/booking/{$id}">ดูรายละเอียด</a>
                        MSG;

        $response = Yii::$app->telegram->sendMessage('book_vehicle', $message, [
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ]);
    }
}
