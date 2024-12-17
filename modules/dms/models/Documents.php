<?php

namespace app\modules\dms\models;

use Yii;
use app\models\Categorise;

/**
 * This is the model class for table "documents".
 *
 * @property int $id
 * @property string|null $doc_number เลขที่หนังสือ
 * @property string|null $topic ชื่อเรื่อง
 * @property string|null $doc_type_id ประเภทหนังสือ
 * @property string|null $document_org_id จากหน่วยงาน
 * @property string|null $thai_year ปี พ.ศ.
 * @property string|null $doc_regis_number เลขรับ
 * @property string|null $urgent ชั้นความเร็ว
 * @property string|null $secret ชั้นความลับ
 * @property string|null $doc_date วันที่หนังสือ
 * @property string|null $doc_expire วันหมดอายุ
 * @property string|null $doc_receive ลงวันรับเข้า
 * @property string|null $doc_time เวลารับ
 * @property string|null $data_json
 */
class Documents extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'documents';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['topic'], 'string'],
            [['data_json'], 'safe'],
            [['doc_number', 'doc_type_id', 'document_org_id', 'thai_year', 'doc_regis_number', 'urgent', 'secret', 'doc_date', 'doc_expire', 'doc_receive', 'doc_time'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'doc_number' => 'เลขที่หนังสือ',
            'topic' => 'ชื่อเรื่อง',
            'doc_type_id' => 'ประเภทหนังสือ',
            'document_org_id' => 'จากหน่วยงาน',
            'thai_year' => 'ปี พ.ศ.',
            'doc_regis_number' => 'เลขรับ',
            'urgent' => 'ชั้นความเร็ว',
            'secret' => 'ชั้นความลับ',
            'doc_date' => 'วันที่หนังสือ',
            'doc_expire' => 'วันหมดอายุ',
            'doc_receive' => 'ลงวันรับเข้า',
            'doc_time' => 'เวลารับ',
            'data_json' => 'Data Json',
        ];
    }
        // section Relationships
        public function getDocumentOrg()
        {
            return $this->hasOne(Categorise::class, ['code' => 'document_org_id'])->andOnCondition(['name' => 'document_org']);
        }

        //แสดงรูปแบบ format วันที่หนังสือ
        public function viewDocDate()
        {
            return Yii::$app->thaiFormatter->asDate($this->doc_date, 'medium');
        }
}
