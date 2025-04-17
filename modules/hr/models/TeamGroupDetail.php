<?php

namespace app\modules\hr\models;

use Htm;
use yii\helpers\Html;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Employees;
use app\modules\hr\models\TeamGroup;
use app\modules\dms\models\Documents;

/**
 * This is the model class for table "team_group_detail".
 *
 * @property int $id
 * @property string $name ชื่อ
 * @property int $thai_year ปี
 * @property int $category_id รหัสกลุ่ม
 * @property int $document_id รหัสเอกสาร
 * @property string|null $description รายละเอียด
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property int|null $status สถานะ
 * @property int|null $deleted_at ลบ
 * @property int|null $deleted_by ลบโดย
 */
class TeamGroupDetail extends \yii\db\ActiveRecord
{


    public $items;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'team_group_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'created_at', 'updated_at', 'created_by', 'updated_by', 'status', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['name', 'thai_year', 'category_id', 'document_id','title'], 'required'],
            [['thai_year', 'category_id', 'document_id', 'created_by', 'updated_by', 'status', 'deleted_at', 'deleted_by','emp_id'], 'integer'],
            [['description'], 'string'],
            [['created_at', 'updated_at','data_json'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'title' => 'ชื่อรายการ',
            'thai_year' => 'Thai Year',
            'category_id' => 'Team Group ID',
            'document_id' => 'Document ID',
            'description' => 'รายละเอียดเพิ่มเติม',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'status' => 'Status',
            'deleted_at' => 'Deleted At',
            'deleted_by' => 'Deleted By',
        ];
    }

    public function getEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }
    
    public function getTeamGroup()
    {
        return $this->hasOne(TeamGroup::class, ['id' => 'category_id']);
    }
    
    public function getAppointment()
    {
        return $this->hasOne(TeamGroupDetail::class, ['id' => 'category_id'])->andOnCondition(['name' => 'appointment']);
    }
    
    //  แสดงหนังสือคำสั่ง
    public function listDocument()
    {
     $documents = Documents::find()->where(['document_group' => 'appointment','thai_year' => (date('Y') + 543)])->all();
     return ArrayHelper::map($documents, 'id', 'topic');
    }


    //  ตำแหน่งของกรรมการ
    public function listCommitteePosition()
    {
        $model = Categorise::find()->where(['name' => 'committee'])->all();
        return ArrayHelper::map($model, 'code', 'title');
        
    }
    public function listCommittee()
    {
        try{
            return  self::find()->where(['name' => 'committee','category_id' => $this->id])->all();
        }catch(\Exception $e){
            return [];
        }
        
    }


    //  ภาพทีมคณะกรรมการ
    public function stackComittee()
    {
        // try {
        $data = '';
        $data .= '<div class="avatar-stack">';
        foreach (self::listCommittee() as $key => $item) {
            // $emp = Employees::findOne(['id' => $item->emp_id]);
            $emp = $item->employee;
            $data .= Html::a(
                Html::img('@web/img/placeholder-img.jpg', ['class' => 'avatar-sm rounded-circle shadow lazyload blur-up',
                    'data' => [
                        'expand' => '-20',
                        'sizes' => 'auto',
                        'src' => $emp->showAvatar()
                    ]]),
                ['/purchase/order-item/update', 'id' => $item->id, 'name' => 'appointment', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการตรวจรับ'],
                [
                    'class' => 'open-modal',
                    'data' => [
                        'size' => 'modal-md',
                        'bs-trigger' => 'hover focus',
                        'bs-toggle' => 'popover',
                        'bs-placement' => 'top',
                        'bs-title' => $item->data_json['committee_name'],
                        'bs-html' => 'true',
                        'bs-content' => $emp->fullname . '<br>' . $emp->positionName()
                    ]
                ]
            );
        }
        $data .= '</div>';
        return $data;
        // } catch (\Throwable $th) {
        // }
    }
    
    
}
