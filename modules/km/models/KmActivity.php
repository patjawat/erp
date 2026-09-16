<?php

namespace app\modules\km\models;

use app\modules\hr\models\Organization;

/**
 * กิจกรรม KM — แกนกลางของ evidence hub
 *
 * @property int $id
 * @property string $title
 * @property int|null $category_id
 * @property int $fiscal_year  ปีงบประมาณ (พ.ศ.)
 * @property string|null $activity_date
 * @property string|null $start_time
 * @property string|null $end_time
 * @property string|null $location
 * @property int|null $owner_unit_id  หน่วยงานเจ้าภาพ (tree.id)
 * @property string|null $summary
 * @property string|null $objective
 * @property string|null $detail
 * @property int|null $cover_photo_id
 * @property string $status  draft | published
 * @property string $ref
 */
class KmActivity extends KmActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'ฉบับร่าง',
            self::STATUS_PUBLISHED => 'เผยแพร่แล้ว',
        ];
    }

    public static function tableName(): string
    {
        return '{{%km_activity}}';
    }

    public function rules(): array
    {
        return [
            [['title', 'fiscal_year'], 'required'],
            [['title'], 'string', 'max' => 500],
            [['location'], 'string', 'max' => 255],
            [['summary', 'objective', 'detail'], 'string'],
            [['category_id', 'fiscal_year', 'owner_unit_id', 'cover_photo_id'], 'integer'],
            [['activity_date', 'start_time', 'end_time'], 'safe'],
            [['status'], 'in', 'range' => array_keys(self::statusLabels())],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['category_id'], 'exist', 'targetClass' => KmCategory::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title' => 'ชื่อกิจกรรม',
            'category_id' => 'หมวดหมู่',
            'fiscal_year' => 'ปีงบประมาณ',
            'activity_date' => 'วันที่จัด',
            'start_time' => 'เวลาเริ่ม',
            'end_time' => 'เวลาสิ้นสุด',
            'location' => 'สถานที่',
            'owner_unit_id' => 'หน่วยงานเจ้าภาพ',
            'summary' => 'สรุปย่อ',
            'objective' => 'วัตถุประสงค์',
            'detail' => 'รายละเอียด/ถอดบทเรียน',
            'status' => 'สถานะ',
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(KmCategory::class, ['id' => 'category_id']);
    }

    public function getOwnerUnit()
    {
        return $this->hasOne(Organization::class, ['id' => 'owner_unit_id']);
    }

    public function getPhotos()
    {
        return $this->hasMany(KmActivityPhoto::class, ['activity_id' => 'id'])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getCoverPhoto()
    {
        return $this->hasOne(KmActivityPhoto::class, ['id' => 'cover_photo_id']);
    }

    public function getLinks()
    {
        return $this->hasMany(KmActivityLink::class, ['activity_id' => 'id']);
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
