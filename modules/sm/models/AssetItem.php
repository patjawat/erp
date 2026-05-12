<?php

namespace app\modules\sm\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;
use app\models\Categorise;
use yii\helpers\Json;

/**
 * This is the model class for table "categorise".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $category_id
 * @property string|null $code รหัส
 * @property string|null $emp_id พนักงาน
 * @property string $name ชนิดข้อมูล
 * @property string|null $title ชื่อ
 * @property string|null $description รายละเอียดเพิ่มเติม
 * @property string|null $data_json
 * @property int|null $active
 */
class AssetItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $fsn_auto; //กำหนดการให้หมายเลขอัตโนมัติถ้า true;
    public $asset_type_id;
    public $ma;
    public $q;
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
            [['name'], 'required'],
            [['data_json', 'fsn_auto', 'ma_items', 'group_id','q'], 'safe'],
            [['active'], 'integer'],
            [['ref', 'category_id', 'code', 'emp_id', 'name', 'title', 'description'], 'string', 'max' => 255],
            [
                ['code'],
                'unique',
                'targetAttribute' => ['category_id', 'code'],
                'filter' => ['group_id' => 'EQUIP'],
                'message' => 'รหัสซ้ำ',
            ],
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
            'category_id' => 'Category ID',
            'code' => 'Code',
            'emp_id' => 'Emp ID',
            'name' => 'Name',
            'title' => 'Title',
            'description' => 'Description',
            'data_json' => 'Data Json',
            'active' => 'Active',
            'fsn_auto' => 'fsn_auto'
        ];
    }

    public function afterFind()
    {
        // if (is_string($this->data_json) && $this->data_json !== '') {
        //     $decoded = json_decode($this->data_json, true);
        //     $this->data_json = is_array($decoded) ? $decoded : [];
        // } elseif (!is_array($this->data_json)) {
        //     $this->data_json = [];
        // }
        // $this->normalizeJsonFields();
        parent::afterFind();
        // $this->ma = Json::encode($this->data_json['ma_items']);
    }

    // protected function jsonData(): array
    // {
    //     if (is_array($this->data_json)) {
    //         return $this->data_json;
    //     }

    //     if (is_string($this->data_json) && $this->data_json !== '') {
    //         $decoded = json_decode($this->data_json, true);
    //         return is_array($decoded) ? $decoded : [];
    //     }

    //     return [];
    // }

    // protected function normalizeJsonScalarValue($value)
    // {
    //     if (!is_array($value)) {
    //         return $value;
    //     }

    //     foreach ($value as $item) {
    //         if (is_array($item)) {
    //             continue;
    //         }

    //         if ($item !== null && $item !== '') {
    //             return $item;
    //         }
    //     }

    //     return '';
    // }

    // protected function normalizeJsonFields(): void
    // {
    //     if (!is_array($this->data_json)) {
    //         return;
    //     }

    //     $dataJson = $this->data_json;
    //     foreach (['price', 'fsn', 'unit', 'depreciation', 'service_life'] as $field) {
    //         if (array_key_exists($field, $dataJson)) {
    //             $dataJson[$field] = $this->normalizeJsonScalarValue($dataJson[$field]);
    //         }
    //     }

    //     $this->data_json = $dataJson;
    // }

    public function getAssetType()
    {
        return $this->hasOne(Categorise::class, ['code' => 'category_id'])
            ->andOnCondition(['name' => 'asset_type']);
    }

    public function getAssetTypeTitle(): string
    {
        return $this->assetType?->title
            ?? ($this->jsonData()['asset_type']['title'] ?? '-');
    }

    public function getAssetGroupTitle(): string
    {
        $json = $this->jsonData();
        if (!empty($json['asset_group']['title'])) {
            return $json['asset_group']['title'];
        }

        $groupCode = $this->assetType?->category_id;
        if ($groupCode) {
            $group = Categorise::find()
                ->select(['title'])
                ->where(['name' => 'asset_group', 'code' => $groupCode])
                ->asArray()
                ->one();

            if (!empty($group['title'])) {
                return $group['title'];
            }
        }

        return '-';
    }

    public function getUnitName(): string
    {
        return $this->jsonData()['unit'] ?? '-';
    }


    public function beforeSave($insert)
    {

        // try {
        //     $ma_items = [
        //         'ma_items' => $this->ma
        //     ];
        //     $this->data_json = ArrayHelper::merge($this->data_json, $ma_items);
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }

        // $this->receive_date = AppHelper::DateToDb($this->receive_date);
        if ($this->name == 'asset_type') {
            $group = self::find()->select(['name', 'title', 'code'])->where(['code' => $this->category_id, 'name' => 'asset_group'])->one();
            $array2 = [
                'asset_group' => $group
            ];

            $this->data_json = ArrayHelper::merge($this->data_json, $array2);

            if ($this->fsn_auto == "1") {
                $this->code = \mdm\autonumber\AutoNumber::generate('G' . $this->category_id . 'AT' . '?');
            }
        }

        if ($this->name == 'asset_item') {
            $type = self::find()->select(['name', 'title', 'code', 'category_id'])->where(['code' => $this->category_id, 'name' => 'asset_type'])->one();
            $groupType = self::find()->select(['name', 'title', 'code'])->where(['code' => $type->category_id, 'name' => 'asset_group'])->one();
            $arrayType = [
                'asset_group' => $groupType,
                'asset_type' => $type,
            ];
            $this->data_json = ArrayHelper::merge($this->data_json, $arrayType);

            //ถ้าเป็นวัสดุ
            if ($this->fsn_auto == "1" && $type->category_id == 1) {
                $this->code = \mdm\autonumber\AutoNumber::generate('AI-' . '?');
            }

            // ถ้าเป็นครุภัณฑ์  
            if ($this->fsn_auto == "1" && $type->category_id == 2) {
                $this->code = \mdm\autonumber\AutoNumber::generate($this->category_id . '-' . '????');
            }
        }

        // $this->normalizeJsonFields();
        // if (is_array($this->data_json)) {
        //     $this->data_json = Json::encode($this->data_json);
        // }

        return parent::beforeSave($insert);
    }


    public function listFsnName()
    {
        return ArrayHelper::map(self::find()->all(), 'code', 'title');
    }

    public function listUnit()
    {
        return ArrayHelper::map(self::find()->where(['name' => 'unit'])->all(), 'title', 'title');
    }



    public function FsnGroup()
    {
        return ArrayHelper::map(self::find()->where(['name' => 'asset_group'])->all(), 'code', 'title');
    }

    public function AssetType()
    {
        return ArrayHelper::map(self::find()->where(['name' => 'asset_type', 'category_id' => 3])->all(), 'code', 'title');
    }


    public function ShowImg()
    {
        $model = Uploads::find()->where(['ref' => $this->ref, 'name' => 'asset'])->one();
        if ($model) {
            return FileManagerHelper::getImg($model->id);
        } else {
            return Yii::getAlias('@web') . '/img/placeholder-img.jpg';
        }
    }

    //นับจำนวนประเภทที่อยู่ในกลุ่ม
    public function CountTypeOnGroup()
    {
        return  Categorise::find()->where(['category_id' => $this->code, 'name' => 'asset_type'])->count();
    }

    //นับจำนวนรายการที่อยู่ในประเภท
    public function CountItemOnType()
    {
        $id = $this->code;
        $sql = "SELECT count(c.id) FROM categorise c
            LEFT JOIN categorise t ON t.code = c.category_id
             WHERE c.name = 'asset_item'
             AND t.category_id = :id";
        $query = Yii::$app->db->createCommand($sql)
            ->bindParam(':id', $id)
            ->queryScalar();
        return $query;
    }

    public function GetAssetTypeID()
    {
        return $asset_type_id;
    }

    public function listAssetCategory()
    {
        return ArrayHelper::map(Categorise::find()->where(['name' => 'asset_type', 'group_id' => 'EQUIP'])->all(), 'code', 'title');
    }


    public function NextCode()
    {
        $prefix = trim((string) $this->category_id);

        $lastNumber = self::find()
            ->where([
                'group_id' => 'EQUIP',
                'name' => 'asset_item',
            ])
            ->andWhere(['like', 'code', $prefix . '-%', false])
            ->select(new \yii\db\Expression("MAX(CAST(SUBSTRING_INDEX(code, '-', -1) AS UNSIGNED))"))
            ->scalar();

        $number = ((int) $lastNumber) + 1;

        return $prefix . '-' . $number;
    }
}

// แก้ไข priceArray
// $sql = "UPDATE categorise
// SET data_json = JSON_SET(
//     data_json,
//     '$.price',
//     JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.price[0]'))
// )
// WHERE name = 'asset_item'
//   AND JSON_VALID(data_json)
//   AND JSON_TYPE(JSON_EXTRACT(data_json, '$.price')) = 'ARRAY';
// ";
