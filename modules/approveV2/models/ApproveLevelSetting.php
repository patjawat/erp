<?php

namespace app\modules\approveV2\models;

use Yii;
use app\models\Categorise;
use yii\helpers\ArrayHelper;

/**
 * ตารางตั้งค่าระดับการอนุมัติของแต่ละระบบ
 *
 * @property int $id
 * @property string $system ระบบ (leave, purchase, development, ...)
 * @property int $level ลำดับระดับ
 * @property string $label ชื่อระดับ
 * @property string $approver_type org_leader1|org_leader2|role|director|fixed
 * @property string|null $approver_value
 * @property int|null $org_node_level ระดับโหนดในผังองค์กร (null=แผนกผู้ขอ, 1=ประเภท, 2=กลุ่มงาน)
 * @property int $sort_order
 * @property int $active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class ApproveLevelSetting extends \yii\db\ActiveRecord
{
    const TYPE_ORG_LEADER1 = 'org_leader1';
    const TYPE_ORG_LEADER2 = 'org_leader2';
    const TYPE_ROLE = 'role';
    const TYPE_DIRECTOR = 'director';
    const TYPE_FIXED = 'fixed';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'approve_level_setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['system', 'level', 'label', 'approver_type'], 'required'],
            [['level', 'sort_order', 'active'], 'integer'],
            [['org_node_level'], 'filter', 'filter' => function ($v) {
                return $v === '' || $v === null ? null : (int) $v;
            }],
            [['org_node_level'], 'integer', 'min' => 0, 'max' => 10, 'skipOnEmpty' => true],
            [['created_at', 'updated_at'], 'safe'],
            [['system'], 'string', 'max' => 64],
            [['label', 'approver_value'], 'string', 'max' => 255],
            [['approver_type'], 'string', 'max' => 32],
            [['system', 'level'], 'unique', 'targetAttribute' => ['system', 'level']],
            [['approver_type'], 'in', 'range' => [self::TYPE_ORG_LEADER1, self::TYPE_ORG_LEADER2, self::TYPE_ROLE, self::TYPE_DIRECTOR, self::TYPE_FIXED]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'system' => 'ระบบ',
            'level' => 'ระดับ',
            'label' => 'ชื่อระดับ',
            'approver_type' => 'ประเภทผู้อนุมัติ',
            'approver_value' => 'ค่า (บทบาท/พนักงาน)',
            'org_node_level' => 'ระดับในผังองค์กร',
            'sort_order' => 'เรียงลำดับ',
            'active' => 'ใช้งาน',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $now = date('Y-m-d H:i:s');
            if ($insert) {
                $this->created_at = $now;
            }
            $this->updated_at = $now;
            return true;
        }
        return false;
    }

    /**
     * รายการประเภทผู้อนุมัติสำหรับ dropdown
     */
    public static function approverTypeOptions()
    {
        return [
            self::TYPE_ORG_LEADER1 => 'หัวหน้าผู้ควบคุม',
            self::TYPE_ROLE => 'ตามบทบาท (Role)',
            self::TYPE_DIRECTOR => 'ผู้อำนวยการ',
            self::TYPE_FIXED => 'ระบุพนักงาน (emp_id)',
        ];
    }

    /**
     * ตัวเลือกระดับโหนดในผังองค์กร (diagram) — ชื่อระดับมาจากการตั้งใน /hr/organization/diagram
     * ระดับที่ 1 กับ ระดับที่ 2 สลับตำแหน่งกัน (ในผัง lvl 1 = บน, lvl 2 = ถัดลงมา — ใน UI ระดับที่ 1 = หัวหน้างาน, ระดับที่ 2 = ผอ./หัวหน้า)
     */
    public static function orgNodeLevelOptions()
    {
        $model = Categorise::findOne(['name' => 'org_diagram_level']);
        $labels = ($model && is_array($model->data_json)) ? $model->data_json : [];
        $defaults = [1 => 'ผอ. หรือ หัวหน้า', 2 => 'หัวหน้างาน', 3 => 'หัวหน้ากลุ่มงาน', 4 => 'ระดับที่ 4', 5 => 'ผอ./บนสุด'];
        $options = [null => 'ใช้แผนกของพนักงานโดยตรง'];
        for ($lvl = 1; $lvl <= 5; $lvl++) {
            $swapLvl = ($lvl === 1) ? 2 : (($lvl === 2) ? 1 : $lvl);
            $name = trim($labels[(string) $swapLvl] ?? '');
            $options[$lvl] = $name !== '' ? 'ระดับที่ ' . $lvl . ' (' . $name . ')' : 'ระดับที่ ' . $lvl . ' (' . ($defaults[$swapLvl] ?? '') . ')';
        }
        return $options;
    }

    /**
     * ชื่อระดับในผังสำหรับแสดงผล — ระดับที่ 1 กับ 2 สลับกับผัง (ระดับที่ 1 = หัวหน้างาน, ระดับที่ 2 = ผอ./หัวหน้า)
     */
    public static function getOrgDiagramLevelLabel($level)
    {
        if ($level === null || $level === '') {
            return null;
        }
        $swapLevel = ((int) $level === 1) ? 2 : (((int) $level === 2) ? 1 : (int) $level);
        $model = Categorise::findOne(['name' => 'org_diagram_level']);
        $labels = ($model && is_array($model->data_json)) ? $model->data_json : [];
        $defaults = [1 => 'ผอ. หรือ หัวหน้า', 2 => 'หัวหน้างาน', 3 => 'หัวหน้ากลุ่มงาน', 4 => 'ระดับที่ 4', 5 => 'ผอ./บนสุด'];
        $name = trim($labels[(string) $swapLevel] ?? '');
        return $name !== '' ? $name : ($defaults[$swapLevel] ?? 'ระดับที่ ' . $level);
    }

    /**
     * รายการระบบที่รองรับ
     */
    public static function systemOptions()
    {
        return [
            'leave' => 'การลา',
            'purchase' => 'จัดซื้อ',
            'development' => 'ไปราชการ/อบรม',
            'main_stock' => 'เบิกพัสดุ',
            'checkin' => 'ลงเวลา',
            'vehicle' => 'ยานพาหนะ',
            'asset-move' => 'โอนทรัพย์สิน',
        ];
    }

    /**
     * ดึงระดับการอนุมัติของระบบหนึ่ง เรียงตาม level
     */
    public static function getLevelsBySystem($system, $activeOnly = true)
    {
        $query = static::find()->where(['system' => $system])->orderBy(['level' => SORT_ASC]);
        if ($activeOnly) {
            $query->andWhere(['active' => 1]);
        }
        return $query->all();
    }
}
