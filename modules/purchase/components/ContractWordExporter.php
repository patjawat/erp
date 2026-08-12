<?php

namespace app\modules\purchase\components;

use Yii;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use app\components\AppHelper;
use app\modules\purchase\models\Contract;

/**
 * ส่งออกเอกสารงานบริหารสัญญาเป็นไฟล์ Word (.docx)
 *
 * เอกสารที่ออกได้
 *   contract  ร่างสัญญา/ข้อตกลง
 *   fine      หนังสือแจ้งค่าปรับ
 *   register  ทะเบียนคุมสัญญาทั้งปี
 *
 * ข้อจำกัดที่ต้องรู้ก่อนใช้งาน
 * "ร่างสัญญา" ที่ออกจากที่นี่เป็นแบบตารางสรุปสาระสำคัญ ไม่ใช่แบบสัญญามาตรฐานของ
 * คณะกรรมการว่าด้วยการพัสดุซึ่งเป็นความเรียงหลายสิบข้อ ใช้เป็นร่างตั้งต้นแล้วนำไป
 * ปรับต่อในโปรแกรมประมวลผลคำได้ หน้าจอที่เรียกใช้ต้องแจ้งผู้ใช้ให้ชัดด้วย
 *
 * ใช้ Html::addHtml() แทน TemplateProcessor ด้วยเหตุผลเดียวกับ TorWordExporter —
 * เนื้อความที่ผู้ใช้จัดรูปแบบเองเก็บเป็น HTML ถ้าใช้ setValue แท็กจะโผล่ในเอกสาร
 */
class ContractWordExporter
{
    /** ฟอนต์ราชการตามระเบียบงานสารบรรณ */
    const FONT = 'TH SarabunPSK';
    const FONT_SIZE = 16;

    const DOC_CONTRACT = 'contract';
    const DOC_FINE = 'fine';
    const DOC_REGISTER = 'register';

    /** ส่งไฟล์ให้ผู้ใช้ดาวน์โหลด */
    public static function send(Contract $model, string $type = self::DOC_CONTRACT)
    {
        $prefix = [
            self::DOC_CONTRACT => 'สัญญา_',
            self::DOC_FINE => 'แจ้งค่าปรับ_',
        ][$type] ?? 'เอกสาร_';

        $phpWord = $type === self::DOC_FINE
            ? self::buildFineNotice($model)
            : self::buildContract($model);

        return self::sendFile($phpWord, $prefix . preg_replace('/[\\\\\/:*?"<>|]/u', '', $model->title) . '.docx');
    }

    /**
     * ทะเบียนคุมสัญญาทั้งปี
     *
     * @param Contract[] $models
     */
    public static function sendRegister(array $models, int $thaiYear)
    {
        return self::sendFile(self::buildRegister($models, $thaiYear), 'ทะเบียนคุมสัญญา_' . $thaiYear . '.docx');
    }

    private static function sendFile(PhpWord $phpWord, string $filename)
    {
        $tmp = Yii::getAlias('@runtime') . '/' . uniqid('contract_', true) . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($tmp);

        return Yii::$app->response->sendFile($tmp, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tmp) {
            @unlink($tmp);
        });
    }

    // ── ร่างสัญญา ────────────────────────────────────────────────────────────

    public static function buildContract(Contract $model): PhpWord
    {
        $phpWord = self::newDocument();
        $section = self::newSection($phpWord);
        $agency = self::agencyName();

        $section->addText($model->typeName(), ['bold' => true, 'size' => 20], ['alignment' => Jc::CENTER]);
        $section->addText(
            'เลขที่ ' . ($model->contract_no ?: $model->doc_no ?: '—'),
            ['bold' => true, 'size' => 17],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 200]
        );

        $section->addText(
            'สัญญาฉบับนี้ทำขึ้น ณ ' . ($agency ?: '[ชื่อส่วนราชการ]')
                . ' เมื่อวันที่ ' . self::thaiDate($model->sign_date),
            [],
            ['alignment' => Jc::BOTH, 'indentation' => ['firstLine' => 720], 'spaceAfter' => 80]
        );
        $section->addText(
            'ระหว่าง ' . ($agency ?: '[ชื่อส่วนราชการ]')
                . ' ซึ่งต่อไปในสัญญานี้เรียกว่า "ผู้ซื้อ/ผู้ว่าจ้าง" ฝ่ายหนึ่ง'
                . ' กับ ' . $model->partyName()
                . ' ซึ่งต่อไปในสัญญานี้เรียกว่า "ผู้ขาย/ผู้รับจ้าง" อีกฝ่ายหนึ่ง'
                . ' คู่สัญญาทั้งสองฝ่ายตกลงทำสัญญากันโดยมีสาระสำคัญดังนี้',
            [],
            ['alignment' => Jc::BOTH, 'indentation' => ['firstLine' => 720], 'spaceAfter' => 160]
        );

        $table = self::table($section);
        $no = 0;
        self::row($table, ++$no . '. วัตถุประสงค์', $model->title);
        self::row($table, ++$no . '. ประเภทสัญญา', $model->typeName());
        self::row($table, ++$no . '. คู่สัญญา', $model->partyName());
        self::row(
            $table,
            ++$no . '. วงเงินตามสัญญา',
            number_format((float) $model->budget, 2) . ' บาท (' . self::bahtText((float) $model->budget) . ')'
        );
        self::row($table, ++$no . '. วันลงนามในสัญญา', self::thaiDate($model->sign_date));
        self::row($table, ++$no . '. วันครบกำหนดส่งมอบ', self::thaiDate($model->end_date));
        self::row(
            $table,
            ++$no . '. ค่าปรับกรณีส่งมอบล่าช้า',
            'ร้อยละ ' . rtrim(rtrim(number_format((float) $model->fine_rate, 4), '0'), '.')
                . ' ของ' . (
                    $model->fine_base === Contract::FINE_BASE_MILESTONE
                    ? 'วงเงินงวดที่ส่งมอบล่าช้า'
                    : 'วงเงินตามสัญญา'
                )
                . ' ต่อวัน นับถัดจากวันครบกำหนดส่งมอบจนถึงวันที่ส่งมอบครบถ้วน'
                . ' ทั้งนี้ค่าปรับรวมต้องไม่เกินวงเงินตามสัญญา'
        );
        self::row($table, ++$no . '. การรับประกัน', self::thaiDate($model->warranty_end) !== '-'
            ? 'ถึงวันที่ ' . self::thaiDate($model->warranty_end)
            : '-');
        if (!empty($model->egp_no)) {
            self::row($table, ++$no . '. เลขที่โครงการ e-GP', $model->egp_no);
        }

        $whtLabel = $model->wht_rate === null
            ? 'ยังไม่ได้ตั้งอัตราภาษีหัก ณ ที่จ่ายในระบบ'
            : 'ร้อยละ ' . rtrim(rtrim(number_format((float) $model->wht_rate, 2), '0'), '.')
                . ' ของฐาน ' . number_format((float) $model->wht_base, 2) . ' บาท'
                . ' เป็นเงิน ' . number_format((float) $model->wht_amount, 2) . ' บาท';
        self::row($table, ++$no . '. ภาษีหัก ณ ที่จ่าย', $whtLabel);

        // ── งวดงาน ───────────────────────────────────────────────────────────
        $milestones = $model->isNewRecord ? [] : $model->milestones;
        if ($milestones) {
            self::heading($section, 'งวดงานและการชำระเงิน');
            $ms = self::table($section);
            $ms->addRow();
            foreach ([['งวดที่', 10], ['รายละเอียด', 46], ['วงเงิน (บาท)', 22], ['กำหนดส่งมอบ', 22]] as [$label, $w]) {
                $ms->addCell($w * 50, ['bgColor' => 'D9E1F2'])
                    ->addText($label, ['bold' => true], ['alignment' => Jc::CENTER]);
            }
            foreach ($milestones as $m) {
                $ms->addRow();
                $ms->addCell(10 * 50)->addText((string) $m->seq, [], ['alignment' => Jc::CENTER]);
                $ms->addCell(46 * 50)->addText($m->detail ?: '-');
                $ms->addCell(22 * 50)->addText(number_format((float) $m->amount, 2), [], ['alignment' => Jc::END]);
                $ms->addCell(22 * 50)->addText(self::thaiDate($m->due_date), [], ['alignment' => Jc::CENTER]);
            }
        }

        // ── เงื่อนไขเพิ่มเติมที่ผู้ใช้พิมพ์เอง ────────────────────────────────
        if (trim(strip_tags((string) $model->extra_term)) !== '') {
            self::heading($section, 'เงื่อนไขเพิ่มเติม');
            self::addHtml($section, $model->extra_term);
        }

        $section->addText(
            'เอกสารฉบับนี้เป็นร่างสรุปสาระสำคัญของสัญญาที่ออกจากระบบ '
                . 'มิใช่แบบสัญญามาตรฐานตามที่คณะกรรมการว่าด้วยการพัสดุกำหนด '
                . 'ก่อนลงนามต้องตรวจสอบและปรับข้อความให้ครบถ้วนตามแบบที่ใช้จริง',
            ['size' => 15, 'italic' => true],
            ['spaceBefore' => 200]
        );

        self::signature($section, ['ผู้ซื้อ/ผู้ว่าจ้าง', 'ผู้ขาย/ผู้รับจ้าง', 'พยาน']);

        return $phpWord;
    }

    // ── หนังสือแจ้งค่าปรับ ───────────────────────────────────────────────────

    public static function buildFineNotice(Contract $model): PhpWord
    {
        $phpWord = self::newDocument();
        $section = self::newSection($phpWord);
        $agency = self::agencyName();

        $section->addText('บันทึกข้อความ', ['bold' => true, 'size' => 24], ['alignment' => Jc::CENTER]);
        $section->addText('ส่วนราชการ  ' . ($agency ?: '[ชื่อส่วนราชการ]'), ['bold' => true], ['spaceBefore' => 120]);
        $section->addText('ที่ ................................    วันที่  ' . self::thaiDate(date('Y-m-d')), ['bold' => true]);
        $section->addText(
            'เรื่อง  แจ้งการปรับตามสัญญา เลขที่ ' . ($model->contract_no ?: $model->doc_no ?: '—'),
            ['bold' => true],
            ['spaceAfter' => 160]
        );

        $fine = $model->fineInfo();

        $section->addText(
            'ตามที่ ' . ($agency ?: '[ชื่อส่วนราชการ]') . ' ได้ทำ' . $model->typeName()
                . ' เลขที่ ' . ($model->contract_no ?: $model->doc_no ?: '—')
                . ' ลงวันที่ ' . self::thaiDate($model->sign_date)
                . ' กับ ' . $model->partyName()
                . ' เพื่อ ' . $model->title
                . ' วงเงิน ' . number_format((float) $model->budget, 2) . ' บาท'
                . ' กำหนดส่งมอบภายในวันที่ ' . self::thaiDate($model->end_date) . ' นั้น',
            [],
            ['alignment' => Jc::BOTH, 'indentation' => ['firstLine' => 720], 'spaceAfter' => 120]
        );

        $section->addText(
            'ปรากฏว่าผู้ขาย/ผู้รับจ้างส่งมอบเมื่อวันที่ ' . self::thaiDate($model->closingDate())
                . ' ล่าช้ากว่ากำหนด ' . $fine['days'] . ' วัน'
                . ' จึงต้องชำระค่าปรับตามที่กำหนดไว้ในสัญญา ดังนี้',
            [],
            ['alignment' => Jc::BOTH, 'indentation' => ['firstLine' => 720], 'spaceAfter' => 120]
        );

        $table = self::table($section);
        self::row($table, 'ฐานที่ใช้คิดค่าปรับ', Contract::fineBaseList()[$model->fine_base] ?? '-');
        self::row($table, 'อัตราค่าปรับ', 'ร้อยละ ' . rtrim(rtrim(number_format((float) $model->fine_rate, 4), '0'), '.') . ' ต่อวัน');
        self::row($table, 'จำนวนวันที่ล่าช้า', $fine['days'] . ' วัน');
        self::row($table, 'ค่าปรับที่คำนวณได้', number_format($fine['raw'], 2) . ' บาท');
        if ($fine['capped']) {
            self::row(
                $table,
                'ค่าปรับที่เรียกเก็บ',
                number_format($fine['amount'], 2) . ' บาท'
                    . ' (ปรับลดลงเท่าวงเงินตามสัญญา เนื่องจากค่าปรับที่คำนวณได้เกินวงเงิน)'
            );
        }
        self::row(
            $table,
            'รวมค่าปรับที่ต้องชำระ',
            number_format($fine['amount'], 2) . ' บาท (' . self::bahtText($fine['amount']) . ')'
        );

        // ค่าปรับที่สูงถึงเกณฑ์นี้เป็นสัญญาณให้ต้องพิจารณาบอกเลิกสัญญา
        // ระบุไว้ในเอกสารตรง ๆ เพื่อให้ผู้มีอำนาจเห็นตอนพิจารณา ไม่ใช่ให้ผ่านไปเงียบ ๆ
        $ratio = ContractCalculator::fineRatio($fine['amount'], (float) $model->budget);
        if ($ratio >= ContractCalculator::WARN_RATIO) {
            $section->addText(
                'หมายเหตุ: ค่าปรับคิดเป็นร้อยละ ' . number_format($ratio, 2) . ' ของวงเงินตามสัญญา '
                    . 'ซึ่งอยู่ในเกณฑ์ที่ต้องพิจารณาบอกเลิกสัญญาตามระเบียบ',
                ['size' => 15, 'bold' => true],
                ['spaceBefore' => 120]
            );
        }

        $section->addText(
            'จึงเรียนมาเพื่อโปรดพิจารณา',
            [],
            ['indentation' => ['firstLine' => 720], 'spaceBefore' => 160]
        );

        self::signature($section, ['เจ้าหน้าที่พัสดุ', 'หัวหน้าเจ้าหน้าที่พัสดุ', 'ผู้อนุมัติ']);

        return $phpWord;
    }

    // ── ทะเบียนคุมสัญญา ──────────────────────────────────────────────────────

    /** @param Contract[] $models */
    public static function buildRegister(array $models, int $thaiYear): PhpWord
    {
        $phpWord = self::newDocument();
        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'marginTop' => 850,
            'marginBottom' => 850,
            'marginLeft' => 850,
            'marginRight' => 850,
        ]);

        $section->addText(self::agencyName(), ['bold' => true, 'size' => 18], ['alignment' => Jc::CENTER]);
        $section->addText(
            'ทะเบียนคุมสัญญา/ข้อตกลง ปีงบประมาณ ' . $thaiYear,
            ['bold' => true, 'size' => 18],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 160]
        );

        $columns = [
            ['ที่', 4],
            ['เลขที่สัญญา', 11],
            ['รายการ', 24],
            ['ประเภท', 9],
            ['คู่สัญญา', 16],
            ['วงเงิน', 10],
            ['ครบกำหนด', 9],
            ['ตรวจรับ', 9],
            ['ค่าปรับ', 8],
        ];

        $table = self::table($section, 12);
        $table->addRow();
        foreach ($columns as [$label, $w]) {
            $table->addCell($w * 50, ['bgColor' => 'D9E1F2'])
                ->addText($label, ['bold' => true, 'size' => 14], ['alignment' => Jc::CENTER]);
        }

        $sumBudget = 0.0;
        $sumFine = 0.0;
        foreach ($models as $i => $m) {
            $sumBudget += (float) $m->budget;
            $sumFine += (float) $m->fine_amount;

            $table->addRow();
            $table->addCell($columns[0][1] * 50)->addText((string) ($i + 1), ['size' => 14], ['alignment' => Jc::CENTER]);
            $table->addCell($columns[1][1] * 50)->addText($m->contract_no ?: ($m->doc_no ?: '-'), ['size' => 14]);
            $table->addCell($columns[2][1] * 50)->addText($m->title, ['size' => 14]);
            $table->addCell($columns[3][1] * 50)->addText($m->typeName(), ['size' => 14]);
            $table->addCell($columns[4][1] * 50)->addText($m->partyName(), ['size' => 14]);
            $table->addCell($columns[5][1] * 50)
                ->addText(number_format((float) $m->budget, 2), ['size' => 14], ['alignment' => Jc::END]);
            $table->addCell($columns[6][1] * 50)
                ->addText(self::thaiDate($m->end_date), ['size' => 14], ['alignment' => Jc::CENTER]);
            $table->addCell($columns[7][1] * 50)
                ->addText(self::thaiDate($m->receive_date), ['size' => 14], ['alignment' => Jc::CENTER]);
            $table->addCell($columns[8][1] * 50)
                ->addText(number_format((float) $m->fine_amount, 2), ['size' => 14], ['alignment' => Jc::END]);
        }

        if (!$models) {
            $table->addRow();
            $table->addCell(100 * 50, ['gridSpan' => count($columns)])
                ->addText('ไม่มีสัญญาในปีงบประมาณนี้', ['size' => 14], ['alignment' => Jc::CENTER]);
        }

        $table->addRow();
        $table->addCell(64 * 50, ['gridSpan' => 5, 'bgColor' => 'D9E1F2'])
            ->addText('รวม ' . count($models) . ' ฉบับ', ['bold' => true, 'size' => 14], ['alignment' => Jc::END]);
        $table->addCell($columns[5][1] * 50, ['bgColor' => 'D9E1F2'])
            ->addText(number_format($sumBudget, 2), ['bold' => true, 'size' => 14], ['alignment' => Jc::END]);
        $table->addCell(18 * 50, ['gridSpan' => 2, 'bgColor' => 'D9E1F2'])->addText('');
        $table->addCell($columns[8][1] * 50, ['bgColor' => 'D9E1F2'])
            ->addText(number_format($sumFine, 2), ['bold' => true, 'size' => 14], ['alignment' => Jc::END]);

        return $phpWord;
    }

    // ── ตัวช่วย ──────────────────────────────────────────────────────────────

    private static function newDocument(): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName(self::FONT);
        $phpWord->setDefaultFontSize(self::FONT_SIZE);
        return $phpWord;
    }

    private static function newSection(PhpWord $phpWord)
    {
        return $phpWord->addSection([
            'marginTop' => 1134,      // 2 ซม.
            'marginBottom' => 1134,
            'marginLeft' => 1701,     // 3 ซม.
            'marginRight' => 1134,
        ]);
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

    private static function heading($section, string $text): void
    {
        $section->addText($text, ['bold' => true, 'size' => 17], ['spaceBefore' => 200, 'spaceAfter' => 80]);
    }

    private static function table($section, int $cellMargin = 60)
    {
        return $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => $cellMargin,
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);
    }

    private static function row($table, string $label, string $value): void
    {
        $table->addRow();
        $table->addCell(30 * 50, ['bgColor' => 'D9E1F2'])->addText($label, ['bold' => true]);
        $table->addCell(70 * 50)->addText($value !== '' ? $value : '-');
    }

    private static function signature($section, array $roles): void
    {
        $section->addTextBreak(2);
        $sign = $section->addTable(['width' => 100 * 50, 'unit' => 'pct']);
        $sign->addRow();
        $width = (int) floor(100 / max(count($roles), 1));
        foreach ($roles as $role) {
            $cell = $sign->addCell($width * 50);
            $cell->addText('ลงชื่อ .............................................', [], ['alignment' => Jc::CENTER]);
            $cell->addText('(.............................................)', [], ['alignment' => Jc::CENTER]);
            $cell->addText($role, [], ['alignment' => Jc::CENTER]);
        }
    }

    /**
     * PhpWord รองรับ HTML เพียงชุดย่อย ถ้าเจอโครงสร้างที่แปลงไม่ได้จะโยน exception
     * ซึ่งทำให้ดาวน์โหลดไม่ได้ทั้งไฟล์ จึงถอยไปใส่เป็นข้อความล้วนแทน
     * เอกสารเสียรูปแบบบางส่วนแต่เนื้อหาไม่หาย
     */
    private static function addHtml($container, ?string $html): void
    {
        $html = (string) $html;
        if (trim(strip_tags($html)) === '') {
            $container->addText('-');
            return;
        }
        try {
            Html::addHtml($container, $html, false, false);
        } catch (\Throwable $e) {
            Yii::warning('แปลง HTML ลง Word ไม่สำเร็จ ใช้ข้อความล้วนแทน: ' . $e->getMessage(), __METHOD__);
            foreach (preg_split('/\R/', trim(strip_tags($html, '<br><p><li>'))) as $line) {
                $line = trim(html_entity_decode(strip_tags($line), ENT_QUOTES, 'UTF-8'));
                if ($line !== '') {
                    $container->addText($line);
                }
            }
        }
    }

    private static function thaiDate(?string $date): string
    {
        if (empty($date)) {
            return '-';
        }
        try {
            return AppHelper::convertToThai($date);
        } catch (\Throwable $e) {
            return $date;
        }
    }

    /**
     * จำนวนเงินเป็นตัวอักษร — เอกสารสัญญาต้องมีกำกับไว้คู่กับตัวเลขเสมอ
     * รองรับทศนิยม 2 ตำแหน่งเป็นสตางค์ ปัดที่ 2 ตำแหน่งก่อนแปลง
     */
    public static function bahtText(float $amount): string
    {
        $negative = $amount < 0;
        $amount = round(abs($amount), 2);

        $baht = (int) floor($amount);
        $satang = (int) round(($amount - $baht) * 100);

        $text = self::readNumber($baht) . 'บาท';
        $text .= $satang > 0 ? self::readNumber($satang) . 'สตางค์' : 'ถ้วน';

        return ($negative ? 'ลบ' : '') . $text;
    }

    /** อ่านจำนวนเต็มเป็นภาษาไทย รองรับหลักล้านซ้อนกันได้ไม่จำกัด */
    private static function readNumber(int $number): string
    {
        if ($number === 0) {
            return 'ศูนย์';
        }

        $digits = ['ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
        $places = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน'];

        // ตัดเป็นก้อนละ 6 หลักแล้วต่อด้วย "ล้าน" — วิธีอ่านเลขไทยซ้ำรูปแบบทุก 6 หลัก
        if ($number >= 1000000) {
            return self::readNumber((int) floor($number / 1000000)) . 'ล้าน'
                . ($number % 1000000 > 0 ? self::readNumber($number % 1000000) : '');
        }

        $text = '';
        $str = (string) $number;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $digit = (int) $str[$i];
            $place = $len - $i - 1;
            if ($digit === 0) {
                continue;
            }
            if ($place === 1 && $digit === 1) {
                $text .= 'สิบ';                       // สิบ ไม่ใช่ หนึ่งสิบ
            } elseif ($place === 1 && $digit === 2) {
                $text .= 'ยี่สิบ';                     // ยี่สิบ ไม่ใช่ สองสิบ
            } elseif ($place === 0 && $digit === 1 && $len > 1) {
                $text .= 'เอ็ด';                      // ...เอ็ด ไม่ใช่ ...หนึ่ง
            } else {
                $text .= $digits[$digit] . $places[$place];
            }
        }

        return $text;
    }
}
