<?php

namespace app\modules\hr\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "leave_summary".
 *
 * @property string|null $code รหัส
 * @property string|null $title ชื่อ
 * @property int|null $active
 * @property int|null $thai_year ปีงบประมาณ
 * @property int $m1
 * @property int $m2
 * @property int $m3
 * @property int $m4
 * @property int $m5
 * @property int $m6
 * @property int $m7
 * @property int $m8
 * @property int $m9
 * @property int $m10
 * @property int $m11
 * @property int $m12
 */
class LeaveSummary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'leave_summary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active', 'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9', 'm10', 'm11', 'm12'], 'integer'],
            [['thai_year'], 'safe'],
            [['code', 'title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'code' => 'รหัส',
            'title' => 'ชื่อ',
            'active' => 'Active',
            'thai_year' => 'ปีงบประมาณ',
            'm1' => 'M1',
            'm2' => 'M2',
            'm3' => 'M3',
            'm4' => 'M4',
            'm5' => 'M5',
            'm6' => 'M6',
            'm7' => 'M7',
            'm8' => 'M8',
            'm9' => 'M9',
            'm10' => 'M10',
            'm11' => 'M11',
            'm12' => 'M12',
        ];
    }

    public function getLeaveSummary($code = null){
        $where = ['and'];
        $where[] = ['thai_year' => $this->thai_year];  // ใช้กรองถ้าค่ามี
        if($code){
            $where[] = ['code' => $code];  // ใช้กรองถ้าค่ามี
        }

        return self::find()
            ->select([
                'total_m1' => 'SUM(m1)',
                'total_m2' => 'SUM(m2)',
                'total_m3' => 'SUM(m3)',
                'total_m4' => 'SUM(m4)',
                'total_m5' => 'SUM(m5)',
                'total_m6' => 'SUM(m6)',
                'total_m7' => 'SUM(m7)',
                'total_m8' => 'SUM(m8)',
                'total_m9' => 'SUM(m9)',
                'total_m10' => 'SUM(m10)',
                'total_m11' => 'SUM(m11)',
                'total_m12' => 'SUM(m12)',
            ])
            ->where($where)
            ->asArray()
            ->one();
    }

    public function getLeaveSummaryByCode($code = null)
    {
        $querys = self::find()->select([
            'code',
            'title',
            'thai_year',
            // 'total' => 'SUM(m1 + m2 + m3 + m4 + m5 + m6 + m7 + m8 + m9 + m10 + m11 + m12)',
            'total' => 'IFNULL(SUM(m1 + m2 + m3 + m4 + m5 + m6 + m7 + m8 + m9 + m10 + m11 + m12), 0)',
        ])
        ->from('leave_summary')
        ->where(['code' => $code ? $code : $this->code ])
        ->andWhere(['IS NOT', 'thai_year', null])
        ->groupBy('thai_year')
        ->asArray()
        ->all();

        $data = [];
        foreach ($querys as $key => $value) {
            $data[] = $value['total'];
        }

        return $data;
    }

    public function groupYear()
    {
        $year = self::find()
            ->andWhere(['IS NOT', 'thai_year', null])
            ->groupBy(['thai_year'])
            ->orderBy(['thai_year' => SORT_DESC])
            ->all();
            return ArrayHelper::map($year,'thai_year','thai_year');
    }
    public function listYear()
    {

        $leaveTypes = self::find()
            ->select(['thai_year','code','title'])
            ->andWhere(['IS NOT', 'thai_year', null])
            ->groupBy(['code'])
            ->orderBy(['thai_year' => SORT_ASC])
            ->asArray()
            ->all();

        $thaiYear = [];
        $seriesSummary = [];
        foreach ($leaveTypes as $key => $value) {

            $seriesSummary[] = [
                'name' =>  $value['title'],
                'data' => $this->getLeaveSummaryByCode($value['code'])
            ];
            $thaiYear[] = $value['thai_year'];
        }

        return [
            'summary' => $seriesSummary,
            'thai_year' => $thaiYear,
        ];
        
    }
}
