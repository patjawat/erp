<?php

namespace app\modules\am\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Reference list: วิธีได้มา (acquisition method).
 * Backed by table `categorise` filtered by name='method_get'.
 */
class ListMethodget extends \yii\db\ActiveRecord
{
    const NAME = 'method_get';

    public static function tableName()
    {
        return 'categorise';
    }

    public static function find()
    {
        return parent::find()->andWhere(['categorise.name' => self::NAME]);
    }

    public function init()
    {
        parent::init();
        if ($this->getIsNewRecord()) {
            $this->name = self::NAME;
        }
    }

    public function rules()
    {
        return [
            [['code', 'title'], 'required'],
            [['title', 'description'], 'string'],
            [['code', 'sort'], 'string', 'max' => 255],
            [['active'], 'integer'],
            [['active'], 'default', 'value' => 1],
            [['code'], 'validateCodeUnique'],
            [['name'], 'safe'],
        ];
    }

    public function validateCodeUnique($attribute, $params)
    {
        $query = self::find()->andWhere(['code' => $this->$attribute]);
        if (!$this->getIsNewRecord()) {
            $query->andWhere(['<>', 'id', $this->id]);
        }
        if ($query->exists()) {
            $this->addError($attribute, 'รหัสนี้ถูกใช้แล้ว');
        }
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'รหัส',
            'title' => 'ชื่อวิธีได้มา',
            'description' => 'รายละเอียดเพิ่มเติม',
            'sort' => 'ลำดับ',
            'active' => 'สถานะ',
        ];
    }

    public static function dropdownList(): array
    {
        return ArrayHelper::map(
            self::find()
                ->andWhere(['active' => 1])
                ->orderBy(['CAST(sort AS UNSIGNED)' => SORT_ASC, 'title' => SORT_ASC])
                ->all(),
            'code',
            'title'
        );
    }
}
