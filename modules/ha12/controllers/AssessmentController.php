<?php

namespace app\modules\ha12\controllers;

use app\modules\ha12\models\Ha12Activity;
use app\modules\ha12\models\Ha12Assessment;
use app\modules\ha12\models\Ha12Audit;
use app\modules\ha12\models\Ha12Round;
use app\modules\ha12\models\Ha12SummaryRow;
use app\modules\ha12\models\Ha12SummarySource;
use app\modules\ha12\services\Ha12RoundService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * ประเมินกิจกรรมในรอบ PCT (เฟส 3)
 *
 * เขียน = ผู้ดูแล/ทีมคุณภาพ ; ดู = ผู้ล็อกอินทุกคน (ผลที่เผยแพร่)
 */
class AssessmentController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'save' => ['POST'], 'add-row' => ['POST'], 'delete-row' => ['POST'],
                'add-source' => ['POST'], 'delete-source' => ['POST'],
                'publish' => ['POST'], 'unpublish' => ['POST'],
            ]],
        ]);
    }

    private function assertManager(): void
    {
        if (!Ha12RoundService::isManager()) {
            throw new ForbiddenHttpException('เฉพาะผู้ดูแล/ทีมคุณภาพเท่านั้น');
        }
    }

    private function findAssessment(int $id): Ha12Assessment
    {
        $a = Ha12Assessment::find()->with(['round', 'activity', 'rows.sources'])->where(['id' => $id])->one();
        if (!$a) {
            throw new NotFoundHttpException('ไม่พบผลประเมิน');
        }
        return $a;
    }

    /** หน้าประเมินกิจกรรม (สร้าง assessment ถ้ายังไม่มี) */
    public function actionEdit($round_id, $activity_id)
    {
        $this->assertManager();
        $round = Ha12Round::findOne((int) $round_id);
        $activity = Ha12Activity::findOne((int) $activity_id);
        if (!$round || !$activity) {
            throw new NotFoundHttpException('ไม่พบรอบหรือกิจกรรม');
        }
        $assessment = Ha12RoundService::getOrCreateAssessment($round, (int) $activity->id);
        $assessment = $this->findAssessment((int) $assessment->id);

        return $this->render('assessment/edit', [
            'round' => $round,
            'activity' => $activity,
            'assessment' => $assessment,
            'criteria' => $activity->criteria,
            'available' => Ha12RoundService::availableSources($round, $activity),
        ]);
    }

    /** ดูผลประเมิน (อ่านอย่างเดียว) — ผู้ใช้ทั่วไปเห็นเฉพาะที่เผยแพร่ */
    public function actionView($id)
    {
        $assessment = $this->findAssessment((int) $id);
        if (!$assessment->isPublished() && !Ha12RoundService::isManager()) {
            throw new ForbiddenHttpException('ผลประเมินนี้ยังไม่เผยแพร่');
        }
        return $this->render('assessment/view', [
            'assessment' => $assessment,
            'round' => $assessment->round,
            'activity' => $assessment->activity,
            'criteria' => $assessment->activity->criteria,
        ]);
    }

    public function actionSave($id)
    {
        $this->assertManager();
        $a = $this->findAssessment((int) $id);
        $post = Yii::$app->request->post();
        $a->setLevelArray((array) ($post['levels'] ?? []));
        $a->reason = trim((string) ($post['reason'] ?? '')) ?: null;
        $a->summary_text = trim((string) ($post['summary_text'] ?? '')) ?: null;
        $a->save();

        // บันทึกข้อความของแถวสรุปพร้อมกัน (row[<id>][unit_name|topic|improvement])
        $rowInput = (array) ($post['row'] ?? []);
        foreach ($a->rows as $row) {
            $rp = $rowInput[$row->id] ?? null;
            if ($rp === null) {
                continue;
            }
            $row->unit_name = trim((string) ($rp['unit_name'] ?? '')) ?: null;
            $row->topic = trim((string) ($rp['topic'] ?? '')) ?: null;
            $row->improvement = trim((string) ($rp['improvement'] ?? '')) ?: null;
            $row->save();
        }
        Yii::$app->session->setFlash('success', 'บันทึกผลประเมินแล้ว');
        return $this->redirect(['edit', 'round_id' => $a->round_id, 'activity_id' => $a->activity_id]);
    }

    public function actionAddRow($id)
    {
        $this->assertManager();
        $a = $this->findAssessment((int) $id);
        $sort = (int) Ha12SummaryRow::find()->where(['assessment_id' => $a->id])->max('sort');
        (new Ha12SummaryRow(['assessment_id' => (int) $a->id, 'sort' => $sort + 1]))->save(false);
        Yii::$app->session->setFlash('success', 'เพิ่มแถวสรุปแล้ว — กรอกข้อมูลแล้วกดบันทึก');
        return $this->redirect(['edit', 'round_id' => $a->round_id, 'activity_id' => $a->activity_id]);
    }

    public function actionDeleteRow($id)
    {
        $this->assertManager();
        $row = Ha12SummaryRow::findOne((int) $id);
        if (!$row) {
            throw new NotFoundHttpException('ไม่พบแถวสรุป');
        }
        $a = $this->findAssessment((int) $row->assessment_id);
        $row->delete();
        Yii::$app->session->setFlash('success', 'ลบแถวสรุปแล้ว');
        return $this->redirect(['edit', 'round_id' => $a->round_id, 'activity_id' => $a->activity_id]);
    }

    /** ผูกหลักฐาน 1 รายการเข้าแถวสรุป (สร้าง snapshot ฝั่ง server) */
    public function actionAddSource($id)
    {
        $this->assertManager();
        $row = Ha12SummaryRow::findOne((int) $id);
        if (!$row) {
            throw new NotFoundHttpException('ไม่พบแถวสรุป');
        }
        $a = $this->findAssessment((int) $row->assessment_id);

        $type = (string) Yii::$app->request->post('source_type');
        $sourceId = (int) Yii::$app->request->post('source_id');
        $snap = Ha12RoundService::buildSnapshot($type, $sourceId);
        if ($snap === null) {
            Yii::$app->session->setFlash('error', 'ไม่พบหลักฐานที่เลือก');
            return $this->redirect(['edit', 'round_id' => $a->round_id, 'activity_id' => $a->activity_id]);
        }
        // กันผูกซ้ำในแถวเดียวกัน
        $dup = Ha12SummarySource::find()->where(['summary_row_id' => $row->id, 'source_type' => $type, 'source_id' => $sourceId])->exists();
        if (!$dup) {
            (new Ha12SummarySource([
                'summary_row_id' => (int) $row->id, 'source_type' => $type, 'source_id' => $sourceId,
                'source_rev' => $snap['rev'], 'label' => mb_substr($snap['label'], 0, 500),
                'snapshot_json' => json_encode($snap['snapshot'], JSON_UNESCAPED_UNICODE),
            ]))->save();
            Yii::$app->session->setFlash('success', 'ผูกหลักฐานแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'ผูกหลักฐานนี้ไว้แล้ว');
        }
        return $this->redirect(['edit', 'round_id' => $a->round_id, 'activity_id' => $a->activity_id]);
    }

    public function actionDeleteSource($id)
    {
        $this->assertManager();
        $src = Ha12SummarySource::findOne((int) $id);
        if (!$src) {
            throw new NotFoundHttpException('ไม่พบหลักฐาน');
        }
        $row = Ha12SummaryRow::findOne((int) $src->summary_row_id);
        $a = $this->findAssessment((int) $row->assessment_id);
        $src->delete();
        Yii::$app->session->setFlash('success', 'ยกเลิกหลักฐานแล้ว');
        return $this->redirect(['edit', 'round_id' => $a->round_id, 'activity_id' => $a->activity_id]);
    }

    public function actionPublish($id)
    {
        $this->assertManager();
        $a = $this->findAssessment((int) $id);
        if (!$a->levelArray()) {
            Yii::$app->session->setFlash('error', 'ต้องเลือกอย่างน้อยหนึ่งระดับก่อนเผยแพร่');
            return $this->redirect(['edit', 'round_id' => $a->round_id, 'activity_id' => $a->activity_id]);
        }
        $a->status = Ha12Assessment::STATUS_PUBLISHED;
        $a->published_at = date('Y-m-d H:i:s');
        $a->save(false);
        Ha12Audit::log('assessment', (int) $a->id, 'publish', $a->activity->name ?? null);
        Yii::$app->session->setFlash('success', 'เผยแพร่ผลประเมินแล้ว');
        return $this->redirect(['edit', 'round_id' => $a->round_id, 'activity_id' => $a->activity_id]);
    }

    public function actionUnpublish($id)
    {
        $this->assertManager();
        $a = $this->findAssessment((int) $id);
        $a->status = Ha12Assessment::STATUS_DRAFT;
        $a->save(false);
        Ha12Audit::log('assessment', (int) $a->id, 'unpublish', $a->activity->name ?? null);
        Yii::$app->session->setFlash('success', 'ยกเลิกการเผยแพร่ (กลับเป็นร่าง)');
        return $this->redirect(['edit', 'round_id' => $a->round_id, 'activity_id' => $a->activity_id]);
    }
}
