<?php

namespace app\modules\hr\models;

use yii\db\ActiveQuery;

/**
 * สายงานมาตรฐานตามเกณฑ์ สป.สธ. — ใช้ร่วมกันทุกโรงพยาบาล
 *
 * ชื่อสายงานที่นี่เป็นชื่อตามเอกสาร ไม่ใช่ชื่อตำแหน่งของโรงพยาบาลใด ๆ
 * การเชื่อมกับตำแหน่งจริงอยู่ที่ WorkforcePositionMap
 *
 * @property int $id
 * @property string $edition
 * @property string $org_type
 * @property int|null $seq
 * @property string $category
 * @property string $title
 * @property string $method
 * @property array|null $formula_json
 * @property string|null $note
 * @property int $sort
 * @property int $active
 */
class WorkforceStandardLine extends \yii\db\ActiveRecord
{
    public const EDITION_CURRENT = 'MOPH-2565-2569';

    public const CATEGORY_LABELS = [
        'professional' => 'สายวิชาชีพ',
        'support' => 'สายสนับสนุน',
        'service' => 'บริการพื้นฐาน',
    ];

    public const METHOD_LABELS = [
        'fte' => 'คำนวณ FTE ตามภาระงาน',
        'service_based' => 'Service based ตามเครื่องมือ',
        'population_based' => 'ตามสัดส่วนประชากร',
        'ratio' => 'สูตรอัตราส่วน',
        'manual' => 'กำหนดเอง',
    ];

    /** วิธีที่ระบบคำนวณให้ได้เอง ถ้ามีตัวขับเคลื่อนครบ */
    public const AUTO_METHODS = ['ratio', 'population_based'];

    public static function tableName()
    {
        return '{{%workforce_standard_line}}';
    }

    public function rules()
    {
        return [
            [['edition', 'category', 'title', 'method'], 'required'],
            [['seq', 'sort', 'active'], 'integer'],
            [['note'], 'string'],
            [['formula_json'], 'safe'],
            [['edition', 'org_type', 'category', 'method'], 'string', 'max' => 30],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'seq' => 'ลำดับตามเกณฑ์',
            'category' => 'ประเภทสายงาน',
            'title' => 'สายงาน',
            'method' => 'วิธีกำหนดกรอบ',
            'note' => 'หมายเหตุจากเกณฑ์',
        ];
    }

    public function getRules0(): ActiveQuery
    {
        return $this->hasMany(WorkforceStandardRule::class, ['line_id' => 'id']);
    }

    public function getPositionMaps(): ActiveQuery
    {
        return $this->hasMany(WorkforcePositionMap::class, ['line_id' => 'id']);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORY_LABELS[$this->category] ?? $this->category;
    }

    public function methodLabel(): string
    {
        return self::METHOD_LABELS[$this->method] ?? $this->method;
    }

    public function isAutoCalculated(): bool
    {
        return in_array($this->method, self::AUTO_METHODS, true);
    }

    /**
     * ชื่อสายงานอาจรวมหลายตำแหน่งด้วย "/" — แยกออกมาเพื่อใช้จับคู่
     *
     * @return string[]
     */
    public function titleAliases(): array
    {
        $parts = preg_split('/\s*\/\s*/u', (string) $this->title) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn ($p) => $p !== ''));
    }

    /**
     * สายงานของเกณฑ์รุ่นที่ใช้อยู่ เรียงตามลำดับในเอกสาร
     *
     * @return static[]
     */
    public static function currentEdition(string $orgType = 'HOSPITAL'): array
    {
        return static::find()
            ->where(['edition' => self::EDITION_CURRENT, 'org_type' => $orgType, 'active' => 1])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
    }
}
