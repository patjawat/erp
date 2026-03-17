<?php

namespace app\modules\pdfTemplate\models;

use yii\db\ActiveRecord;

/**
 * Field position on a PDF template (normalized coordinates).
 *
 * position_json structure:
 * {
 *   "field": "officer_name",
 *   "page": 1,
 *   "x_percent": 0.32,
 *   "y_percent": 0.48,
 *   "width_percent": 0.20,
 *   "height_percent": 0.03,
 *   "font_size": 14,
 *   "alignment": "L"
 * }
 *
 * @property int $id
 * @property int $template_id
 * @property string $field_name
 * @property string $position_json
 * @property int $sort
 * @property PdfTemplate $template
 */
class PdfTemplateField extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%pdf_template_fields}}';
    }

    public function rules(): array
    {
        return [
            [['template_id', 'field_name', 'position_json'], 'required'],
            [['template_id', 'sort'], 'integer'],
            [['field_name'], 'string', 'max' => 100],
            [['template_id'], 'exist', 'targetClass' => PdfTemplate::class, 'targetAttribute' => ['template_id' => 'id']],
        ];
    }

    public function getTemplate(): \yii\db\ActiveQuery
    {
        return $this->hasOne(PdfTemplate::class, ['id' => 'template_id']);
    }

    /**
     * Decode position_json and return array with x_percent, y_percent, etc.
     */
    public function getPosition(): array
    {
        $raw = is_string($this->position_json) ? json_decode($this->position_json, true) : $this->position_json;
        return is_array($raw) ? $raw : [];
    }

    /**
     * Set position from array (will be stored as JSON).
     */
    public function setPosition(array $pos): void
    {
        $this->position_json = json_encode($pos);
    }
}
