<?php

namespace app\modules\am\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Reference list: ผู้ขาย/ผู้จำหน่าย/ผู้บริจาค (vendor / distributor / donor).
 * Backed by table `categorise` filtered by name='vendor'.
 */
class ListVendor extends \yii\db\ActiveRecord
{
    const NAME = 'vendor';

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
            if ($this->active === null) {
                $this->active = 1;
            }
        }
    }

    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title', 'description'], 'string'],
            [['code'], 'string', 'max' => 255],
            [['active'], 'integer'],
            [['active'], 'default', 'value' => 1],
            [['code'], 'default', 'value' => null],
            [['code'], 'validateCodeUnique'],
            [['name'], 'safe'],
        ];
    }

    public function validateCodeUnique($attribute, $params)
    {
        if ($this->$attribute === null || $this->$attribute === '') {
            return;
        }
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
            'title' => 'ชื่อผู้ขาย/ผู้จำหน่าย/ผู้บริจาค',
            'description' => 'รายละเอียดเพิ่มเติม',
            'active' => 'สถานะ',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // auto-generate next code if empty
        if ($insert && ($this->code === null || $this->code === '')) {
            $this->code = self::nextCode();
        }
        return true;
    }

    public static function nextCode(): string
    {
        $max = (int) self::find()
            ->select(new \yii\db\Expression('MAX(CAST(code AS UNSIGNED))'))
            ->scalar();
        return str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT);
    }

    public static function dropdownList(): array
    {
        return ArrayHelper::map(
            self::find()
                ->andWhere(['active' => 1])
                ->orderBy(['title' => SORT_ASC])
                ->all(),
            'code',
            'title'
        );
    }
}
