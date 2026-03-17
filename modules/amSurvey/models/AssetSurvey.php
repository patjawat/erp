<?php

namespace app\modules\amSurvey\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use app\modules\hr\models\Organization;

/**
 * Survey campaign (การสำรวจครุภัณฑ์ประจำปี).
 *
 * @property int $id
 * @property string $survey_name
 * @property int $survey_year
 * @property int|null $department_id
 * @property string|null $started_at
 * @property string|null $finished_at
 * @property int|null $created_by
 * @property string|null $created_at
 * @property string $status draft|active|closed
 */
class AssetSurvey extends \yii\db\ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';

    public static function tableName()
    {
        return '{{%am_asset_surveys}}';
    }

    public function rules()
    {
        return [
            [['survey_name', 'survey_year'], 'required'],
            [['survey_year', 'department_id', 'created_by'], 'integer'],
            [['started_at', 'finished_at', 'created_at'], 'safe'],
            [['survey_name'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 50],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_ACTIVE, self::STATUS_CLOSED]],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'survey_name' => 'ชื่อโครงการสำรวจ',
            'survey_year' => 'ปีสำรวจ (พ.ศ.)',
            'department_id' => 'หน่วยงานรับผิดชอบ',
            'started_at' => 'วันเริ่ม',
            'finished_at' => 'วันสิ้นสุด',
            'created_by' => 'ผู้สร้าง',
            'created_at' => 'สร้างเมื่อ',
            'status' => 'สถานะ',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => null,
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => null,
            ],
        ];
    }

    public function getDepartment()
    {
        return $this->hasOne(Organization::class, ['id' => 'department_id']);
    }

    public function getSurveyItems()
    {
        return $this->hasMany(AssetSurveyItem::class, ['survey_id' => 'id']);
    }

    /** Count items by found_status for this survey. */
    public function getCountByStatus()
    {
        return AssetSurveyItem::find()
            ->where(['survey_id' => $this->id])
            ->select(['found_status', 'COUNT(*) as cnt'])
            ->groupBy('found_status')
            ->indexBy('found_status')
            ->column();
    }
}
