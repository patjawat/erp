<?php

namespace app\modules\inventoryV2\models;

use yii\db\ActiveQuery;

/**
 * ActiveQuery สำหรับ StockItem ที่ map ตรง categorise(asset_item, MATER)
 *
 * หน้าที่:
 *   1. Inject default scope name='asset_item' AND group_id='MATER' ทุก query
 *   2. Translate column alias ใน where/andWhere/orWhere/onCondition:
 *        item_code -> code
 *        item_name -> title
 *        min_qty   -> qty_min
 *        max_qty   -> qty_max
 *        is_active -> active
 */
class StockItemQuery extends ActiveQuery
{
    private static $aliases = [
        'item_code' => 'code',
        'item_name' => 'title',
        'min_qty'   => 'qty_min',
        'max_qty'   => 'qty_max',
        'is_active' => 'active',
    ];

    public function init()
    {
        parent::init();
        $this->andWhere([
            'categorise.name' => 'asset_item',
            'categorise.group_id' => 'MATER',
        ]);
    }

    public function where($condition, $params = [])
    {
        return parent::where($this->translateCondition($condition), $params);
    }

    public function andWhere($condition, $params = [])
    {
        return parent::andWhere($this->translateCondition($condition), $params);
    }

    public function orWhere($condition, $params = [])
    {
        return parent::orWhere($this->translateCondition($condition), $params);
    }

    public function andFilterWhere(array $condition)
    {
        return parent::andFilterWhere($this->translateCondition($condition));
    }

    public function orFilterWhere(array $condition)
    {
        return parent::orFilterWhere($this->translateCondition($condition));
    }

    /**
     * รองรับ findOne(['item_code' => 'X']) — แปลง alias ใน hash condition
     * ไม่แตะ operator condition แบบ ['like', 'item_name', 'foo']
     * (TODO Phase 3: ถ้าเจอ pattern แบบนั้นค่อยขยาย)
     */
    private function translateCondition($condition)
    {
        if (!is_array($condition)) {
            return $condition;
        }
        $out = [];
        foreach ($condition as $key => $value) {
            if (is_string($key) && isset(self::$aliases[$key])) {
                $out[self::$aliases[$key]] = $value;
            } else {
                $out[$key] = $value;
            }
        }
        return $out;
    }
}
