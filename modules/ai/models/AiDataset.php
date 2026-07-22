<?php

declare(strict_types=1);

namespace app\modules\ai\models;

use yii\db\ActiveQuery;

/**
 * @property string $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $view_name
 * @property string $permission_name
 * @property int $max_rows
 * @property int $is_exportable
 * @property int $is_active
 * @property string|null $metadata_json
 * @property string $created_at
 * @property string $updated_at
 */
class AiDataset extends AiActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ai_datasets}}';
    }

    public function rules(): array
    {
        return [
            [['id', 'code', 'name', 'view_name', 'permission_name'], 'required'],
            [['description', 'metadata_json'], 'string'],
            [['max_rows', 'is_exportable', 'is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['id'], 'string', 'max' => 36],
            [['code', 'view_name', 'permission_name'], 'string', 'max' => 128],
            [['name'], 'string', 'max' => 255],
            [['code'], 'unique'],
        ];
    }

    public function getFields(): ActiveQuery
    {
        return $this->hasMany(AiDatasetField::class, ['dataset_id' => 'id'])->orderBy(['sort_order' => SORT_ASC, 'field_name' => SORT_ASC]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->decodeJson($this->metadata_json);
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata_json = $this->encodeJson($metadata);
    }
}
