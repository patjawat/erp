<?php

namespace app\modules\finance\components;

use app\components\SiteHelper;
use app\modules\finance\models\FinancePayable;
use yii\helpers\Html;

/**
 * ปั้น HTML "ใบอนุมัติจ่ายเงินบำรุง (ต่อบิล)" จากทะเบียนเจ้าหนี้ 1 ใบ
 * โครง/ถ้อยคำ/ช่องเซ็น อ้างจากแบบฟอร์มพัสดุ legacy_purchase_12 (ขออนุมัติจ่ายเงินบำรุง)
 * ใส่ค่าจริงตอน build (ไม่ใช้ placeholder engine) — เก็บเฉพาะ {{emblem}} ให้ DocRenderer แทนตราครุฑ
 */
class FinancePayableDocumentBuilder
{
    public const CODE = 'payable_payment_approval';
    private const VERSION = 1;

    public static function version(): int
    {
        return self::VERSION;
    }

    public static function build(FinancePayable $p): string
    {
        $site = SiteHelper::getInfo();
        $company = trim((string) ($site['company_name'] ?? '')) ?: 'โรงพยาบาล';

        $director = self::person($site['director'] ?? null);
        $directorName = $director ?: '';
        $directorPos = trim((string) ($site['director_position'] ?? '')) ?: ('ผู้อำนวยการ' . $company);
        $leaderName = trim((string) ($site['leader_fullname'] ?? ''));
        $leaderPos = trim((string) ($site['leader_position'] ?? '')) ?: 'หัวหน้าฝ่ายบริหารงานทั่วไป';

        $vendor = Html::encode((string) $p->vendor_name_snapshot);
        $invoiceNo = Html::encode((string) ($p->invoice_no ?: '-'));
        $payableNo = Html::encode((string) ($p->payable_no ?: ('#' . $p->id)));
        $net = number_format((float) $p->net_amount, 2);
        $netText = self::bahtText((float) $p->net_amount);

        $head = self::memoHead('ขออนุมัติจ่ายเงินบำรุง', 'ผู้อำนวยการ' . Html::encode($company),
            (string) ($p->payable_no ?: ''), self::thaiDate(date('Y-m-d')), Html::encode($company));

        $s1 = self::sign('', '', 'เจ้าหน้าที่การเงิน');
        $s2 = self::sign('', Html::encode($leaderName), Html::encode($leaderPos));
        $s3 = self::sign('', Html::encode($directorName), Html::encode($directorPos));

        return <<<HTML
{$head}

<p class="d-body">ด้วย{$company} มีภาระต้องชำระหนี้ค่าสินค้า/บริการให้แก่ <strong>{$vendor}</strong>
ตามใบส่งของ/ใบแจ้งหนี้เลขที่ {$invoiceNo} ซึ่งเจ้าหน้าที่ได้ตรวจรับและตั้งเป็นเจ้าหนี้ในทะเบียนคุมเลขที่
{$payableNo} เรียบร้อยแล้ว รายละเอียดตามที่แนบมาพร้อมนี้</p>

<p class="d-body">อาศัยอำนาจตามคำสั่งสำนักงานปลัดกระทรวงสาธารณสุข เรื่อง มอบอำนาจให้หัวหน้าหน่วยบริการ
เกี่ยวกับการอนุมัติจ่ายเงินหรือก่อหนี้ผูกพันเงินบำรุงของหน่วยบริการ ตามระเบียบกระทรวงสาธารณสุข
ว่าด้วยเงินบำรุงของหน่วยบริการในสังกัดกระทรวงสาธารณสุข</p>

<p class="d-body">จึงเรียนมาเพื่อโปรดพิจารณาอนุมัติจ่ายเงินบำรุง เพื่อจ่ายให้ <strong>{$vendor}</strong>
เป็นเงินจำนวน <strong>{$net} บาท</strong> ({$netText})</p>

{$s1}

<p class="d-caption">ความเห็นของหัวหน้าฝ่ายบริหารงานทั่วไป</p>
<p class="d-approve">ได้ตรวจสอบหลักฐานถูกต้องแล้ว เห็นควรอนุมัติจ่ายเงินต่อไป</p>

{$s2}

<p class="d-approve">อนุมัติ</p>

{$s3}
HTML;
    }

    private static function person($obj): string
    {
        if (!is_object($obj)) {
            return '';
        }
        if (method_exists($obj, 'fullname')) {
            return (string) $obj->fullname();
        }
        return (string) ($obj->fullname ?? '');
    }

    private static function memoHead(string $subject, string $to, string $docNo, string $dateThai, string $companyFull): string
    {
        $docNo = Html::encode($docNo);
        return <<<HTML
<table class="d-masthead">
    <tr>
        <td class="d-masthead-side">{{emblem}}</td>
        <td class="d-masthead-title"><p class="d-title">บันทึกข้อความ</p></td>
        <td class="d-masthead-side"></td>
    </tr>
</table>

<table class="d-head">
    <tr>
        <td class="d-lbl">ส่วนราชการ</td>
        <td class="d-val" colspan="3">{$companyFull}</td>
    </tr>
    <tr>
        <td class="d-lbl">ที่</td>
        <td class="d-val">{$docNo}</td>
        <td class="d-lbl-sm">วันที่</td>
        <td class="d-val">{$dateThai}</td>
    </tr>
    <tr>
        <td class="d-lbl">เรื่อง</td>
        <td class="d-val" colspan="3">{$subject}</td>
    </tr>
</table>

<p class="d-to">เรียน&nbsp;&nbsp;{$to}</p>
HTML;
    }

    private static function sign(string $line, string $name, string ...$positions): string
    {
        $nameLine = $name !== '' ? $name : '..............................................';
        $pos = '';
        foreach ($positions as $p) {
            $pos .= '            <p class="d-sign-pos">' . $p . "</p>\n";
        }
        return <<<HTML
<table class="d-sign">
    <tr>
        <td class="d-sign-cell"></td>
        <td class="d-sign-cell">
            <p class="d-sign-line">{$line}</p>
            <p class="d-sign-name">( {$nameLine} )</p>
{$pos}        </td>
    </tr>
</table>
HTML;
    }

    private static function thaiDate(string $dbDate): string
    {
        $months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $t = date_create($dbDate);
        if (!$t) {
            return $dbDate;
        }
        return (int) $t->format('j') . ' ' . $months[(int) $t->format('n')] . ' พ.ศ. ' . ((int) $t->format('Y') + 543);
    }

    private static function bahtText($number): string
    {
        $number = number_format((float) $number, 2, '.', '');
        [$int, $dec] = explode('.', $number);
        $num = ['ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
        $dig = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];
        $conv = function ($n) use ($num, $dig, &$conv) {
            $n = (string) (int) $n;
            $len = strlen($n);
            if ($len == 0 || (int) $n === 0) {
                return '';
            }
            if ($len > 7) {
                return $conv(substr($n, 0, $len - 6)) . 'ล้าน' . $conv(substr($n, $len - 6));
            }
            $r = '';
            for ($i = 0; $i < $len; $i++) {
                $d = (int) $n[$i];
                $pos = $len - $i - 1;
                if ($d === 0) {
                    continue;
                }
                if ($pos === 0 && $d === 1 && $len > 1) {
                    $r .= 'เอ็ด';
                } elseif ($pos === 1 && $d === 2) {
                    $r .= 'ยี่' . $dig[$pos];
                } elseif ($pos === 1 && $d === 1) {
                    $r .= $dig[$pos];
                } else {
                    $r .= $num[$d] . $dig[$pos];
                }
            }
            return $r;
        };
        $baht = $conv($int);
        $out = ($baht === '' ? 'ศูนย์บาท' : $baht . 'บาท');
        $out .= ((int) $dec === 0) ? 'ถ้วน' : ($conv($dec) . 'สตางค์');
        return $out;
    }
}
