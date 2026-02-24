<?php

namespace app\modules\inventoryV2\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockBalance;

/**
 * StockItemSearch represents the model behind the search form of `app\modules\inventoryV2\models\StockItem`.
 */
class StockItemSearch extends StockItem
{
    /** คลังที่ใช้กรอง/แสดงจำนวนคงเหลือ */
    public $warehouse_id;

    /** กรองตามสถานะยอดเทียบ Min/Max: below = ต่ำกว่ากำหนด, above = มากกว่ากำหนด (ใช้ได้เมื่อเลือกคลัง) */
    public $balance_status;

    /** กรองจำนวนคงเหลือ: ยอดตั้งแต่ (>=) ใช้ได้เมื่อเลือกคลัง */
    public $balance_min;

    /** กรองจำนวนคงเหลือ: ยอดไม่เกิน (<=) ใช้ได้เมื่อเลือกคลัง */
    public $balance_max;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','is_asset', 'is_innovation', 'is_active', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['item_code', 'item_name', 'ref', 'data_json','category_id','q', 'warehouse_id', 'balance_status'], 'safe'],
            [['min_qty', 'max_qty', 'balance_min', 'balance_max'], 'number'],
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
        $query = StockItem::find();

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
            'category_id' => $this->category_id,
            'min_qty' => $this->min_qty,
            'max_qty' => $this->max_qty,
            'is_asset' => $this->is_asset,
            'is_innovation' => $this->is_innovation,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'item_code', $this->item_code])
            ->andFilterWhere(['like', 'item_name', $this->item_name])
            ->andFilterWhere(['like', 'ref', $this->ref])
            ->andFilterWhere(['like', 'data_json', $this->data_json]);

        $warehouseId = $this->warehouse_id ? (int) $this->warehouse_id : 0;
        $balanceStatus = $this->balance_status === 'below' || $this->balance_status === 'above' ? $this->balance_status : '';
        $balanceMin = $this->balance_min !== null && $this->balance_min !== '' ? (float) $this->balance_min : null;
        $balanceMax = $this->balance_max !== null && $this->balance_max !== '' ? (float) $this->balance_max : null;
        $needBalanceJoin = $warehouseId > 0 && ($balanceStatus !== '' || $balanceMin !== null || $balanceMax !== null);

        if ($needBalanceJoin) {
            $balanceSubQuery = (new Query())
                ->select(['item_code', 'SUM([[balance_qty]]) AS balance_qty'])
                ->from(StockBalance::tableName())
                ->where(['warehouse_id' => $warehouseId])
                ->groupBy('item_code');
            $query->leftJoin(['b' => $balanceSubQuery], 'b.item_code = stock_item.item_code');
            if ($balanceStatus === 'below') {
                $query->andWhere('stock_item.min_qty IS NOT NULL AND stock_item.min_qty > 0')
                    ->andWhere('COALESCE(b.balance_qty, 0) < stock_item.min_qty');
            } elseif ($balanceStatus === 'above') {
                $query->andWhere('stock_item.max_qty IS NOT NULL AND stock_item.max_qty > 0')
                    ->andWhere('COALESCE(b.balance_qty, 0) > stock_item.max_qty');
            }
            if ($balanceMin !== null) {
                $query->andWhere('COALESCE(b.balance_qty, 0) >= :bal_min', [':bal_min' => $balanceMin]);
            }
            if ($balanceMax !== null) {
                $query->andWhere('COALESCE(b.balance_qty, 0) <= :bal_max', [':bal_max' => $balanceMax]);
            }
        }

        return $dataProvider;
    }
}
