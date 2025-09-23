<?php

namespace app\modules\inventory\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\inventory\models\StockEvent;

/**
 * StockEventSearch represents the model behind the search form of `app\modules\inventory\models\StockEvent`.
 */
class StockEventSearch extends StockEvent
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'warehouse_id', 'from_warehouse_id', 'qty', 'thai_year', 'created_by', 'updated_by'], 'integer'],
            [[
                'name',
                'emp_id',
                'code',
                'asset_item',
                'receive_type',
                'movement_date',
                'lot_number',
                'category_id',
                'order_status',
                'ref',
                'data_json',
                'created_at',
                'updated_at',
                'q',
                'asset_type_name',
                'date_start',
                'date_end',
                'req_date_start',
                'req_date_end',
                'transaction_type',
                'q_month',
                'receive_month',
                'date_filter',
                'vendor_id',
                'asset_type_id',
                'q_asset_type',
                'q_warehouse_id',
                'q_code',
                'q_vendor'
            ], 'safe'],
            [['total_price', 'unit_price'], 'number'],
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = StockEvent::find()->alias('e'); // ให้ alias ต้นตอเป็น e

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => 'SORT_DESC',
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // filter ที่มีคอลัมน์ซ้ำ ต้อง prefix ด้วย alias
        $query->andFilterWhere([
            'e.id' => $this->id,
            'e.warehouse_id' => $this->warehouse_id,
            'e.from_warehouse_id' => $this->from_warehouse_id,
            'e.transaction_type' => $this->transaction_type,
            'e.asset_type_id' => $this->asset_type_id,
            'e.qty' => $this->qty,
            'e.emp_id' => $this->emp_id,
            'e.total_price' => $this->total_price,
            'e.unit_price' => $this->unit_price,
            'e.movement_date' => $this->movement_date,
            'e.vendor_id' => $this->vendor_id,
            'e.thai_year' => $this->thai_year,   // ✅ ระบุ alias
            'e.created_at' => $this->created_at,
            'e.updated_at' => $this->updated_at,
            'e.created_by' => $this->created_by,
            'e.updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'e.name', $this->name])
            ->andFilterWhere(['like', 'e.code', $this->code])
            ->andFilterWhere(['like', 'e.asset_item', $this->asset_item])
            ->andFilterWhere(['like', 'e.receive_type', $this->receive_type])
            ->andFilterWhere(['like', 'e.lot_number', $this->lot_number])
            ->andFilterWhere(['like', 'e.category_id', $this->category_id])
            ->andFilterWhere(['like', 'e.order_status', $this->order_status])
            ->andFilterWhere(['like', 'e.ref', $this->ref])
            ->andFilterWhere(['like', 'e.data_json', $this->data_json]);

        return $dataProvider;
    }

    public  function getDemo()
    {

        return 100;
    }
}
