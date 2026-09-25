<?php

namespace app\modules\plan\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * รายการแนบของแผนประจำปี
 *  - kind = reserve    : แนบ 1 เงินกองทุนรอการจัดสรร (4)
 *  - kind = commitment : แนบ 2 ภาระผูกพันของหน่วยงาน (5)
 *
 * @property int $id
 * บรรทัดตายตัวตามแบบฟอร์มเขต (cfomoph.com/monthlycash เมนู 1.5) อยู่ใน lines(); แถวที่ line_code = null
 * คือรายการเดิมที่กรอกแบบอิสระก่อนปรับแบบฟอร์ม — ยังนับรวมยอด (4)/(5) จนกว่าผู้ใช้จะย้ายเข้าบรรทัด
 *
 * @property int $fiscal_year
 * @property string $kind
 * @property string|null $line_code
 * @property string $name
 * @property float $amount
 * @property string|null $note
 * @property int $sort_order
 */
class PlanAnnualAttachment extends ActiveRecord
{
    public const KIND_RESERVE = 'reserve';       // แนบ 1 กองทุนรอจัดสรร -> (4)
    public const KIND_COMMITMENT = 'commitment'; // แนบ 2 ภาระผูกพัน -> (5)

    public static function tableName(): string
    {
        return '{{%plan_annual_attachment}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['fiscal_year', 'kind', 'name'], 'required'],
            [['fiscal_year', 'sort_order'], 'integer'],
            [['amount'], 'number'],
            [['kind'], 'in', 'range' => [self::KIND_RESERVE, self::KIND_COMMITMENT]],
            [['name', 'note'], 'string', 'max' => 255],
            [['line_code'], 'string', 'max' => 10],
        ];
    }

    /**
     * บรรทัดแบบฟอร์มเขต: [code => [title, section]] — section = หัวข้อย่อยที่แสดงเหนือบรรทัด (null = ไม่มี)
     * ชื่อต้องสะกดตรงแม่แบบเขต เพราะระบบเขตนำเข้า Excel โดยจับคู่ด้วยชื่อรายการ
     */
    public static function lines(string $kind): array
    {
        if ($kind === self::KIND_RESERVE) {
            $s = 'เงินรอการจัดสรร';
            return [
                'r1' => ['เงินกองทุนหลักประกันสุขภาพถ้วนหน้ารอการจัดสรร', $s],
                'r2' => ['เงินกองทุนประกันสังคมรอการจัดสรร', $s],
                'r3' => ['เงินกองทุนแรงงานต่างด้าวรอการจัดสรร', $s],
            ];
        }
        $p = 'ค่าใช้จ่ายบุคลากรค้างจ่าย';
        $o = 'ค่าใช้จ่ายจากการดำเนินงานค้างจ่าย';
        return [
            'c1' => ['ค่าจ้างลูกจ้างชั่วคราว / พนักงานกระทรวง', $p],
            'c2' => ['ค่าล่วงเวลางานบริการ / งานสนับสนุน', $p],
            'c3' => ['ค่าตอบแทนการปฏิบัติงานเวรผลัดบ่ายหรือผลัดดึกของพยาบาล', $p],
            'c4' => ['ค่าตอบแทนเงินเพิ่มพิเศษไม่ทำเวชปฏิบัติส่วนตัว หรือปฏิบัติงาน รพ.เอกชน', $p],
            'c5' => ['ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.11)', $p],
            'c6' => ['ค่าตอบแทนตามผลการปฏิบัติงาน (ฉ.12)', $p],
            'c7' => ['เงินเพิ่ม (พ.ต.ส)', $p],
            'c8' => ['ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานในคลินิกพิเศษเฉพาะทางนอกเวลาราชการ (SMC)', $p],
            'c9' => ['ค่าตอบแทนอื่น', $p],
            'c10' => ['เงินค่าใช้จ่ายบุคลากรอื่น', $p],
            'c11' => ['ค่ายา', $o],
            'c12' => ['ค่าวัสดุทางการแพทย์ / วัสดุวิทยาศาสตร์การแพทย์ / วัสดุทันตกรรม', $o],
            'c13' => ['ค่าวัสดุอื่น', $o],
            'c14' => ['ค่าสาธารณูปโภค', $o],
            'c15' => ['ค่าใช้สอย', $o],
            'c16' => ['ค่าครุภัณฑ์ค้างจ่าย', null],
            'c17' => ['ค่าที่ดินและสิ่งก่อสร้างค้างจ่าย', null],
            'c18' => ['รายจ่ายอื่นค้างจ่าย', null],
            'c19' => ['เงินประกัน เงินมัดจำ เงินรับฝากอื่น', null],
        ];
    }

    /** ผลรวมยอดตามชนิด/ปี — คืน map [fiscal_year => sum] */
    public static function sumByYear(string $kind, array $years): array
    {
        $out = array_fill_keys($years, 0.0);
        if (!$years) {
            return $out;
        }
        $rows = static::find()->select(['fiscal_year', 's' => 'SUM(amount)'])
            ->where(['kind' => $kind, 'fiscal_year' => $years])
            ->groupBy('fiscal_year')->asArray()->all();
        foreach ($rows as $r) {
            $out[(int) $r['fiscal_year']] = (float) $r['s'];
        }
        return $out;
    }
}
