<?php

namespace app\modules\roster\helpers;

use app\modules\roster\models\Item;
use app\modules\roster\models\Period;
use Yii;

/**
 * คัดลอกตารางเวรจากเดือนก่อนหน้า
 *
 * จับคู่ตาม "วันในสัปดาห์" ไม่ใช่เลขวันที่ เพราะรอบหมุนเวรของพยาบาลเดินเป็นสัปดาห์
 * ถ้าคัดลอกด้วยเลขวันที่ตรงๆ คนที่เคยอยู่ดึกทุกวันจันทร์จะกลายเป็นวันพุธ รอบเวรเพี้ยนทั้งเดือน
 *
 * ระยะเลื่อนคือ floor(ระยะห่างวันแรกของสองเดือน / 7) × 7 ซึ่งได้ 28 วันเสมอ
 * (เดือนมี 28–31 วัน) — ตรงกับรอบหมุนเวร 4 สัปดาห์พอดี
 *
 * ผลข้างเคียงที่ตั้งใจ: เดือนปลายทางที่ยาวกว่า 28 วัน จะเหลือ 1–3 วันท้ายเดือนว่าง
 * ให้หัวหน้าเติมเอง — ดีกว่าเดาแล้ววนข้อมูลกลับมาซ้ำ
 */
class RosterCopier
{
    /** ระยะเลื่อนเป็นวัน จากเดือนต้นทางไปเดือนปลายทาง */
    public static function offsetDays(Period $source, Period $target): int
    {
        $gap = (int) round((strtotime($target->firstDate()) - strtotime($source->firstDate())) / 86400);
        return intdiv($gap, 7) * 7;
    }

    /**
     * หาแผ่นเดือนก่อนหน้าที่ "ชื่อเดียวกัน" ในหน่วยงานเดียวกัน
     *
     * เดือนหนึ่งมีได้หลายแผ่น (ตารางหลัก / Refer / On call / บ่ายดึก) ถ้าจับคู่ผิดแผ่น
     * เวร Refer ของเดือนก่อนจะถูกคัดลอกมาลงแผ่นบ่ายดึก ซึ่งผิดทั้งคนและชนิดเวร
     * จึงยึดชื่อแผ่นเป็นตัวจับคู่ และถ้าไม่เจอชื่อตรงกันก็ไม่คัดลอกให้เดา
     */
    public static function previousPeriod(Period $target): ?Period
    {
        $month = (int) $target->month - 1;
        $year = (int) $target->year_ce;
        if ($month < 1) {
            $month = 12;
            $year--;
        }
        return Period::findOne([
            'unit_id' => $target->unit_id,
            'month' => $month,
            'year_ce' => $year,
            'title' => $target->title,
            'deleted_at' => null,
        ]);
    }

    /**
     * @return array{copied:int, skipped:int, uncoveredDays:int[], source:Period}
     * @throws \RuntimeException เมื่อไม่มีเดือนก่อนหน้า หรือเดือนนั้นยังไม่มีการจัดเวร
     */
    public static function copy(Period $target): array
    {
        $source = static::previousPeriod($target);
        if (!$source) {
            throw new \RuntimeException('ไม่พบแผ่น "' . $target->title . '" ของเดือนก่อนหน้า — คัดลอกได้เฉพาะแผ่นที่ชื่อตรงกัน');
        }

        $sourceItems = Item::find()
            ->where(['period_id' => $source->id])
            ->andWhere(['<>', 'status', Item::STATUS_CANCELLED])
            ->all();
        if (empty($sourceItems)) {
            throw new \RuntimeException('เดือนก่อนหน้ายังไม่มีการจัดเวร');
        }

        $offset = static::offsetDays($source, $target);
        $firstDate = $target->firstDate();
        $lastDate = $target->lastDate();

        $existingKeys = [];
        foreach (Item::find()->where(['period_id' => $target->id])->asArray()->all() as $row) {
            $existingKeys[$row['work_date'] . '|' . $row['emp_id'] . '|' . $row['shift_type_id']] = true;
        }

        $copied = 0;
        $skipped = 0;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($sourceItems as $item) {
                $newDate = date('Y-m-d', strtotime($item->work_date) + $offset * 86400);
                if ($newDate < $firstDate || $newDate > $lastDate) {
                    continue;
                }
                $key = $newDate . '|' . $item->emp_id . '|' . $item->shift_type_id;
                if (isset($existingKeys[$key])) {
                    $skipped++;
                    continue;
                }
                $copy = new Item([
                    'period_id' => $target->id,
                    'emp_id' => $item->emp_id,
                    'work_date' => $newDate,
                    'shift_type_id' => $item->shift_type_id,
                    'is_extra' => $item->is_extra,
                ]);
                if ($copy->save()) {
                    $existingKeys[$key] = true;
                    $copied++;
                } else {
                    $skipped++;
                }
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // เดือนต้นทางเลื่อนมาแล้วครอบเดือนปลายทางได้ถึงวันไหน — วันหลังจากนั้นไม่มีต้นทางให้คัดลอก
        // (เลื่อน 28 วันเสมอ เดือนที่ยาว 29–31 วันจึงเหลือท้ายเดือน 1–3 วันที่หัวหน้าต้องจัดเอง)
        $reach = strtotime($source->lastDate()) + $offset * 86400;
        $uncovered = [];
        if ($reach < strtotime($lastDate)) {
            $from = (int) date('j', $reach) + 1;
            for ($d = $from; $d <= $target->daysInMonth(); $d++) {
                $uncovered[] = $d;
            }
        }

        return ['copied' => $copied, 'skipped' => $skipped, 'uncoveredDays' => $uncovered, 'source' => $source];
    }
}
