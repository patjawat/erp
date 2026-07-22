<?php

declare(strict_types=1);

namespace app\modules\ai\models;

use yii\db\ActiveQuery;

/**
 * @property string $id
 * @property string $dataset_id
 * @property string $field_name
 * @property string $label
 * @property string $data_type
 * @property int $is_filterable
 * @property int $is_sortable
 * @property int $is_selectable
 * @property string|null $allowed_operators
 * @property int $sort_order
 * @property string $created_at
 */
class AiDatasetField extends AiActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ai_dataset_fields}}';
    }

    public function rules(): array
    {
        return [
            [['id', 'dataset_id', 'field_name', 'label', 'data_type'], 'required'],
            [['is_filterable', 'is_sortable', 'is_selectable', 'sort_order'], 'integer'],
            [['created_at'], 'safe'],
            [['id', 'dataset_id'], 'string', 'max' => 36],
            [['field_name'], 'string', 'max' => 128],
            [['label'], 'string', 'max' => 255],
            [['data_type'], 'string', 'max' => 32],
            [['allowed_operators'], 'string', 'max' => 255],
        ];
    }

    public function getDataset(): ActiveQuery
    {
        return $this->hasOne(AiDataset::class, ['id' => 'dataset_id']);
    }

    /**
     * @return array<int, string>
     */
    public function getAllowedOperatorList(): array
    {
        if ($this->allowed_operators === null || trim($this->allowed_operators) === '') {
            return ['=', '!='];
        }

        $operators = json_decode($this->allowed_operators, true);
        if (!is_array($operators)) {
            $operators = explode(',', $this->allowed_operators);
        }

        return array_values(array_filter(array_map(static fn ($operator): string => trim((string) $operator), $operators)));
    }
}
