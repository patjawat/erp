<?php

use yii\db\Migration;

class m260718_000003_add_image_to_appreciation extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%appreciation}}', 'image_path', $this->string(500)->after('message'));
        $this->addColumn('{{%appreciation}}', 'frame_style', $this->string(32)->notNull()->defaultValue('classic')->after('image_path'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%appreciation}}', 'frame_style');
        $this->dropColumn('{{%appreciation}}', 'image_path');
    }
}
