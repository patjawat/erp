<?php

namespace app\modules\attendance\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * บริเวณที่อนุญาตให้ลงเวลา (จุดลงเวลา).
 *
 * @property int $id
 * @property string $name
 * @property string|null $lat
 * @property string|null $lng
 * @property int $radius_m
 * @property string|null $qr_token
 * @property int $active
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class CheckinLocation extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'checkin_location';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
            BlameableBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['lat', 'lng'], 'number'],
            [['radius_m', 'active', 'created_by', 'updated_by'], 'integer'],
            [['radius_m'], 'default', 'value' => 0],
            [['active'], 'default', 'value' => 1],
            [['name', 'qr_token'], 'string', 'max' => 255],
            [['qr_token'], 'unique'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ชื่อจุด/บริเวณ',
            'lat' => 'Latitude',
            'lng' => 'Longitude',
            'radius_m' => 'รัศมี (เมตร)',
            'qr_token' => 'ค่า QR',
            'active' => 'ใช้งาน',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
            'created_by' => 'สร้างโดย',
            'updated_by' => 'แก้ไขโดย',
        ];
    }

    /**
     * ตรวจว่าพิกัด (lat, lng) อยู่ในบริเวณนี้หรือไม่ (ถ้า radius_m > 0).
     * ใช้ Haversine ประมาณ (ระยะตรงเป็นเมตร).
     */
    public function isPointInside($lat, $lng)
    {
        if (empty($this->lat) || empty($this->lng) || $this->radius_m <= 0) {
            return true;
        }
        $meters = self::haversineDistance((float)$this->lat, (float)$this->lng, (float)$lat, (float)$lng);
        return $meters <= (float)$this->radius_m;
    }

    /**
     * ระยะทางโดยประมาณเป็นเมตร (Haversine).
     */
    public static function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000; // รัศมีโลก เมตร
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    /**
     * หา location ที่ตรงกับ qr_token.
     */
    public static function findByQrToken($token)
    {
        return static::findOne(['qr_token' => $token, 'active' => 1]);
    }

    /**
     * หา location ที่พิกัดอยู่ภายใน (location แรกที่ตรง).
     */
    public static function findLocationAt($lat, $lng)
    {
        foreach (static::find()->where(['active' => 1])->andWhere(['>', 'radius_m', 0])->all() as $loc) {
            if ($loc->isPointInside($lat, $lng)) {
                return $loc;
            }
        }
        return null;
    }
}
