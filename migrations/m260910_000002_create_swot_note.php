<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * โพสต์อิทแต่ละใบในกระดาน SWOT/SOAR — ตารางลูกของ swot_board
 * แยกเป็นตารางลูกเพื่อให้ query/รายงานข้อมูลระดับโน้ตได้ในอนาคต
 */
final class m260910_000002_create_swot_note extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%swot_note}}', [
            'id' => $this->primaryKey(),
            'board_id' => $this->integer()->notNull(),
            // strengths | weaknesses | opportunities | threats | aspirations | results
            'quadrant' => $this->string(20)->notNull(),
            'content' => $this->text()->notNull(),
            'category' => $this->string(255)->null(),
            'weight' => $this->tinyInteger()->notNull()->defaultValue(3), // 1-5
            'color' => $this->string(20)->notNull()->defaultValue('yellow'),
            'priority' => $this->string(10)->notNull()->defaultValue('medium'), // high|medium|low
            'author' => $this->string(255)->null(),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('idx-swot_note-board', '{{%swot_note}}', ['board_id', 'quadrant']);

        $this->addForeignKey(
            'fk-swot_note-board',
            '{{%swot_note}}',
            'board_id',
            '{{%swot_board}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-swot_note-board', '{{%swot_note}}');
        $this->dropTable('{{%swot_note}}');
    }
}
