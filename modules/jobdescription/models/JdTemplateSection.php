<?php

namespace app\modules\jobdescription\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * หัวข้อใน template JD (หน้าที่, คุณสมบัติ, KPI ฯลฯ)
 *
 * @property int $id
 * @property int $template_id
 * @property string $title
 * @property string|null $content
 * @property int $sort_order
 * @property JdTemplate $template
 */
class JdTemplateSection extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%jd_template_section}}';
    }

    public function rules()
    {
        return [
            [['template_id', 'title'], 'required'],
            [['template_id', 'sort_order'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['sort_order'], 'default', 'value' => 0],
            [['template_id'], 'exist', 'targetClass' => JdTemplate::class, 'targetAttribute' => ['template_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'template_id' => 'Template',
            'title' => 'หัวข้อ',
            'content' => 'เนื้อหา',
            'sort_order' => 'ลำดับ',
        ];
    }

    public function getTemplate()
    {
        return $this->hasOne(JdTemplate::class, ['id' => 'template_id']);
    }
}
