<?php

use yii\db\Migration;

/** Adopts the simpler general-purpose 10-section JD structure. */
class m260718_000002_adopt_simple_jd_structure extends Migration
{
    public function safeUp()
    {
        $this->update('{{%jd_template_block}}', ['is_enabled' => 0], ['section_code' => 'authority']);
        $templates = (new \yii\db\Query())->select('id')->from('{{%jd_template}}')->column();
        foreach ($templates as $templateId) {
            $exists = (new \yii\db\Query())->from('{{%jd_template_block}}')->where([
                'template_id' => $templateId,
                'section_code' => 'approval',
            ])->exists();
            if (!$exists) {
                $this->insert('{{%jd_template_block}}', [
                    'template_id' => $templateId,
                    'section_code' => 'approval',
                    'title' => '10. การอนุมัติเอกสาร',
                    'block_type' => 'approval',
                    'data_json' => null,
                    'sort_order' => 100,
                    'is_enabled' => 1,
                ]);
            }
        }
    }

    public function safeDown()
    {
        $this->delete('{{%jd_template_block}}', ['section_code' => 'approval']);
        $this->update('{{%jd_template_block}}', ['is_enabled' => 1], ['section_code' => 'authority']);
    }
}
