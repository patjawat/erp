<?php

namespace app\modules\dms\models;

use Yii;
use app\modules\hr\models\Employees;
use app\modules\dms\models\Documents;

/**
 * This is the model class for table "document_tags".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $name ชื่อการ tags เอกสาร
 * @property string|null $doc_number เลขที่หนังสือ
 * @property string|null $doc_regis_number เลขรับ
 * @property string|null $emp_id
 * @property string|null $status สถานะ
 * @property string|null $department_id
 * @property string|null $document_org_id จากหน่วยงาน
 * @property string|null $data_json
 */
class DocumentTags extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'document_tags';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_json'], 'safe'],
            [['ref', 'name', 'doc_number','document_id','doc_regis_number', 'emp_id', 'status', 'department_id', 'document_org_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ref' => 'Ref',
            'name' => 'ชื่อการ tags เอกสาร',
            'doc_number' => 'เลขที่หนังสือ',
            'doc_regis_number' => 'เลขรับ',
            'emp_id' => 'Emp ID',
            'status' => 'สถานะ',
            'department_id' => 'Department ID',
            'document_org_id' => 'จากหน่วยงาน',
            'data_json' => 'Data Json',
        ];
    }
    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }
    public function getDocument()
    {
        return $this->hasOne(Documents::class, ['id' => 'document_id']);
    }

}
