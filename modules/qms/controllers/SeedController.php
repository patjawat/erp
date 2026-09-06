<?php

namespace app\modules\qms\controllers;

use app\components\AppHelper;
use app\modules\qms\models\Cycle;
use app\modules\qms\models\CycleItem;
use app\modules\qms\models\Requirement;
use app\modules\qms\models\Standard;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Seed ข้อมูลตัวอย่าง QMS
 *
 *   docker exec dansai php yii qms/seed          # ใส่ 9 มาตรฐานตัวอย่าง (ข้ามตัวที่มีแล้ว)
 *   docker exec dansai php yii qms/seed --wipe=1 # ล้างมาตรฐานตัวอย่างที่ยังไม่มีข้อกำหนด/รอบ ออกก่อน
 *
 * idempotent: ยึด code เป็นตัวกันซ้ำ
 */
class SeedController extends Controller
{
    public $wipe = 0;

    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), ['wipe']);
    }

    /** มาตรฐานตัวอย่างตามภาพต้นแบบ */
    private const STANDARDS = [
        ['HA', 'Hospital Accreditation', 'HA', 'คณะกรรมการ HA', '#E4572E'],
        ['PMQA', 'Public Sector Management Quality Award', 'PMQA', 'งานพัฒนาคุณภาพ', '#B5179E'],
        ['ISO9001', 'Quality Management System (ISO 9001)', 'ISO', 'งานประกันคุณภาพ', '#1D4E89'],
        ['ISO27001', 'Information Security Management (ISO 27001)', 'ISO', 'งานเทคโนโลยีสารสนเทศ', '#1B998B'],
        ['PDPA', 'Personal Data Protection Act', 'PDPA', 'งานคุ้มครองข้อมูลส่วนบุคคล', '#6A4C93'],
        ['HPH', 'Health Promoting Hospital', 'HPH', 'งานส่งเสริมสุขภาพ', '#2A9D8F'],
        ['GREENCLEAN', 'Green & Clean Hospital', 'G&C', 'งานสิ่งแวดล้อม', '#38B000'],
        ['DIGITAL', 'Digital Hospital', 'DGH', 'งานเทคโนโลยีสารสนเทศ', '#0096C7'],
        ['SMART', 'Smart Hospital', 'SMT', 'งานนวัตกรรม', '#3A0CA3'],
    ];

    public function actionIndex(): int
    {
        if ($this->wipe) {
            foreach (Standard::find()->all() as $standard) {
                if (!$standard->getRequirements()->exists() && !$standard->getCycles()->exists()) {
                    $standard->delete();
                    $this->stdout("  ลบ {$standard->code}\n");
                }
            }
        }

        $created = 0;
        $skipped = 0;
        foreach (self::STANDARDS as $i => [$code, $name, $short, $owner, $color]) {
            if (Standard::find()->where(['code' => $code])->exists()) {
                $skipped++;
                continue;
            }
            $model = new Standard([
                'code' => $code,
                'name' => $name,
                'short_name' => $short,
                'owner_label' => $owner,
                'color' => $color,
                'sort' => ($i + 1) * 10,
                'is_active' => 1,
            ]);
            if ($model->save()) {
                $created++;
                $this->stdout("  + {$code}\n");
            } else {
                $this->stderr("  ! {$code}: " . implode(' ', $model->getFirstErrors()) . "\n");
            }
        }

        $this->stdout("เสร็จ: เพิ่ม {$created} ข้าม {$skipped}\n");
        return ExitCode::OK;
    }

    /** โครงข้อกำหนด HA ตัวอย่าง (หมวด → ข้อย่อย) */
    private const HA = [
        ['I-1', 'การนำองค์กรและการจัดการ', null, [
            ['I-1.1', 'มีคำสั่งแต่งตั้งคณะกรรมการบริหารความเสี่ยง', 'คำสั่งแต่งตั้ง'],
            ['I-1.2', 'มีการประชุมทบทวนระบบคุณภาพอย่างน้อยปีละ 2 ครั้ง', 'รายงานการประชุม'],
        ]],
        ['II-1', 'ระบบงานสำคัญของโรงพยาบาล', null, [
            ['II-1.1', 'มีระบบป้องกันและควบคุมการติดเชื้อ (IC)', 'SOP/WI + บันทึกเฝ้าระวัง'],
            ['II-2.1', 'มีระบบบริหารความเสี่ยงทางคลินิก', 'ทะเบียนความเสี่ยง'],
            ['II-3.1', 'มีระบบยาที่ปลอดภัย (Medication Safety)', 'SOP + รายงานอุบัติการณ์'],
        ]],
        ['III-1', 'การดูแลผู้ป่วย', null, [
            ['III-1.1', 'มีแนวทางการดูแลผู้ป่วยตามมาตรฐานวิชาชีพ', 'CPG / แนวทางปฏิบัติ'],
        ]],
        ['IV-1', 'ผลลัพธ์การดำเนินงาน', null, [
            ['IV-1.1', 'มีการเก็บและวิเคราะห์ตัวชี้วัดคุณภาพสำคัญ', 'รายงานตัวชี้วัด'],
        ]],
    ];

    /**
     * Seed ข้อกำหนดตัวอย่างของ HA + เปิดรอบปีปัจจุบันให้ดูวงจร
     *   docker exec dansai php yii qms/seed-requirements
     */
    public function actionRequirements(string $code = 'HA'): int
    {
        $standard = Standard::find()->where(['code' => $code])->one();
        if (!$standard) {
            $this->stderr("ไม่พบมาตรฐานรหัส {$code} (รัน qms/seed ก่อน)\n");
            return ExitCode::DATAERR;
        }

        $created = 0;
        $order = 0;
        foreach (self::HA as [$secCode, $secTitle, $_, $children]) {
            $order += 10;
            $section = $this->ensureRequirement($standard->id, null, $secCode, $secTitle, null, $order, $created);
            $childOrder = 0;
            foreach ($children as [$cCode, $cTitle, $hint]) {
                $childOrder += 10;
                $this->ensureRequirement($standard->id, (int) $section->id, $cCode, $cTitle, $hint, $childOrder, $created);
            }
        }
        $this->stdout("ข้อกำหนด HA: เพิ่ม {$created} ข้อ\n");

        // เปิดรอบปีปัจจุบันให้เห็น checklist ทันที
        $fy = (int) AppHelper::YearBudget();
        $cycle = Cycle::find()->where(['standard_id' => $standard->id, 'fiscal_year' => $fy])->one();
        if (!$cycle) {
            $cycle = new Cycle(['standard_id' => $standard->id, 'fiscal_year' => $fy, 'status' => Cycle::STATUS_OPEN]);
            $cycle->save();
        }
        $existing = array_map('intval', CycleItem::find()->select('requirement_id')->where(['cycle_id' => $cycle->id])->column());
        $itemAdded = 0;
        foreach (Requirement::find()->where(['standard_id' => $standard->id, 'is_active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all() as $r) {
            if (in_array((int) $r->id, $existing, true)) {
                continue;
            }
            $item = new CycleItem([
                'cycle_id' => $cycle->id,
                'requirement_id' => $r->id,
                'title_snapshot' => $r->title,
                'status' => CycleItem::STATUS_NONE,
                'sort' => $r->sort,
            ]);
            if ($item->save()) {
                $itemAdded++;
            }
        }
        $this->stdout("เปิดรอบปี {$fy}: สร้าง checklist {$itemAdded} ข้อ\n");
        return ExitCode::OK;
    }

    /**
     * นำเข้าแม่แบบสำเร็จรูปทุกตัวเข้ามาตรฐานที่รหัสตรงกัน (idempotent)
     *   docker exec dansai php yii qms/seed/templates
     */
    public function actionTemplates(): int
    {
        foreach (\app\modules\qms\models\TemplateLibrary::all() as $key => $tpl) {
            $standard = Standard::find()->where(['code' => $key])->one();
            if (!$standard) {
                $this->stdout("ข้าม {$key}: ไม่มีมาตรฐานรหัสนี้\n");
                continue;
            }
            $created = 0;
            $order = (int) Requirement::find()->where(['standard_id' => $standard->id])->max('sort');
            foreach ($tpl['sections'] as [$secCode, $secTitle, $children]) {
                $order += 10;
                $section = $this->ensureRequirement($standard->id, null, $secCode, $secTitle, null, $order, $created);
                $childOrder = 0;
                foreach ($children as [$cCode, $cTitle, $hint]) {
                    $childOrder += 10;
                    $this->ensureRequirement($standard->id, (int) $section->id, $cCode, $cTitle, $hint, $childOrder, $created);
                }
            }
            $this->stdout("{$key}: เพิ่ม {$created} ข้อ\n");
        }
        return ExitCode::OK;
    }

    /**
     * Seed ตัวอย่างการเชื่อมโยงข้ามมาตรฐาน (HA → ISO9001/PDPA) ให้ matrix มีข้อมูล
     *   docker exec dansai php yii qms/seed/links
     */
    public function actionLinks(): int
    {
        // [รหัสข้อ HA => [ [standard_code, relation], ... ] ]
        $map = [
            'I-1.1' => [['ISO9001', 'direct'], ['PDPA', 'partial']],   // คำสั่งแต่งตั้งกรรมการ
            'I-1.2' => [['ISO9001', 'direct']],                        // ประชุมทบทวน = Management Review
            'II-2.1' => [['ISO9001', 'direct']],                       // บริหารความเสี่ยง = ISO 6.1
        ];
        $created = 0;
        foreach ($map as $haCode => $targets) {
            $req = Requirement::find()->alias('r')->innerJoinWith('standard s')
                ->where(['r.code' => $haCode, 's.code' => 'HA'])->one();
            if (!$req) {
                $this->stdout("ข้าม {$haCode}: ไม่พบข้อกำหนด HA\n");
                continue;
            }
            foreach ($targets as [$stdCode, $rel]) {
                $std = Standard::find()->where(['code' => $stdCode])->one();
                if (!$std) {
                    continue;
                }
                $exist = \app\modules\qms\models\RequirementLink::find()
                    ->where(['requirement_id' => $req->id, 'standard_id' => $std->id])->exists();
                if ($exist) {
                    continue;
                }
                $link = new \app\modules\qms\models\RequirementLink([
                    'requirement_id' => $req->id, 'standard_id' => $std->id, 'relation' => $rel,
                ]);
                if ($link->save()) {
                    $created++;
                    $this->stdout("  {$haCode} → {$stdCode} ({$rel})\n");
                }
            }
        }
        $this->stdout("เชื่อมโยง: เพิ่ม {$created} รายการ\n");
        return ExitCode::OK;
    }

    private function ensureRequirement(int $standardId, ?int $parentId, string $code, string $title, ?string $hint, int $sort, int &$created): Requirement
    {
        $model = Requirement::find()->where(['standard_id' => $standardId, 'code' => $code])->one();
        if ($model) {
            return $model;
        }
        $model = new Requirement([
            'standard_id' => $standardId,
            'parent_id' => $parentId,
            'code' => $code,
            'title' => $title,
            'evidence_hint' => $hint,
            'sort' => $sort,
            'is_active' => 1,
        ]);
        if ($model->save()) {
            $created++;
        } else {
            $this->stderr("  ! {$code}: " . implode(' ', $model->getFirstErrors()) . "\n");
        }
        return $model;
    }
}
