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
 * @property int $fiscal_year
 * @property string $kind
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
