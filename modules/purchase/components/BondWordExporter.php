<?php

namespace app\modules\purchase\components;

use Yii;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use app\components\AppHelper;
use app\modules\purchase\models\Bond;

/**
 * ส่งออกทะเบียนคุมหลักประกันเป็นไฟล์ Word (.docx)
 *
 * ทะเบียนนี้ใช้แสดงต่อผู้ตรวจสอบว่าหลักประกันที่หน่วยงานถืออยู่มีอะไรบ้าง ใบไหน
 * หมดอายุแล้ว และใบไหนคืนไปแล้วเมื่อไร ตัวเลขทุกคอลัมน์จึงต้องมาจากฐานข้อมูลตรง ๆ
 * ไม่มีการคำนวณเพิ่มระหว่างพิมพ์
 *
 * ยอดรวมท้ายตารางนับเฉพาะใบที่ยังเดินอยู่ (ยังไม่วาง/วางแล้ว) เพราะใบที่คืนหรือยึด
 * ไปแล้วไม่ใช่เงินที่หน่วยงานถืออยู่ ณ วันที่พิมพ์ — โปรแกรมต้นแบบรวมทุกใบเข้าด้วยกัน
 * ทำให้ยอด "รวมหลักประกันทั้งสิ้น" สูงกว่าของจริง
 */
class BondWordExporter
{
    /** ฟอนต์ราชการตามระเบียบงานสารบรรณ */
    const FONT = 'TH SarabunPSK';
    const FONT_SIZE = 16;

    /**
     * @param Bond[] $models
     */
    public static function sendRegister(array $models, int $thaiYear)
    {
        return self::sendFile(
            self::buildRegister($models, $thaiYear),
            'ทะเบียนคุมหลักประกัน_' . $thaiYear . '.docx'
        );
    }

    /** @param Bond[] $models */
    public static function buildRegister(array $models, int $thaiYear): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName(self::FONT);
        $phpWord->setDefaultFontSize(self::FONT_SIZE);

        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'marginTop' => 850,
            'marginBottom' => 850,
            'marginLeft' => 850,
            'marginRight' => 850,
        ]);

        $section->addText(self::agencyName(), ['bold' => true, 'size' => 18], ['alignment' => Jc::CENTER]);
        $section->addText(
            'ทะเบียนคุมหลักประกัน ปีงบประมาณ ' . $thaiYear,
            ['bold' => true, 'size' => 18],
            ['alignment' => Jc::CENTER]
        );
        $section->addText(
            'พิมพ์เมื่อ ' . self::thaiDate(date('Y-m-d')),
            ['size' => 14],
            ['alignment' => Jc::END, 'spaceAfter' => 120]
        );

        $columns = [
            ['ที่', 4],
            ['เลขที่', 8],
            ['รายการ/โครงการ', 20],
            ['ผู้วางหลักประกัน', 14],
            ['ประเภท', 10],
            ['รูปแบบ', 11],
            ['วงเงิน', 9],
            ['วางเมื่อ', 8],
            ['สิ้นอายุ', 8],
            ['สถานะ', 8],
        ];

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 12,
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);

        $table->addRow();
        foreach ($columns as [$label, $width]) {
            $table->addCell($width * 50, ['bgColor' => 'D9E1F2'])
                ->addText($label, ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER]);
        }

        $active = 0.0;
        foreach ($models as $i => $bond) {
            if (in_array($bond->status, [Bond::STATUS_PENDING, Bond::STATUS_ACTIVE], true)) {
                $active += (float) $bond->amount;
            }

            // ใบที่หมดอายุแล้วแต่ยังไม่ปิดเรื่อง ต้องเห็นได้ทันทีบนกระดาษ ไม่ใช่เห็นแต่บนจอ
            $expired = $bond->isExpired();
            $rowStyle = $expired ? ['bgColor' => 'FDE8E6'] : [];

            $table->addRow();
            $table->addCell($columns[0][1] * 50, $rowStyle)
                ->addText((string) ($i + 1), ['size' => 14], ['alignment' => Jc::CENTER]);
            $table->addCell($columns[1][1] * 50, $rowStyle)
                ->addText($bond->doc_no ?: '-', ['size' => 14]);
            $table->addCell($columns[2][1] * 50, $rowStyle)
                ->addText($bond->title . self::sourceSuffix($bond), ['size' => 14]);
            $table->addCell($columns[3][1] * 50, $rowStyle)
                ->addText($bond->partyName(), ['size' => 14]);
            $table->addCell($columns[4][1] * 50, $rowStyle)
                ->addText($bond->typeName(), ['size' => 14]);
            $table->addCell($columns[5][1] * 50, $rowStyle)
                ->addText($bond->bondFormName() . self::docRefSuffix($bond), ['size' => 14]);
            $table->addCell($columns[6][1] * 50, $rowStyle)
                ->addText(number_format((float) $bond->amount, 2), ['size' => 14], ['alignment' => Jc::END]);
            $table->addCell($columns[7][1] * 50, $rowStyle)
                ->addText(self::thaiDate($bond->place_date), ['size' => 14], ['alignment' => Jc::CENTER]);
            $table->addCell($columns[8][1] * 50, $rowStyle)
                ->addText(
                    self::thaiDate($bond->expiry_date) . ($expired ? ' (หมดอายุ)' : ''),
                    ['size' => 14],
                    ['alignment' => Jc::CENTER]
                );
            $table->addCell($columns[9][1] * 50, $rowStyle)
                ->addText(self::statusText($bond), ['size' => 14], ['alignment' => Jc::CENTER]);
        }

        if (!$models) {
            $table->addRow();
            $table->addCell(100 * 50, ['gridSpan' => count($columns)])
                ->addText('ไม่มีหลักประกันในปีงบประมาณนี้', ['size' => 14], ['alignment' => Jc::CENTER]);
        }

        $table->addRow();
        $table->addCell(67 * 50, ['gridSpan' => 6, 'bgColor' => 'D9E1F2'])
            ->addText(
                'รวม ' . count($models) . ' ฉบับ · ยอดหลักประกันที่ยังอยู่ในความดูแล',
                ['bold' => true, 'size' => 14],
                ['alignment' => Jc::END]
            );
        $table->addCell($columns[6][1] * 50, ['bgColor' => 'D9E1F2'])
            ->addText(number_format($active, 2), ['bold' => true, 'size' => 14], ['alignment' => Jc::END]);
        $table->addCell(24 * 50, ['gridSpan' => 3, 'bgColor' => 'D9E1F2'])->addText('');

        $section->addTextBreak(1);
        $section->addText(
            'หมายเหตุ: ยอดรวมนับเฉพาะหลักประกันที่ยังไม่คืนและไม่ถูกยึด '
                . 'ใบที่ได้รับการยกเว้นไม่มีวงเงินจึงไม่ถูกนับ',
            ['size' => 14]
        );

        return $phpWord;
    }

    // ── ตัวช่วย ──────────────────────────────────────────────────────────────

    private static function sendFile(PhpWord $phpWord, string $filename)
    {
        $tmp = Yii::getAlias('@runtime') . '/' . uniqid('bond_', true) . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($tmp);

        return Yii::$app->response->sendFile($tmp, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tmp) {
            @unlink($tmp);
        });
    }

    /**
     * ชื่อหน่วยงาน — อ่านจากทะเบียนตั้งค่าโดยตรง ไม่ผ่าน SiteHelper::getInfo()
     * เพราะ getInfo() ต้องมี alias @web ซึ่งไม่มีตอนรันจาก console (งานทดสอบ/batch)
     */
    private static function agencyName(): string
    {
        try {
            $site = \app\models\Categorise::findOne(['name' => 'site']);
            return (string) ($site->data_json['company_name'] ?? '');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private static function sourceSuffix(Bond $bond): string
    {
        $label = $bond->sourceLabel();
        return $label === '—' ? '' : ' (' . $label . ')';
    }

    private static function docRefSuffix(Bond $bond): string
    {
        return $bond->doc_ref ? ' เลขที่ ' . $bond->doc_ref : '';
    }

    /** สถานะพร้อมวันที่คืน เพราะทะเบียนที่พิมพ์ออกไปต้องตอบได้ว่าคืนเมื่อไร */
    private static function statusText(Bond $bond): string
    {
        $text = $bond->statusName();
        if ($bond->return_date) {
            $text .= ' ' . self::thaiDate($bond->return_date);
        }
        return $text;
    }

    private static function thaiDate(?string $date): string
    {
        if (empty($date)) {
            return '-';
        }
        try {
            return (string) AppHelper::convertToThai($date);
        } catch (\Throwable $e) {
            return (string) $date;
        }
    }
}
