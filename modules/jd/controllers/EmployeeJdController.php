<?php

namespace app\modules\jd\controllers;

use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\jd\models\JdEmployee;
use app\modules\jd\models\JdEmployeeSection;
use app\modules\jd\models\JdEmployeeAcknowledgement;
use app\modules\jd\components\RichText;
use app\modules\jd\models\JdChangeRequest;
use app\modules\jd\models\JdTemplate;
use app\modules\jd\models\JdTemplateBlock;
use app\modules\jd\services\JdApprovalService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\components\SiteHelper;

class EmployeeJdController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'load-template' => ['POST'],
                    'create-draft' => ['POST'],
                    'import-template' => ['POST'],
                    'acknowledge' => ['POST'],
                    'activate' => ['POST'],
                    'sign' => ['POST'],
                    'delete-section' => ['POST'],
                    'cancel-review' => ['POST'],
                    'resolve-review' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionInbox()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            throw new NotFoundHttpException('ไม่พบบัญชีบุคลากรของผู้ใช้งาน');
        }

        $rows = \app\modules\approveV2\models\Approve::find()
            ->where([
                'name' => JdApprovalService::APPROVE_NAME,
                'emp_id' => (int) $me->id,
                'status' => 'Pending',
            ])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        $items = [];
        foreach ($rows as $row) {
            $jd = JdEmployee::find()->where(['id' => (int) $row->from_id])->with('employee')->one();
            if ($jd && $jd->status === JdEmployee::STATUS_PENDING) {
                $items[] = ['approve' => $row, 'jd' => $jd];
            }
        }

        return $this->render('inbox', [
            'employee' => $me,
            'items' => $items,
        ]);
    }

    public function actionView($emp_id, $id = null)
    {
        $employee = $this->findEmployee($emp_id);
        $jd = $id
            ? JdEmployee::find()->where(['id' => (int) $id, 'emp_id' => $emp_id])->with(['sections', 'template', 'approvalRows.employee'])->one()
            : JdEmployee::findCurrent((int) $emp_id);
        if ($id && !$jd) {
            throw new NotFoundHttpException('ไม่พบ JD ฉบับที่ต้องการ');
        }
        if (!$jd) {
            $jd = new JdEmployee(['emp_id' => (int) $emp_id]);
        }
        $this->assertCanView($employee, $jd);

        return $this->render('view', [
            'employee' => $employee,
            'jd' => $jd,
            'templateForPosition' => $this->findTemplateFor($employee),
        ]);
    }

    public function actionLoadTemplate($emp_id)
    {
        $employee = $this->findEmployee($emp_id);
        $this->assertCanManage();
        $existingDraft = JdEmployee::find()
            ->where(['emp_id' => $emp_id, 'status' => [JdEmployee::STATUS_DRAFT, JdEmployee::STATUS_PENDING]])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        if ($existingDraft) {
            Yii::$app->session->setFlash('warning', 'พนักงานคนนี้มี JD ฉบับร่างอยู่แล้ว กรุณาตรวจสอบหรือประกาศใช้ฉบับเดิมก่อน');
            return $this->redirect(['view', 'emp_id' => $emp_id, 'id' => $existingDraft->id]);
        }
        $templateId = (int) (Yii::$app->request->post('template_id') ?? Yii::$app->request->get('template_id'));
        $template = $templateId
            ? JdTemplate::find()->where(['id' => $templateId])->with(['sections', 'blocks'])->one()
            : $this->findTemplateFor($employee);

        if (!$template) {
            Yii::$app->session->setFlash('error', 'ไม่พบ Template สำหรับตำแหน่งนี้');
            return $this->redirect(['view', 'emp_id' => $emp_id]);
        }
        JdTemplateBlock::ensureForTemplate((int) $template->id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $previous = JdEmployee::findCurrent((int) $emp_id);
            $jd = new JdEmployee([
                'emp_id' => (int) $emp_id,
                'template_id' => (int) $template->id,
                'revision_no' => (int) JdEmployee::find()->where(['emp_id' => $emp_id])->max('revision_no') + 1,
                'status' => JdEmployee::STATUS_DRAFT,
                'supersedes_id' => $previous?->id,
                'position_code' => (string) ($employee->position_name ?? ''),
                'position_title' => strip_tags((string) $employee->positionName()),
                'department_code' => (string) ($employee->department ?? ''),
                'department_title' => (string) $employee->departmentName(),
            ]);
            if (!$jd->save()) {
                throw new \RuntimeException(implode(', ', $jd->getFirstErrors()));
            }

            $templateBlocks = $template->getBlocks()->all();
            if ($templateBlocks) {
                foreach ($templateBlocks as $block) {
                    $section = new JdEmployeeSection([
                        'jd_employee_id' => $jd->id,
                        'section_code' => $block->section_code,
                        'title' => $block->title,
                        'block_type' => $block->block_type,
                        'data_json' => $block->data_json,
                        'sort_order' => $block->sort_order,
                    ]);
                    if (!$section->save()) {
                        throw new \RuntimeException(implode(', ', $section->getFirstErrors()));
                    }
                }
            } else {
                foreach ($template->sections as $source) {
                    $section = new JdEmployeeSection([
                        'jd_employee_id' => $jd->id,
                        'title' => $source->title,
                        'block_type' => 'prose',
                        'content' => $source->content,
                        'sort_order' => $source->sort_order,
                    ]);
                    $section->save(false);
                }
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        Yii::$app->session->setFlash('success', 'สร้าง JD ฉบับที่ ' . $jd->revision_no . ' จาก Template แล้ว กรุณาตรวจสอบก่อนประกาศใช้');
        return $this->redirect(['view', 'emp_id' => $emp_id, 'id' => $jd->id]);
    }

    public function actionCreateDraft($emp_id)
    {
        $employee = $this->findEmployee($emp_id);
        $this->assertCanManage();
        $existingDraft = JdEmployee::find()
            ->where(['emp_id' => $emp_id, 'status' => [JdEmployee::STATUS_DRAFT, JdEmployee::STATUS_PENDING]])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        if ($existingDraft) {
            return $this->redirect(['view', 'emp_id' => $emp_id, 'id' => $existingDraft->id]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $current = JdEmployee::findCurrent((int) $emp_id);
            $jd = new JdEmployee([
                'emp_id' => (int) $emp_id,
                'revision_no' => (int) JdEmployee::find()->where(['emp_id' => $emp_id])->max('revision_no') + 1,
                'status' => JdEmployee::STATUS_DRAFT,
                'supersedes_id' => $current?->id,
                'position_code' => (string) ($employee->position_name ?? ''),
                'position_title' => strip_tags((string) $employee->positionName()),
                'department_code' => (string) ($employee->department ?? ''),
                'department_title' => (string) $employee->departmentName(),
            ]);
            if (!$jd->save()) {
                throw new \RuntimeException(implode(', ', $jd->getFirstErrors()));
            }
            $order = 10;
            foreach (JdTemplateBlock::definitions() as $code => [$title, $type]) {
                $section = new JdEmployeeSection([
                    'jd_employee_id' => $jd->id,
                    'section_code' => $code,
                    'title' => $title,
                    'block_type' => $type,
                    'data_json' => json_encode(['intro' => '', 'items' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'sort_order' => $order,
                ]);
                $section->save(false);
                $order += 10;
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        Yii::$app->session->setFlash('success', 'สร้าง JD ฉบับร่างพร้อมโครงสร้างมาตรฐานแล้ว');
        return $this->redirect(['view', 'emp_id' => $emp_id, 'id' => $jd->id]);
    }

    public function actionSelectTemplate($id)
    {
        $this->assertCanManage();
        $jd = $this->findDraft($id);
        $employee = $this->findEmployee($jd->emp_id);
        $templates = JdTemplate::find()
            ->where(['is_active' => 1])
            ->with('positionName')
            ->orderBy(new \yii\db\Expression('CASE WHEN position_code = :position THEN 0 ELSE 1 END', [':position' => $employee->position_name]))
            ->addOrderBy(['position_code' => SORT_ASC, 'revision_no' => SORT_DESC, 'name' => SORT_ASC])
            ->all();
        return $this->render('select-template', compact('jd', 'employee', 'templates'));
    }

    public function actionImportTemplate($id)
    {
        $this->assertCanManage();
        $jd = $this->findDraft($id);
        $templateId = (int) Yii::$app->request->post('template_id');
        $template = JdTemplate::find()->where(['id' => $templateId, 'is_active' => 1])->with(['blocks', 'sections'])->one();
        if (!$template) {
            Yii::$app->session->setFlash('error', 'ไม่พบ Template ที่เลือก');
            return $this->redirect(['select-template', 'id' => $jd->id]);
        }
        JdTemplateBlock::ensureForTemplate((int) $template->id);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            JdEmployeeSection::deleteAll(['jd_employee_id' => $jd->id]);
            $this->copyTemplateSections($template, $jd);
            $jd->template_id = $template->id;
            $jd->save(false);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        Yii::$app->session->setFlash('success', 'นำเข้า Template “' . $template->name . '” ลงในฉบับร่างแล้ว');
        return $this->redirect(['view', 'emp_id' => $jd->emp_id, 'id' => $jd->id]);
    }

    public function actionActivate($id)
    {
        $this->assertCanManage();
        $jd = JdEmployee::find()->with('sections')->where(['id' => $id])->one();
        if (!$jd || $jd->status !== JdEmployee::STATUS_DRAFT) {
            throw new NotFoundHttpException('ไม่พบ JD ฉบับร่าง');
        }
        if (!$jd->sections) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถประกาศใช้ JD ที่ไม่มีรายละเอียดได้');
            return $this->redirect(['view', 'emp_id' => $jd->emp_id, 'id' => $jd->id]);
        }
        $requiredCodes = array_keys(JdTemplateBlock::definitions());
        $sectionCodes = array_values(array_filter(array_map(static fn($section) => $section->section_code, $jd->sections)));
        $missingCodes = array_diff($requiredCodes, $sectionCodes);
        if ($missingCodes) {
            Yii::$app->session->setFlash('error', 'โครงสร้าง JD ยังไม่ครบ กรุณาสร้างฉบับร่างใหม่หรือนำเข้า Template อีกครั้งก่อนประกาศใช้');
            return $this->redirect(['view', 'emp_id' => $jd->emp_id, 'id' => $jd->id]);
        }

        $effectiveFrom = Yii::$app->request->post('effective_from') ?: date('Y-m-d');
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $effectiveFrom);
        if (!$date || $date->format('Y-m-d') !== $effectiveFrom) {
            Yii::$app->session->setFlash('error', 'วันที่เริ่มมีผลไม่ถูกต้อง');
            return $this->redirect(['view', 'emp_id' => $jd->emp_id, 'id' => $jd->id]);
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $jd->effective_from = $effectiveFrom;
            $jd->save(false);
            (new JdApprovalService())->start($jd);
            $transaction->commit();
        } catch (\DomainException $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect(['view', 'emp_id' => $jd->emp_id, 'id' => $jd->id]);
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        Yii::$app->session->setFlash('success', 'ส่ง JD Revision ' . $jd->revision_no . ' เข้าสู่กระบวนการลงนามแล้ว');
        return $this->redirect(['view', 'emp_id' => $jd->emp_id, 'id' => $jd->id]);
    }

    public function actionSign($id)
    {
        $jd = JdEmployee::findOne($id);
        if (!$jd || $jd->status !== JdEmployee::STATUS_PENDING) {
            throw new NotFoundHttpException('ไม่พบ JD ที่รอลงนาม');
        }
        $employee = UserHelper::GetEmployee();
        if (!$employee) {
            throw new NotFoundHttpException('ไม่พบบัญชีบุคลากรของผู้ใช้งาน');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $published = (new JdApprovalService())->sign($jd, $employee);
            $transaction->commit();
        } catch (\DomainException $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect(['view', 'emp_id' => $jd->emp_id, 'id' => $jd->id]);
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        Yii::$app->session->setFlash('success', $published
            ? 'ลงนามครบทุกลำดับและประกาศใช้ JD Revision ' . $jd->revision_no . ' แล้ว'
            : 'ลงนามเรียบร้อย ระบบส่งต่อให้ผู้ลงนามลำดับถัดไปแล้ว');
        return $this->redirect(['view', 'emp_id' => $jd->emp_id, 'id' => $jd->id]);
    }

    public function actionPdf($id)
    {
        $jd = JdEmployee::find()
            ->where(['id' => (int) $id])
            ->with(['employee', 'sections', 'approvalRows.employee'])
            ->one();
        if (!$jd) {
            throw new NotFoundHttpException('ไม่พบเอกสาร JD ที่ต้องการพิมพ์');
        }
        $this->assertCanView($jd->employee, $jd);

        $fontPath = Yii::getAlias('@webroot/fonts/THSarabunNew');
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 12,
            'margin_bottom' => 14,
            'fontDir' => array_merge($defaultConfig['fontDir'], [$fontPath]),
            'fontdata' => $defaultFontConfig['fontdata'] + [
                'thsarabunnew' => [
                    'R' => 'THSarabunNew.ttf',
                    'B' => 'THSarabunNew-Bold.ttf',
                    'I' => 'THSarabunNew-Italic.ttf',
                    'BI' => 'THSarabunNew BoldItalic.ttf',
                ],
            ],
            'default_font' => 'thsarabunnew',
            'tempDir' => Yii::getAlias('@runtime/mpdf'),
        ]);
        $mpdf->SetTitle('JD - ' . $jd->employee->fullname);
        $mpdf->SetAuthor((string) (SiteHelper::getInfo()['company_name'] ?? 'ERP Hospital'));
        $mpdf->SetHTMLFooter(
            '<table style="width:100%;border-top:0.3mm solid #777;font-size:10pt;color:#555">'
            . '<tr><td>JD Revision ' . (int) $jd->revision_no . '</td>'
            . '<td style="text-align:right">หน้า {PAGENO} จาก {nbpg}</td></tr></table>'
        );
        $pdfHtml = $this->renderPartial('_pdf', [
            'jd' => $jd,
            'employee' => $jd->employee,
            'siteInfo' => SiteHelper::getInfo(),
            'logoPath' => Yii::getAlias('@webroot/images/Logo-moph.png'),
        ]);
        $mpdf->WriteHTML($pdfHtml);

        $safeName = preg_replace('/[^a-zA-Z0-9_-]+/', '_', 'JD_' . $jd->emp_id . '_R' . $jd->revision_no);
        return $mpdf->Output($safeName . '.pdf', \Mpdf\Output\Destination::INLINE);
    }

    public function actionAcknowledge($id)
    {
        $jd = JdEmployee::findCurrent((int) (JdEmployee::findOne($id)?->emp_id ?? 0));
        if (!$jd || (int) $jd->id !== (int) $id) {
            throw new NotFoundHttpException('ไม่พบ JD ฉบับปัจจุบัน');
        }
        $employee = $this->assertOwner($jd);
        $openRequest = JdChangeRequest::find()->where([
            'jd_employee_id' => $jd->id,
            'emp_id' => $employee->id,
            'status' => JdChangeRequest::openStatuses(),
        ])->exists();
        if ($openRequest) {
            Yii::$app->session->setFlash('warning', 'ยังลงนามรับทราบไม่ได้ เนื่องจากมีคำขอทบทวนที่กำลังดำเนินการ');
            return $this->redirect(['/hr/employees/view', 'id' => $employee->id, 'name' => 'job_description_current']);
        }
        if (!JdEmployeeAcknowledgement::find()->where(['jd_employee_id' => $jd->id, 'emp_id' => $employee->id])->exists()) {
            $ack = new JdEmployeeAcknowledgement([
                'jd_employee_id' => $jd->id,
                'emp_id' => $employee->id,
                'user_id' => (int) Yii::$app->user->id,
                'employee_name' => $employee->fullname,
                'acknowledged_at' => date('Y-m-d H:i:s'),
                'ip_address' => Yii::$app->request->userIP,
                'user_agent' => mb_substr((string) Yii::$app->request->userAgent, 0, 255),
            ]);
            if (!$ack->save()) {
                throw new \RuntimeException(implode(', ', $ack->getFirstErrors()));
            }
        }
        Yii::$app->session->setFlash('success', 'ลงนามรับทราบ JD Revision ' . $jd->revision_no . ' เรียบร้อยแล้ว');
        return $this->redirect(['/hr/employees/view', 'id' => $employee->id, 'name' => 'job_description_current']);
    }

    public function actionRequestReview($id)
    {
        $jd = JdEmployee::findOne($id);
        if (!$jd || (int) (JdEmployee::findCurrent((int) $jd->emp_id)?->id ?? 0) !== (int) $jd->id) {
            throw new NotFoundHttpException('ไม่พบ JD ฉบับปัจจุบัน');
        }
        $employee = $this->assertOwner($jd);
        if (JdEmployeeAcknowledgement::find()->where(['jd_employee_id' => $jd->id, 'emp_id' => $employee->id])->exists()) {
            Yii::$app->session->setFlash('warning', 'JD ฉบับนี้ลงนามรับทราบแล้ว หากต้องการเปลี่ยนแปลงกรุณาติดต่อ HR');
            return $this->redirect(['/hr/employees/view', 'id' => $employee->id, 'name' => 'job_description_current']);
        }
        $existing = JdChangeRequest::find()->where([
            'jd_employee_id' => $jd->id,
            'emp_id' => $employee->id,
            'status' => JdChangeRequest::openStatuses(),
        ])->one();
        if ($existing) {
            Yii::$app->session->setFlash('info', 'มีคำขอทบทวนที่กำลังดำเนินการอยู่แล้ว');
            return $this->redirect(['/hr/employees/view', 'id' => $employee->id, 'name' => 'job_description_current']);
        }

        $request = new JdChangeRequest([
            'jd_employee_id' => $jd->id,
            'emp_id' => $employee->id,
            'user_id' => (int) Yii::$app->user->id,
            'submitted_at' => date('Y-m-d H:i:s'),
            'status' => JdChangeRequest::STATUS_SUBMITTED,
        ]);
        if ($request->load(Yii::$app->request->post())) {
            $section = $request->section_id ? JdEmployeeSection::findOne(['id' => $request->section_id, 'jd_employee_id' => $jd->id]) : null;
            $request->section_id = $section?->id;
            $request->section_code = $section?->section_code;
            $request->section_title = $section?->title;
            if ($request->save()) {
                Yii::$app->session->setFlash('success', 'ส่งคำขอทบทวน JD ให้ HR แล้ว');
                return $this->redirect(['/hr/employees/view', 'id' => $employee->id, 'name' => 'job_description_current']);
            }
        }
        return $this->render('request-review', compact('jd', 'employee', 'request'));
    }

    /** เจ้าของยกเลิกคำขอทบทวนของตนเอง → กลับมาลงนามรับทราบได้ */
    public function actionCancelReview($id)
    {
        $request = JdChangeRequest::findOne($id);
        if (!$request) {
            throw new NotFoundHttpException('ไม่พบคำขอทบทวน');
        }
        $me = UserHelper::GetEmployee();
        if (!$me || (int) $request->emp_id !== (int) $me->id) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        if (in_array($request->status, JdChangeRequest::openStatuses(), true)) {
            $request->status = JdChangeRequest::STATUS_CANCELLED;
            $request->reviewed_at = date('Y-m-d H:i:s');
            $request->save(false);
            Yii::$app->session->setFlash('success', 'ยกเลิกคำขอทบทวนแล้ว — กลับมาลงนามรับทราบ JD ได้');
        } else {
            Yii::$app->session->setFlash('info', 'คำขอนี้ถูกดำเนินการไปแล้ว');
        }
        return $this->redirect(['/hr/employees/view', 'id' => $request->emp_id, 'name' => 'job_description_current']);
    }

    /** HR: รายการคำขอทบทวน JD ที่ยังเปิดอยู่ */
    public function actionReviewInbox()
    {
        $this->assertCanManage();
        $requests = JdChangeRequest::find()
            ->where(['status' => JdChangeRequest::openStatuses()])
            ->orderBy(['submitted_at' => SORT_ASC])
            ->all();
        return $this->render('review-inbox', ['requests' => $requests]);
    }

    /** HR: รับ/ไม่รับคำขอทบทวน (decision=accept|reject) */
    public function actionResolveReview($id)
    {
        $this->assertCanManage();
        $request = JdChangeRequest::findOne($id);
        if (!$request) {
            throw new NotFoundHttpException('ไม่พบคำขอทบทวน');
        }
        if (!in_array($request->status, JdChangeRequest::openStatuses(), true)) {
            Yii::$app->session->setFlash('info', 'คำขอนี้ถูกดำเนินการไปแล้ว');
            return $this->redirect(['review-inbox']);
        }
        $decision = (string) Yii::$app->request->post('decision');
        $request->reviewed_by = (int) Yii::$app->user->id;
        $request->reviewed_at = date('Y-m-d H:i:s');
        $request->resolution_note = trim((string) Yii::$app->request->post('resolution_note')) ?: null;

        if ($decision === 'reject') {
            $request->status = JdChangeRequest::STATUS_REJECTED;
            $request->save(false);
            Yii::$app->session->setFlash('success', 'ไม่รับคำขอทบทวน — เจ้าของกลับมาลงนามรับทราบ JD ฉบับเดิมได้');
            return $this->redirect(['review-inbox']);
        }

        // accept → สร้าง JD ฉบับร่างใหม่ (คัดลอกจากฉบับปัจจุบัน) ให้ HR แก้ไข
        $empId = (int) $request->emp_id;
        $existingDraft = JdEmployee::find()
            ->where(['emp_id' => $empId, 'status' => [JdEmployee::STATUS_DRAFT, JdEmployee::STATUS_PENDING]])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        if ($existingDraft) {
            $request->status = JdChangeRequest::STATUS_ACCEPTED;
            $request->new_jd_employee_id = (int) $existingDraft->id;
            $request->save(false);
            Yii::$app->session->setFlash('info', 'มีฉบับร่างอยู่แล้ว — เปิดให้แก้ไขต่อ');
            return $this->redirect(['view', 'emp_id' => $empId, 'id' => $existingDraft->id]);
        }

        $employee = $this->findEmployee($empId);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $current = JdEmployee::findCurrent($empId);
            $jd = new JdEmployee([
                'emp_id' => $empId,
                'revision_no' => (int) JdEmployee::find()->where(['emp_id' => $empId])->max('revision_no') + 1,
                'status' => JdEmployee::STATUS_DRAFT,
                'supersedes_id' => $current?->id,
                'position_code' => (string) ($employee->position_name ?? ''),
                'position_title' => strip_tags((string) $employee->positionName()),
                'department_code' => (string) ($employee->department ?? ''),
                'department_title' => (string) $employee->departmentName(),
            ]);
            if (!$jd->save()) {
                throw new \RuntimeException(implode(', ', $jd->getFirstErrors()));
            }
            if ($current) {
                foreach ($current->sections as $src) {
                    $section = new JdEmployeeSection([
                        'jd_employee_id' => $jd->id,
                        'section_code' => $src->section_code,
                        'title' => $src->title,
                        'content' => $src->content,
                        'block_type' => $src->block_type,
                        'data_json' => $src->data_json,
                        'sort_order' => $src->sort_order,
                    ]);
                    $section->save(false);
                }
            }
            $request->status = JdChangeRequest::STATUS_ACCEPTED;
            $request->new_jd_employee_id = (int) $jd->id;
            $request->save(false);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        Yii::$app->session->setFlash('success', 'รับคำขอทบทวน — สร้าง JD ฉบับร่างใหม่แล้ว กรุณาแก้ไขและส่งลงนาม');
        return $this->redirect(['view', 'emp_id' => $empId, 'id' => $jd->id]);
    }

    public function actionAddSection($emp_id)
    {
        $employee = $this->findEmployee($emp_id);
        $this->assertCanManage();
        $jd = JdEmployee::find()->where(['emp_id' => $emp_id, 'status' => JdEmployee::STATUS_DRAFT])->orderBy(['id' => SORT_DESC])->one();
        if (!$jd) {
            $jd = new JdEmployee([
                'emp_id' => (int) $emp_id,
                'revision_no' => (int) JdEmployee::find()->where(['emp_id' => $emp_id])->max('revision_no') + 1,
                'status' => JdEmployee::STATUS_DRAFT,
                'position_code' => (string) ($employee->position_name ?? ''),
                'position_title' => strip_tags((string) $employee->positionName()),
                'department_code' => (string) ($employee->department ?? ''),
                'department_title' => (string) $employee->departmentName(),
            ]);
            $jd->save(false);
        }

        $section = new JdEmployeeSection([
            'jd_employee_id' => $jd->id,
            'sort_order' => (int) JdEmployeeSection::find()->where(['jd_employee_id' => $jd->id])->max('sort_order') + 1,
        ]);
        if ($section->load(Yii::$app->request->post())) {
            $payloadJson = Yii::$app->request->post('section_payload');
            if ($payloadJson !== null && $section->section_code) {
                $payload = json_decode((string) $payloadJson, true);
                if (!is_array($payload)) {
                    $section->addError('data_json', 'รูปแบบข้อมูลหัวข้อไม่ถูกต้อง');
                } else {
                    $payload['intro'] = RichText::sanitize($payload['intro'] ?? '');
                    foreach (($payload['items'] ?? []) as &$item) {
                        if (!is_array($item)) {
                            $item = [];
                            continue;
                        }
                        foreach ($item as $key => $value) {
                            if ($key !== 'employee_id') {
                                $item[$key] = RichText::sanitize((string) $value);
                            }
                        }
                    }
                    unset($item);
                    $section->setData($payload);
                }
            } else {
                $section->content = RichText::sanitize($section->content);
            }
        }
        if (!$section->hasErrors() && Yii::$app->request->isPost && $section->save()) {
            Yii::$app->session->setFlash('success', 'เพิ่มหัวข้อแล้ว');
            return $this->redirect(['view', 'emp_id' => $emp_id, 'id' => $jd->id]);
        }
        return $this->render('add-section', compact('employee', 'jd', 'section'));
    }

    public function actionUpdateSection($id)
    {
        $this->assertCanManage();
        $section = JdEmployeeSection::findOne($id);
        $editableStatuses = [JdEmployee::STATUS_DRAFT, JdEmployee::STATUS_ACTIVE];
        if (!$section || !$section->jdEmployee || !in_array($section->jdEmployee->status, $editableStatuses, true)) {
            throw new NotFoundHttpException('JD ที่อยู่ระหว่างรอลงนามหรือถูกยกเลิกแล้วแก้ไขไม่ได้');
        }
        $jd = $section->jdEmployee;
        $employee = $jd->employee;
        if ($section->load(Yii::$app->request->post())) {
            $payloadJson = Yii::$app->request->post('section_payload');
            if ($payloadJson !== null && $section->section_code) {
                $payload = json_decode((string) $payloadJson, true);
                if (!is_array($payload)) {
                    $section->addError('data_json', 'รูปแบบข้อมูลหัวข้อไม่ถูกต้อง');
                } else {
                    $payload['intro'] = RichText::sanitize($payload['intro'] ?? '');
                    foreach (($payload['items'] ?? []) as &$item) {
                        if (!is_array($item)) {
                            $item = [];
                            continue;
                        }
                        foreach ($item as $key => $value) {
                            if ($key !== 'employee_id') {
                                $item[$key] = RichText::sanitize((string) $value);
                            }
                        }
                    }
                    unset($item);
                    $section->setData($payload);
                }
            } else {
                $section->content = RichText::sanitize($section->content);
            }
        }
        if (!$section->hasErrors() && Yii::$app->request->isPost && $section->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกหัวข้อแล้ว');
            return $this->redirect(['view', 'emp_id' => $jd->emp_id, 'id' => $jd->id]);
        }
        return $this->render('update-section', compact('employee', 'jd', 'section'));
    }

    public function actionDeleteSection($id)
    {
        $this->assertCanManage();
        $section = JdEmployeeSection::findOne($id);
        if (!$section || !$section->jdEmployee || $section->jdEmployee->status !== JdEmployee::STATUS_DRAFT) {
            throw new NotFoundHttpException('JD ที่ประกาศใช้แล้วลบหัวข้อไม่ได้');
        }
        $jd = $section->jdEmployee;
        $section->delete();
        Yii::$app->session->setFlash('success', 'ลบหัวข้อแล้ว');
        return $this->redirect(['view', 'emp_id' => $jd->emp_id, 'id' => $jd->id]);
    }

    protected function findTemplateFor(Employees $employee): ?JdTemplate
    {
        if (!$employee->position_name) {
            return null;
        }
        return JdTemplate::find()
            ->where(['position_code' => $employee->position_name, 'is_active' => 1])
            ->with(['sections', 'blocks'])
            ->orderBy(new \yii\db\Expression("CASE WHEN lifecycle_status = 'active' THEN 0 ELSE 1 END"))
            ->addOrderBy([
                'revision_no' => SORT_DESC,
                'effective_date' => SORT_DESC,
                'id' => SORT_DESC,
            ])
            ->one();
    }

    protected function findDraft($id): JdEmployee
    {
        $jd = JdEmployee::findOne(['id' => $id, 'status' => JdEmployee::STATUS_DRAFT]);
        if (!$jd) {
            throw new NotFoundHttpException('ไม่พบ JD ฉบับร่าง');
        }
        return $jd;
    }

    protected function copyTemplateSections(JdTemplate $template, JdEmployee $jd): void
    {
        $templateBlocks = $template->getBlocks()->all();
        if ($templateBlocks) {
            foreach ($templateBlocks as $block) {
                $section = new JdEmployeeSection([
                    'jd_employee_id' => $jd->id,
                    'section_code' => $block->section_code,
                    'title' => $block->title,
                    'block_type' => $block->block_type,
                    'data_json' => $block->data_json,
                    'sort_order' => $block->sort_order,
                ]);
                if (!$section->save()) {
                    throw new \RuntimeException(implode(', ', $section->getFirstErrors()));
                }
            }
            return;
        }
        foreach ($template->sections as $source) {
            $section = new JdEmployeeSection([
                'jd_employee_id' => $jd->id,
                'title' => $source->title,
                'block_type' => 'prose',
                'content' => $source->content,
                'sort_order' => $source->sort_order,
            ]);
            $section->save(false);
        }
    }

    protected function findEmployee($id): Employees
    {
        $model = Employees::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบพนักงาน');
        }
        return $model;
    }

    protected function assertCanView(Employees $employee, ?JdEmployee $jd = null): void
    {
        $me = UserHelper::GetEmployee();
        $isAssignedSigner = $jd && !$jd->isNewRecord && $me
            ? \app\modules\approveV2\models\Approve::find()->where([
                'name' => JdApprovalService::APPROVE_NAME,
                'from_id' => (string) $jd->id,
                'emp_id' => (int) $me->id,
            ])->exists()
            : false;
        if ((!$me || (int) $employee->id !== (int) $me->id)
            && !$isAssignedSigner
            && !Yii::$app->user->can('hr')
            && !Yii::$app->user->can('admin')) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function assertCanManage(): void
    {
        if (!Yii::$app->user->can('hr') && !Yii::$app->user->can('admin')) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function assertOwner(JdEmployee $jd): Employees
    {
        $me = UserHelper::GetEmployee();
        if (!$me || (int) $me->id !== (int) $jd->emp_id) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $me;
    }
}
