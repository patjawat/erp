<?php

namespace app\models;

use app\components\CategoriseHelper;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\hr\models\Employees;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "categorise".
 *
 * @property int $id
 * @property int|null $category_id
 * @property string|null $code รหัส
 * @property string $name ชนิดข้อมูล
 * @property string|null $title ชื่อ
 * @property string|null $description รายละเอียดเพิ่มเติม
 * @property string|null $data_json
 * @property int|null $active
 */
class Categorise extends \yii\db\ActiveRecord
{
    public $position_group;
    public $position_type;
    public $total;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'categorise';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active'], 'integer'],
            [['name'], 'required'],
            [['category_id', 'data_json', 'position_group', 'position_type', 'qty','total'], 'safe'],
            [['code', 'name', 'title', 'description'], 'string', 'max' => 255],
            [['code'], 'validateCode'],
            // [['code'], 'exist', 'skipOnError' => true, 'targetClass' => Categorise::className(), 'targetAttribute' => ['medication_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category ID',
            'code' => 'รหัส',
            'name' => 'ชนิดข้อมูล',
            'title' => 'ชื่อ',
            'description' => 'รายละเอียดเพิ่มเติม',
            'data_json' => 'Data Json',
            'active' => 'Active',
            'position_group' => 'กลุ่มงาน',
            'position_type' => 'ประเภทบุคลากร',
        ];
    }

    public function validateCode()
    {
        $data = self::find()->where(['name' => $this->name, 'code' => $this->code])->all();
        if (count($data) > 1) {
            $this->addError('code', 'รหัสซ้ำ');
        }
        // if(isset($this->code)){
        // }
    }

    public function Upload($ref, $name)
    {
        return FileManagerHelper::FileUpload($ref, $name);
    }

    public function getEmpPosition()
    {
        return $this->hasMany(Employees::className(), ['id' => 'emp_id']);
    }


    // แสดงบุคลากรที่อยู่ในกลุ่ม
    public function EmpOnWorkGroup($groupId)
    {
        $sql = "SELECT e.id,wg.code,wg.name,wg.title,dep.title,department FROM `employees` e
            LEFT JOIN categorise dep ON dep.code = e.department
            LEFT JOIN categorise wg ON wg.code = dep.code
            WHERE dep.name = 'department' AND wg.name = 'workgroup'
            AND wg.code = :id;";

        return Yii::$app
            ->db
            ->createCommand($sql)
            ->bindParam(':id', $groupId)
            ->queryAll();
    }

    // ผู็นำทีม
    public function getLeaderFormWorkGroup()
    {
        $leader = isset($this->data_json['leader']) ? $this->data_json['leader'] : null;
        if ($leader) {
            return Employees::findOne($leader);
        } else {
            return null;
        }
        return $this->id;
    }

    // public function listPositionGroup()
    // {
    //     return ArrayHelper::map(self::find()->where(['name' => 'position_group'])->all(), 'code', function($model){
    //         return $model->title.' (ประเภท | '.$model->positionType->title.')';
    //     });
    // }

    public function ListPositionType()
    {
        return CategoriseHelper::PositionType();
    }

    // ระดับของข้าราชการ
    public function ListPositionLevel()
    {
        return CategoriseHelper::PositionLevel();
    }

    // กลุ่มงาน
    public function ListPositionGroup()
    {
        return CategoriseHelper::PositionGroup();
    }

    // Relation ประเภท/กลุ่มงาน

    public function getPositionType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'position_type']);
    }

    public function getPositionGroup()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'position_group']);
    }

    // เอาไว้ join table
    public function getJoinPositionGroup()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])->andOnCondition(['position_group.name' => 'position_group']);
    }

    public function getPositionNames()
    {
        return $this->hasMany(Categorise::class, ['code' => 'category_id'])->andOnCondition(['name' => 'position_name']);
    }
}
