<?php

use yii\db\Migration;

/** Makes key processes structured so they can be synchronized to CSA. */
class m260826_120000_structure_service_profile_key_processes extends Migration
{
    public function safeUp()
    {
        $this->update('{{%service_profile_template_section}}', [
            'block_type' => 'key_process_table',
        ], [
            'section_code' => 'key_processes',
            'block_type' => 'rich_text',
        ]);
        $this->update('{{%service_profile_section}}', [
            'block_type' => 'key_process_table',
        ], [
            'section_code' => 'key_processes',
            'block_type' => 'rich_text',
        ]);
    }

    public function safeDown()
    {
        $this->update('{{%service_profile_template_section}}', [
            'block_type' => 'rich_text',
        ], ['section_code' => 'key_processes']);
        $this->update('{{%service_profile_section}}', [
            'block_type' => 'rich_text',
        ], ['section_code' => 'key_processes']);
    }
}
