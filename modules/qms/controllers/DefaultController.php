<?php

namespace app\modules\qms\controllers;

use app\components\AppHelper;
use app\modules\hr\models\Employees;
use app\modules\qms\models\Cycle;
use app\modules\qms\models\CycleItem;
use app\modules\qms\models\Evidence;
use app\modules\qms\models\Requirement;
use app\modules\qms\models\Standard;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * QMS DefaultController
 *
 * ภาพรวม + ทะเบียนมาตรฐาน (เริ่มมีเนื้อหาจริง) ส่วนที่เหลือยังเป็นโครง (_scaffold)
 *
 * สิทธิ์: ตอนนี้ยังเปิดให้ผู้ล็อกอินทุกคน (roles => ['@'])
 * เฟสถัดไปจะเปลี่ยนเป็น guard can('qms.view') / can('qms.admin')
 */
class DefaultController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'standard-delete' => ['POST'],
                    'requirement-delete' => ['POST'],
                    'cycle-open' => ['POST'],
                    'cycle-sync' => ['POST'],
                    'cycle-copy' => ['POST'],
                    'item-save' => ['POST'],
                    'evidence-add' => ['POST'],
                    'evidence-delete' => ['POST'],
                ],
            ],
        ]);
    }

    /** ปีงบประมาณที่กำลังดู (พ.ศ.) — ?fy= override, ค่าเริ่มต้น = ปีงบปัจจุบัน */
    private function fiscalYear(): int
    {
        $fy = (int) Yii::$app->request->get('fy');
        return $fy ?: (int) AppHelper::YearBudget();
    }

    /** ภาพรวม (Dashboard ผู้บริหาร) */
    public function actionIndex()
    {
        return $this->render('index', ['fiscalYear' => $this->fiscalYear()]);
    }

    /** ทะเบียนมาตรฐาน — การ์ดพร้อม % ความพร้อมของปีที่เลือก */
    public function actionStandards()
    {
        $fiscalYear = $this->fiscalYear();
        $standards = Standard::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all();

        $stats = [];
        foreach ($standards as $standard) {
            $stats[$standard->id] = $this->standardStats((int) $standard->id, $fiscalYear);
        }

        return $this->render('standards', [
            'fiscalYear' => $fiscalYear,
            'standards' => $standards,
            'stats' => $stats,
        ]);
    }

    /** ฟอร์มเพิ่ม/แก้ไขมาตรฐาน */
    public function actionStandardForm(?int $id = null)
    {
        $model = $id ? $this->findStandard($id) : new Standard(['is_active' => 1, 'sort' => 0, 'color' => '#1a508e']);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', $id ? 'แก้ไขมาตรฐานแล้ว' : 'เพิ่มมาตรฐานแล้ว');
            return $this->redirect(['standards']);
        }

        return $this->render('standard-form', ['model' => $model]);
    }

    /** ลบมาตรฐาน (ต้องไม่มีข้อกำหนด/รอบผูกอยู่) */
    public function actionStandardDelete(int $id)
    {
        $model = $this->findStandard($id);
        if (Requirement::find()->where(['standard_id' => $id])->exists()
            || Cycle::find()->where(['standard_id' => $id])->exists()) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ เพราะยังมีข้อกำหนดหรือรอบปีผูกอยู่');
            return $this->redirect(['standards']);
        }
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบมาตรฐานแล้ว');
        return $this->redirect(['standards']);
    }

    /** จัดการข้อกำหนดของมาตรฐานหนึ่ง (เป็นชั้น) */
    public function actionRequirements(int $standard_id)
    {
        $standard = $this->findStandard($standard_id);
        $requirements = Requirement::find()
            ->where(['standard_id' => $standard_id])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        // จัดกลุ่มตาม parent_id เพื่อ render เป็นชั้น
        $byParent = [];
        foreach ($requirements as $r) {
            $byParent[(int) $r->parent_id][] = $r;
        }

        return $this->render('requirements', [
            'standard' => $standard,
            'byParent' => $byParent,
            'total' => count($requirements),
        ]);
    }

    /** ฟอร์มเพิ่ม/แก้ไขข้อกำหนด */
    public function actionRequirementForm(int $standard_id, ?int $id = null, ?int $parent = null)
    {
        $standard = $this->findStandard($standard_id);
        $model = $id
            ? $this->findRequirement($id, $standard_id)
            : new Requirement(['standard_id' => $standard_id, 'is_active' => 1, 'sort' => 0, 'parent_id' => $parent ?: null]);

        // เลือก parent ได้เฉพาะ "หมวด" (ข้อบนสุด) ของมาตรฐานนี้ และห้ามเลือกตัวเอง
        $parentOptions = Requirement::find()
            ->where(['standard_id' => $standard_id, 'parent_id' => null])
            ->andFilterWhere(['not', ['id' => $id]])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        if ($model->load(Yii::$app->request->post())) {
            $model->standard_id = $standard_id; // กันสลับมาตรฐาน
            if ($model->save()) {
                Yii::$app->session->setFlash('success', $id ? 'แก้ไขข้อกำหนดแล้ว' : 'เพิ่มข้อกำหนดแล้ว');
                return $this->redirect(['requirements', 'standard_id' => $standard_id]);
            }
        }

        return $this->render('requirement-form', [
            'standard' => $standard,
            'model' => $model,
            'parentOptions' => $parentOptions,
            'employeeOptions' => $this->employeeOptions(),
        ]);
    }

    /** ลบข้อกำหนด (ต้องไม่มีข้อย่อย และไม่ถูกใช้ใน checklist ปีใด) */
    public function actionRequirementDelete(int $id)
    {
        $model = Requirement::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบข้อกำหนด');
        }
        $standardId = (int) $model->standard_id;

        if (Requirement::find()->where(['parent_id' => $id])->exists()) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ เพราะยังมีข้อย่อยอยู่ภายใน');
        } elseif (CycleItem::find()->where(['requirement_id' => $id])->exists()) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ เพราะถูกใช้ใน checklist ของบางปีแล้ว');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบข้อกำหนดแล้ว');
        }
        return $this->redirect(['requirements', 'standard_id' => $standardId]);
    }

    /** นำเข้าข้อกำหนดจากแม่แบบสำเร็จรูป หรือคัดลอกจากมาตรฐานอื่น */
    public function actionRequirementImport(int $standard_id)
    {
        $standard = $this->findStandard($standard_id);

        if (Yii::$app->request->isPost) {
            $mode = (string) Yii::$app->request->post('mode');
            if ($mode === 'template') {
                $tpl = \app\modules\qms\models\TemplateLibrary::get((string) Yii::$app->request->post('template_key'));
                if (!$tpl) {
                    Yii::$app->session->setFlash('error', 'ไม่พบแม่แบบที่เลือก');
                    return $this->redirect(['requirement-import', 'standard_id' => $standard_id]);
                }
                $added = $this->applyTemplate($standard, $tpl);
                Yii::$app->session->setFlash('success', "นำเข้าจากแม่แบบ “{$tpl['name']}” แล้ว (เพิ่ม {$added} ข้อ ข้ามที่ซ้ำ)");
            } elseif ($mode === 'clone') {
                $sourceId = (int) Yii::$app->request->post('source_id');
                if ($sourceId === $standard_id || !Standard::find()->where(['id' => $sourceId])->exists()) {
                    Yii::$app->session->setFlash('error', 'เลือกมาตรฐานต้นทางไม่ถูกต้อง');
                    return $this->redirect(['requirement-import', 'standard_id' => $standard_id]);
                }
                $added = $this->cloneRequirements($sourceId, $standard);
                Yii::$app->session->setFlash('success', "คัดลอกข้อกำหนดมาแล้ว (เพิ่ม {$added} ข้อ ข้ามที่ซ้ำ)");
            }
            return $this->redirect(['requirements', 'standard_id' => $standard_id]);
        }

        // มาตรฐานอื่นที่มีข้อกำหนดให้คัดลอก
        $sourceStandards = Standard::find()
            ->where(['not', ['id' => $standard_id]])
            ->andWhere(['exists', Requirement::find()->where('{{%qms_requirement}}.standard_id = {{%qms_standard}}.id')])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all();

        return $this->render('requirement-import', [
            'standard' => $standard,
            'templates' => \app\modules\qms\models\TemplateLibrary::options(),
            'sourceStandards' => $sourceStandards,
        ]);
    }

    /** สร้างข้อกำหนดจากแม่แบบ (หมวด → ข้อย่อย) ข้ามรหัสที่มีแล้ว */
    private function applyTemplate(Standard $standard, array $tpl): int
    {
        $added = 0;
        $order = (int) Requirement::find()->where(['standard_id' => $standard->id])->max('sort');
        foreach ($tpl['sections'] as [$secCode, $secTitle, $children]) {
            $order += 10;
            $section = $this->ensureRequirementByCode($standard->id, null, $secCode, $secTitle, null, $order, $added);
            $childOrder = 0;
            foreach ($children as [$cCode, $cTitle, $hint]) {
                $childOrder += 10;
                $this->ensureRequirementByCode($standard->id, (int) $section->id, $cCode, $cTitle, $hint, $childOrder, $added);
            }
        }
        return $added;
    }

    /** คัดลอกทั้งต้นไม้ข้อกำหนดจากมาตรฐานต้นทาง ข้ามรหัสที่มีแล้ว */
    private function cloneRequirements(int $sourceStandardId, Standard $target): int
    {
        $source = Requirement::find()->where(['standard_id' => $sourceStandardId])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all();
        $byParent = [];
        foreach ($source as $r) {
            $byParent[(int) $r->parent_id][] = $r;
        }
        $added = 0;
        $clone = function (int $srcParentId, ?int $newParentId) use (&$clone, $byParent, $target, &$added) {
            foreach ($byParent[$srcParentId] ?? [] as $r) {
                $new = $this->ensureRequirementByCode($target->id, $newParentId, $r->code, $r->title, $r->evidence_hint, (int) $r->sort, $added, $r->detail);
                $clone((int) $r->id, (int) $new->id);
            }
        };
        $clone(0, null);
        return $added;
    }

    /** สร้าง requirement ถ้ายังไม่มีรหัสนี้ในมาตรฐาน (คืน model เดิมถ้ามีแล้ว) */
    private function ensureRequirementByCode(int $standardId, ?int $parentId, ?string $code, string $title, ?string $hint, int $sort, int &$added, ?string $detail = null): Requirement
    {
        if ($code !== null && $code !== '') {
            $exist = Requirement::find()->where(['standard_id' => $standardId, 'code' => $code])->one();
            if ($exist) {
                return $exist;
            }
        }
        $model = new Requirement([
            'standard_id' => $standardId,
            'parent_id' => $parentId,
            'code' => $code ?: null,
            'title' => $title,
            'detail' => $detail,
            'evidence_hint' => $hint,
            'sort' => $sort,
            'is_active' => 1,
        ]);
        if ($model->save()) {
            $added++;
        }
        return $model;
    }

    // ===== รอบปี + checklist =====

    /** หน้า checklist ของมาตรฐาน × ปีงบ */
    public function actionChecklist(int $standard_id, ?int $fy = null)
    {
        $standard = $this->findStandard($standard_id);
        $fiscalYear = $fy ?: $this->fiscalYear();
        $cycle = Cycle::find()->where(['standard_id' => $standard_id, 'fiscal_year' => $fiscalYear])->one();

        $items = [];
        $byParent = [];
        $reqActive = (int) Requirement::find()->where(['standard_id' => $standard_id, 'is_active' => 1])->count();
        if ($cycle) {
            $items = CycleItem::find()->with('evidences', 'requirement')
                ->where(['cycle_id' => $cycle->id])
                ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all();
            foreach ($items as $it) {
                $pid = (int) ($it->requirement->parent_id ?? 0);
                $byParent[$pid][] = $it;
            }
        }

        // ปีก่อนหน้าที่มีรอบ (ไว้เสนอปุ่มคัดลอกผู้รับผิดชอบ)
        $prevCycle = Cycle::find()->where(['standard_id' => $standard_id])
            ->andWhere(['<', 'fiscal_year', $fiscalYear])
            ->orderBy(['fiscal_year' => SORT_DESC])->one();

        return $this->render('checklist', [
            'standard' => $standard,
            'fiscalYear' => $fiscalYear,
            'cycle' => $cycle,
            'items' => $items,
            'byParent' => $byParent,
            'reqActive' => $reqActive,
            'prevCycle' => $prevCycle,
            'employeeById' => $this->employeeOptions(),
        ]);
    }

    /** เปิดรอบปี = สร้าง cycle + generate checklist item จากข้อกำหนด active */
    public function actionCycleOpen(int $standard_id, int $fy)
    {
        $this->findStandard($standard_id);
        $cycle = Cycle::find()->where(['standard_id' => $standard_id, 'fiscal_year' => $fy])->one();
        if (!$cycle) {
            $cycle = new Cycle(['standard_id' => $standard_id, 'fiscal_year' => $fy, 'status' => Cycle::STATUS_OPEN]);
            $cycle->save();
        }
        $added = $this->generateItems($cycle);
        Yii::$app->session->setFlash('success', "เปิดรอบปี {$fy} แล้ว (สร้าง checklist {$added} ข้อ)");
        return $this->redirect(['checklist', 'standard_id' => $standard_id, 'fy' => $fy]);
    }

    /**
     * เปิดรอบปีใหม่โดยคัดลอกผู้รับผิดชอบจากปีก่อนหน้า (ปีล่าสุดที่มีรอบ)
     * หลักฐานไม่คัดลอก สถานะเริ่มใหม่เป็น "ยังขาด"
     */
    public function actionCycleCopy(int $standard_id, int $fy)
    {
        $this->findStandard($standard_id);
        $source = Cycle::find()->where(['standard_id' => $standard_id])
            ->andWhere(['<', 'fiscal_year', $fy])
            ->orderBy(['fiscal_year' => SORT_DESC])->one();
        if (!$source) {
            Yii::$app->session->setFlash('error', 'ไม่พบรอบปีก่อนหน้าให้คัดลอก');
            return $this->redirect(['checklist', 'standard_id' => $standard_id, 'fy' => $fy]);
        }

        $cycle = Cycle::find()->where(['standard_id' => $standard_id, 'fiscal_year' => $fy])->one()
            ?: new Cycle(['standard_id' => $standard_id, 'fiscal_year' => $fy, 'status' => Cycle::STATUS_OPEN]);
        if ($cycle->isNewRecord) {
            $cycle->save();
        }
        $this->generateItems($cycle);

        // แผนที่ผู้รับผิดชอบจากปีต้นทาง (ตาม requirement_id)
        $srcAssignee = [];
        foreach (CycleItem::find()->where(['cycle_id' => $source->id])->all() as $s) {
            $srcAssignee[(int) $s->requirement_id] = ['unit' => $s->assignee_unit_id, 'emp' => $s->assignee_emp_id];
        }
        $copied = 0;
        foreach (CycleItem::find()->where(['cycle_id' => $cycle->id])->all() as $t) {
            $a = $srcAssignee[(int) $t->requirement_id] ?? null;
            if ($a && ($a['unit'] || $a['emp']) && !$t->assignee_emp_id && !$t->assignee_unit_id) {
                $t->assignee_unit_id = $a['unit'];
                $t->assignee_emp_id = $a['emp'];
                $t->save(false);
                $copied++;
            }
        }
        Yii::$app->session->setFlash('success', "เปิดรอบปี {$fy} + คัดลอกผู้รับผิดชอบจากปี {$source->fiscal_year} แล้ว ({$copied} ข้อ)");
        return $this->redirect(['checklist', 'standard_id' => $standard_id, 'fy' => $fy]);
    }

    /** ซิงก์ข้อกำหนดที่เพิ่มใหม่เข้ารอบเดิม */
    public function actionCycleSync(int $cycle_id)
    {
        $cycle = $this->findCycle($cycle_id);
        $added = $this->generateItems($cycle);
        Yii::$app->session->setFlash($added ? 'success' : 'info', $added ? "เพิ่มข้อกำหนดใหม่ {$added} ข้อ" : 'ไม่มีข้อกำหนดใหม่');
        return $this->redirect(['checklist', 'standard_id' => $cycle->standard_id, 'fy' => $cycle->fiscal_year]);
    }

    /** หน้า checklist item เดียว + จัดการหลักฐาน */
    public function actionItem(int $id)
    {
        $item = $this->findItem($id);
        return $this->render('item', [
            'item' => $item,
            'cycle' => $item->cycle,
            'standard' => $item->cycle->standard,
            'evidences' => $item->getEvidences()->orderBy(['id' => SORT_DESC])->all(),
            'employeeOptions' => $this->employeeOptions(),
        ]);
    }

    /** บันทึกสถานะ/กำหนดส่ง/หมายเหตุ ของ item */
    public function actionItemSave(int $id)
    {
        $item = $this->findItem($id);
        $post = Yii::$app->request->post();
        $item->status = (string) ($post['status'] ?? $item->status);
        $item->due_date = trim((string) ($post['due_date'] ?? '')) ?: null;
        $item->note = trim((string) ($post['note'] ?? '')) ?: null;
        $item->assignee_emp_id = (int) ($post['assignee_emp_id'] ?? 0) ?: null;
        if ($item->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกสถานะแล้ว');
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $item->getFirstErrors()));
        }
        return $this->redirect(['item', 'id' => $id]);
    }

    /** เพิ่มหลักฐาน (เฟส 1: แนบไฟล์ / ลิงก์) */
    public function actionEvidenceAdd(int $cycle_item_id)
    {
        $item = $this->findItem($cycle_item_id);
        $sourceType = (string) Yii::$app->request->post('source_type');
        $ev = new Evidence([
            'cycle_item_id' => $item->id,
            'source_type' => $sourceType,
            'title' => trim((string) Yii::$app->request->post('title')) ?: null,
            'note' => trim((string) Yii::$app->request->post('note')) ?: null,
        ]);

        if ($sourceType === Evidence::SOURCE_LINK) {
            $ev->url = trim((string) Yii::$app->request->post('url')) ?: null;
            if (!$ev->url) {
                Yii::$app->session->setFlash('error', 'กรุณาระบุลิงก์');
                return $this->redirect(['item', 'id' => $item->id]);
            }
        } elseif ($sourceType === Evidence::SOURCE_FILE) {
            $file = UploadedFile::getInstanceByName('file');
            if (!$file) {
                Yii::$app->session->setFlash('error', 'กรุณาเลือกไฟล์');
                return $this->redirect(['item', 'id' => $item->id]);
            }
            $dir = Yii::getAlias('@webroot/uploads/qms/' . $item->id);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            $safe = Yii::$app->security->generateRandomString(16) . '.' . $file->extension;
            if (!$file->saveAs($dir . '/' . $safe)) {
                Yii::$app->session->setFlash('error', 'อัปโหลดไฟล์ไม่สำเร็จ');
                return $this->redirect(['item', 'id' => $item->id]);
            }
            $ev->file_path = 'uploads/qms/' . $item->id . '/' . $safe;
            $ev->file_name = $file->baseName . '.' . $file->extension;
            if (!$ev->title) {
                $ev->title = $ev->file_name;
            }
        } else {
            Yii::$app->session->setFlash('error', 'ประเภทหลักฐานยังไม่รองรับในเฟสนี้');
            return $this->redirect(['item', 'id' => $item->id]);
        }

        if ($ev->save()) {
            Yii::$app->session->setFlash('success', 'เพิ่มหลักฐานแล้ว');
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $ev->getFirstErrors()));
        }
        return $this->redirect(['item', 'id' => $item->id]);
    }

    /** ลบหลักฐาน */
    public function actionEvidenceDelete(int $id)
    {
        $ev = Evidence::findOne($id);
        if (!$ev) {
            throw new NotFoundHttpException('ไม่พบหลักฐาน');
        }
        $itemId = (int) $ev->cycle_item_id;
        if ($ev->source_type === Evidence::SOURCE_FILE && $ev->file_path) {
            @unlink(Yii::getAlias('@webroot/' . $ev->file_path));
        }
        $ev->delete();
        Yii::$app->session->setFlash('success', 'ลบหลักฐานแล้ว');
        return $this->redirect(['item', 'id' => $itemId]);
    }

    /** ดาวน์โหลดไฟล์หลักฐาน */
    public function actionEvidenceFile(int $id)
    {
        $ev = Evidence::findOne($id);
        if (!$ev || $ev->source_type !== Evidence::SOURCE_FILE || !$ev->file_path) {
            throw new NotFoundHttpException('ไม่พบไฟล์');
        }
        $path = Yii::getAlias('@webroot/' . $ev->file_path);
        if (!is_file($path)) {
            throw new NotFoundHttpException('ไฟล์ถูกลบไปแล้ว');
        }
        return Yii::$app->response->sendFile($path, $ev->file_name ?: basename($path));
    }

    /**
     * สร้าง cycle_item จากข้อกำหนด active ที่ยังไม่มีในรอบนี้
     * @return int จำนวนที่เพิ่ม
     */
    private function generateItems(Cycle $cycle): int
    {
        $existing = CycleItem::find()->select('requirement_id')
            ->where(['cycle_id' => $cycle->id])->column();
        $existing = array_map('intval', $existing);

        $requirements = Requirement::find()
            ->where(['standard_id' => $cycle->standard_id, 'is_active' => 1])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->all();

        $added = 0;
        foreach ($requirements as $r) {
            if (in_array((int) $r->id, $existing, true)) {
                continue;
            }
            $item = new CycleItem([
                'cycle_id' => $cycle->id,
                'requirement_id' => $r->id,
                'title_snapshot' => $r->title,
                'assignee_unit_id' => $r->default_assignee_unit_id,
                'assignee_emp_id' => $r->default_assignee_emp_id,
                'status' => CycleItem::STATUS_NONE,
                'sort' => $r->sort,
            ]);
            if ($item->save()) {
                $added++;
            }
        }
        return $added;
    }

    private function findCycle(int $id): Cycle
    {
        $model = Cycle::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรอบปี');
        }
        return $model;
    }

    private function findItem(int $id): CycleItem
    {
        $model = CycleItem::find()->with('cycle.standard')->where(['id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการ checklist');
        }
        return $model;
    }

    // ----- ส่วนที่ยังเป็นโครง -----
    public function actionIndicators() { return $this->scaffold('indicators', 'ตัวชี้วัด', 'bi-graph-up'); }
    public function actionPlans()      { return $this->scaffold('plans', 'แผนงาน', 'bi-calendar2-check'); }
    public function actionEvidence()   { return $this->scaffold('evidence', 'คลังหลักฐาน', 'bi-folder2-open'); }
    public function actionRisk()       { return $this->scaffold('risk', 'ความเสี่ยง', 'bi-exclamation-triangle'); }
    public function actionAudit()      { return $this->scaffold('audit', 'ตรวจประเมิน', 'bi-clipboard2-check'); }
    public function actionReport()     { return $this->scaffold('report', 'รายงาน', 'bi-bar-chart-line'); }

    /**
     * สถิติความพร้อมของมาตรฐานในปีที่เลือก
     * @return array{requirements:int, total:int, complete:int, percent:int, cycle:?Cycle}
     */
    private function standardStats(int $standardId, int $fiscalYear): array
    {
        $requirements = (int) Requirement::find()
            ->where(['standard_id' => $standardId, 'is_active' => 1])->count();

        $cycle = Cycle::find()->where(['standard_id' => $standardId, 'fiscal_year' => $fiscalYear])->one();
        $total = 0;
        $complete = 0;
        if ($cycle) {
            // นับเฉพาะข้อย่อย (leaf) — ตัดหมวด (requirement ที่เป็น parent ของข้ออื่น) ออก
            $parentIds = Requirement::find()->select('parent_id')
                ->where(['standard_id' => $standardId])->andWhere(['not', ['parent_id' => null]])
                ->distinct()->column();
            $leafQuery = CycleItem::find()->where(['cycle_id' => $cycle->id])
                ->andWhere(['<>', 'status', CycleItem::STATUS_NA]);
            if ($parentIds) {
                $leafQuery->andWhere(['not in', 'requirement_id', $parentIds]);
            }
            $total = (int) (clone $leafQuery)->count();
            $complete = (int) (clone $leafQuery)->andWhere(['status' => CycleItem::STATUS_COMPLETE])->count();
        }
        $percent = $total > 0 ? (int) round($complete * 100 / $total) : 0;

        return compact('requirements', 'total', 'complete', 'percent', 'cycle');
    }

    private function findStandard(int $id): Standard
    {
        $model = Standard::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบมาตรฐาน');
        }
        return $model;
    }

    /** รายชื่อพนักงานที่ยังทำงาน [id => "ชื่อ สกุล"] สำหรับ picker ผู้รับผิดชอบ */
    private function employeeOptions(): array
    {
        $rows = Employees::find()
            ->where(['status' => Employees::STATUS_WORKING])
            ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])->all();
        $out = [];
        foreach ($rows as $e) {
            $out[(int) $e->id] = trim(($e->prefix ?: '') . $e->fname . ' ' . $e->lname);
        }
        return $out;
    }

    private function findRequirement(int $id, int $standardId): Requirement
    {
        $model = Requirement::findOne(['id' => $id, 'standard_id' => $standardId]);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบข้อกำหนด');
        }
        return $model;
    }

    private function scaffold(string $active, string $heading, string $icon)
    {
        return $this->render('_scaffold', ['active' => $active, 'heading' => $heading, 'icon' => $icon]);
    }
}
