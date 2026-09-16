<?php

namespace app\modules\flowchart\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * ขั้นตอนหนึ่งของผังกระบวนการ
 *
 * @property int $id
 * @property int $flowchart_id
 * @property int $seq
 * @property string $type
 * @property string|null $title
 * @property string|null $actor
 * @property string|null $related_doc
 * @property string|null $duration
 * @property string|null $note
 * @property int|null $branch_yes
 * @property int|null $branch_no
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Flowchart $flowchart
 */
class FlowchartStep extends ActiveRecord
{
    public const TYPE_START = 'start';
    public const TYPE_PROCESS = 'process';
    public const TYPE_DECISION = 'decision';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_SUBPROCESS = 'subprocess';
    public const TYPE_END = 'end';

    public const TYPES = [
        self::TYPE_START,
        self::TYPE_PROCESS,
        self::TYPE_DECISION,
        self::TYPE_DOCUMENT,
        self::TYPE_SUBPROCESS,
        self::TYPE_END,
    ];

    /**
     * ข้อมูลประจำแต่ละประเภทขั้นตอน
     *  - label : ชื่อประเภท (ไทย)
     *  - tone  : โทน Bootstrap สำหรับ badge/seg-control
     *  - icon  : Bootstrap Icons
     *  - open/close : วงเล็บครอบ label ในไวยากรณ์ Mermaid (กำหนดรูปทรงกล่อง)
     *  - shape : ชื่อรูปทรงเชิงสัญลักษณ์ สำหรับวาด SVG เล็กในคอลัมน์ "สัญลักษณ์" ของตาราง
     */
    public const TYPE_INFO = [
        self::TYPE_START => ['label' => 'เริ่ม', 'tone' => 'success', 'icon' => 'bi-play-circle', 'open' => '([', 'close' => '])', 'shape' => 'stadium'],
        self::TYPE_PROCESS => ['label' => 'ดำเนินการ', 'tone' => 'primary', 'icon' => 'bi-square', 'open' => '[', 'close' => ']', 'shape' => 'rect'],
        self::TYPE_DECISION => ['label' => 'ตัดสินใจ', 'tone' => 'warning', 'icon' => 'bi-diamond', 'open' => '{', 'close' => '}', 'shape' => 'diamond'],
        self::TYPE_DOCUMENT => ['label' => 'เอกสาร', 'tone' => 'info', 'icon' => 'bi-file-earmark-text', 'open' => '[/', 'close' => '/]', 'shape' => 'doc'],
        self::TYPE_SUBPROCESS => ['label' => 'กระบวนการย่อย', 'tone' => 'secondary', 'icon' => 'bi-box', 'open' => '[[', 'close' => ']]', 'shape' => 'subroutine'],
        self::TYPE_END => ['label' => 'จบ', 'tone' => 'danger', 'icon' => 'bi-stop-circle', 'open' => '([', 'close' => '])', 'shape' => 'stadium'],
    ];

    public static function tableName(): string
    {
        return '{{%flowchart_step}}';
    }

    public function behaviors(): array
    {
        return [
            ['class' => TimestampBehavior::class, 'value' => static fn () => date('Y-m-d H:i:s')],
        ];
    }

    public function rules(): array
    {
        return [
            [['flowchart_id'], 'required'],
            [['flowchart_id', 'seq', 'branch_yes', 'branch_no'], 'integer'],
            [['title', 'note'], 'string'],
            ['type', 'in', 'range' => self::TYPES],
            ['type', 'default', 'value' => self::TYPE_PROCESS],
            [['actor', 'related_doc'], 'string', 'max' => 255],
            [['duration'], 'string', 'max' => 120],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'seq' => 'ลำดับ',
            'type' => 'ประเภท',
            'title' => 'ขั้นตอน',
            'actor' => 'ผู้รับผิดชอบ',
            'related_doc' => 'เอกสารที่เกี่ยวข้อง',
            'duration' => 'ระยะเวลา',
            'note' => 'หมายเหตุ',
        ];
    }

    public function getFlowchart()
    {
        return $this->hasOne(Flowchart::class, ['id' => 'flowchart_id']);
    }

    public function info(): array
    {
        return self::TYPE_INFO[$this->type] ?? self::TYPE_INFO[self::TYPE_PROCESS];
    }

    public function typeLabel(): string
    {
        return $this->info()['label'];
    }

    public function isDecision(): bool
    {
        return $this->type === self::TYPE_DECISION;
    }

    public function isTerminal(): bool
    {
        return $this->type === self::TYPE_END;
    }

    /** ข้อความในกล่อง (กันว่าง) */
    public function displayTitle(): string
    {
        $t = trim((string) $this->title);
        return $t !== '' ? $t : $this->typeLabel();
    }

    /** node key ที่ใช้อ้างในไวยากรณ์ Mermaid */
    public function nodeKey(): string
    {
        return 'n' . (int) $this->id;
    }

    /** ข้อมูลย่อสำหรับส่งกลับเป็น JSON ให้ฝั่ง JS (เติมข้อมูลในฟอร์ม/พรีวิว) */
    public function toArray(array $fields = [], array $expand = [], $recursive = true): array
    {
        return [
            'id' => (int) $this->id,
            'seq' => (int) $this->seq,
            'type' => $this->type,
            'title' => (string) $this->title,
            'actor' => (string) $this->actor,
            'related_doc' => (string) $this->related_doc,
            'duration' => (string) $this->duration,
            'note' => (string) $this->note,
            'branch_yes' => $this->branch_yes !== null ? (int) $this->branch_yes : null,
            'branch_no' => $this->branch_no !== null ? (int) $this->branch_no : null,
        ];
    }
}
