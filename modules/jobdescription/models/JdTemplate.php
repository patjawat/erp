<?php

namespace app\modules\jobdescription\models;

use app\models\Categorise;
use Yii;
use yii\db\ActiveRecord;

/**
 * Template คำอธิบายงาน (JD) ต่อตำแหน่งงาน
 *
 * @property int $id
 * @property string $name ชื่อ template
 * @property string $position_code รหัสตำแหน่ง (categorise position_name)
 * @property int $is_active
 * @property string $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property JdTemplateSection[] $sections
 * @property Categorise $positionName
 */
class JdTemplate extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%jd_template}}';
    }

    public function rules()
    {
        return [
            [['name', 'position_code'], 'required'],
            [['is_active', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['position_code'], 'string', 'max' => 64],
            [['is_active'], 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ชื่อ template',
            'position_code' => 'ตำแหน่งงาน',
            'is_active' => 'ใช้งาน',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
        ];
    }

    public function getSections()
    {
        return $this->hasMany(JdTemplateSection::class, ['template_id' => 'id'])->orderBy(['sort_order' => SORT_ASC]);
    }

    /** ชื่อตำแหน่งจาก Categorise */
    public function getPositionName()
    {
        return $this->hasOne(Categorise::class, ['code' => 'position_code'])->andOnCondition(['name' => 'position_name']);
    }

    public function getPositionTitle()
    {
        $m = $this->positionName;
        return $m ? $m->title : $this->position_code;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $now = date('Y-m-d H:i:s');
            if ($insert) {
                $this->created_at = $now;
                if (Yii::$app->has('user') && !Yii::$app->user->isGuest) {
                    $this->created_by = (int) Yii::$app->user->id;
                }
            }
            $this->updated_at = $now;
            if (Yii::$app->has('user') && !Yii::$app->user->isGuest) {
                $this->updated_by = (int) Yii::$app->user->id;
            }
            return true;
        }
        return false;
    }
}
