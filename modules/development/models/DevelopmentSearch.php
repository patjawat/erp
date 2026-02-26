<?php

namespace app\modules\development\models;

use app\components\AppHelper;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * DevelopmentSearch สำหรับค้นหาจากตาราง development (ใช้ table เดิม).
 */
class DevelopmentSearch extends Development
{
    /** ตัวกรอง: วันที่เริ่มตั้งแต่ (Y-m-d) */
    public $date_start_from;
    /** ตัวกรอง: วันที่เริ่มไม่เกิน (Y-m-d) */
    public $date_start_to;
    /** ค้นหาบุคลากร (ชื่อ นามสกุล หรือรหัส) */
    public $emp_keyword;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'document_id', 'thai_year', 'assigned_to', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['response_status', 'development_type_id', 'topic', 'status', 'date_start', 'time_start', 'date_end', 'time_end', 'vehicle_type_id', 'vehicle_date_start', 'vehicle_date_end', 'driver_id', 'leader_id', 'leader_group_id', 'emp_id', 'data_json', 'created_at', 'updated_at', 'deleted_at', 'date_start_from', 'date_start_to', 'emp_keyword'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'date_start_from' => 'วันที่เริ่มตั้งแต่',
            'date_start_to' => 'ถึงวันที่',
            'emp_keyword' => 'ค้นหาบุคลากร',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @param array $params
     * @param string|null $formName
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = Development::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params, $formName);

        // แปลงค่าวันที่จาก widget (d/m/Y พ.ศ.) เป็น Y-m-d สำหรับ query (ไม่เขียนทับ attribute เพื่อให้ฟอร์มแสดงค่าวันที่ไทยได้)
        $dateFromDb = null;
        $dateToDb = null;
        if (is_string($this->date_start_from) && trim($this->date_start_from) !== '' && strpos($this->date_start_from, '/') !== false) {
            $dateFromDb = AppHelper::DateToDb($this->date_start_from);
        } elseif (is_string($this->date_start_from) && trim($this->date_start_from) !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->date_start_from)) {
            $dateFromDb = $this->date_start_from;
        }
        if (is_string($this->date_start_to) && trim($this->date_start_to) !== '' && strpos($this->date_start_to, '/') !== false) {
            $dateToDb = AppHelper::DateToDb($this->date_start_to);
        } elseif (is_string($this->date_start_to) && trim($this->date_start_to) !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->date_start_to)) {
            $dateToDb = $this->date_start_to;
        }

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'document_id' => $this->document_id,
            'thai_year' => $this->thai_year,
            'vehicle_date_start' => $this->vehicle_date_start,
            'vehicle_date_end' => $this->vehicle_date_end,
            'assigned_to' => $this->assigned_to,
            'development_type_id' => $this->development_type_id,
            'response_status' => $this->response_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', Development::tableName() . '.topic', $this->topic])
            ->andFilterWhere(['like', Development::tableName() . '.status', $this->status])
            ->andFilterWhere(['>=', Development::tableName() . '.date_start', $dateFromDb])
            ->andFilterWhere(['<=', Development::tableName() . '.date_start', $dateToDb])
            ->andFilterWhere(['like', 'time_start', $this->time_start])
            ->andFilterWhere(['like', 'time_end', $this->time_end])
            ->andFilterWhere(['like', 'vehicle_type_id', $this->vehicle_type_id])
            ->andFilterWhere(['like', 'driver_id', $this->driver_id])
            ->andFilterWhere(['like', 'leader_id', $this->leader_id])
            ->andFilterWhere(['like', 'leader_group_id', $this->leader_group_id])
            ->andFilterWhere(['like', Development::tableName() . '.emp_id', $this->emp_id])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        if (is_string($this->emp_keyword) && trim($this->emp_keyword) !== '') {
            $keyword = trim($this->emp_keyword);
            $query->joinWith(['emp']);
            $query->andWhere([
                'or',
                ['like', 'emp.fname', $keyword],
                ['like', 'emp.lname', $keyword],
                ['like', 'emp.cid', $keyword],
                ['like', Development::tableName() . '.emp_id', $keyword],
            ]);
        }

        return $dataProvider;
    }
}
