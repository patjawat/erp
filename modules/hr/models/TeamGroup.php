<?php

namespace app\modules\hr\models;

use Htm;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\modules\hr\models\Employees;

/**
 * This is the model class for table "team_group".
 *
 * @property int $id
 * @property string|null $title ชื่อกลุ่ม
 * @property string|null $description รายละเอียด
 * @property string|null $created_at วันที่สร้าง
 * @property string|null $updated_at วันที่แก้ไข
 * @property int|null $created_by ผู้สร้าง
 * @property int|null $updated_by ผู้แก้ไข
 * @property int|null $status สถานะ
 * @property int|null $deleted_at ลบ
 * @property int|null $deleted_by ลบโดย
 */
class TeamGroup extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public $q;
    public $thai_year;
    public static function tableName()
    {
        return 'team_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['title', 'required'],
            [['title', 'description', 'created_at', 'updated_at', 'created_by', 'updated_by', 'status', 'deleted_at', 'deleted_by'], 'default', 'value' => null],
            [['description'], 'string'],
            [['created_at', 'updated_at','q','thai_year'], 'safe'],
            [['created_by', 'updated_by', 'status', 'deleted_at', 'deleted_by'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'ชื่อกลุ่ม/ทีมประสาน',
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

    public function getTeamGroupDetail()
    {
        return $this->hasMany(TeamGroupDetail::className(), ['category_id' => 'id'])->andOnCondition(['name' => 'appointment']);
    }

    public function teamGroupDetail()
    {
        try {
            return TeamGroupDetail::find()->where(['name' => 'appointment', 'category_id' => $this->id])->orderBy(['thai_year' => SORT_DESC])->one();
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function ListThaiYear()
    {
        $model = TeamGroupDetail::find()
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


    


}
