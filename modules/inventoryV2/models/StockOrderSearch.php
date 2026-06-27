<?php

namespace app\modules\inventoryV2\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use app\modules\inventoryV2\models\StockOrder;

/**
 * StockOrderSearch represents the model behind the search form of `app\modules\inventoryV2\models\StockOrder`.
 */
class StockOrderSearch extends StockOrder
{
    /** ช่วงวันที่รับเข้า (ค้นหา) มาตรฐาน date_start / date_end */
    public $date_start;
    public $date_end;
    /** ช่วงวันที่จ่าย (สำหรับใบเบิก - filter ตาม updated_at) */
    public $confirmed_date_start;
    public $confirmed_date_end;
    /** ช่วงมูลค่ารับเข้า (ค้นหา) */
    public $total_from;
    public $total_to;
    /** ตัวกรองประเภทวัสดุ (categorise.code ของ asset_type) */
    public $category_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'main_warehouse_id', 'sub_warehouse_id', 'contact_id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['order_no', 'order_type', 'order_date', 'status', 'ref', 'data_json', 'date_start', 'date_end', 'confirmed_date_start', 'confirmed_date_end', 'total_from', 'total_to', 'category_id'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * formName for GET search params
     */
    public function formName()
    {
        return 'StockOrderSearch';
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
        $query = StockOrder::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $formName = $formName ?? $this->formName();
        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'order_date' => $this->order_date,
            'main_warehouse_id' => $this->main_warehouse_id,
            'sub_warehouse_id' => $this->sub_warehouse_id,
            'contact_id' => $this->contact_id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        if ($this->order_no !== null && $this->order_no !== '') {
            $query->andWhere(['like', 'order_no', $this->order_no]);
        }
        if ($this->status !== null && $this->status !== '') {
            $query->andWhere(['status' => $this->status]);
        }

        // ช่วงวันที่: แปลงและกรองฝั่ง Controller (AppHelper::convertToGregorian + andFilterWhere)

        $totalFromSet = $this->total_from !== null && $this->total_from !== '';
        $totalToSet = $this->total_to !== null && $this->total_to !== '';
        if ($totalFromSet || $totalToSet) {
            $subQuery = (new Query())
                ->select('stock_order_id')
                ->from('stock_detail')
                ->groupBy('stock_order_id');
            $havings = [];
            if ($totalFromSet) {
                $havings[] = ['>=', new Expression('SUM(qty * COALESCE(unit_price, 0))'), (float) $this->total_from];
            }
            if ($totalToSet) {
                $havings[] = ['<=', new Expression('SUM(qty * COALESCE(unit_price, 0))'), (float) $this->total_to];
            }
            if (!empty($havings)) {
                $subQuery->having(array_merge(['and'], $havings));
            }
            $query->andWhere(['id' => $subQuery]);
        }

        $query->andFilterWhere(['like', 'order_type', $this->order_type])
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        if ($this->category_id !== null && $this->category_id !== '') {
            $categorySub = (new Query())
                ->select('sd.stock_order_id')
                ->from(['sd' => 'stock_detail'])
                ->innerJoin(
                    ['c' => 'categorise'],
                    "c.code = sd.item_code AND c.name = 'asset_item' AND c.group_id = 'MATER'"
                )
                ->where(['c.category_id' => (string) $this->category_id]);
            $query->andWhere(['id' => $categorySub]);
        }

        return $dataProvider;
    }
}
