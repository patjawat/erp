<?php

namespace app\modules\health\models;

use Yii;
use app\components\AppHelper;
use app\modules\hr\models\Employees;

/**
 * This is the model class for table "health_screen".
 *
 * @property int $id
 * @property int $thai_year ปีงบประมาณ
 * @property int $emp_id รหัสพนักงาน
 * @property string|null $date_checkup ข้อมูลการตรวจสุขภาพ
 * @property string|null $ref อ้างอิง
 * @property float|null $weight น้ำหนัก
 * @property float|null $height ส่วนสูง
 * @property float|null $bmi ดัชนีมวลกาย
 * @property string|null $health_status สถานะ (SCREEN|CONFIRM|SUCCESS)
 * @property string|null $appointment_date วันที่การนัดหมาย
 * @property string|null $data_json data_json
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class HealthScreen extends \yii\db\ActiveRecord
{


public $q_department;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'health_screen';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_checkup', 'data_json', 'created_at', 'updated_at', 'created_by', 'updated_by', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['thai_year', 'emp_id', 'date_checkup', 'weight', 'height'], 'required'],
            [['thai_year', 'emp_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['date_checkup', 'data_json', 'created_at', 'updated_at', 'deleted_at', 'ref', 'bmi','health_status','appointment_date','q_department'], 'safe'],
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
            'emp_id' => 'รหัสพนักงาน',
            'date_checkup' => 'วันที่ตรวจสุขภาพ',
            'data_json' => 'ข้อมูล JSON',
            'weight' => 'น้ำหนัก',
            'height' => 'ส่วนสูง',
            'bmi' => 'BMI',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
            'appointment_date' => 'วันที่การนัดหมาย',
        ];
    }

    /**
     * คืนค่า array ของ validation errors สำหรับฟอร์มคัดกรองสุขภาพ (ใช้ร่วมกับ Ajax validation)
     * @param HealthScreen $model โมเดลที่ load post data แล้ว
     * @return array [inputId => [error message], ...]
     */
    public static function getScreenFormValidationErrors($model)
    {
        $result = [];
        if (!$model->data_json || !is_array($model->data_json)) {
            return $result;
        }
        $requiredName = 'ต้องระบุ';
        $fields = [
            'smoking_status',
            'alcohol_status',
            'exercise_status',
            'food_taste',
            'driving_safety',
            'condom_usage',
        ];
        foreach ($fields as $field) {
            if (!isset($model->data_json[$field]) || $model->data_json[$field] === '') {
                $id = \yii\helpers\Html::getInputId($model, "data_json[$field]");
                $result[$id] = [$requiredName];
            }
        }
        if (empty($model->data_json['family_history'])) {
            $id = \yii\helpers\Html::getInputId($model, 'data_json[family_history]');
            $result[$id] = ['กรุณาเลือกอย่างน้อย 1 รายการ'];
        }
        return $result;
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

        public function getLabs()
    {
        return $this->hasMany(HealthLabConfirm::class, ['lab_screen_id' => 'id']);
    }




    public function getBmiResult()
    {
        try {
            $bmi = $this->bmi;
            return AppHelper::getBmiResult($bmi);
        } catch (\Throwable $th) {
            return NULL;
        }
    }

    public function getYearList()
    {
        // ดึงค่า thai_year ที่ไม่ซ้ำกันจากฐานข้อมูลออกมา
        $years = self::find()
            ->select(['thai_year'])
            ->distinct()
            ->where(['not', ['thai_year' => null]])
            ->orderBy(['thai_year' => SORT_DESC])
            ->column();

        // จัดรูปแบบให้เป็น Array [2569 => '2569', 2568 => '2568'] สำหรับ Select2
        return array_combine($years, $years);
    }

public function labTotalPrice()
{
    $total = 0;
    // ดึงรายการ labs ผ่าน Relation getLabs()
    foreach ($this->labs as $lab) {
        // เปลี่ยน 'price' เป็นชื่อคอลัมน์ราคาในตาราง health_lab_confirm ของคุณ
        $total += ($lab->qty*$lab->lab_price); 
    }
    return $total;
}

    /**
     * คืนค่ารายการสถานะทั้งหมดสำหรับ Dropdown / Select2
     */
    public static function getHealthStatusList()
    {
        return [
            'SCREEN'  => 'คัดกรอง',
            'CONFIRM' => 'ยืนยันการตรวจ',
            'SUCCESS' => 'ตรวจแล้ว',
        ];
    }

    //สถานะการคัดกรอง
    /**
     * คืนค่าข้อมูลสำหรับการแสดงผลตามสถานะ
     */
    public static function getHealthStatusDisplay($status, $type = 'label')
    {
        $status = strtoupper($status);
        $items = [
            'SCREEN'  => [
                'label' => 'คัดกรอง',
                'color' => 'info',
                'icon'  => 'bi bi-search' // Bootstrap Icon
            ],
            'CONFIRM' => [
                'label' => 'ยืนยันการตรวจ',
                'color' => 'warning',
                'icon'  => 'bi bi-check2-square'
            ],
            'SUCCESS' => [
                'label' => 'ตรวจแล้ว',
                'color' => 'success',
                'icon'  => 'bi bi-person-check-fill'
            ],
        ];

        $default = ['label' => 'ไม่ทราบสถานะ', 'color' => 'secondary', 'icon' => 'bi bi-question-circle'];
        $data = $items[$status] ?? $default;

        return $data[$type];
    }

    public function viewStatus()
    {
        // ดึงค่ามาพักไว้ก่อนเพื่อความเร็วและอ่านง่าย
        $status = $this->health_status;
        $label  = self::getHealthStatusDisplay($status, 'label');
        $color  = self::getHealthStatusDisplay($status, 'color');
        $icon   = self::getHealthStatusDisplay($status, 'icon');

        // ใช้ Bootstrap 5 subtle classes เพื่อความทันสมัย
        return sprintf(
            '<span class="badge rounded-pill bg-%s-subtle text-%s border border-%s-subtle px-3 py-2">
            <i class="%s me-1"></i> %s
        </span>',
            $color,
            $color,
            $color,
            $icon,
            $label
        );
    }

    /**
     * จัดการ UI ของสรุปผลสุขภาพ (healthy, risk, sick)
     */
    public static function getFinalSummaryDisplay($value = null, $type = 'label')
    {
        $items = [
            'healthy' => [
                'label' => 'สุขภาพดี (ปกติ)',
                'color' => 'success',
                'icon'  => 'bi bi-check-circle-fill',
                'bg'    => 'bg-success-subtle',
                'desc'  => 'ร่างกายแข็งแรงอยู่ในเกณฑ์ปกติ'
            ],
            'risk' => [
                'label' => 'กลุ่มเสี่ยง',
                'color' => 'warning',
                'icon'  => 'bi bi-exclamation-triangle-fill',
                'bg'    => 'bg-warning-subtle',
                'desc'  => 'พบความผิดปกติเล็กน้อย ควรปรับพฤติกรรม'
            ],
            'sick' => [
                'label' => 'กลุ่มป่วย',
                'color' => 'danger',
                'icon'  => 'bi bi-heart-pulse-fill',
                'bg'    => 'bg-danger-subtle',
                'desc'  => 'ควรพบแพทย์เพื่อวินิจฉัยและรักษา'
            ],
        ];

        $data = $items[$value] ?? [
            'label' => 'ไม่ระบุ',
            'color' => 'secondary',
            'icon' => 'bi bi-question-circle',
            'bg' => 'bg-light',
            'desc' => '-'
        ];

        return $data[$type];
    }

    /**
     * คำนวณจำนวนพนักงานแยกตามกลุ่ม BMI สำหรับ ApexCharts
     * @param integer $year ปีงบประมาณที่ต้องการกรอง (ถ้ามี)
     */
    public function getBmiChartData()
    {
        $query = self::find();

        $query->andWhere(['thai_year' => $this->thai_year]);

        $allData = $query->all();

        // เตรียมตัวแปรนับจำนวน
        $underweight = 0; // < 18.5
        $normal = 0;      // 18.5 - 22.9
        $overweight = 0;  // 23 - 24.9
        $obese = 0;       // >= 25

        foreach ($allData as $model) {
            $bmi = (float)$model->bmi;
            if ($bmi <= 0) continue;

            if ($bmi < 18.5) {
                $underweight++;
            } elseif ($bmi < 23) {
                $normal++;
            } elseif ($bmi < 25) {
                $overweight++;
            } else {
                $obese++;
            }
        }

        return [
            'series' => [$normal, $overweight, $obese, $underweight], // ลำดับต้องตรงกับ Labels ใน JS
            'total' => $normal + $overweight + $obese + $underweight
        ];
    }

    /**
 * คำนวณอัตราการเข้าตรวจสุขภาพแยกตามหน่วยงาน
 * @return array ข้อมูลสำหรับ ApexCharts (Categories, SuccessData, PendingData)
 */
/**
 * คำนวณอัตราการเข้าตรวจสุขภาพแยกตามหน่วยงาน
 * @param string $year ปีงบประมาณ (thai_year)
 * @return array
 */
public function getDeptExamStats()
{
    // 1. ดึงหน่วยงานทั้งหมดที่มีพนักงานสถานะ 1 และไม่ใช่ id 1
    // สมมติว่าความสัมพันธ์ใน Employee คือ 'department' และมีฟิลด์ 'name'
    $departments = Employees::find()
        ->select(['department']) // ปรับชื่อฟิลด์ตามจริง
        ->where(['status' => 1,'branch' => 'MAIN'])
        ->andWhere(['<>', 'id', 1])
        ->groupBy(['department'])
        // ->asArray()
        ->all();

    $categories = [];
    $successData = [];
    $pendingData = [];

    foreach ($departments as $dept) {
        $deptId = $dept->department;
        $categories[] = $dept->departmentName();

        // 2. นับจำนวนพนักงานทั้งหมดในหน่วยงานนี้
        $totalEmp = Employees::find()
            ->where(['department' => $deptId, 'status' => 1,'branch' => 'MAIN'])
            ->andWhere(['<>', 'id', 1])
            ->count();

        // 3. นับคนที่ตรวจแล้ว (SUCCESS) ในปีที่กำหนด
        $successCount = self::find()
            ->innerJoinWith('employee') // ใช้ relation ที่คุณทำไว้
            ->where([
                'employees.department' => $dept->department,
                'health_screen.thai_year' => $this->thai_year,
                'health_screen.health_status' => 'SUCCESS'
            ])
            ->count();

        // 4. คำนวณคนที่ยังไม่ได้ตรวจ (Total - Success)
        $pendingCount = $totalEmp - $successCount;

        $successData[] = (int)$successCount;
        $pendingData[] = (int)($pendingCount > 0 ? $pendingCount : 0);
    }

    return [
        'categories' => $categories,
        'success' => $successData,
        'pending' => $pendingData,
    ];
}

}
