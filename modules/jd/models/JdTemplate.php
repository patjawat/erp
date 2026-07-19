<?php

namespace app\modules\jd\models;

use app\models\Categorise;
use Yii;
use yii\db\ActiveRecord;

/**
 * Template คำอธิบายงาน (JD) ต่อตำแหน่งงาน
 *
 * @property int         $id
 * @property string      $name              ชื่อ Template
 * @property string      $position_code     รหัสตำแหน่ง (categorise position_name)
 * @property string|null $job_code          รหัสตำแหน่ง (Job Code/ID)
 * @property string|null $job_level         ระดับตำแหน่ง
 * @property string|null $department        แผนก/ฝ่าย
 * @property string|null $report_to         รายงานตรงต่อ
 * @property int         $has_subordinates  มีผู้ใต้บังคับบัญชา
 * @property int         $is_active
 * @property string|null $job_purpose       วัตถุประสงค์ของตำแหน่ง
 * @property string|null $edu_requirement   ข้อกำหนดด้านการศึกษา
 * @property int|null    $exp_years         จำนวนปีประสบการณ์ขั้นต่ำ
 * @property string|null $exp_detail        รายละเอียดประสบการณ์
 * @property string|null $hard_skills       ทักษะเฉพาะทาง
 * @property string|null $soft_skills       ทักษะด้านพฤติกรรม
 * @property string|null $core_competency
 * @property string|null $functional_competency
 * @property string|null $leadership_competency
 * @property string|null $kpis              ตัวชี้วัดผลงาน
 * @property int|null    $salary_min        เงินเดือนต่ำสุด
 * @property int|null    $salary_max        เงินเดือนสูงสุด
 * @property string|null $benefits          สวัสดิการหลัก
 * @property string|null $variable_pay      ค่าตอบแทนผันแปร
 * @property string|null $work_type         รูปแบบการทำงาน
 * @property string|null $work_location     สถานที่ปฏิบัติงาน
 * @property string|null $work_hours        เวลาทำงาน/กะ
 * @property string|null $work_conditions   สภาพแวดล้อมพิเศษ
 * @property string|null $career_vertical   เส้นทางอาชีพแนวดิ่ง
 * @property string|null $career_lateral    เส้นทางอาชีพแนวราบ
 * @property string|null $employment_type   ประเภทการจ้าง
 * @property int|null    $headcount         จำนวนอัตรา
 * @property string|null $jd_approved_by    ผู้อนุมัติ JD
 * @property string|null $jd_approved_at    วันที่อนุมัติ JD
 * @property string      $created_at
 * @property string|null $updated_at
 * @property int|null    $created_by
 * @property int|null    $updated_by
 * @property JdTemplateSection[] $sections
 * @property Categorise  $positionName
 */
class JdTemplate extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%jd_template}}';
    }

    public function rules()
    {
        return [
            [['name', 'position_code'], 'required'],
            [['is_active', 'created_by', 'updated_by', 'exp_years', 'salary_min', 'salary_max', 'headcount', 'has_subordinates', 'parent_template_id', 'revision_no'], 'integer'],
            [['job_purpose', 'edu_requirement', 'exp_detail', 'hard_skills', 'soft_skills',
              'core_competency', 'functional_competency', 'leadership_competency',
              'kpis', 'benefits', 'variable_pay', 'work_conditions',
              'career_vertical', 'career_lateral', 'description'], 'string'],
            [['created_at', 'updated_at', 'jd_approved_at', 'effective_date', 'ai_generated_at'], 'safe'],
            [['name', 'department', 'report_to', 'work_location', 'work_hours', 'jd_approved_by'], 'string', 'max' => 255],
            [['position_code', 'job_code'], 'string', 'max' => 64],
            [['job_level', 'employment_type'], 'string', 'max' => 100],
            [['work_type'], 'string', 'max' => 20],
            [['template_code'], 'string', 'max' => 80],
            [['document_no'], 'string', 'max' => 100],
            [['template_type', 'lifecycle_status'], 'string', 'max' => 20],
            [['template_type'], 'in', 'range' => ['base', 'variant']],
            [['lifecycle_status'], 'in', 'range' => ['draft', 'review', 'active', 'retired']],
            [['is_active'], 'default', 'value' => 1],
            [['has_subordinates'], 'default', 'value' => 0],
            [['template_type'], 'default', 'value' => 'base'],
            [['revision_no'], 'default', 'value' => 1],
            [['lifecycle_status'], 'default', 'value' => 'draft'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'                     => 'ID',
            'name'                   => 'ชื่อ Template',
            'position_code'          => 'ตำแหน่งงาน',
            'job_code'               => 'รหัสตำแหน่ง (Job Code)',
            'job_level'              => 'ระดับตำแหน่ง (Job Level)',
            'department'             => 'แผนก / ฝ่าย',
            'report_to'              => 'รายงานตรงต่อ',
            'has_subordinates'       => 'มีผู้ใต้บังคับบัญชา',
            'is_active'              => 'สถานะ',
            'job_purpose'            => 'วัตถุประสงค์ของตำแหน่ง (Job Purpose)',
            'edu_requirement'        => 'การศึกษา',
            'exp_years'              => 'ประสบการณ์ขั้นต่ำ (ปี)',
            'exp_detail'             => 'รายละเอียดประสบการณ์',
            'hard_skills'            => 'ทักษะเฉพาะทาง (Hard Skills)',
            'soft_skills'            => 'ทักษะด้านพฤติกรรม (Soft Skills)',
            'core_competency'        => 'Core Competency',
            'functional_competency'  => 'Functional Competency',
            'leadership_competency'  => 'Leadership Competency',
            'kpis'                   => 'ตัวชี้วัดผลงาน (KPIs)',
            'salary_min'             => 'เงินเดือนต่ำสุด (บาท)',
            'salary_max'             => 'เงินเดือนสูงสุด (บาท)',
            'benefits'               => 'สวัสดิการหลัก',
            'variable_pay'           => 'ค่าตอบแทนผันแปร',
            'work_type'              => 'รูปแบบการทำงาน',
            'work_location'          => 'สถานที่ปฏิบัติงาน',
            'work_hours'             => 'เวลาทำงาน / กะ',
            'work_conditions'        => 'สภาพแวดล้อมพิเศษ',
            'career_vertical'        => 'เส้นทางแนวดิ่ง (Vertical)',
            'career_lateral'         => 'เส้นทางแนวราบ (Lateral)',
            'employment_type'        => 'ประเภทการจ้าง',
            'headcount'              => 'Headcount ที่ได้รับอนุมัติ (อัตรา)',
            'jd_approved_by'         => 'ผู้อนุมัติ JD',
            'jd_approved_at'         => 'วันที่อนุมัติ JD',
            'created_at'             => 'สร้างเมื่อ',
            'updated_at'             => 'แก้ไขเมื่อ',
        ];
    }

    public function getSections()
    {
        return $this->hasMany(JdTemplateSection::class, ['template_id' => 'id'])->orderBy(['sort_order' => SORT_ASC]);
    }

    public function getBlocks()
    {
        return $this->hasMany(JdTemplateBlock::class, ['template_id' => 'id'])
            ->andWhere(['is_enabled' => 1])
            ->orderBy(['sort_order' => SORT_ASC]);
    }

    public function getParentTemplate()
    {
        return $this->hasOne(self::class, ['id' => 'parent_template_id']);
    }

    public function getPositionName()
    {
        return $this->hasOne(Categorise::class, ['code' => 'position_code'])->andOnCondition(['name' => 'position_name']);
    }

    public function getPositionTitle()
    {
        $m = $this->positionName;
        return $m ? $m->title : $this->position_code;
    }

    public static function jobLevelOptions(): array
    {
        return [
            ''               => '-- เลือก --',
            'Entry'          => 'Entry Level',
            'Junior'         => 'Junior',
            'Senior'         => 'Senior',
            'Lead'           => 'Lead / Principal',
            'Manager'        => 'Manager',
            'Senior Manager' => 'Senior Manager',
            'Director'       => 'Director',
            'VP'             => 'VP / C-Level',
        ];
    }

    public static function workTypeOptions(): array
    {
        return [
            ''       => '-- เลือก --',
            'Onsite' => 'Onsite (ทำงานที่สำนักงาน)',
            'Hybrid' => 'Hybrid (ผสม)',
            'Remote' => 'Remote (ทำงานจากที่บ้าน)',
        ];
    }

    public static function employmentTypeOptions(): array
    {
        return [
            ''          => '-- เลือก --',
            'fulltime'  => 'พนักงานประจำ (Full-time)',
            'parttime'  => 'พนักงานนอกเวลา (Part-time)',
            'contract'  => 'พนักงานสัญญาจ้าง (Contract)',
            'freelance' => 'ฟรีแลนซ์ (Freelance)',
            'intern'    => 'นักศึกษาฝึกงาน (Intern)',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $now = date('Y-m-d H:i:s');
            if ($insert) {
                $this->created_at = $now;
                if (Yii::$app->has('user') && !Yii::$app->user->isGuest) {
                    $this->created_by = (int) Yii::$app->user->id;
                }
            }
            $this->updated_at = $now;
            if (Yii::$app->has('user') && !Yii::$app->user->isGuest) {
                $this->updated_by = (int) Yii::$app->user->id;
            }
            return true;
        }
        return false;
    }
}
