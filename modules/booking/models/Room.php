<?php

namespace app\modules\booking\models;

use Yii;
use app\models\Uploads;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\modules\booking\models\Room;
use app\modules\hr\models\Employees;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * This is the model class for table "categorise".
 *
 * @property int $id
 * @property string|null $ref
 * @property string|null $group_id กลุ่ม
 * @property string|null $category_id
 * @property string|null $code รหัส
 * @property string|null $emp_id พนักงาน
 * @property string $name ชนิดข้อมูล
 * @property string|null $title ชื่อ
 * @property int|null $qty
 * @property string|null $description รายละเอียดเพิ่มเติม
 * @property string|null $data_json
 * @property string|null $ma_items รายการบำรุงรักษา
 * @property int|null $active
 */
class Room extends \yii\db\ActiveRecord
{
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
            [['name', 'code', 'title'], 'required'],
            [['qty', 'active'], 'integer'],
            [['data_json', 'ma_items', 'code'], 'safe'],
            [['ref', 'group_id', 'category_id', 'emp_id', 'name', 'title', 'description'], 'string', 'max' => 255],
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
            'group_id' => 'กลุ่ม',
            'category_id' => 'Category ID',
            'code' => 'รหัส',
            'emp_id' => 'พนักงาน',
            'name' => 'ชนิดข้อมูล',
            'title' => 'ชื่อ',
            'qty' => 'Qty',
            'description' => 'รายละเอียดเพิ่มเติม',
            'data_json' => 'Data Json',
            'ma_items' => 'รายการบำรุงรักษา',
            'active' => 'Active',
        ];
    }

    public function Upload()
    {
        $ref = $this->ref;
        $name = 'meeting_room';
        return FileManagerHelper::FileUpload($ref, $name);
    }

    // แสดงภาพของห้อง
    public function showImg($class = null)
    {
        try {
            $model = Uploads::find()->where(['ref' => $this->ref, 'name' => 'meeting_room'])->one();

            // return $this->ref;
            // return FileManagerHelper::getImg($model->id);
            if ($model) {
                // return Html::img('@web/avatar/' . $this->avatar, ['class' => 'view-avatar']);
                return FileManagerHelper::getImg($model->id);
            } else {
                return \Yii::getAlias('@web') . '/img/placeholder-img.jpg';
            }
        } catch (\Throwable $th) {
            // throw $th;
            return \Yii::getAlias('@web') . '/img/placeholder-img.jpg';
        }
    }

    // ผู้ดูแลห้องประชุม
    public function showOwner($msg = '')
    {
        try {
            $emp = Employees::findOne($this->data_json['owner']);
            return [
                'emp' => $emp,
                'avatar' => $emp->getAvatar(false, 'ผู้ดูแลรับผิดชอบ  '.$msg) ?? null,
                'line_id' => $emp->user->line_id ?? null
            ];
        } catch (\Throwable $th) {
            return [
                'emp' => '',
                'avatar' => null,
                'line_id' => null
            ];
        }
    }

    // แสดงรายการอุปกรณ์
    public function ListAccessory()
    {
        $model = Categorise::find()
            ->where(['name' => 'room_accessory', 'category_id' => $this->code])
            ->asArray()
            ->all();
        return ArrayHelper::map($model, 'title', 'title');
    }

    public function checkRoom($date)
    {
        return Booking::find()
            ->where(['name' => 'meeting', 'date_start' => $date, 'room_id' => $this->code])
            ->andWhere(['<>', 'status', 'cancel'])
            ->one();
    }


}