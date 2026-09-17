<?php

namespace app\modules\ha12\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\ha12\models\Ha12Activity;
use app\modules\ha12\models\Ha12Audit;
use app\modules\ha12\models\Ha12Review;
use app\modules\ha12\models\Ha12ReviewForm;
use app\modules\ha12\models\Ha12ReviewVersion;
use app\modules\ha12\services\Ha12ReviewService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * นำเข้าการทบทวน (กิจกรรมทั่วไป) จาก Excel — เฟส 5
 *
 * รองรับการย้ายข้อมูลเดิม/บันทึกทีละหลายรายการ:
 * - ดาวน์โหลดแม่แบบตามฟิลด์ของกิจกรรม
 * - อัปโหลด xlsx → สร้าง ha12_review (กันซ้ำด้วย source_ref, เก็บ source_system=import)
 *
 * เขียน = ผู้ดูแล/ทีมคุณภาพ (การย้ายข้อมูลจริงจาก Google Sheets เดิมทำผ่านช่องทางนี้
 * เมื่อ export เป็น xlsx ตามแม่แบบแล้ว)
 */
class ImportController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['upload' => ['POST']]],
        ]);
    }

    private function assertManager(): void
    {
        if (!Ha12ReviewService::isManager()) {
            throw new ForbiddenHttpException('เฉพาะผู้ดูแล/ทีมคุณภาพเท่านั้น');
        }
    }

    private function generalActivities(): array
    {
        return array_values(array_filter(Ha12Activity::activeList(), static fn ($a) => Ha12ReviewForm::isSupported((int) $a->no)));
    }

    /** คอลัมน์ของแม่แบบ (label เรียงตามลำดับ) สำหรับกิจกรรม */
    private function templateColumns(Ha12Activity $activity): array
    {
        $cols = ['รหัสอ้างอิงเดิม (ถ้ามี)', 'วันที่ทบทวน (วว/ดด/พ.ศ.)'];
        foreach (Ha12ReviewForm::fieldsFor((int) $activity->no) as $f) {
            $cols[] = $f['label'];
        }
        $cols[] = 'ผู้ทบทวน';
        return $cols;
    }

    public function actionIndex()
    {
        $this->assertManager();
        $activities = $this->generalActivities();
        $activityId = (int) Yii::$app->request->get('activity_id') ?: (int) ($activities[0]->id ?? 0);
        $me = UserHelper::GetEmployee();
        return $this->render('index', [
            'activities' => $activities,
            'activityId' => $activityId,
            'fiscalYear' => (int) AppHelper::YearBudget(),
            'years' => range((int) AppHelper::YearBudget() + 1, (int) AppHelper::YearBudget() - 3),
            'units' => Ha12ReviewService::selectableUnits($me ? (int) $me->department : null),
        ]);
    }

    /** ดาวน์โหลดแม่แบบ xlsx ของกิจกรรม */
    public function actionTemplate($activity_id)
    {
        $this->assertManager();
        $activity = Ha12Activity::findOne((int) $activity_id);
        if (!$activity || !Ha12ReviewForm::isSupported((int) $activity->no)) {
            throw new NotFoundHttpException('ไม่พบกิจกรรม (รองรับเฉพาะกิจกรรมทั่วไป)');
        }
        $cols = $this->templateColumns($activity);

        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('แม่แบบนำเข้า');
        $col = 1;
        foreach ($cols as $label) {
            $letter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($letter . '1', $label);
            $sheet->getColumnDimension($letter)->setWidth(24);
            $col++;
        }
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
        // แถวคำอธิบายตัวอย่าง
        $sheet->setCellValue('A2', '(เว้นว่างได้)');

        $tmp = tempnam(sys_get_temp_dir(), 'ha12-tpl-');
        (new Xlsx($ss))->save($tmp);
        return Yii::$app->response
            ->sendFile($tmp, 'ha12-import-template-act' . $activity->no . '.xlsx')
            ->on(Response::EVENT_AFTER_SEND, static function () use ($tmp) {
                @unlink($tmp);
            });
    }

    /** อัปโหลด + นำเข้า */
    public function actionUpload($activity_id)
    {
        $this->assertManager();
        $activity = Ha12Activity::findOne((int) $activity_id);
        if (!$activity || !Ha12ReviewForm::isSupported((int) $activity->no)) {
            throw new NotFoundHttpException('ไม่พบกิจกรรม');
        }
        $me = UserHelper::GetEmployee();
        $fy = (int) Yii::$app->request->post('fiscal_year') ?: (int) AppHelper::YearBudget();
        $unitId = (int) Yii::$app->request->post('owner_unit_id') ?: null;

        // ตรวจหน่วยงานในสิทธิ์
        $allowed = array_map(static fn ($o) => (int) $o['id'], Ha12ReviewService::selectableUnits($me ? (int) $me->department : null));
        if ($unitId && !in_array($unitId, $allowed, true) && !Ha12ReviewService::isManager()) {
            $unitId = $me ? (int) $me->department : null;
        }

        $file = UploadedFile::getInstanceByName('file');
        if (!$file) {
            Yii::$app->session->setFlash('error', 'ยังไม่ได้เลือกไฟล์');
            return $this->redirect(['index', 'activity_id' => $activity->id]);
        }

        try {
            $spreadsheet = IOFactory::load($file->tempName);
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', 'อ่านไฟล์ไม่สำเร็จ: ' . $e->getMessage());
            return $this->redirect(['index', 'activity_id' => $activity->id]);
        }
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        array_shift($rows); // header

        $fields = Ha12ReviewForm::fieldsFor((int) $activity->no);
        $created = 0;
        $skipped = 0;
        $errors = [];
        $line = 1;

        foreach ($rows as $r) {
            $line++;
            // ข้ามแถวว่าง
            if (!array_filter(array_map(static fn ($v) => trim((string) $v), $r))) {
                continue;
            }
            $sourceRef = trim((string) ($r[0] ?? ''));
            $reviewDate = AppHelper::normalizeDateToDb($r[1] ?? null);
            if (!$reviewDate) {
                $errors[] = "แถว $line: วันที่ทบทวนไม่ถูกต้อง";
                continue;
            }
            // กันซ้ำด้วย source_ref
            if ($sourceRef !== '' && Ha12Review::find()->where(['activity_id' => $activity->id, 'source_ref' => $sourceRef])->exists()) {
                $skipped++;
                continue;
            }

            $model = new Ha12Review([
                'activity_id' => (int) $activity->id,
                'owner_unit_id' => $unitId,
                'fiscal_year' => $fy,
                'review_date' => $reviewDate,
                'revision' => 1,
                'source_system' => 'import',
                'source_ref' => $sourceRef !== '' ? $sourceRef : null,
            ]);
            // map ฟิลด์ (คอลัมน์ที่ 3 เป็นต้นไป ตามลำดับ fields, คอลัมน์สุดท้าย = ผู้ทบทวน)
            $vals = [];
            $idx = 2;
            foreach ($fields as $f) {
                $vals[$f['key']] = trim((string) ($r[$idx] ?? ''));
                $idx++;
            }
            $model->fields = $vals;
            $model->reviewer_name = trim((string) ($r[$idx] ?? '')) ?: null;

            if ($model->save()) {
                Ha12ReviewService::recordVersion($model, Ha12ReviewVersion::ACTION_CREATE);
                $created++;
            } else {
                $errors[] = "แถว $line: " . implode(' ', $model->getFirstErrors());
            }
        }

        Ha12Audit::log('import', (int) $activity->id, 'import', "กิจกรรม {$activity->no}: สร้าง $created ข้าม $skipped");
        $msg = "นำเข้าสำเร็จ $created รายการ" . ($skipped ? " · ข้าม (ซ้ำ) $skipped" : '');
        Yii::$app->session->setFlash('success', $msg);
        if ($errors) {
            Yii::$app->session->setFlash('error', implode(' / ', array_slice($errors, 0, 8)));
        }
        return $this->redirect(['index', 'activity_id' => $activity->id]);
    }
}
