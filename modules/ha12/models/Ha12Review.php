<?php

namespace app\modules\ha12\models;

use app\modules\hr\models\Organization;

/**
 * รายการทบทวน (สถานะล่าสุด) — เจ้าของ = หน่วยงาน (owner_unit_id = tree.id)
 *
 * รายละเอียดตามแบบฟอร์มของกิจกรรมเก็บใน payload_json (คีย์ตาม Ha12ReviewForm)
 * เข้าถึงผ่าน virtual property $fields (array) — decode/encode อัตโนมัติ
 *
 * @property int $id
 * @property int $activity_id
 * @property int|null $owner_unit_id
 * @property int $fiscal_year
 * @property string|null $review_date
 * @property string|null $title
 * @property string|null $reviewer_name
 * @property string|null $payload_json
 * @property int $schema_version
 * @property int $revision
 * @property int $deleted
 * @property string $ref
 */
class Ha12Review extends Ha12ActiveRecord
{
    /** @var array<string,mixed> รายละเอียดฟอร์ม (map key=>value) — ไม่ใช่คอลัมน์ */
    public $fields = [];

    public static function tableName(): string
    {
        return '{{%ha12_review}}';
    }

    public function rules(): array
    {
        return [
            [['activity_id', 'fiscal_year'], 'required'],
            [['activity_id', 'fiscal_year', 'owner_unit_id', 'schema_version', 'revision', 'deleted'], 'integer'],
            [['review_date'], 'required'],
            [['review_date'], 'date', 'format' => 'php:Y-m-d'],
            [['reviewer_name'], 'string', 'max' => 255],
            [['title'], 'string', 'max' => 500],
            [['fields'], 'safe'],
            [['activity_id'], 'exist', 'targetClass' => Ha12Activity::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'activity_id' => 'กิจกรรม',
            'owner_unit_id' => 'หน่วยงาน',
            'fiscal_year' => 'ปีงบประมาณ',
            'review_date' => 'วันที่ทบทวน',
            'reviewer_name' => 'ผู้ทบทวน',
        ];
    }

    public function afterFind(): void
    {
        parent::afterFind();
        $decoded = $this->payload_json ? json_decode($this->payload_json, true) : [];
        $this->fields = is_array($decoded) ? $decoded : [];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        // encode รายละเอียดฟอร์ม → payload_json (เฉพาะฟิลด์ที่นิยามไว้ของกิจกรรมนี้)
        $activity = $this->activity;
        $no = $activity ? (int) $activity->no : 0;
        $clean = [];
        foreach (Ha12ReviewForm::fieldsFor($no) as $f) {
            $key = $f['key'];
            $val = $this->fields[$key] ?? null;
            if (is_string($val)) {
                $val = trim($val);
            }
            if ($val !== null && $val !== '') {
                $clean[$key] = $val;
            }
        }
        $this->payload_json = $clean ? json_encode($clean, JSON_UNESCAPED_UNICODE) : null;
        $this->schema_version = Ha12ReviewForm::SCHEMA_VERSION;

        // derive title จากฟิลด์หลัก
        $pk = Ha12ReviewForm::primaryKey($no);
        $primaryVal = $pk ? ($clean[$pk] ?? null) : null;
        $this->title = $primaryVal ? mb_substr((string) $primaryVal, 0, 500) : null;

        return true;
    }

    public function getActivity()
    {
        return $this->hasOne(Ha12Activity::class, ['id' => 'activity_id']);
    }

    public function getOwnerUnit()
    {
        return $this->hasOne(Organization::class, ['id' => 'owner_unit_id']);
    }

    public function getFollowups()
    {
        return $this->hasMany(Ha12ReviewFollowup::class, ['review_id' => 'id'])
            ->orderBy(['followup_date' => SORT_ASC, 'id' => SORT_ASC]);
    }

    public function getVersions()
    {
        return $this->hasMany(Ha12ReviewVersion::class, ['review_id' => 'id'])
            ->orderBy(['revision' => SORT_DESC]);
    }

    /** ค่าฟิลด์เดียว (จาก payload) */
    public function field(string $key)
    {
        return $this->fields[$key] ?? null;
    }
}
