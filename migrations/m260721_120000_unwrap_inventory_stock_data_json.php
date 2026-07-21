<?php

use yii\db\Migration;

class m260721_120000_unwrap_inventory_stock_data_json extends Migration
{
    public function safeUp()
    {
        foreach (['stock_order', 'stock_detail'] as $table) {
            $this->execute("
                UPDATE {$table}
                SET data_json = JSON_UNQUOTE(data_json)
                WHERE data_json IS NOT NULL
                  AND JSON_TYPE(data_json) = 'STRING'
                  AND JSON_VALID(JSON_UNQUOTE(data_json)) = 1
                  AND (
                      LTRIM(JSON_UNQUOTE(data_json)) LIKE '{%'
                      OR LTRIM(JSON_UNQUOTE(data_json)) LIKE '[%'
                  )
            ");
        }
    }

    public function safeDown()
    {
        echo "m260721_120000_unwrap_inventory_stock_data_json cannot be reverted.\n";
        return false;
    }
}
