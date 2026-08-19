<?php

namespace app\modules\hr\controllers;

use app\modules\hr\models\WorkforceFrame;
use app\modules\hr\models\WorkforceProfile;
use app\modules\hr\services\WorkforceFrameCalculator;
use app\modules\hr\services\WorkforceFrameReport;
use app\modules\plan\components\PlanHelper;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * กรอบอัตรากำลัง — หน้าแสดงผล (เฟส 2 ยังอ่านอย่างเดียว)
 *
 * แสดงกรอบที่คำนวณได้จากเกณฑ์ เทียบกับคนที่มีอยู่จริงในทะเบียนบุคลากร
 * ยังไม่เปิดให้กรอก FTE หรือกรอกทับ — จะมาในเฟสถัดไปพร้อมลำดับอนุมัติ
 *
 * สิทธิ์: ทุกคนที่ล็อกอินเข้าดูได้ เพราะหัวหน้ากลุ่มงานต้องเห็นกรอบของหน่วยตัวเอง
 * ส่วนการแก้ไขจะคุมสิทธิ์ตอนเปิดให้แก้ในเฟส 3
 */
class WorkforceFrameController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'save-fte' => ['post'],
                    'override' => ['post'],
                    'submit' => ['post'],
                    'approve' => ['post'],
                    'reopen' => ['post'],
                ],
            ],
        ];
    }

    /** แก้ตัวเลขได้เฉพาะ HR/admin และเฉพาะรอบที่ยังไม่อนุมัติ */
    private function requireEditable(WorkforceProfile $profile): void
    {
        if (!Yii::$app->user->can('hr') && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์แก้ไขกรอบอัตรากำลัง');
        }

        if ($profile->isLocked()) {
            throw new ForbiddenHttpException('รอบปี ' . $profile->thai_year . ' ' . $profile->statusLabel() . ' แล้ว แก้ไขไม่ได้');
        }
    }

    public function actionIndex()
    {
        $year = (int) ($this->request->get('thai_year') ?: PlanHelper::currentPlanYear());
        $calculator = WorkforceFrameCalculator::forYear($year);
        $rows = $calculator->results();

        $category = (string) $this->request->get('category', '');
        if ($category !== '') {
            $rows = array_values(array_filter($rows, static fn ($r) => $r['line']->category === $category));
        }

        return $this->render('index', [
            'year' => $year,
            'years' => $this->yearOptions($year),
            'profile' => $calculator->profile(),
            'rows' => $rows,
            'category' => $category,
            'summary' => $this->summarize($calculator->results()),
            'unmapped' => $calculator->unmappedPositions(),
            'outOfScope' => $calculator->outOfScopePositions(),
            'canManage' => Yii::$app->user->can('hr') || Yii::$app->user->can('admin'),
            'canApprove' => Yii::$app->user->can('director') || Yii::$app->user->can('admin'),
        ]);
    }

    /**
     * กรอบ Outsource — คนที่เกณฑ์ไม่ให้นับรวมในกรอบสายสนับสนุน Back Office
     *
     * เกณฑ์ระบุว่านับเฉพาะ 5 ประเภทการจ้าง ที่เหลือ (รายวัน รายคาบ จ้างเหมา)
     * ไปอยู่ในกรอบ Outsource แยกต่างหาก หน้านี้จึงเป็นอีกถังหนึ่งของยอดเดียวกัน
     */
    public function actionOutsource()
    {
        $year = (int) ($this->request->get('thai_year') ?: PlanHelper::currentPlanYear());
        $report = new WorkforceFrameReport($year);

        $rows = array_values(array_filter($report->rows(), static fn ($r) => $r['outsource'] > 0));
        usort($rows, static fn ($a, $b) => $b['outsource'] <=> $a['outsource']);

        return $this->render('outsource', [
            'year' => $year,
            'profile' => $report->calculator()->profile(),
            'rows' => $rows,
            'types' => $report->employmentTypes(),
            'totals' => $report->totals(),
            'outOfScope' => $report->calculator()->outOfScopePositions(),
        ]);
    }

    /** สรุปสำหรับส่ง สสจ. — ดูบนจอหรือส่งออกเป็น xlsx */
    public function actionReport()
    {
        $year = (int) ($this->request->get('thai_year') ?: PlanHelper::currentPlanYear());
        $report = new WorkforceFrameReport($year);

        if ($this->request->get('format') === 'xlsx') {
            $output = $report->saveXlsx();

            return Yii::$app->response->sendFile($output['filePath'], $output['fileName']);
        }

        return $this->render('report', [
            'year' => $year,
            'years' => $this->yearOptions($year),
            'profile' => $report->calculator()->profile(),
            'rows' => $report->rows(),
            'types' => $report->employmentTypes(),
            'totals' => $report->totals(),
        ]);
    }

    /** หน้ากรอก FTE ของสายงานที่เกณฑ์ให้โรงพยาบาลคำนวณเอง */
    public function actionFte()
    {
        $year = (int) ($this->request->get('thai_year') ?: PlanHelper::currentPlanYear());
        $calculator = WorkforceFrameCalculator::forYear($year);
        $profile = $calculator->profile();

        $rows = array_values(array_filter($calculator->results(), static fn ($r) => in_array($r['status'], [
            WorkforceFrameCalculator::STATUS_NEEDS_FTE,
            WorkforceFrameCalculator::STATUS_MANUAL_FTE,
        ], true)));

        return $this->render('fte', [
            'year' => $year,
            'profile' => $profile,
            'rows' => $rows,
            'editable' => (Yii::$app->user->can('hr') || Yii::$app->user->can('admin')) && !$profile->isLocked(),
        ]);
    }

    public function actionSaveFte()
    {
        $year = (int) $this->request->post('thai_year');
        $profile = WorkforceProfile::forYear($year);
        $this->requireEditable($profile);

        $input = (array) $this->request->post('fte', []);
        $notes = (array) $this->request->post('note', []);
        $saved = 0;
        $cleared = 0;

        foreach ($input as $lineId => $value) {
            $lineId = (int) $lineId;
            $value = trim((string) $value);
            $model = WorkforceFrame::forLine($year, $lineId);

            if ($value === '') {
                if (!$model->isNewRecord) {
                    $model->delete();
                    $cleared++;
                }
                continue;
            }

            $model->frame_qty = $value;
            $model->source = WorkforceFrame::SOURCE_MANUAL_FTE;
            $model->note = trim((string) ($notes[$lineId] ?? '')) ?: null;

            if ($model->save()) {
                $saved++;
            } else {
                Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));

                return $this->redirect(['fte', 'thai_year' => $year]);
            }
        }

        Yii::$app->session->setFlash('success', 'บันทึก FTE ' . $saved . ' สายงาน' . ($cleared > 0 ? ' · ล้างค่า ' . $cleared . ' สายงาน' : ''));

        return $this->redirect(['fte', 'thai_year' => $year]);
    }

    /** กรอกทับค่าที่ระบบคำนวณ ต้องมีเหตุผลเสมอ */
    public function actionOverride()
    {
        $year = (int) $this->request->post('thai_year');
        $lineId = (int) $this->request->post('line_id');
        $profile = WorkforceProfile::forYear($year);
        $this->requireEditable($profile);

        $model = WorkforceFrame::forLine($year, $lineId);
        $value = trim((string) $this->request->post('frame_qty', ''));

        if ($value === '') {
            if (!$model->isNewRecord) {
                $model->delete();
            }
            Yii::$app->session->setFlash('success', 'ยกเลิกการกรอกทับแล้ว — กลับไปใช้ค่าจากเกณฑ์');

            return $this->redirect(['index', 'thai_year' => $year]);
        }

        $model->frame_qty = $value;
        $model->source = WorkforceFrame::SOURCE_OVERRIDE;
        $model->override_reason = trim((string) $this->request->post('override_reason', ''));

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกการกรอกทับแล้ว');
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()));
        }

        return $this->redirect(['index', 'thai_year' => $year]);
    }

    public function actionSubmit()
    {
        $year = (int) $this->request->post('thai_year');
        $profile = WorkforceProfile::forYear($year);
        $this->requireEditable($profile);

        $profile->status = WorkforceProfile::STATUS_SUBMITTED;
        $profile->submitted_at = date('Y-m-d H:i:s');
        $profile->submitted_by = Yii::$app->user->id;
        $profile->save(false);

        Yii::$app->session->setFlash('success', 'ส่งกรอบปี ' . $year . ' ให้ผู้อำนวยการพิจารณาแล้ว');

        return $this->redirect(['index', 'thai_year' => $year]);
    }

    public function actionApprove()
    {
        $year = (int) $this->request->post('thai_year');
        $profile = WorkforceProfile::forYear($year);

        if (!Yii::$app->user->can('director') && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('เฉพาะผู้อำนวยการเท่านั้นที่อนุมัติกรอบอัตรากำลังได้');
        }

        if (!$profile->canApprove()) {
            throw new ForbiddenHttpException('รอบนี้ยังไม่ได้ส่งให้พิจารณา');
        }

        $profile->status = WorkforceProfile::STATUS_APPROVED;
        $profile->approved_at = date('Y-m-d H:i:s');
        $profile->approved_by = Yii::$app->user->id;
        $profile->approval_note = trim((string) $this->request->post('approval_note', '')) ?: null;
        $profile->save(false);

        Yii::$app->session->setFlash('success', 'อนุมัติกรอบปี ' . $year . ' แล้ว — ตัวเลขถูกล็อกไว้อ้างอิง');

        return $this->redirect(['index', 'thai_year' => $year]);
    }

    /** ตีกลับให้แก้ — ใช้เมื่อพบว่าตัวเลขผิดหลังส่งไปแล้ว */
    public function actionReopen()
    {
        $year = (int) $this->request->post('thai_year');
        $profile = WorkforceProfile::forYear($year);

        if (!Yii::$app->user->can('director') && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เปิดรอบที่ส่งไปแล้ว');
        }

        $profile->status = WorkforceProfile::STATUS_DRAFT;
        $profile->submitted_at = null;
        $profile->submitted_by = null;
        $profile->approved_at = null;
        $profile->approved_by = null;
        $profile->save(false);

        Yii::$app->session->setFlash('warning', 'เปิดรอบปี ' . $year . ' กลับมาแก้ไขได้แล้ว');

        return $this->redirect(['index', 'thai_year' => $year]);
    }

    private function summarize(array $rows): array
    {
        $summary = [
            'frame' => 0.0,
            'in_frame' => 0,
            'outsource' => 0,
            'gap' => 0.0,
            'calculated' => 0,
            'blocked' => 0,
        ];

        foreach ($rows as $row) {
            $summary['in_frame'] += $row['in_frame'];
            $summary['outsource'] += $row['outsource'];

            if ($row['status'] === WorkforceFrameCalculator::STATUS_CALCULATED) {
                $summary['calculated']++;
                $summary['frame'] += (float) $row['frame'];
                $summary['gap'] += max(0, (float) $row['gap']);
            } elseif ($row['status'] !== WorkforceFrameCalculator::STATUS_NOT_ELIGIBLE) {
                $summary['blocked']++;
            }
        }

        return $summary;
    }

    private function yearOptions(int $current): array
    {
        $years = array_map('intval', WorkforceProfile::find()->select('thai_year')->distinct()->column());
        $years[] = $current;
        $years = array_values(array_unique($years));
        rsort($years);

        return array_combine($years, $years);
    }
}
