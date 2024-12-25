<?php

namespace app\modules\dms\models;

use Yii;
use app\models\Uploads;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\hr\models\Employees;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * This is the model class for table "documents".
 *
 * @property int $id
 * @property string|null $doc_number เลขที่หนังสือ
 * @property string|null $topic ชื่อเรื่อง
 * @property string|null $document_type ประเภทหนังสือ
 * @property string|null $document_org_id จากหน่วยงาน
 * @property string|null $thai_year ปี พ.ศ.
 * @property string|null $doc_regis_number เลขรับ
 * @property string|null $doc_speed ชั้นความเร็ว
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
    public $q;

    public function rules()
    {
        return [
            [['topic'], 'string'],
            [['data_json', 'q','document_group','department_tag','employee_tag'], 'safe'],
            [['doc_number', 'document_type', 'document_org', 'thai_year', 'doc_regis_number', 'doc_speed', 'secret', 'doc_date', 'doc_expire', 'doc_receive', 'doc_time'], 'string', 'max' => 255],
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
            'document_type' => 'ประเภทหนังสือ',
            'document_org' => 'จากหน่วยงาน',
            'thai_year' => 'ปี พ.ศ.',
            'doc_regis_number' => 'เลขรับ',
            'doc_speed' => 'ชั้นความเร็ว',
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
        return $this->hasOne(Categorise::class, ['code' => 'document_org'])->andOnCondition(['name' => 'document_org']);
    }

    // ประเภทหนังสือ
    public function getDocumentType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'document_type'])->andOnCondition(['name' => 'document_type']);
    }

    // แสดงรูปแบบ format วันที่หนังสือ
    public function viewDocDate()
    {
        return Yii::$app->thaiFormatter->asDate($this->doc_date, 'medium');
    }

    public function Upload($name)
    {
        return FileManagerHelper::FileUpload($this->ref, $name);
    }
    // แสดงปีงบประมานทั้งหมด

    public function ListThaiYear()
    {
        $model = self::find()
            ->select('thai_year')
            ->groupBy('thai_year')
            ->orderBy(['thai_year' => SORT_DESC])
            ->asArray()
            ->all();

        $year = AppHelper::YearBudget();
        $isYear = [['thai_year' => $year]];  // ห่อด้วย array เพื่อให้รูปแบบตรงกัน
        // รวมข้อมูล
        $model = ArrayHelper::merge($isYear, $model);
        return ArrayHelper::map($model, 'thai_year', 'thai_year');
    }

    // แสดงรายการประเภทเอกสาร
    public function ListDocumentType()
    {
        $model = Categorise::find()
            ->where(['name' => 'document_type'])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'code', 'title');
    }

    // ตรวจเช็คว่ามีการแบไฟล์หรือไม่
    public function isFile()
    {
        $ref = $this->ref;
        $directory = Yii::getAlias('@app/modules/filemanager/fileupload/' . $ref . '/');
        $checkFileUpload = Uploads::findOne(['ref' => $ref]);
        if ($checkFileUpload) {
            $fileName = $checkFileUpload->real_filename;
            $filePath = $directory . $fileName;

            // ตรวจสอบว่าไฟล์มีอยู่หรือไม่
            if (file_exists($filePath) && is_file($filePath)) {
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function listEmployee()
    {
        // ดึงข้อมูลจากตาราง Employee
         $employees = Employees::find()->limit(5)->all();
         return ArrayHelper::map($employees, 'id', function($model){
            return $model->fullname;
         });
    }

    //ดึงค่าไปแสดงตอนที่เรา update
    public function listEmployeeSelectTag()
    {
        try {

        // ดึงข้อมูลจากฐานข้อมูล
        $items = Employees::find()
            ->select(['id', 'fname', 'lname']) // เลือกเฉพาะฟิลด์ที่ต้องการ
            ->where(['id' => $this->data_json['employee_tag']]) // เฉพาะรายการที่เคยเลือก
            ->andWhere(['status' => 1])
            ->andWhere(['>','id',1])
            ->asArray()
            ->all();
    
        // คืนค่าในรูปแบบ [id => 'fname lname']
        $result = [];
        foreach ($items as $item) {
            $result[$item['id']] = $item['fname'] . ' ' . $item['lname'];
        }
    
        return $result;
                    
    } catch (\Throwable $th) {
        return [];
    }
    }
}
