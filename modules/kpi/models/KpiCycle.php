<?php

namespace app\modules\kpi\models;

use app\modules\hr\models\Employees;
use app\modules\jd\models\JdEmployee;
use Yii;
use yii\db\ActiveRecord;

/**
 * ชุด KPI ประจำปีของพนักงาน 1 คน / 1 ปีงบประมาณ (ต.ค.–ก.ย.)
 *
 * @property int $id
 * @property int $emp_id
 * @property int $fiscal_year ปีงบประมาณ พ.ศ.
 * @property int|null $jd_employee_id JD revision ที่ seed KPI มา
 * @property string $status
 * @property int|null $approved_by
 * @property string|null $approved_at
 * @property KpiItem[] $items
 * @property Employees $employee
 * @property JdEmployee|null $jdEmployee
 */
class KpiCycle extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';     // กำลังตั้งค่า/ทบทวน KPI
    public const STATUS_PENDING = 'pending'; // รอหัวหน้า/HR อนุมัติชุด
    public const STATUS_ACTIVE = 'active';   // อนุมัติแล้ว เริ่มบันทึกผลได้
    public const STATUS_CLOSED = 'closed';   // ปิดปีงบ

    public static function tableName()
    {
        return '{{%kpi_cycle}}';
    }

    public function rules()
    {
        return [
            [['emp_id', 'fiscal_year'], 'required'],
            [['emp_id', 'fiscal_year', 'jd_employee_id', 'approved_by', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at', 'approved_at'], 'safe'],
            [['note'], 'string'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_ACTIVE, self::STATUS_CLOSED]],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['emp_id'], 'exist', 'targetClass' => Employees::class, 'targetAttribute' => ['emp_id' => 'id']],
            [['emp_id', 'fiscal_year'], 'unique', 'targetAttribute' => ['emp_id', 'fiscal_year'], 'message' => 'มีชุด KPI ของพนักงานคนนี้ในปีงบประมาณนี้อยู่แล้ว'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'emp_id' => 'พนักงาน',
            'fiscal_year' => 'ปีงบประมาณ',
            'jd_employee_id' => 'JD ที่อ้างอิง',
            'status' => 'สถานะ',
            'approved_by' => 'ผู้อนุมัติ',
            'approved_at' => 'อนุมัติเมื่อ',
        ];
    }

    public function getItems()
    {
        return $this->hasMany(KpiItem::class, ['cycle_id' => 'id'])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getActiveItems()
    {
        return $this->getItems()->andWhere(['status' => KpiItem::STATUS_ACTIVE]);
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getJdEmployee()
    {
        return $this->hasOne(JdEmployee::class, ['id' => 'jd_employee_id']);
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'ฉบับร่าง',
            self::STATUS_PENDING => 'รออนุมัติ',
            self::STATUS_ACTIVE => 'ใช้งาน',
            self::STATUS_CLOSED => 'ปิดปีแล้ว',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $now = date('Y-m-d H:i:s');
            $uid = (Yii::$app->has('user') && !Yii::$app->user->isGuest) ? (int) Yii::$app->user->id : null;
            if ($insert) {
                $this->created_at = $now;
                $this->created_by = $uid;
            }
            $this->updated_at = $now;
            $this->updated_by = $uid;
            return true;
        }
        return false;
    }
}
