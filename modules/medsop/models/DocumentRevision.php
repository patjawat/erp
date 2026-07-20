<?php

namespace app\modules\medsop\models;

use app\modules\filemanager\models\Uploads;
use yii\db\ActiveRecord;

class DocumentRevision extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%medsop_document_revision}}';
    }

    public function rules()
    {
        return [
            [['document_id', 'revision_no', 'snapshot_json', 'file_ref'], 'required'],
            [['document_id', 'revision_no', 'created_emp_id', 'created_by'], 'integer'],
            [['snapshot_json'], 'string'],
            [['created_at', 'approved_at'], 'safe'],
            [['file_ref'], 'string', 'max' => 100],
            [['approval_status'], 'string', 'max' => 20],
        ];
    }

    public function getDocument()
    {
        return $this->hasOne(Document::class, ['id' => 'document_id']);
    }

    public function getUploads()
    {
        return $this->hasMany(Uploads::class, ['ref' => 'file_ref']);
    }

    public function getSnapshot(): array
    {
        $value = json_decode((string) $this->snapshot_json, true);
        return is_array($value) ? $value : [];
    }
}
