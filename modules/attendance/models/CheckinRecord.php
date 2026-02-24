<?php

namespace app\modules\attendance\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\components\SiteHelper;
use app\modules\approveV2\models\Approve;

/**
 * บันทึกการลงเวลาเข้างาน.
 *
 * @property int $id
 * @property int $emp_id
 * @property string $checkin_at
 * @property string $method
 * @property string $check_type in=บันทึกเข้า, out=บันทึกออก
 * @property string|null $lat
 * @property string|null $lng
 * @property int|null $location_id
 * @property int $is_in_location
 * @property string|null $out_of_location_reason
 * @property string|null $photo_path
 * @property string|null $qr_token
 * @property array|null $data_json
 * @property string $status
 * @property int|null $approved_by
 * @property string|null $approved_at
 * @property string|null $comment
 */
class CheckinRecord extends \yii\db\ActiveRecord
{
    const METHOD_QRCODE = 'qrcode';
    const METHOD_PHOTO = 'photo';
    const METHOD_MANUAL = 'manual';

    const CHECK_TYPE_IN = 'in';
    const CHECK_TYPE_OUT = 'out';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public static function tableName()
    {
        return 'checkin_record';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
            BlameableBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['emp_id', 'checkin_at', 'method', 'check_type'], 'required'],
            [['emp_id', 'location_id', 'is_in_location', 'approved_by', 'created_by', 'updated_by'], 'integer'],
            [['checkin_at', 'approved_at', 'created_at', 'updated_at'], 'safe'],
            [['out_of_location_reason', 'comment'], 'string'],
            [['method'], 'in', 'range' => [self::METHOD_QRCODE, self::METHOD_PHOTO, self::METHOD_MANUAL]],
            [['check_type'], 'in', 'range' => [self::CHECK_TYPE_IN, self::CHECK_TYPE_OUT]],
            [['check_type'], 'default', 'value' => self::CHECK_TYPE_IN],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED]],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['is_in_location'], 'default', 'value' => 1],
            [['lat', 'lng'], 'number'],
            [['photo_path', 'qr_token'], 'string', 'max' => 500],
            [['data_json'], 'safe'],
            ['out_of_location_reason', 'required', 'when' => function ($m) {
                return (int)$m->is_in_location === 0;
            }, 'message' => 'กรุณากรอกเหตุผลเมื่อลงเวลานอกบริเวณ'],
            [['location_id'], 'exist', 'targetClass' => CheckinLocation::class, 'targetAttribute' => ['location_id' => 'id']],
            [['emp_id'], 'exist', 'targetClass' => Employees::class, 'targetAttribute' => ['emp_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_id' => 'พนักงาน',
            'checkin_at' => 'วันเวลาที่ลงเวลา',
            'method' => 'วิธีลงเวลา',
            'check_type' => 'ประเภทการลง',
            'lat' => 'Latitude',
            'lng' => 'Longitude',
            'location_id' => 'จุดลงเวลา',
            'is_in_location' => 'อยู่ในบริเวณ',
            'out_of_location_reason' => 'เหตุผล (นอกบริเวณ)',
            'photo_path' => 'รูปถ่าย',
            'qr_token' => 'QR ที่สแกน',
            'data_json' => 'ข้อมูลเพิ่ม',
            'status' => 'สถานะ',
            'approved_by' => 'ผู้อนุมัติ',
            'approved_at' => 'อนุมัติเมื่อ',
            'comment' => 'ความเห็น',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
        ];
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getLocation()
    {
        return $this->hasOne(CheckinLocation::class, ['id' => 'location_id']);
    }

    public function getApprover()
    {
        return $this->hasOne(Employees::class, ['id' => 'approved_by']);
    }

    public function getMethodLabel()
    {
        $labels = [
            self::METHOD_QRCODE => 'สแกน QR',
            self::METHOD_PHOTO => 'ถ่ายรูป',
            self::METHOD_MANUAL => 'กดลงเวลา',
        ];
        return $labels[$this->method] ?? $this->method;
    }

    public function getCheckTypeLabel()
    {
        $labels = [
            self::CHECK_TYPE_IN => 'บันทึกเข้า',
            self::CHECK_TYPE_OUT => 'บันทึกออก',
        ];
        return $labels[$this->check_type] ?? $this->check_type;
    }

    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_PENDING => 'รออนุมัติ',
            self::STATUS_APPROVED => 'อนุมัติแล้ว',
            self::STATUS_REJECTED => 'ไม่อนุมัติ',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    /**
     * สร้างรายการ approve ให้หัวหน้าอนุมัติ (level=1).
     * เรียกหลัง save checkin_record ครั้งแรก.
     */
    public function createApproveRecord()
    {
        $approve = Approve::findOne(['name' => 'checkin', 'from_id' => (string)$this->id]);
        if ($approve) {
            return $approve;
        }
        $me = UserHelper::GetEmployee();
        $leaderId = $this->getLeaderEmpId();
        if (!$leaderId) {
            $leaderId = $me->id; // fallback ตัวเอง
        }
        $approve = new Approve();
        $approve->from_id = (string)$this->id;
        $approve->name = 'checkin';
        $approve->emp_id = $leaderId;
        $approve->title = 'หัวหน้าอนุมัติการลงเวลา';
        $approve->data_json = ['label' => 'อนุมัติการลงเวลา'];
        $approve->level = 1;
        $approve->status = 'Pending';
        $approve->save(false);
        return $approve;
    }

    /**
     * หา emp_id ของผู้อนุมัติ (ใช้ผู้อำนวยการจาก SiteHelper).
     */
    protected function getLeaderEmpId()
    {
        $info = SiteHelper::viewDirector();
        return isset($info['id']) ? (int)$info['id'] : null;
    }

    /**
     * อัปเดต status หลังหัวหน้าอนุมัติ/ไม่อนุมัติ.
     */
    public function applyApproveResult($status, $comment = null, $approverEmpId = null)
    {
        $this->status = ($status === 'Pass' || $status === 'Y') ? self::STATUS_APPROVED : self::STATUS_REJECTED;
        $this->approved_at = date('Y-m-d H:i:s');
        if ($approverEmpId !== null) {
            $this->approved_by = $approverEmpId;
        } else {
            $me = UserHelper::GetEmployee();
            $this->approved_by = $me ? $me->id : null;
        }
        if ($comment !== null) {
            $this->comment = $comment;
        }
        return $this->save(false);
    }
}
