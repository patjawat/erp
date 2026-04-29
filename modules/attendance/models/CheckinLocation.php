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
            [['active', 'created_by', 'updated_by'], 'integer'],
            [['radius_m'], 'integer', 'min' => 0, 'max' => 100000],
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
            'radius_m' => 'รัศมีอนุญาตลงเวลา (เมตร)',
            'qr_token' => 'ค่า QR',
            'active' => 'ใช้งาน',
            'created_at' => 'สร้างเมื่อ',
            'updated_at' => 'แก้ไขเมื่อ',
            'created_by' => 'สร้างโดย',
            'updated_by' => 'แก้ไขโดย',
        ];
    }

    /**
     * มีพิกัดศูนย์กลางครบสำหรับคำนวณระยะหรือไม่
     */
    public function hasValidCenter()
    {
        return $this->lat !== null && $this->lat !== '' && $this->lng !== null && $this->lng !== '';
    }

    /**
     * จุดนี้บังคับตรวจ GPS ตามรัศมีหรือไม่ (รัศมี > 0 และมี lat/lng)
     */
    public function hasGeofence()
    {
        return $this->hasValidCenter() && (int)$this->radius_m > 0;
    }

    /**
     * ตรวจว่าพิกัด (lat, lng) อยู่ในบริเวณนี้หรือไม่ (ถ้า radius_m > 0).
     * ใช้ Haversine ประมาณ (ระยะตรงเป็นเมตร).
     */
    public function isPointInside($lat, $lng)
    {
        if (!$this->hasValidCenter() || $this->radius_m <= 0) {
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
            if (!$loc->hasValidCenter()) {
                continue;
            }
            if ($loc->isPointInside($lat, $lng)) {
                return $loc;
            }
        }
        return null;
    }

    /**
     * มีจุดลงเวลาที่เปิดใช้และกำหนดรัศมี GPS อย่างน้อยหนึ่งจุดหรือไม่ (ศูนย์กลางครบ)
     */
    public static function requiresOpenCheckinGeofence()
    {
        return static::findActiveGeofenced() !== [];
    }

    /**
     * จุดที่มีรัศมี + พิกัดศูนย์กลาง (ใช้แสดงผล/คำนวณระยะฝั่ง client)
     *
     * @return static[]
     */
    public static function findActiveGeofenced()
    {
        $list = static::find()->where(['active' => 1])->andWhere(['>', 'radius_m', 0])
            ->andWhere(['not', ['lat' => null]])
            ->andWhere(['not', ['lng' => null]])
            ->orderBy(['name' => SORT_ASC])
            ->all();
        return array_values(array_filter($list, static function ($loc) {
            return $loc->hasValidCenter();
        }));
    }

    /**
     * ข้อมูลจุดที่ใกล้ที่สุดเมื่ออยู่นอกรัศมีทุกจุด (สำหรับข้อความแจ้งผู้ใช้)
     *
     * @return array{location: static, distance_m: float}|null
     */
    public static function nearestGeofenceFrom($lat, $lng)
    {
        $best = null;
        $bestD = null;
        foreach (static::findActiveGeofenced() as $loc) {
            $d = static::haversineDistance((float)$loc->lat, (float)$loc->lng, (float)$lat, (float)$lng);
            if ($bestD === null || $d < $bestD) {
                $bestD = $d;
                $best = $loc;
            }
        }
        if ($best === null) {
            return null;
        }
        return ['location' => $best, 'distance_m' => $bestD];
    }

    /**
     * ตรวจสอบการลงเวลาตามพิกัดและรัศมีที่ตั้งในแต่ละจุด
     *
     * @param float|string|null $lat
     * @param float|string|null $lng
     * @param string|null $qrToken
     * @return array{ok: bool, location: ?static, message: string, meta: array}
     */
    public static function validateClockIn($lat, $lng, $qrToken)
    {
        $qrToken = $qrToken !== null && $qrToken !== '' ? trim((string)$qrToken) : null;

        $parseCoord = static function ($v) {
            if ($v === null || $v === '') {
                return null;
            }
            if (!is_numeric($v)) {
                return null;
            }
            return (float)$v;
        };
        $latF = $parseCoord($lat);
        $lngF = $parseCoord($lng);

        if ($qrToken !== null) {
            $loc = static::findByQrToken($qrToken);
            if (!$loc) {
                return ['ok' => false, 'location' => null, 'message' => 'ไม่พบ QR จุดลงเวลา หรือจุดนี้ปิดใช้งาน', 'meta' => []];
            }
            if ($loc->hasGeofence()) {
                if ($latF === null || $lngF === null) {
                    return ['ok' => false, 'location' => null, 'message' => 'จุดลงเวลานี้กำหนดรัศมี GPS — กรุณาเปิดการเข้าถึงตำแหน่งแล้วลองใหม่', 'meta' => []];
                }
                if (!$loc->isPointInside($latF, $lngF)) {
                    $d = static::haversineDistance((float)$loc->lat, (float)$loc->lng, $latF, $lngF);
                    return [
                        'ok' => false,
                        'location' => null,
                        'message' => sprintf(
                            'อยู่ห่างจากจุด «%s» ประมาณ %d เมตร (รัศมีที่อนุญาต %d เมตร)',
                            $loc->name,
                            (int)round($d),
                            (int)$loc->radius_m
                        ),
                        'meta' => ['distance_m' => $d, 'location_id' => $loc->id],
                    ];
                }
            }
            return ['ok' => true, 'location' => $loc, 'message' => '', 'meta' => []];
        }

        if (!static::requiresOpenCheckinGeofence()) {
            $inside = ($latF !== null && $lngF !== null) ? static::findLocationAt($latF, $lngF) : null;
            return ['ok' => true, 'location' => $inside, 'message' => '', 'meta' => []];
        }

        if ($latF === null || $lngF === null) {
            return [
                'ok' => false,
                'location' => null,
                'message' => 'องค์กรกำหนดบริเวณลงเวลาด้วย GPS — กรุณาเปิดการเข้าถึงตำแหน่งแล้วลองใหม่',
                'meta' => [],
            ];
        }

        $loc = static::findLocationAt($latF, $lngF);
        if (!$loc) {
            $near = static::nearestGeofenceFrom($latF, $lngF);
            $msg = 'ไม่อยู่ในบริเวณที่กำหนดสำหรับลงเวลา';
            if ($near !== null) {
                $msg .= sprintf(
                    ' (ห่างจากจุดใกล้ที่สุด «%s» ~%d เมตร รัศมีอนุญาต %d เมตร)',
                    $near['location']->name,
                    (int)round($near['distance_m']),
                    (int)$near['location']->radius_m
                );
            }
            return ['ok' => false, 'location' => null, 'message' => $msg, 'meta' => $near ?? []];
        }

        return ['ok' => true, 'location' => $loc, 'message' => '', 'meta' => []];
    }
}
