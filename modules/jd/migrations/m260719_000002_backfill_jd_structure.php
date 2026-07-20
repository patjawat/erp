<?php

use app\modules\jd\models\JdTemplateBlock;
use yii\db\Migration;
use yii\db\Query;

/** Ensures legacy templates and employee drafts contain the standard JD structure. */
class m260719_000002_backfill_jd_structure extends Migration
{
    public function safeUp()
    {
        $definitions = JdTemplateBlock::definitions();
        $templateIds = (new Query())->select('id')->from('{{%jd_template}}')->column();
        foreach ($templateIds as $templateId) {
            $order = 10;
            foreach ($definitions as $code => [$title, $type]) {
                $exists = (new Query())->from('{{%jd_template_block}}')->where([
                    'template_id' => $templateId,
                    'section_code' => $code,
                ])->exists();
                if (!$exists) {
                    $this->insert('{{%jd_template_block}}', [
                        'template_id' => $templateId,
                        'section_code' => $code,
                        'title' => $title,
                        'block_type' => $type,
                        'data_json' => json_encode(['intro' => '', 'items' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'sort_order' => $order,
                        'is_enabled' => 1,
                    ]);
                }
                $order += 10;
            }
        }

        $draftIds = (new Query())->select('id')->from('{{%jd_employee}}')->where(['status' => 'draft'])->column();
        foreach ($draftIds as $jdId) {
            $order = 10;
            foreach ($definitions as $code => [$title, $type]) {
                $exists = (new Query())->from('{{%jd_employee_section}}')->where([
                    'jd_employee_id' => $jdId,
                    'section_code' => $code,
                ])->exists();
                if (!$exists) {
                    $this->insert('{{%jd_employee_section}}', [
                        'jd_employee_id' => $jdId,
                        'section_code' => $code,
                        'title' => $title,
                        'block_type' => $type,
                        'data_json' => json_encode(['intro' => '', 'items' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'sort_order' => $order,
                    ]);
                }
                $order += 10;
            }
        }
    }

    public function safeDown()
    {
        return true;
    }
}
