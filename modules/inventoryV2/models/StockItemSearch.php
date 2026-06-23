<?php

namespace app\modules\inventoryV2\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockBalance;

/**
 * StockItemSearch — Phase 2 refactor
 *
 * Map กับ categorise table:
 *   item_code  -> code         (alias ผ่าน StockItemQuery)
 *   item_name  -> title
 *   min_qty    -> qty_min
 *   max_qty    -> qty_max
 *   is_active  -> active
 *
 * เนื่องจาก categorise table ไม่มีคอลัมน์ created_at/created_by/updated_at/updated_by
 * จึงตัด field เหล่านั้นออกจาก search rules
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

    public function rules()
    {
        return [
            [['id', 'is_asset', 'is_innovation', 'is_active'], 'integer'],
            [['item_code', 'item_name', 'ref', 'data_json', 'category_id', 'q', 'warehouse_id', 'balance_status'], 'safe'],
            [['min_qty', 'max_qty', 'balance_min', 'balance_max'], 'number'],
        ];
    }

    public function scenarios()
    {
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

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment to return empty results when invalid:
            // $query->where('0=1');
            return $dataProvider;
        }

        $formName = $formName ?? $this->formName();
        $subParams = $params[$formName] ?? [];

        // Hash filters — translate ผ่าน StockItemQuery::andFilterWhere โดยอัตโนมัติ
        $query->andFilterWhere([
            'id' => $this->id,
            'category_id' => $this->category_id,
            'qty_min' => $this->min_qty,
            'qty_max' => $this->max_qty,
        ]);

        // is_asset / is_innovation — เก็บใน data_json (ใช้ JSON_EXTRACT)
        if ($this->is_asset !== null && $this->is_asset !== '') {
            $query->andWhere("JSON_EXTRACT(categorise.data_json, '$.is_asset') = :is_asset",
                [':is_asset' => (int) $this->is_asset]);
        }
        if ($this->is_innovation !== null && $this->is_innovation !== '') {
            $query->andWhere("JSON_EXTRACT(categorise.data_json, '$.is_innovation') = :is_inno",
                [':is_inno' => (int) $this->is_innovation]);
        }

        // สถานะ active: กรองตาม dropdown (ทั้งหมด / เปิดใช้งาน / ปิดใช้งาน)
        $isActiveRaw = $subParams['is_active'] ?? $this->is_active ?? null;
        if ($isActiveRaw !== null && $isActiveRaw !== '') {
            $query->andWhere(['categorise.active' => (int) $isActiveRaw]);
        }

        // Like filters — ใช้ column name จริง (operator form ไม่ผ่าน translator)
        $query->andFilterWhere(['like', 'categorise.code', $this->item_code])
              ->andFilterWhere(['like', 'categorise.title', $this->item_name])
              ->andFilterWhere(['like', 'categorise.ref', $this->ref])
              ->andFilterWhere(['like', 'categorise.data_json', $this->data_json]);

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
            // join b.item_code (stock_balance) = categorise.code (StockItem)
            $query->leftJoin(['b' => $balanceSubQuery], 'b.item_code = categorise.code');
            if ($balanceStatus === 'below') {
                $query->andWhere('categorise.qty_min IS NOT NULL AND categorise.qty_min > 0')
                      ->andWhere('COALESCE(b.balance_qty, 0) < categorise.qty_min');
            } elseif ($balanceStatus === 'above') {
                $query->andWhere('categorise.qty_max IS NOT NULL AND categorise.qty_max > 0')
                      ->andWhere('COALESCE(b.balance_qty, 0) > categorise.qty_max');
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
