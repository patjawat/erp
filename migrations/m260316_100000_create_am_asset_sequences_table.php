<?php

use yii\db\Migration;

/**
 * Sequence table for asset number generation. Tracks current_sequence per category and year.
 * Resets every year per category.
 */
class m260316_100000_create_am_asset_sequences_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%am_asset_sequences}}', [
            'id' => $this->primaryKey(),
            'category_id' => $this->string(100)->notNull()->comment('FSN prefix e.g. 7910-003-0003'),
            'year' => $this->integer()->notNull()->comment('Buddhist year e.g. 2566'),
            'current_sequence' => $this->integer()->notNull()->defaultValue(0)->comment('Last used sequence'),
            'updated_at' => $this->dateTime()->null(),
        ]);
        $this->createIndex('idx_am_asset_sequences_category_year', '{{%am_asset_sequences}}', ['category_id', 'year'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%am_asset_sequences}}');
    }
}
