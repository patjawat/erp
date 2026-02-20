<?php

namespace app\modules\health\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\health\models\HealthScreen;
use app\modules\hr\models\Employees;

/**
 * HealthScreenSearch represents the model behind the search form of `app\modules\health\models\HealthScreen`.
 */
class HealthScreenSearch extends HealthScreen
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'thai_year', 'emp_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['date_checkup', 'data_json', 'created_at', 'updated_at', 'deleted_at','weight','height','health_status','q_department'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = HealthScreen::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'thai_year' => $this->thai_year,
            'emp_id' => $this->emp_id,
            'weight' => $this->weight,
            'height' => $this->height,
            'health_status' => $this->health_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'date_checkup', $this->date_checkup]);

        return $dataProvider;
    }

    /**
     * คำนวณตัวชี้วัดสุขภาพสำคัญ (KPIs)
     * @return array ['percent_screened' => float, 'percent_normal_bmi' => float]
     */
    public function getKpiStats()
    {
        // 1. นับจำนวนพนักงานทั้งหมด (status=1, branch=MAIN, id != 1)
        $totalEmployees = Employees::find()
            ->where(['status' => 1, 'branch' => 'MAIN'])
            ->andWhere(['<>', 'id', 1])
            ->count();

        // 2. นับจำนวนคนที่ตรวจแล้ว (health_status = SUCCESS) ในปีงบประมาณที่กำหนด
        $screenedCount = HealthScreen::find()
            ->innerJoinWith('employee')
            ->where([
                'health_screen.thai_year' => $this->thai_year,
                'health_screen.health_status' => 'SUCCESS',
                'employees.status' => 1,
                'employees.branch' => 'MAIN'
            ])
            ->andWhere(['<>', 'employees.id', 1])
            ->count();

        // 3. คำนวณอัตราการคัดกรอง
        $percentScreened = $totalEmployees > 0 ? round(($screenedCount / $totalEmployees) * 100, 1) : 0;

        // 4. นับจำนวนคนที่ BMI ปกติ (18.5 - 22.9) ในปีงบประมาณที่กำหนด
        $normalBmiCount = HealthScreen::find()
            ->innerJoinWith('employee')
            ->where([
                'health_screen.thai_year' => $this->thai_year,
                'employees.status' => 1,
                'employees.branch' => 'MAIN'
            ])
            ->andWhere(['<>', 'employees.id', 1])
            ->andWhere(['>=', 'health_screen.bmi', 18.5])
            ->andWhere(['<', 'health_screen.bmi', 23])
            ->count();

        // 5. นับจำนวนคนที่ตรวจทั้งหมด (มี BMI) ในปีงบประมาณที่กำหนด
        $totalWithBmi = HealthScreen::find()
            ->innerJoinWith('employee')
            ->where([
                'health_screen.thai_year' => $this->thai_year,
                'employees.status' => 1,
                'employees.branch' => 'MAIN'
            ])
            ->andWhere(['<>', 'employees.id', 1])
            ->andWhere(['IS NOT', 'health_screen.bmi', null])
            ->andWhere(['>', 'health_screen.bmi', 0])
            ->count();

        // 6. คำนวณอัตรา BMI ปกติ
        $percentNormalBmi = $totalWithBmi > 0 ? round(($normalBmiCount / $totalWithBmi) * 100, 1) : 0;

        return [
            'percent_screened' => $percentScreened,
            'percent_normal_bmi' => $percentNormalBmi,
            'screened_count' => $screenedCount,
            'total_employees' => $totalEmployees,
            'normal_bmi_count' => $normalBmiCount,
            'total_with_bmi' => $totalWithBmi,
        ];
    }

    /**
     * ดึงข้อมูลสรุปประวัติการเจ็บป่วยสำหรับกราฟ
     * @return array ['has' => [HT, DM, Heart, DLP], 'no' => [HT, DM, Heart, DLP]]
     */
    public function getDiseaseHistoryStats()
    {
        // ดึงข้อมูล HealthScreen ทั้งหมดในปีงบประมาณที่กำหนด (ที่ตรวจแล้ว SUCCESS)
        $healthScreens = HealthScreen::find()
            ->innerJoinWith('employee')
            ->where([
                'health_screen.thai_year' => $this->thai_year,
                'health_screen.health_status' => 'SUCCESS',
                'employees.status' => 1,
                'employees.branch' => 'MAIN'
            ])
            ->andWhere(['<>', 'employees.id', 1])
            ->all();

        // Mapping โรค: key ในฐานข้อมูล => index ใน array
        $diseaseMap = [
            'HT' => 0,      // โรคความดัน
            'DM' => 1,      // เบาหวาน
            'Heart' => 2,   // โรคหัวใจ
            'DLP' => 3,     // ไขมันในเลือดสูง
        ];

        // เตรียมตัวนับ: [HT, DM, Heart, DLP]
        $hasDisease = [0, 0, 0, 0];
        $totalChecked = count($healthScreens);

        // นับจำนวนคนที่มีโรคแต่ละประเภท
        foreach ($healthScreens as $screen) {
            $dataJson = $screen->data_json ?? [];
            $historyDiseases = $dataJson['history_diseases'] ?? [];
            
            if (is_array($historyDiseases)) {
                foreach ($historyDiseases as $disease) {
                    if (isset($diseaseMap[$disease])) {
                        $hasDisease[$diseaseMap[$disease]]++;
                    }
                }
            }
        }

        // คำนวณจำนวนคนที่ไม่มีโรคแต่ละประเภท
        $noDisease = array_map(function($has) use ($totalChecked) {
            return $totalChecked - $has;
        }, $hasDisease);

        return [
            'has' => $hasDisease,
            'no' => $noDisease,
            'total' => $totalChecked,
        ];
    }

    /**
     * ดึงข้อมูลแนวโน้มระดับความเสี่ยงรายปี
     * @param int $limitYears จำนวนปีที่ต้องการดึง (default: 5 ปีล่าสุด)
     * @return array ['years' => [ปี1, ปี2, ...], 'low' => [จำนวน], 'medium' => [จำนวน], 'high' => [จำนวน]]
     */
    public function getRiskTrendByYear($limitYears = 5)
    {
        // ดึงปีงบประมาณล่าสุด N ปี
        $years = HealthScreen::find()
            ->select(['thai_year'])
            ->distinct()
            ->where(['not', ['thai_year' => null]])
            ->orderBy(['thai_year' => SORT_DESC])
            ->limit($limitYears)
            ->column();
        
        // เรียงจากน้อยไปมากเพื่อแสดงในกราฟ
        sort($years);
        
        $lowRisk = [];    // healthy = เสี่ยงต่ำ
        $mediumRisk = []; // risk = เสี่ยงกลาง
        $highRisk = [];   // sick = เสี่ยงสูง
        
        foreach ($years as $year) {
            // ดึงข้อมูลทั้งหมดในปีนี้แล้วนับใน PHP เพื่อความแม่นยำ
            $healthScreens = HealthScreen::find()
                ->innerJoinWith('employee')
                ->where([
                    'health_screen.thai_year' => $year,
                    'health_screen.health_status' => 'SUCCESS',
                    'employees.status' => 1,
                    'employees.branch' => 'MAIN'
                ])
                ->andWhere(['<>', 'employees.id', 1])
                ->all();
            
            $low = 0;
            $medium = 0;
            $high = 0;
            
            foreach ($healthScreens as $screen) {
                $dataJson = $screen->data_json ?? [];
                $finalSummary = $dataJson['final_summary'] ?? null;
                
                if ($finalSummary === 'healthy') {
                    $low++;
                } elseif ($finalSummary === 'risk') {
                    $medium++;
                } elseif ($finalSummary === 'sick') {
                    $high++;
                }
            }
            
            $lowRisk[] = $low;
            $mediumRisk[] = $medium;
            $highRisk[] = $high;
        }
        
        return [
            'years' => $years,
            'low' => $lowRisk,
            'medium' => $mediumRisk,
            'high' => $highRisk,
        ];
    }
}
