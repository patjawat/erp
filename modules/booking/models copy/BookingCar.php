<?php

namespace app\modules\booking\models;

use Yii;
use yii\db\Expression;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use app\modules\dms\models\DocumentTags;
use app\modules\booking\models\BookingCarItems;

/**
 * This is the model class for table "booking_cars".
 *
 * @property int $id
 * @property string|null $ref
 * @property int|null $thai_year ปีงบประมาณ
 * @property string|null $booking_type ประเภทของรถ general หรือ ambulance
 * @property int|null $document_id ตามหนังสือ
 * @property string|null $urgent ตามหนังสือ
 * @property string|null $location สถานที่ไป
 * @property string|null $data_json ยานพาหนะ
 * @property string|null $status ความเห็น Y ผ่าน N ไม่ผ่าน
 * @property string|null $date_start เริ่มวันที่
 * @property string|null $time_start เริ่มเวลา
 * @property string|null $date_end ถึงวันที่
 * @property string|null $time_end ถึงเวลา
 * @property string|null $driver_id พนักงานขับ
 * @property string|null $leader_id หัวหน้างานรับรอง
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property string|null $deleted_at วันที่ลบ
 * @property int|null $deleted_by ผู้ลบ
 */
class BookingCar extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'booking_cars';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['booking_type','date_start','time_start','date_end','time_end','leader_id','reason','location','license_plate','urgent'], 'required'],
            [['thai_year', 'document_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['data_json', 'date_start', 'date_end', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['ref', 'booking_type', 'urgent','location', 'status', 'time_start', 'time_end', 'driver_id', 'leader_id'], 'string', 'max' => 255],
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
            'thai_year' => 'ปีงบประมาณ',
            'booking_type' => 'ประเภทของรถ general หรือ ambulance',
            'document_id' => 'ตามหนังสือ',
            'reason' => 'เหตุผลขอใช้รถ',
            'urgent' => 'ความเร่งด่วน',
            'location' => 'สถานที่ไป',
            'data_json' => 'ยานพาหนะ',
            'status' => 'ความเห็น Y ผ่าน N ไม่ผ่าน',
            'date_start' => 'เริ่มวันที่',
            'time_start' => 'เริ่มเวลา',
            'date_end' => 'ถึงวันที่',
            'time_end' => 'ถึงเวลา',
            'driver_id' => 'พนักงานขับ',
            'leader_id' => 'หัวหน้างานรับรอง',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'created_by' => 'ผู้สร้าง',
            'updated_by' => 'ผู้แก้ไข',
            'deleted_at' => 'วันที่ลบ',
            'deleted_by' => 'ผู้ลบ',
        ];
    }


    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => ['updated_at'],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

        // section Relationships
        public function getCar()
        {
            return $this->hasOne(BookingCarItems::class, ['license_plate' => 'license_plate']);
        }
        
    
        // แสดงหน่วยงานภานนอก
        public function ListOrg()
        {
            $model = Categorise::find()
                ->where(['name' => 'document_org'])
                ->asArray()
                ->all();
            return ArrayHelper::map($model, 'code', 'title');
        }
        
    public function listDocument()
    {
        $me = UserHelper::GetEmployee();
        $document = DocumentTags::find()->where(['tag_id' => 1,'name' => 'comment'])->all();
        return ArrayHelper::map($document, 'id',function($model){
            return $model->document->topic;
        });
    }

    //แสดงรายการทะยานพาหนะ
    public function ListCarItems()
    {
            $items = BookingCarItems::find()->all();
            return ArrayHelper::map($items, 'license_plate','license_plate');
    }
}
