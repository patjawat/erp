<?php

namespace app\modules\attendance\models;

use Yii;
use yii\data\ActiveDataProvider;
use app\components\AppHelper;
use app\components\DateFilterHelper;

class CheckinRecordSearch extends CheckinRecord
{
    public $date_filter;
    public $date_start;
    public $date_end;
    public $q;
    public $q_emp;

    public function rules()
    {
        return [
            [['id', 'emp_id', 'location_id', 'is_in_location', 'approved_by'], 'integer'],
            [['checkin_at', 'method', 'status', 'check_type', 'date_filter', 'date_start', 'date_end', 'q', 'q_emp'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = CheckinRecord::find()->with(['employee', 'employee.empDepartment', 'location', 'approver']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['checkin_at' => SORT_DESC]],
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['checkin_record.id' => $this->id]);
        $query->andFilterWhere(['checkin_record.emp_id' => $this->emp_id]);
        $query->andFilterWhere(['checkin_record.location_id' => $this->location_id]);
        $query->andFilterWhere(['checkin_record.method' => $this->method]);
        $query->andFilterWhere(['checkin_record.status' => $this->status]);
        $query->andFilterWhere(['checkin_record.check_type' => $this->check_type]);
        $query->andFilterWhere(['checkin_record.is_in_location' => $this->is_in_location]);
        $query->andFilterWhere(['checkin_record.approved_by' => $this->approved_by]);

        // preset ช่วงเวลา (date_filter) — เติม date_start/date_end ถ้ายังว่าง
        if ((empty($this->date_start) || empty($this->date_end)) && !empty($this->date_filter)) {
            $range = DateFilterHelper::getRange((string)$this->date_filter);
            if ($range !== null) {
                if (empty($this->date_start)) {
                    $this->date_start = date('Y-m-d', strtotime($range[0]));
                }
                if (empty($this->date_end)) {
                    $this->date_end = date('Y-m-d', strtotime($range[1]));
                }
            }
        }

        $dateStart = $this->normalizeDate($this->date_start);
        $dateEnd = $this->normalizeDate($this->date_end);
        if ($dateStart) {
            $query->andWhere(['>=', 'checkin_record.checkin_at', $dateStart . ' 00:00:00']);
        }
        if ($dateEnd) {
            $query->andWhere(['<=', 'checkin_record.checkin_at', $dateEnd . ' 23:59:59']);
        }

        if ($this->q_emp !== null && $this->q_emp !== '') {
            $query->joinWith(['employee']);
            $query->andWhere([
                'or',
                ['like', 'employees.fname', $this->q_emp],
                ['like', 'employees.lname', $this->q_emp],
                ['like', 'employees.cid', $this->q_emp],
                ['like', 'employees.email', $this->q_emp],
            ]);
        }

        return $dataProvider;
    }

    /**
     * แปลงค่าวันที่จากรูปแบบไทย (d/m/พ.ศ.) เป็น Y-m-d สำหรับ query
     */
    protected function normalizeDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = trim($value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }
        $gregorian = AppHelper::convertToGregorian($value);
        return $gregorian ?: $value;
    }
}
