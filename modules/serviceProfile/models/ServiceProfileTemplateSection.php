<?php

namespace app\modules\serviceProfile\models;

use Yii;
use yii\db\ActiveRecord;

class ServiceProfileTemplateSection extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%service_profile_template_section}}';
    }

    public function rules()
    {
        return [
            [['template_id', 'section_code', 'title', 'block_type'], 'required'],
            [['template_id', 'sort_order', 'created_by', 'updated_by'], 'integer'],
            [['is_required', 'is_enabled'], 'boolean'],
            [['description'], 'string'],
            [['config_json', 'created_at', 'updated_at'], 'safe'],
            [['section_code'], 'string', 'max' => 80],
            [['title'], 'string', 'max' => 255],
            [['block_type'], 'in', 'range' => array_keys(self::blockTypeLabels())],
            [['sort_order'], 'default', 'value' => 10],
            [['is_required', 'is_enabled'], 'default', 'value' => 1],
            [['section_code'], 'unique', 'targetAttribute' => ['template_id', 'section_code'], 'message' => 'รหัสหัวข้อนี้มีอยู่ใน Template แล้ว'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'section_code' => 'รหัสหัวข้อ',
            'title' => 'ชื่อหัวข้อ',
            'description' => 'คำแนะนำการกรอก',
            'block_type' => 'รูปแบบข้อมูล',
            'is_required' => 'บังคับกรอก',
            'is_enabled' => 'เปิดใช้งาน',
            'sort_order' => 'ลำดับ',
        ];
    }

    public function getTemplate()
    {
        return $this->hasOne(ServiceProfileTemplate::class, ['id' => 'template_id']);
    }

    public static function blockTypeLabels(): array
    {
        return [
            'rich_text' => 'ข้อความและรายการ',
            'goal_ha_table' => 'เป้าหมายและมาตรฐาน HA',
            'service_scope_table' => 'ขอบเขตบริการ',
            'stakeholder_table' => 'ผู้รับผลงานและความต้องการ',
            'year_series_table' => 'ข้อมูลย้อนหลังหลายปี',
            'quality_dimension_table' => 'ประเด็นคุณภาพ',
            'challenge_risk_table' => 'ความท้าทายและความเสี่ยง',
            'staffing_table' => 'อัตรากำลัง',
            'key_process_table' => 'กระบวนการสำคัญ',
            'cqi_review_table' => 'การทบทวนคุณภาพ',
            'kpi_table' => 'ตัวชี้วัดสำคัญ',
            'pppp_process' => 'Purpose, Problem, Process, Performance',
            'development_plan_table' => 'แผนพัฒนา',
            'risk_incident_table' => 'สถิติอุบัติการณ์',
            'risk_control_table' => 'มาตรการป้องกันความเสี่ยง',
            'integration_table' => 'การบูรณาการและเครือข่าย',
            'attachment' => 'เอกสารแนบ',
            'team_responsibility_table' => 'โครงสร้างทีมและหน้าที่รับผิดชอบ',
            'service_guideline_table' => 'แนวทางบริการตามกลุ่มและสถานการณ์',
            'risk_profile_table' => 'Risk Profile และมาตรการป้องกัน',
            'competency_table' => 'สมรรถนะบุคลากร',
            'document_reference_table' => 'รายการเอกสาร แบบฟอร์ม และ WI',
            'reference_table' => 'เอกสารอ้างอิง',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $uid = Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id;
        if ($insert) {
            $this->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $this->created_at = $now;
            $this->created_by = $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;
        return true;
    }
}
