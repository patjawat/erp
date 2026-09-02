<?php

namespace app\modules\hr\components;

use yii\helpers\Html;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Development;
use app\modules\hr\models\DevelopmentDetail;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

/**
 * สร้างเนื้อหาเริ่มต้นจากทะเบียน ก่อนเก็บเป็น snapshot ที่ผู้ใช้แก้ไขได้
 *
 * รูปแบบของทุกฉบับอ้างอิงจากไฟล์ Excel ที่งานการเงินใช้จริง โดยคงถ้อยคำคงที่
 * คำชี้แจง และลำดับบรรทัดให้ตรงต้นฉบับ ส่วนข้อมูลที่ทะเบียนยังไม่เก็บ (จุดออก
 * เดินทาง อัตราน้ำมันต่อกิโลเมตร ผู้อนุมัติ ผู้จ่ายเงิน) เว้นเป็นจุดไข่ปลาให้ผู้ใช้
 * พิมพ์เองบนหน้าจอแก้ไขก่อนสั่งพิมพ์
 */
final class DevelopmentDocumentBuilder
{
    /**
     * รุ่นของแม่แบบแต่ละฉบับ เพิ่มเลขทุกครั้งที่แก้เนื้อหาแม่แบบ เอกสารเก่าที่ยัง
     * ไม่มีเครื่องหมายรุ่นนี้จะถูกสร้างใหม่จากทะเบียนให้อัตโนมัติตอนเปิด
     */
    private const VERSIONS = [
        'travel_expense_8708_part_1' => 10,
        'travel_expense_8708_part_2' => 2,
        'travel_expense_bk_111' => 2,
        'travel_expense_cover_sheet' => 1,
        'travel_expense_payment_approval' => 2,
        'travel_permission_memo' => 2,
        'travel_registration_memo' => 2,
    ];

    /** เครื่องหมายรุ่นที่ฝังไว้ในเนื้อหา ใช้ตรวจว่า snapshot เก่าหรือใหม่ */
    public static function versionMarker(string $code): string
    {
        return 'd-form-' . str_replace('_', '-', $code) . '-v' . (self::VERSIONS[$code] ?? 1);
    }

    public static function build(string $code, Development $model): string
    {
        $data = self::payload($model);
        $marker = '<div class="d-form-version ' . self::versionMarker($code) . '"></div>';

        switch ($code) {
            case 'travel_expense_8708_part_1':
                return $marker . self::partOne($data);
            case 'travel_expense_8708_part_2':
                return $marker . self::partTwo($data);
            case 'travel_expense_bk_111':
                return $marker . self::bk111($data);
            case 'travel_expense_cover_sheet':
                return $marker . self::coverSheet($data);
            case 'travel_expense_payment_approval':
                return $marker . self::paymentApproval($data);
            case 'travel_permission_memo':
                return $marker . self::permissionMemo($data);
            case 'travel_registration_memo':
                return $marker . self::registrationMemo($data);
            default:
                throw new \InvalidArgumentException('ไม่พบแม่แบบเอกสารที่เลือก');
        }
    }

    private static function payload(Development $model): array
    {
        $json = is_array($model->data_json) ? $model->data_json : [];
        $employee = $model->createdByEmp;
        $expenses = $model->estimatedCostAmounts(true);
        $site = SiteHelper::getInfo();
        $configuredDocNumber = trim((string) ($site['doc_number'] ?? ''));

        $fullname = $employee
            ? (method_exists($employee, 'fullname') ? $employee->fullname() : trim($employee->fname . ' ' . $employee->lname))
            : '';
        $position = $employee && method_exists($employee, 'positionName')
            ? (string) ($employee->positionName() ?: '')
            : '';

        $organization = (string) ($site['company_name'] ?? '');
        $province = trim((string) ($site['province'] ?? ''));
        $members = self::members($model);
        $timeStart = (string) ($json['vehicle_time_start'] ?? $model->time_start ?? '');
        $timeEnd = (string) ($json['vehicle_time_end'] ?? $model->time_end ?? '');
        $dateStart = (string) ($model->vehicle_date_start ?: $model->date_start);
        $dateEnd = (string) ($model->vehicle_date_end ?: $model->date_end);
        $duration = self::duration($dateStart, $timeStart, $dateEnd, $timeEnd);

        return [
            'organization' => $organization,
            'organization_line' => trim($organization . ($province !== '' ? ' จังหวัด' . $province : '')),
            'doc_number' => $configuredDocNumber === '' ? '' : rtrim($configuredDocNumber, '/') . '/',
            'fullname' => $fullname,
            'position' => $position,
            'department' => (string) ($json['department_name'] ?? ''),
            'topic' => (string) ($model->topic ?? ''),
            'location' => trim((string) ($json['location'] ?? '') . ' ' . (string) ($json['province_name'] ?? '')),
            'reference_number' => $model->document ? (string) ($model->document->doc_number ?? '') : '',
            'reference_date' => $model->document ? self::thaiDate($model->document->doc_date ?? null) : '',
            'loan_number' => (string) ($json['loan_contract_number'] ?? ''),
            'loan_date' => self::thaiDate($json['loan_contract_date'] ?? null),
            'loan_amount' => (float) ($json['loan_amount'] ?? 0),
            'date_start' => self::thaiDate($dateStart),
            'date_end' => self::thaiDate($dateEnd),
            'time_start' => $timeStart,
            'time_end' => $timeEnd,
            'days' => $duration['days'],
            'hours' => $duration['hours'],
            'has_duration' => $duration['ok'],
            'vehicle_type' => self::vehicleType($model),
            'license_plate' => trim((string) ($json['license_plate'] ?? '')),
            'distance' => trim((string) ($json['distance'] ?? '')),
            'members' => $members,
            'members_text' => self::membersText($members),
            'allowance' => (float) ($expenses['allowance_amount'] ?? 0),
            'accommodation' => (float) ($expenses['accommodation_amount'] ?? 0),
            'vehicle' => (float) ($expenses['vehicle_amount'] ?? 0),
            'other' => (float) ($expenses['other_amount'] ?? 0) + (float) ($expenses['registration_amount'] ?? 0),
            'total' => (float) $model->totalEstimatedCost(true),
            'registration' => (float) ($expenses['registration_amount'] ?? 0),
            'assignee' => self::person($model->assignedTo),
            'organizer' => (string) ($json['location_org'] ?? ''),
            'budget_source' => (string) ($json['claim_type_name'] ?? ''),
            'director' => self::person($site['director'] ?? null),
            'admin_head' => self::person(self::adminHead($site)),
            'today' => self::thaiDate(date('Y-m-d')),
        ];
    }

    /** คณะเดินทาง (ไม่รวมผู้ขอเบิก) พร้อมตำแหน่ง สำหรับตารางหลักฐานการจ่าย */
    private static function members(Development $model): array
    {
        $rows = [];
        $details = DevelopmentDetail::find()
            ->where(['name' => 'member', 'development_id' => $model->id])
            ->andWhere(['<>', 'emp_id', $model->emp_id])
            ->with('emp')
            ->orderBy(['id' => SORT_ASC])
            ->all();

        foreach ($details as $item) {
            $emp = $item->emp;
            $label = is_array($item->data_json) ? trim((string) ($item->data_json['label'] ?? '')) : '';
            $name = $emp ? $emp->fullname() : ($label !== '' ? $label : (string) $item->emp_id);
            if (trim((string) $name) === '') {
                continue;
            }
            $rows[] = [
                'name' => (string) $name,
                'position' => $emp && method_exists($emp, 'positionName') ? (string) ($emp->positionName() ?: '') : '',
            ];
        }

        return $rows;
    }

    /** ชื่อ + ตำแหน่ง ของบุคลากรหนึ่งคน สำหรับช่องลงนามในบันทึกข้อความ */
    private static function person($employee): array
    {
        if (!$employee) {
            return ['name' => '', 'position' => ''];
        }

        return [
            'name' => method_exists($employee, 'fullname') ? (string) $employee->fullname() : '',
            'position' => method_exists($employee, 'positionName') ? (string) ($employee->positionName() ?: '') : '',
        ];
    }

    /**
     * หัวหน้าฝ่ายบริหารงานทั่วไป ผู้ลงนามช่องความเห็นก่อนเสนอผู้อำนวยการ
     *
     * ใช้หัวหน้าของกลุ่มงานบริหารทั่วไปในทะเบียนหน่วยงานก่อน ถ้ายังไม่ได้ตั้งไว้
     * จึงถอยไปใช้หัวหน้าเจ้าหน้าที่ที่ตั้งไว้ในหน้าตั้งค่าองค์กร
     */
    private static function adminHead(array $site)
    {
        $unit = Organization::find()->where(['like', 'name', 'บริหารทั่วไป'])->one();
        $leader = $unit ? $unit->leader : null;
        if ($leader) {
            return $leader;
        }

        $id = $site['leader'] ?? null;

        return $id ? Employees::findOne($id) : null;
    }

    /** วงเล็บชื่อผู้ลงนาม ถ้าไม่มีข้อมูลให้เว้นเป็นจุดไข่ปลา */
    private static function nameLine(array $person, int $length): string
    {
        return '(' . ($person['name'] !== '' ? self::escape($person['name']) : self::dots($length)) . ')';
    }

    private static function membersText(array $members): string
    {
        $parts = [];
        foreach ($members as $member) {
            $parts[] = trim($member['name'] . ($member['position'] !== '' ? ' ตำแหน่ง ' . $member['position'] : ''));
        }

        return implode('  ', $parts);
    }

    private static function vehicleType(Development $model): string
    {
        try {
            $type = $model->vehicleType;
            return $type ? (string) $type->title : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /** รวมเวลาไปราชการเป็นวัน/ชั่วโมง จากวันเวลาไป-กลับที่บันทึกไว้ */
    private static function duration(string $dateStart, string $timeStart, string $dateEnd, string $timeEnd): array
    {
        if ($dateStart === '' || $dateEnd === '') {
            return ['ok' => false, 'days' => 0, 'hours' => 0];
        }

        $start = strtotime(trim($dateStart . ' ' . ($timeStart !== '' ? $timeStart : '00:00')));
        $end = strtotime(trim($dateEnd . ' ' . ($timeEnd !== '' ? $timeEnd : '00:00')));
        if ($start === false || $end === false || $end <= $start) {
            return ['ok' => false, 'days' => 0, 'hours' => 0];
        }

        $diff = $end - $start;

        return [
            'ok' => true,
            'days' => intdiv($diff, 86400),
            'hours' => (int) floor(($diff % 86400) / 3600),
        ];
    }

    private static function partOne(array $d): string
    {
        $e = [self::class, 'escape'];
        $loanNumber = self::valueOrDots($d['loan_number'], 24);
        $loanDate = self::valueOrDots($d['loan_date'], 18);
        $loanAmount = $d['loan_amount'] > 0 ? self::money($d['loan_amount']) : self::dots(16);
        $referenceNumber = self::valueOrDots($d['reference_number'], 24);
        $referenceDate = self::valueOrDots($d['reference_date'], 18);
        $members = self::valueOrDots($d['members_text'], 80);
        $moneyWords = self::bahtText($d['total']);
        $isGroup = $d['members'] !== [];
        $days = $d['has_duration'] ? (string) $d['days'] : self::dots(6);
        $hours = $d['has_duration'] ? (string) $d['hours'] : self::dots(6);

        $page1 = '<table class="d-8708-loan"><tr><td>สัญญายืมเงินเลขที่ ' . $loanNumber . '</td><td>วันที่ ' . $loanDate
            . '</td><td class="d-right"><strong>ส่วนที่&nbsp;1<br>แบบ&nbsp;8708</strong></td></tr>'
            . '<tr><td>ชื่อผู้ยืม ' . $e($d['fullname']) . '</td><td>จำนวนเงิน ' . $loanAmount . ' บาท</td><td></td></tr></table>'
            . '<p class="d-8708-title">ใบเบิกค่าใช้จ่ายในการเดินทางไปราชการ</p>'
            . '<table class="d-8708-office"><tr><td></td><td><strong>ที่ทำการ</strong> ' . $e($d['organization']) . '</td></tr>'
            . '<tr><td></td><td><strong>วันที่</strong> ' . $e($d['today']) . '</td></tr></table>'
            . '<p class="d-8708-line"><strong>เรื่อง</strong> ขออนุมัติเบิกค่าใช้จ่ายในการเดินทางไปราชการ</p>'
            . '<p class="d-8708-line"><strong>เรียน</strong> ผู้อำนวยการ' . $e($d['organization']) . '</p>'
            . '<p class="d-8708-indent">ตามบันทึกคำสั่งที่ ' . $referenceNumber . ' ลงวันที่ ' . $referenceDate . ' ได้อนุมัติให้</p>'
            . '<p class="d-8708-line">ข้าพเจ้า ' . $e($d['fullname']) . ' ตำแหน่ง ' . self::valueOrDots($d['position'], 30)
            . ' สังกัด ' . $e($d['organization']) . ' พร้อมด้วย</p>'
            . '<p class="d-8708-line">' . $members . '</p>'
            . '<p class="d-8708-line">เดินทางไปราชการเพื่อ ' . $e($d['topic']) . '</p>'
            . '<table class="d-8708-split"><tr><td>ณ ' . $e($d['location']) . '</td>'
            . '<td class="d-right">โดยออกเดินทางจาก</td></tr></table>';

        $page1 .= '<p class="d-8708-line">' . self::box(false) . ' บ้านพัก&nbsp;&nbsp; ' . self::box(false) . ' สำนักงาน&nbsp;&nbsp; '
            . self::box(false) . ' ประเทศไทย ตั้งแต่วันที่ ' . self::valueOrDots($d['date_start'], 20) . ' เวลา '
            . self::valueOrDots($d['time_start'], 10) . ' น. และเดินทางกลับถึง</p>'
            . '<p class="d-8708-line">' . self::box(false) . ' บ้านพัก&nbsp;&nbsp; ' . self::box(false) . ' สำนักงาน&nbsp;&nbsp; '
            . self::box(false) . ' ประเทศไทย ในวันที่ ' . self::valueOrDots($d['date_end'], 20) . ' เวลา '
            . self::valueOrDots($d['time_end'], 10) . ' น. รวมเวลาไปราชการครั้งนี้</p>'
            . '<p class="d-8708-line">' . $days . ' วัน ' . $hours . ' ชั่วโมง</p>'
            . '<p class="d-8708-indent">ข้าพเจ้าขอเบิกค่าใช้จ่ายในการเดินทางไปราชการสำหรับ ' . self::box(!$isGroup) . ' ข้าพเจ้า&nbsp;&nbsp; '
            . self::box($isGroup) . ' คณะเดินทาง ดังนี้</p>'
            . self::expenseTable($d)
            . '<p class="d-8708-line">จำนวนเงิน (ตัวอักษร) ' . self::escape($moneyWords) . '</p>'
            . '<p class="d-8708-indent">ข้าพเจ้าขอรับรองว่า รายการที่กล่าวมาเป็นความจริง และหลักฐานการจ่ายที่ส่งมาด้วย จำนวน '
            . self::dots(6) . ' ฉบับ รวมทั้งจำนวนเงินที่ขอเบิกถูกต้องตามกฎหมายทุกประการ</p>'
            . self::signature($d['fullname'], $d['position'], 'ผู้ขอรับเงิน');

        $page2 = '<table class="d-8708-approval"><tr>'
            . '<td><strong>ได้ตรวจสอบหลักฐานการเบิกจ่ายที่แนบถูกต้องแล้ว<br>เห็นควรอนุมัติให้เบิกจ่ายได้</strong>'
            . '<br><br><br>ลงชื่อ ' . self::dots(30) . '<br>' . self::nameLine($d['admin_head'], 34)
            . '<br>ตำแหน่ง ' . self::valueOrDots($d['admin_head']['position'], 28)
            . '<br>วันที่ ' . self::dots(30) . '</td>'
            . '<td><strong>อนุมัติให้จ่ายได้</strong>'
            . '<br><br><br><br>ลงชื่อ ' . self::dots(30) . '<br>' . self::nameLine($d['director'], 34)
            . '<br>ตำแหน่ง ผู้อำนวยการ' . $e($d['organization'])
            . '<br>วันที่ ' . self::dots(30) . '</td></tr></table>'
            . '<p class="d-8708-received">ได้รับเงินค่าใช้จ่ายในการเดินทางไปราชการ จำนวน ' . self::amount($d['total'])
            . ' บาท (' . self::escape($moneyWords) . ') ไว้เป็นการถูกต้องแล้ว</p>'
            . '<table class="d-8708-signatures"><tr>'
            . '<td>ลงชื่อ ' . self::dots(26) . ' ผู้รับเงิน<br>(' . self::escape($d['fullname']) . ')<br>ตำแหน่ง '
            . self::valueOrDots($d['position'], 26) . '<br>วันที่ ' . self::dots(26) . '</td>'
            . '<td>ลงชื่อ ' . self::dots(26) . ' ผู้จ่ายเงิน<br>(' . self::dots(30) . ')<br>ตำแหน่ง ' . self::dots(26)
            . '<br>วันที่ ' . self::dots(26) . '</td></tr></table>'
            . '<p class="d-8708-line">จากเงินยืมตามสัญญาที่ ' . $loanNumber . ' ลงวันที่ ' . $loanDate . '</p>';

        $page2 .= '<div class="d-8708-notes"><strong>หมายเหตุ</strong><br>' . self::dots(96) . '<br>' . self::dots(96)
            . '<br>' . self::dots(96) . '</div>'
            . '<p class="d-8708-line d-right">รวมขอเบิกทั้งสิ้น ' . self::amount($d['allowance']) . ' บาท</p>'
            . '<table class="d-8708-clarify"><tr><td class="d-8708-clarify-label">คำชี้แจง</td><td>'
            . '1. กรณีเดินทางเป็นหมู่คณะและจัดทำใบเบิกค่าใช้จ่ายรวมฉบับเดียวกัน หากระยะเวลาในการเริ่มต้นและสิ้นสุดการเดินทาง'
            . 'ของแต่ละบุคคลแตกต่างกัน ให้แสดงรายละเอียดของวันเวลาที่แตกต่างกันของบุคคลนั้นในช่องหมายเหตุ<br>'
            . '2. กรณียื่นขอเบิกค่าใช้จ่ายรายบุคคล ให้ผู้ขอรับเงินเป็นผู้ลงลายมือชื่อผู้รับเงินและวันเดือนปีที่รับเงิน '
            . 'กรณีที่มีการยืมเงิน ให้ระบุวันที่ที่ได้รับเงินยืม เลขที่สัญญายืม และวันที่อนุมัติเงินยืมด้วย<br>'
            . '3. กรณีที่ยื่นขอเบิกค่าใช้จ่ายรวมเป็นหมู่คณะ ผู้ขอรับเงินมิต้องลงลายมือชื่อในช่องผู้รับเงิน ทั้งนี้ '
            . 'ให้ผู้มีสิทธิแต่ละคน ลงลายมือชื่อผู้รับเงินในหลักฐานการจ่ายเงิน (ส่วนที่ 2)</td></tr></table>';

        return '<div class="d-8708-form"><section class="d-doc-page">' . $page1 . '</section>'
            . '<p class="d-page-break"><br></p><section class="d-doc-page">' . $page2 . '</section></div>';
    }

    private static function expenseTable(array $d): string
    {
        $days = $d['has_duration'] && $d['days'] > 0 ? (string) $d['days'] : self::dots(6);
        $vehicle = trim($d['vehicle_type'] . ($d['license_plate'] !== '' ? ' ทะเบียน ' . $d['license_plate'] : ''));

        return '<table class="d-8708-expense">'
            . '<tr><td class="d-8708-expense-name">ค่าเบี้ยเลี้ยงเดินทาง</td><td class="d-8708-expense-days">จำนวน ' . $days
            . ' วัน</td><td class="d-8708-expense-total">รวม ' . self::amount($d['allowance']) . ' บาท</td></tr>'
            . '<tr><td class="d-8708-expense-name">ค่าเช่าที่พัก</td><td class="d-8708-expense-days">จำนวน ' . self::dots(6)
            . ' วัน</td><td class="d-8708-expense-total">รวม ' . self::amount($d['accommodation']) . ' บาท</td></tr>'
            . '<tr><td colspan="2">ค่ายานพาหนะ ' . ($vehicle !== '' ? self::escape($vehicle) : self::dots(44))
            . '</td><td class="d-8708-expense-total">รวม ' . self::amount($d['vehicle']) . ' บาท</td></tr>'
            . '<tr><td colspan="2">ค่าใช้จ่ายอื่น ๆ ' . self::dots(44)
            . '</td><td class="d-8708-expense-total">รวม ' . self::amount($d['other']) . ' บาท</td></tr>'
            . '<tr><td colspan="2"></td><td class="d-8708-expense-total"><strong>รวมทั้งสิ้น ' . self::amount($d['total'])
            . ' บาท</strong></td></tr></table>';
    }

    /**
     * หลักฐานการจ่ายเงินเป็นหมู่คณะ (แบบ 8707 ส่วนที่ 2)
     *
     * ค่าเบี้ยเลี้ยงหารเท่ากันตามจำนวนผู้เดินทาง (เศษบาทตกที่ผู้ขอเบิก) ส่วนค่าเช่า
     * ที่พัก ค่าพาหนะ และค่าใช้จ่ายอื่นอยู่ที่แถวผู้ขอเบิกซึ่งเป็นผู้สำรองจ่าย
     */
    private static function partTwo(array $d): string
    {
        $people = array_merge([['name' => $d['fullname'], 'position' => $d['position']]], $d['members']);
        $count = max(1, count($people));
        $share = $d['allowance'] > 0 ? floor(($d['allowance'] / $count) * 100) / 100 : 0.0;
        $firstShare = $d['allowance'] - ($share * ($count - 1));

        $rows = '';
        $lines = max(10, $count);
        for ($i = 0; $i < $lines; $i++) {
            $person = $people[$i] ?? null;
            $first = $i === 0;
            $sum = $first ? $firstShare + $d['accommodation'] + $d['vehicle'] + $d['other'] : $share;

            $rows .= '<tr class="d-8707-row"><td class="d-8707-c-no">' . ($i + 1) . '</td>'
                . '<td>' . ($person ? self::escape($person['name']) : '&nbsp;') . '</td>'
                . '<td>' . ($person ? self::escape($person['position']) : '&nbsp;') . '</td>'
                . '<td class="d-right">' . ($person ? self::amount($first ? $firstShare : $share) : '&nbsp;') . '</td>'
                . '<td class="d-right">' . ($person ? self::amount($first ? $d['accommodation'] : 0) : '&nbsp;') . '</td>'
                . '<td class="d-right">' . ($person ? self::amount($first ? $d['vehicle'] : 0) : '&nbsp;') . '</td>'
                . '<td class="d-right">' . ($person ? self::amount($first ? $d['other'] : 0) : '&nbsp;') . '</td>'
                . '<td class="d-right">' . ($person ? self::amount($sum) : '&nbsp;') . '</td>'
                . '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
        }

        return '<table class="d-8707-head"><tr><td class="d-8707-head-title">'
            . '<p class="d-title">หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปราชการ</p>'
            . '<p class="d-8707-org">ส่วนราชการ ' . self::escape($d['organization_line']) . '</p></td>'
            . '<td class="d-8707-head-form"><strong>ส่วนที่&nbsp;2<br>แบบ&nbsp;8707</strong></td></tr></table>'
            . '<p class="d-8707-ref">ประกอบใบเบิกค่าใช้จ่ายในการเดินทางไปราชการของ ' . self::escape($d['fullname'])
            . ($d['members'] !== [] ? ' และคณะ' : '') . ' ลงวันที่ ' . self::escape($d['today']) . '</p>'
            . '<table class="d-8707-items">'
            . '<tr class="d-items-head"><td rowspan="2" class="d-8707-c-no">ลำดับ</td>'
            . '<td rowspan="2" class="d-8707-c-name">ชื่อ - สกุล</td>'
            . '<td rowspan="2" class="d-8707-c-position">ตำแหน่ง</td><td colspan="4" class="d-8707-c-expense">ค่าใช้จ่าย</td>'
            . '<td rowspan="2" class="d-8707-c-sum">รวม</td>'
            . '<td rowspan="2" class="d-8707-c-sign">ลายมือชื่อ<br>ผู้รับเงิน</td>'
            . '<td rowspan="2" class="d-8707-c-date">วัน เดือน ปี</td>'
            . '<td rowspan="2" class="d-8707-c-note">หมายเหตุ</td></tr>'
            . '<tr class="d-items-head"><td class="d-8707-c-money">ค่าเบี้ยเลี้ยง</td>'
            . '<td class="d-8707-c-money">ค่าเช่าที่พัก</td><td class="d-8707-c-money">ค่าพาหนะ</td>'
            . '<td class="d-8707-c-money">อื่น ๆ</td></tr>'
            . $rows
            . '<tr class="d-8707-total"><td colspan="3" class="d-c-total">รวมเงิน</td>'
            . '<td class="d-right">' . self::amount($d['allowance']) . '</td>'
            . '<td class="d-right">' . self::amount($d['accommodation']) . '</td>'
            . '<td class="d-right">' . self::amount($d['vehicle']) . '</td>'
            . '<td class="d-right">' . self::amount($d['other']) . '</td>'
            . '<td class="d-right">' . self::amount($d['total']) . '</td>'
            . '<td colspan="3">&nbsp;</td></tr></table>'
            . self::partTwoFooter($d);
    }

    private static function partTwoFooter(array $d): string
    {
        return '<table class="d-8707-foot"><tr><td>'
            . '<p class="d-8707-words">รวมเงินทั้งสิ้น (ตัวอักษร) ' . self::escape(self::bahtText($d['total'])) . '</p>'
            . '<p class="d-8707-clarify">คำชี้แจง 1. ค่าเบี้ยเลี้ยงและค่าเช่าที่พักให้ระบุอัตราวันละและจำนวนวันที่ขอเบิก'
            . 'ของแต่ละบุคคลในช่องหมายเหตุ<br>'
            . '2. ให้ผู้มีสิทธิแต่ละคนเป็นผู้ลงลายมือชื่อผู้รับเงิน และวันเดือนปีที่ได้รับเงิน กรณีเป็นการรับจากเงินยืม '
            . 'ให้ระบุวันที่ได้รับจากเงินยืม<br>'
            . '3. ผู้จ่ายเงินหมายถึง ผู้ที่ยืมเงินจากทางราชการ และจ่ายเงินยืมนั้นให้แก่ผู้เดินทางแต่ละคน '
            . 'เป็นผู้ลงลายมือชื่อผู้จ่ายเงิน</p></td>'
            . '<td class="d-8707-foot-sign">ลงชื่อ ' . self::dots(28) . ' ผู้จ่ายเงิน<br>(' . self::dots(30) . ')<br>'
            . 'ตำแหน่ง ' . self::dots(26) . '<br>วันที่ ' . self::dots(28) . '</td></tr></table>';
    }

    /** ใบรับรองแทนใบเสร็จรับเงิน (แบบ บก.111) ใช้กับค่าพาหนะที่เรียกใบเสร็จไม่ได้ */
    private static function bk111(array $d): string
    {
        $vehicle = $d['vehicle_type'] !== '' ? $d['vehicle_type'] : 'รถยนต์ส่วนบุคคล';
        $detail = '- เดินทางจาก ' . self::dots(36) . ' ถึง ' . self::valueOrDots($d['location'], 26)
            . ' โดย' . self::escape($vehicle) . ' ทะเบียน ' . self::valueOrDots($d['license_plate'], 14)
            . ' ระยะทาง ' . self::valueOrDots($d['distance'], 8) . ' กิโลเมตร รวมระยะทางไป-กลับ'
            . ' เบิกค่าน้ำมันเชื้อเพลิงชดเชยตามระเบียบฯ กิโลเมตรละ ' . self::dots(6) . ' บาท รวมเป็นเงิน';

        return '<div class="d-bill-form">'
            . '<p class="d-right"><strong>แบบ บก.111</strong></p>'
            . '<p class="d-title">ใบรับรองแทนใบเสร็จรับเงิน</p>'
            . '<p class="d-bill-org">ของส่วนราชการ ' . self::escape($d['organization_line']) . '</p>'
            . self::billTable($detail, self::valueOrDots($d['date_start'], 12), $d['vehicle'])
            . self::billFooter($d)
            . '</div>';
    }

    /** ใบหน้างบสำคัญ ค่าพาหนะ ใบปะหน้าชุดเบิกที่งานการเงินใช้คู่กับ บก.111 */
    private static function coverSheet(array $d): string
    {
        $detail = '<strong>ตามแบบ บก.111 ค่าพาหนะเดินทางภายในประเทศ</strong>'
            . '<br><span class="d-bill-sub">ตั้งแต่วันที่ ' . self::valueOrDots($d['date_start'], 18) . '</span>'
            . '<br><span class="d-bill-sub">ถึงวันที่ ' . self::valueOrDots($d['date_end'], 18) . '</span>';

        return '<div class="d-bill-form">'
            . '<p class="d-title">ใบหน้างบสำคัญ ค่าพาหนะ เดินทางภายในประเทศ</p>'
            . '<p class="d-bill-org">ประกอบรายงานของ ' . self::escape($d['fullname']) . '</p>'
            . '<p class="d-bill-org">ลงวันที่ ' . self::escape($d['today']) . '</p>'
            . self::billTable($detail, '&nbsp;', $d['vehicle'])
            . self::billFooter($d)
            . '</div>';
    }

    /**
     * หัวบันทึกข้อความมาตรฐาน (ครุฑ + ส่วนราชการ/ที่/วันที่/เรื่อง + เรียน)
     *
     * ป้ายกับเนื้อความอยู่ในช่องเดียวกันแล้วคั่นด้วยเว้นวรรคสองตัวจริง ๆ ไม่ได้ใช้
     * ความกว้างคอลัมน์เป็นตัวเว้น เพราะคอลัมน์จะกว้างตามกรอบที่วางและฟอนต์ที่ใช้
     * เวลาเปิดในหน้าจอแก้ไขซึ่งกว้างกว่ากระดาษจริง ป้ายจะลอยห่างจากเนื้อความ
     * ส่วนเส้นบรรทัดเป็นเส้นใต้ของช่อง จึงยังลากยาวถึงขอบขวาตามแบบหนังสือราชการ
     */
    private static function memoHead(array $d, string $subject): string
    {
        return '<table class="d-masthead"><tr><td class="d-masthead-side">{{emblem}}</td>'
            . '<td class="d-masthead-title"><p class="d-title">บันทึกข้อความ</p></td>'
            . '<td class="d-masthead-side"></td></tr></table>'
            . '<table class="d-tvm-head"><tr><td class="d-tvm-rule">' . self::memoLabel('ส่วนราชการ')
            . self::escape($d['organization_line']) . '</td></tr></table>'
            . '<table class="d-tvm-head"><tr><td class="d-tvm-rule d-tvm-ref">' . self::memoLabel('ที่')
            . self::valueOrDots($d['doc_number'], 20) . '</td><td class="d-tvm-space"></td>'
            . '<td class="d-tvm-rule">' . self::memoLabel('วันที่') . self::escape($d['today']) . '</td></tr></table>'
            . '<table class="d-tvm-head"><tr><td class="d-tvm-rule">' . self::memoLabel('เรื่อง')
            . self::escape($subject) . '</td></tr></table>'
            . '<p class="d-to">' . self::memoLabel('เรียน', false) . 'ผู้อำนวยการ' . self::escape($d['organization']) . '</p>';
    }

    /** ป้ายหัวบันทึกข้อความพร้อมเว้นวรรคสองเคาะก่อนเนื้อความ */
    private static function memoLabel(string $text, bool $bold = true): string
    {
        return '<span class="d-tvm-lbl' . ($bold ? '' : ' d-tvm-lbl-plain') . '">' . self::escape($text)
            . '</span>&nbsp;&nbsp;';
    }

    /**
     * บันทึกข้อความขออนุมัติจ่ายเงินบำรุง
     *
     * ถ้อยคำและการอ้างคำสั่งมอบอำนาจยึดตามแบบที่งานการเงินใช้จริง ผู้เสนอเรื่อง
     * ผู้ตรวจหลักฐาน และผู้อนุมัติเว้นเป็นจุดไข่ปลาให้เซ็นบนกระดาษ
     */
    private static function paymentApproval(array $d): string
    {
        $requester = self::escape($d['fullname']) . ($d['members'] !== [] ? ' และคณะ' : '');

        return self::memoHead($d, 'ขออนุมัติจ่ายเงินบำรุง')
            . '<p class="d-body">ด้วย' . self::escape($d['organization'])
            . ' ได้รับแบบใบเบิกค่าใช้จ่ายในการเดินทางไปราชการของ ' . $requester
            . ' ซึ่งได้เดินทางไปราชการเพื่อเข้าร่วมอบรม / ประชุม จำนวน ' . self::dots(4)
            . ' ชุด ดังมีรายละเอียดตามเอกสารที่แนบมาพร้อมนี้</p>'
            . '<p class="d-body">อาศัยอำนาจตามคำสั่งสำนักงานปลัดกระทรวงสาธารณสุข ที่ 2692/2553 ลงวันที่ 22 ตุลาคม 2553 '
            . 'เรื่อง การมอบอำนาจ ในการอนุมัติจ่ายเงินบำรุงของหน่วยบริการ ข้อ 1(3) ผู้อำนวยการโรงพยาบาลชุมชน'
            . 'มีอำนาจอนุมัติได้ครั้งหนึ่งไม่เกิน 2,000,000 บาท (สองล้านบาทถ้วน)</p>'
            . '<p class="d-body">จึงเรียนมาเพื่อโปรดพิจารณาอนุมัติจ่ายเงินบำรุงเพื่อจ่ายให้แก่ <strong>' . $requester
            . '</strong> จำนวนเงิน <strong>' . self::amount($d['total']) . ' บาท</strong> ( '
            . self::escape(self::bahtText($d['total'])) . ' ) ต่อไป</p>'
            . self::signature('', '', '')
            . self::approvalBlocks($d);
    }

    /** บล็อกท้ายบันทึก: ความเห็นหัวหน้าฝ่ายบริหารงานทั่วไป (ซ้าย) และช่องอนุมัติ (ขวา) */
    private static function approvalBlocks(array $d): string
    {
        return '<table class="d-tvm-approve"><tr>'
            . '<td>- ความเห็นของหัวหน้าฝ่ายบริหารงานทั่วไป<br>&nbsp;&nbsp;&nbsp;ได้ตรวจสอบหลักฐานถูกต้องแล้ว เห็นควรอนุมัติ'
            . '<br><br><br>ลงชื่อ ' . self::dots(26) . '<br>' . self::nameLine($d['admin_head'], 30)
            . '<br>ตำแหน่ง ' . self::valueOrDots($d['admin_head']['position'], 24) . '</td>'
            . '<td class="d-tvm-approve-right">อนุมัติ'
            . '<br><br><br><br><br><br>ลงชื่อ ' . self::dots(26) . '<br>' . self::nameLine($d['director'], 30) . '<br>ผู้อำนวยการ'
            . self::escape($d['organization']) . '</td></tr></table>';
    }

    /**
     * บันทึกข้อความขออนุญาตไปราชการนอกสำนักงาน
     *
     * เป็นเอกสารต้นทางของชุดเบิก ใช้ก่อนเดินทาง จึงดึงคณะเดินทาง ผู้รับมอบงาน
     * และแหล่งเงินจากทะเบียนโดยตรง ส่วนความเห็นหัวหน้าฝ่ายและคำสั่งเว้นไว้เซ็น
     */
    private static function permissionMemo(array $d): string
    {
        $e = [self::class, 'escape'];
        $vehicle = trim($d['vehicle_type'] . ($d['license_plate'] !== '' ? ' ทะเบียนรถ ' . $d['license_plate'] : ''));
        $days = $d['has_duration'] ? (string) ($d['days'] + ($d['hours'] > 0 ? 1 : 0)) : self::dots(4);

        $memberLines = '';
        foreach ($d['members'] as $member) {
            $memberLines .= '<p class="d-tvm-line">' . $e($member['name']) . ' ตำแหน่ง '
                . self::valueOrDots($member['position'], 30) . '</p>';
        }

        return self::memoHead($d, 'ขออนุญาตไปราชการนอกสำนักงาน')
            . '<p class="d-body">ด้วยข้าพเจ้า ' . $e($d['fullname']) . ' ตำแหน่ง ' . self::valueOrDots($d['position'], 30)
            . ($d['members'] !== [] ? ' พร้อมด้วย' : '') . '</p>'
            . $memberLines
            . '<p class="d-tvm-line">มีความประสงค์ขออนุญาตเดินทางไปราชการเพื่อ ' . $e($d['topic']) . '</p>'
            . '<p class="d-tvm-line">สถานที่ไป ' . self::valueOrDots($d['location'], 40) . '</p>'
            . '<p class="d-tvm-line">ในวันที่ ' . self::valueOrDots($d['date_start'], 16) . ' ถึงวันที่ '
            . self::valueOrDots($d['date_end'], 16) . '</p>'
            . '<p class="d-tvm-line">โดยจะออกเดินทางในวันที่ ' . self::valueOrDots($d['date_start'], 16) . ' เวลา '
            . self::valueOrDots($d['time_start'], 8) . ' น. และจะกลับในวันที่ ' . self::valueOrDots($d['date_end'], 16)
            . ' เวลา ' . self::valueOrDots($d['time_end'], 8) . ' น.</p>'
            . '<p class="d-tvm-line">รวมเวลาไปราชการ ' . $days . ' วัน เดินทางไปราชการโดย '
            . ($vehicle !== '' ? $e($vehicle) : self::dots(40)) . '</p>'
            . '<p class="d-tvm-line">เบิกค่าใช้จ่ายจาก ' . self::valueOrDots($d['budget_source'], 40) . '</p>'
            . '<p class="d-tvm-line">ในการไปราชการครั้งนี้ได้มอบหมายงานหน้าที่ให้กับ '
            . self::valueOrDots($d['assignee']['name'], 30) . '</p>'
            . '<p class="d-tvm-line">ตำแหน่ง ' . self::valueOrDots($d['assignee']['position'], 30) . ' ปฏิบัติหน้าที่แทน</p>'
            . '<p class="d-body">จึงเรียนมาเพื่อโปรดพิจารณาอนุญาต</p>'
            . '<table class="d-tvm-sign"><tr>'
            . '<td>ลงชื่อ ' . self::dots(24) . ' ผู้ขออนุญาต<br>(' . $e($d['fullname']) . ')<br>'
            . self::valueOrDots($d['position'], 26) . '</td>'
            . '<td>ลงชื่อ ' . self::dots(24) . ' ผู้รับมอบงาน<br>(' . self::valueOrDots($d['assignee']['name'], 28) . ')<br>'
            . self::valueOrDots($d['assignee']['position'], 26) . '</td></tr></table>'
            . '<table class="d-tvm-decision"><tr>'
            . '<td><strong>ความเห็นหัวหน้าฝ่าย/หัวหน้างาน</strong><br>' . self::dots(44) . '<br>' . self::dots(44)
            . '<br><br>(' . self::dots(40) . ')<br>วันที่ ....... เดือน .................. พ.ศ. ...........</td>'
            . '<td><strong>คำสั่ง</strong><br>' . self::dots(44) . '<br>' . self::dots(44)
            . '<br><br>' . self::nameLine($d['director'], 40) . '<br>ผู้อำนวยการ' . $e($d['organization'])
            . '<br>วันที่ ....... เดือน .................. พ.ศ. ...........</td></tr></table>';
    }

    /**
     * บันทึกข้อความขออนุมัติเบิกเงินบำรุงเพื่อจ่ายค่าลงทะเบียนอบรม
     *
     * ใช้เมื่อมีค่าลงทะเบียน โดยแยกยอดค่าลงทะเบียน (ตามใบเสร็จ) ออกจากค่าใช้จ่าย
     * เดินทางตามแบบ 8708 เลขที่ใบเสร็จเว้นให้กรอกเพราะทะเบียนยังไม่เก็บ
     */
    private static function registrationMemo(array $d): string
    {
        $e = [self::class, 'escape'];
        $travel = max(0, $d['total'] - $d['registration']);

        return self::memoHead($d, 'ขออนุมัติเบิกเงินบำรุงเพื่อจ่ายค่าลงทะเบียนอบรม')
            . '<p class="d-body">เนื่องด้วย ' . $e($d['fullname']) . ' ตำแหน่ง ' . self::valueOrDots($d['position'], 30) . '</p>'
            . '<p class="d-tvm-line">ปฏิบัติงานที่ฝ่าย/กลุ่มงาน ' . self::valueOrDots($d['department'], 30) . ' '
            . $e($d['organization']) . ' ได้รับอนุญาตและเดินทางไปราชการเพื่อเข้ารับการอบรม/ประชุม/สัมมนาเรื่อง</p>'
            . '<p class="d-tvm-line">' . $e($d['topic']) . '</p>'
            . '<p class="d-tvm-line">ระหว่างวันที่ ' . self::valueOrDots($d['date_start'], 16) . ' ถึงวันที่ '
            . self::valueOrDots($d['date_end'], 16) . ' จัดโดย ' . self::valueOrDots($d['organizer'], 30) . '</p>'
            . '<p class="d-body">ข้าพเจ้าใคร่ขออนุมัติเบิกเงินเพื่อจ่ายค่าเดินทางไปราชการ '
            . 'รายละเอียดตามที่แนบมาเรียนพร้อมหนังสือนี้</p>'
            . '<table class="d-tvm-amounts">'
            . '<tr><td>1. ค่าลงทะเบียนตามใบเสร็จรับเงิน เล่มที่ ' . self::dots(10) . ' เลขที่ ' . self::dots(14) . '</td>'
            . '<td class="d-tvm-amount">จำนวน ' . self::amount($d['registration']) . ' บาท</td></tr>'
            . '<tr><td>2. ค่าใช้จ่ายเดินทางไปราชการ ตามแบบ 8708</td>'
            . '<td class="d-tvm-amount">จำนวน ' . self::amount($travel) . ' บาท</td></tr>'
            . '<tr><td></td><td class="d-tvm-amount"><strong>รวมทั้งสิ้น ' . self::amount($d['total']) . ' บาท</strong></td></tr>'
            . '</table>'
            . '<p class="d-tvm-line">จำนวนเงิน ( ' . self::escape(self::bahtText($d['total'])) . ' )</p>'
            . '<p class="d-body">จึงเรียนมาเพื่อโปรดพิจารณาอนุมัติต่อไป</p>'
            . self::signature($d['fullname'], $d['position'], '')
            . self::approvalBlocks($d);
    }

    /** ตารางรายละเอียดการจ่ายที่ บก.111 กับใบหน้างบสำคัญใช้ร่วมกัน */
    private static function billTable(string $detail, string $date, float $amount): string
    {
        $blank = '';
        for ($i = 0; $i < 10; $i++) {
            $blank .= '<tr class="d-bill-blank"><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
        }

        return '<table class="d-bill-items">'
            . '<tr class="d-items-head"><td class="d-bill-c-date">วันเดือนปี</td>'
            . '<td class="d-bill-c-detail">รายละเอียดการจ่าย</td>'
            . '<td class="d-bill-c-amount">จำนวนเงิน</td><td class="d-bill-c-note">หมายเหตุ</td></tr>'
            . '<tr><td>' . $date . '</td><td>' . $detail . '</td>'
            . '<td class="d-right">' . self::amount($amount) . '</td><td>&nbsp;</td></tr>'
            . $blank
            . '<tr><td>&nbsp;</td><td class="d-c-total">รวมทั้งสิ้น</td>'
            . '<td class="d-right"><strong>' . self::amount($amount) . '</strong></td><td>&nbsp;</td></tr></table>';
    }

    private static function billFooter(array $d): string
    {
        return '<p class="d-bill-words">รวมทั้งสิ้น ( ' . self::escape(self::bahtText($d['vehicle'])) . ' )</p>'
            . '<p class="d-bill-certify">ข้าพเจ้า ' . self::escape($d['fullname']) . ' ตำแหน่ง '
            . self::valueOrDots($d['position'], 26) . ' ' . self::escape($d['organization'])
            . ' ขอรับรองว่า รายจ่ายข้างต้นนี้ ไม่อาจเรียกเก็บใบเสร็จรับเงินจากผู้รับได้'
            . ' และข้าพเจ้าได้จ่ายไปในงานของทางราชการโดยแท้จริง</p>'
            . self::signature($d['fullname'], $d['position'], '');
    }

    private static function signature(string $name, string $position, string $role): string
    {
        return '<table class="d-sign"><tr><td></td><td class="d-sign-cell">ลงชื่อ ' . self::dots(32) . ' ' . self::escape($role)
            . '<br>(' . ($name !== '' ? self::escape($name) : self::dots(34)) . ')'
            . '<br>ตำแหน่ง ' . ($position !== '' ? self::escape($position) : self::dots(28)) . '</td></tr></table>';
    }

    private static function box(bool $checked): string
    {
        return '<span class="d-8708-box">[' . ($checked ? '<strong>X</strong>' : '&nbsp;&nbsp;') . ']</span>';
    }

    private static function dots(int $length): string
    {
        return str_repeat('.', max(1, $length));
    }

    private static function valueOrDots($value, int $length): string
    {
        $value = trim((string) $value);

        return $value !== '' ? self::escape($value) : self::dots($length);
    }

    private static function thaiDate(?string $date): string
    {
        return $date ? (string) ThaiDateHelper::formatThaiDate($date, 'short') : '';
    }

    /** ยอดเงินที่ต้องแสดงเสมอแม้เป็นศูนย์ (ช่องในตารางหลักฐานการจ่าย) */
    private static function amount(float $value): string
    {
        return number_format($value, 2);
    }

    private static function money(float $amount): string
    {
        return $amount > 0 ? number_format($amount, 2) : '';
    }

    /** จำนวนเงินเป็นตัวอักษรพร้อมหน่วย รองรับเศษสตางค์ */
    private static function bahtText(float $amount): string
    {
        if ($amount <= 0) {
            return self::dots(36);
        }

        $baht = (int) floor($amount);
        $satang = (int) round(($amount - $baht) * 100);
        if ($satang >= 100) {
            $baht += 1;
            $satang = 0;
        }

        $text = ($baht > 0 ? AppHelper::convertNumberToWords($baht) : 'ศูนย์') . 'บาท';

        return $satang > 0 ? $text . AppHelper::convertNumberToWords($satang) . 'สตางค์' : $text . 'ถ้วน';
    }

    private static function escape($value): string
    {
        return Html::encode((string) $value);
    }
}
