<?php

namespace app\modules\helpdesk2\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\helpdesk2\models\Helpdesk;

/**
 * HelpdeskSearch represents the model behind the search form of `app\modules\helpdesk\models\Helpdesk`.
 */
class HelpdeskSearch extends Helpdesk
{
    /** @var string|null ช่วงวันที่แจ้ง (พ.ศ.) สำหรับหน้าช่าง V2 */
    public $created_date_from;
    /** @var string|null */
    public $created_date_to;
    /** @var string|null ค้นข้อความในสถานที่ (data_json.location) */
    public $q_location;
    /** @var string|null ค้นชื่อ/นามสกุลผู้แจ้ง */
    public $q_requester;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_by', 'updated_by'], 'integer'],
            [['ref', 'code', 'date_start', 'date_end', 'name', 'title', 'data_json','created_at', 'updated_at','repair_group','status','q','urgency','thai_year','auth_item','emp_id','date_filter','date_filter','device_type_id','repair_number','q_department', 'created_date_from', 'created_date_to', 'q_location', 'q_requester'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'title' => 'อาการ',
            'device_type_id' => 'อุปกรณ์',
            'created_date_from' => 'วันที่แจ้ง (ตั้งแต่)',
            'created_date_to' => 'วันที่แจ้ง (ถึง)',
            'q_location' => 'สถานที่',
            'q_requester' => 'ผู้แจ้ง',
        ]);
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Helpdesk::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions (qualify columns used after joinWith('emp') to avoid ambiguous status/name)
        $query->andFilterWhere([
            'helpdesk.id' => $this->id,
            'helpdesk.emp_id' => $this->emp_id,
            'helpdesk.repair_group' => $this->repair_group,
            'helpdesk.status' => $this->status,
            'helpdesk.thai_year' => $this->thai_year,
            'helpdesk.device_type_id' => $this->device_type_id,
            'helpdesk.asset_number' => $this->asset_number,
            'helpdesk.created_at' => $this->created_at,
            'helpdesk.updated_at' => $this->updated_at,
            'helpdesk.created_by' => $this->created_by,
            'helpdesk.updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'helpdesk.ref', $this->ref])
            ->andFilterWhere(['like', 'helpdesk.repair_number', $this->repair_number])
            ->andFilterWhere(['like', 'helpdesk.code', $this->code])
            ->andFilterWhere(['like', 'helpdesk.name', $this->name])
            ->andFilterWhere(['like', 'helpdesk.title', $this->title])
            ->andFilterWhere(['like', 'helpdesk.data_json', $this->data_json]);

        return $dataProvider;
    }
}
