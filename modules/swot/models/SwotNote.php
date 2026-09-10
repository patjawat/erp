<?php

namespace app\modules\swot\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * โพสต์อิทหนึ่งใบในกระดาน SWOT/SOAR
 *
 * @property int $id
 * @property int $board_id
 * @property string $quadrant
 * @property string $content
 * @property string|null $category
 * @property int $weight  1-5
 * @property string $color
 * @property string $priority  high|medium|low
 * @property string|null $author
 * @property int $sort
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property SwotBoard $board
 */
class SwotNote extends ActiveRecord
{
    public const PRIORITIES = ['high', 'medium', 'low'];

    public const COLORS = ['yellow', 'green', 'pink', 'blue', 'purple', 'orange', 'cyan', 'amber'];

    /** ข้อมูลประจำแต่ละช่อง: รหัส/ชื่อ/สี Bootstrap tone/ไอคอน bi */
    public const QUADRANT_INFO = [
        'strengths' => ['code' => 'S', 'title' => 'จุดแข็ง (Strengths)', 'short' => 'จุดแข็ง', 'sub' => 'ปัจจัยภายในเชิงบวก', 'tone' => 'success', 'icon' => 'bi-hand-thumbs-up'],
        'weaknesses' => ['code' => 'W', 'title' => 'จุดอ่อน (Weaknesses)', 'short' => 'จุดอ่อน', 'sub' => 'ปัจจัยภายในเชิงลบ', 'tone' => 'danger', 'icon' => 'bi-hand-thumbs-down'],
        'opportunities' => ['code' => 'O', 'title' => 'โอกาส (Opportunities)', 'short' => 'โอกาส', 'sub' => 'ปัจจัยภายนอกเชิงบวก', 'tone' => 'info', 'icon' => 'bi-arrow-up-right-circle'],
        'threats' => ['code' => 'T', 'title' => 'อุปสรรค (Threats)', 'short' => 'อุปสรรค', 'sub' => 'ปัจจัยภายนอกเชิงลบ', 'tone' => 'warning', 'icon' => 'bi-exclamation-triangle'],
        'aspirations' => ['code' => 'A', 'title' => 'ความปรารถนา (Aspirations)', 'short' => 'ความปรารถนา', 'sub' => 'วิสัยทัศน์และอนาคตที่ต้องการ', 'tone' => 'primary', 'icon' => 'bi-stars'],
        'results' => ['code' => 'R', 'title' => 'ผลลัพธ์ (Results)', 'short' => 'ผลลัพธ์', 'sub' => 'เป้าหมายความสำเร็จที่วัดได้', 'tone' => 'teal', 'icon' => 'bi-graph-up-arrow'],
    ];

    /** สีโพสต์อิท (โทนพาสเทล ตัวอักษรดำ) — bg/border ชื่อ */
    public const COLOR_STYLES = [
        'yellow' => ['bg' => '#FEF9C3', 'border' => '#FDE047', 'name' => 'เหลือง'],
        'green' => ['bg' => '#DCFCE7', 'border' => '#86EFAC', 'name' => 'เขียว'],
        'pink' => ['bg' => '#FCE7F3', 'border' => '#F472B6', 'name' => 'ชมพู'],
        'blue' => ['bg' => '#E0F2FE', 'border' => '#7DD3FC', 'name' => 'ฟ้า'],
        'purple' => ['bg' => '#F3E8FF', 'border' => '#C084FC', 'name' => 'ม่วง'],
        'orange' => ['bg' => '#FFEDD5', 'border' => '#FB923C', 'name' => 'ส้ม'],
        'cyan' => ['bg' => '#CFFAFE', 'border' => '#67E8F9', 'name' => 'ฟ้าไอซ์'],
        'amber' => ['bg' => '#FEF3C7', 'border' => '#FCD34D', 'name' => 'ครีมทอง'],
    ];

    public static function tableName(): string
    {
        return '{{%swot_note}}';
    }

    public function behaviors(): array
    {
        return [
            ['class' => TimestampBehavior::class, 'value' => static fn () => date('Y-m-d H:i:s')],
            ['class' => BlameableBehavior::class],
        ];
    }

    public function rules(): array
    {
        return [
            [['board_id', 'quadrant', 'content'], 'required'],
            [['board_id', 'weight', 'sort'], 'integer'],
            [['content'], 'string'],
            ['weight', 'in', 'range' => [1, 2, 3, 4, 5]],
            ['weight', 'default', 'value' => 3],
            ['priority', 'in', 'range' => self::PRIORITIES],
            ['priority', 'default', 'value' => 'medium'],
            ['color', 'in', 'range' => self::COLORS],
            ['color', 'default', 'value' => 'yellow'],
            [['category', 'author'], 'string', 'max' => 255],
            [['quadrant'], 'string', 'max' => 20],
            [['color', 'priority'], 'string', 'max' => 20],
        ];
    }

    public function getBoard()
    {
        return $this->hasOne(SwotBoard::class, ['id' => 'board_id']);
    }

    public function colorStyle(): array
    {
        return self::COLOR_STYLES[$this->color] ?? self::COLOR_STYLES['yellow'];
    }

    /** ข้อมูลย่อสำหรับส่งกลับเป็น JSON ให้ฝั่ง JS */
    public function toArray(array $fields = [], array $expand = [], $recursive = true): array
    {
        return [
            'id' => (int) $this->id,
            'board_id' => (int) $this->board_id,
            'quadrant' => $this->quadrant,
            'content' => (string) $this->content,
            'category' => $this->category,
            'weight' => (int) $this->weight,
            'color' => $this->color,
            'priority' => $this->priority,
            'author' => $this->author,
            'sort' => (int) $this->sort,
        ];
    }
}
