<?php

namespace app\modules\swot\controllers;

use app\components\AppHelper;
use app\modules\hr\models\Employees;
use app\modules\swot\models\SwotBoard;
use app\modules\swot\models\SwotBoardSearch;
use app\modules\swot\models\SwotNote;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * เครื่องมือวิเคราะห์ SWOT & SOAR
 *
 * สิทธิ์: เปิดให้ผู้ล็อกอินทุกคน (roles => ['@']) — โมดูล standalone
 * เมนูถูกจัดวางในโซนแผนงาน/โครงการ (navbar gate ด้วย can('pm'))
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
                    'create' => ['POST'],
                    'update-board' => ['POST'],
                    'delete' => ['POST'],
                    'note-save' => ['POST'],
                    'note-delete' => ['POST'],
                    'note-move' => ['POST'],
                    'note-field' => ['POST'],
                    'matrix-save' => ['POST'],
                    'ai-analyze' => ['POST'],
                ],
            ],
        ]);
    }

    /** คลังการวิเคราะห์ — รายการกระดานทั้งหมด */
    public function actionIndex()
    {
        $searchModel = new SwotBoardSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /** สร้างเรื่องวิเคราะห์ใหม่ (จาก modal ในหน้าคลัง) แล้วพาเข้ากระดาน */
    public function actionCreate()
    {
        $model = new SwotBoard();
        $model->load(Yii::$app->request->post());

        $model->owner_id = Yii::$app->user->id;
        $model->org_unit_id = $this->currentDepartmentId();
        if (empty($model->budget_year)) {
            $model->budget_year = (int) AppHelper::YearBudget();
        }

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'สร้างเรื่องวิเคราะห์ใหม่แล้ว เริ่มเพิ่มประเด็นได้เลย');
            return $this->redirect(['board', 'id' => $model->id]);
        }

        Yii::$app->session->setFlash('error', 'สร้างไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        return $this->redirect(['index']);
    }

    /** หน้ากระดาน Canvas ของเรื่องหนึ่ง */
    public function actionBoard($id)
    {
        $model = $this->findBoard((int) $id);

        return $this->render('board', [
            'model' => $model,
            'notesByQuadrant' => $model->notesByQuadrant(),
        ]);
    }

    /** ขั้นตอนที่ 2: ตารางจัดหมวดหมู่ + ให้ค่าน้ำหนัก */
    public function actionTable($id)
    {
        $model = $this->findBoard((int) $id);

        return $this->render('table', [
            'model' => $model,
            'notesByQuadrant' => $model->notesByQuadrant(),
        ]);
    }

    /** ขั้นตอนที่ 3: เรดาร์สรุปน้ำหนัก */
    public function actionRadar($id)
    {
        $model = $this->findBoard((int) $id);

        return $this->render('radar', [
            'model' => $model,
            'weightByQuadrant' => $model->weightByQuadrant(),
            'weightByCategory' => $model->weightByCategory(),
        ]);
    }

    /** [ajax] แก้ไขค่าเดียวของโน้ต (category / weight) จากหน้าตาราง */
    public function actionNoteField()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request;
        $note = SwotNote::findOne((int) $req->post('id'));
        if (!$note) {
            return ['success' => false, 'message' => 'ไม่พบโน้ต'];
        }
        $field = (string) $req->post('field');
        $value = $req->post('value');

        if ($field === 'category') {
            $note->category = ($value === '' || $value === null) ? null : (string) $value;
        } elseif ($field === 'weight') {
            $note->weight = (int) $value;
        } elseif ($field === 'content') {
            $note->content = trim((string) $value);
        } elseif ($field === 'priority') {
            $note->priority = (string) $value;
        } else {
            return ['success' => false, 'message' => 'ฟิลด์ไม่ถูกต้อง'];
        }

        if ($note->save()) {
            if ($note->board) {
                $this->touchBoard($note->board);
            }
            return ['success' => true, 'note' => $note->toArray()];
        }
        return ['success' => false, 'message' => implode(' ', $note->getFirstErrors())];
    }

    /** ขั้นตอนที่ 4: สังเคราะห์กลยุทธ์ TOWS (SWOT) / SOAR Matrix */
    public function actionMatrix($id)
    {
        $model = $this->findBoard((int) $id);

        return $this->render('matrix', [
            'model' => $model,
            'notesByQuadrant' => $model->notesByQuadrant(),
        ]);
    }

    /** ขั้นตอนที่ 5: รายงานรวม (พิมพ์/บันทึก PDF ผ่านเบราว์เซอร์) */
    public function actionReport($id)
    {
        $model = $this->findBoard((int) $id);

        return $this->render('report', [
            'model' => $model,
            'notesByQuadrant' => $model->notesByQuadrant(),
            'weightByQuadrant' => $model->weightByQuadrant(),
            'weightByCategory' => $model->weightByCategory(),
        ]);
    }

    /** ส่งออกผลวิเคราะห์เป็น Excel (.xlsx) — ชีตประเด็น + ชีตกลยุทธ์ */
    public function actionExport($id)
    {
        $model = $this->findBoard((int) $id);

        $tfLabels = ['quick-win' => 'ทำได้ทันที', 'short-term' => 'ระยะสั้น', 'medium-term' => 'ระยะกลาง', 'long-term' => 'ระยะยาว'];
        $prLabels = ['high' => 'สูง', 'medium' => 'กลาง', 'low' => 'ต่ำ'];
        $quadInfo = SwotNote::QUADRANT_INFO;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ชีต 1: ประเด็น
        $s1 = $spreadsheet->getActiveSheet();
        $s1->setTitle('ประเด็น');
        $head = ['ช่อง', 'ประเด็น', 'หมวดหมู่', 'ค่าน้ำหนัก', 'ความสำคัญ', 'ผู้เขียน'];
        foreach ($head as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $s1->setCellValue($col . '1', $h);
            $s1->getStyle($col . '1')->getFont()->setBold(true);
        }
        $r = 2;
        foreach ($model->quadrants() as $q) {
            foreach (($model->notesByQuadrant()[$q] ?? []) as $note) {
                $s1->setCellValue('A' . $r, $quadInfo[$q]['short'] ?? $q);
                $s1->setCellValue('B' . $r, $note->content);
                $s1->setCellValue('C' . $r, (string) $note->category);
                $s1->setCellValue('D' . $r, (int) $note->weight);
                $s1->setCellValue('E' . $r, $prLabels[$note->priority] ?? $note->priority);
                $s1->setCellValue('F' . $r, (string) $note->author);
                $r++;
            }
        }
        foreach (range('A', 'F') as $col) {
            $s1->getColumnDimension($col)->setAutoSize(true);
        }

        // ชีต 2: กลยุทธ์
        $s2 = $spreadsheet->createSheet();
        $isSoar = $model->isSoar();

        if ($isSoar) {
            // SOAR: รายการริเริ่มเชิงกลยุทธ์ (S+O → A → R)
            $s2->setTitle('ริเริ่มกลยุทธ์');
            $head2 = ['ริเริ่มเชิงกลยุทธ์', 'รายละเอียด (ใช้ S+O อย่างไร)', 'มุ่งสู่ (Aspiration)', 'ตัวชี้วัดผลลัพธ์ (Result)', 'กรอบเวลา', 'ความสำคัญ'];
            foreach ($head2 as $i => $h) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $s2->setCellValue($col . '1', $h);
                $s2->getStyle($col . '1')->getFont()->setBold(true);
            }
            $items = is_array($model->soar_matrix['initiatives'] ?? null) ? $model->soar_matrix['initiatives'] : [];
            $r = 2;
            foreach ($items as $it) {
                $s2->setCellValue('A' . $r, (string) ($it['title'] ?? ''));
                $s2->setCellValue('B' . $r, (string) ($it['description'] ?? ''));
                $s2->setCellValue('C' . $r, (string) ($it['aspiration'] ?? ''));
                $s2->setCellValue('D' . $r, (string) ($it['metric'] ?? ''));
                $s2->setCellValue('E' . $r, $tfLabels[$it['timeframe'] ?? ''] ?? '');
                $s2->setCellValue('F' . $r, $prLabels[$it['priority'] ?? ''] ?? '');
                $r++;
            }
        } else {
            // SWOT: กลยุทธ์ TOWS (จับคู่ 4 ช่อง)
            $s2->setTitle('กลยุทธ์');
            $head2 = ['ช่อง', 'กลยุทธ์', 'รายละเอียด', 'กรอบเวลา', 'ความสำคัญ'];
            foreach ($head2 as $i => $h) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $s2->setCellValue($col . '1', $h);
                $s2->getStyle($col . '1')->getFont()->setBold(true);
            }
            $matrix = is_array($model->tows_matrix) ? $model->tows_matrix : [];
            $r = 2;
            foreach ($matrix as $cell => $items) {
                foreach ((array) $items as $it) {
                    $s2->setCellValue('A' . $r, strtoupper($cell));
                    $s2->setCellValue('B' . $r, (string) ($it['title'] ?? ''));
                    $s2->setCellValue('C' . $r, (string) ($it['description'] ?? ''));
                    $s2->setCellValue('D' . $r, $tfLabels[$it['timeframe'] ?? ''] ?? '');
                    $s2->setCellValue('E' . $r, $prLabels[$it['priority'] ?? ''] ?? '');
                    $r++;
                }
            }
        }
        foreach (range('A', 'F') as $col) {
            $s2->getColumnDimension($col)->setAutoSize(true);
        }

        $dir = Yii::getAlias('@runtime/swot-exports');
        \yii\helpers\FileHelper::createDirectory($dir);
        $fileName = 'SWOT_' . $model->id . '_' . date('Ymd_His') . '.xlsx';
        $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($filePath);

        return Yii::$app->response->sendFile($filePath, $fileName);
    }

    /** [ajax] วิเคราะห์กระดานด้วย AI แล้วเก็บผลลง ai_analysis */
    public function actionAiAnalyze($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findBoard((int) $id);

        try {
            $result = (new \app\modules\swot\services\SwotAiAnalyzer())->analyze($model);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        $model->ai_analysis = $result;
        $model->save(false);

        return ['success' => true, 'analysis' => $result];
    }

    /** [ajax] บันทึกทั้ง matrix (ส่งโครงสร้างเต็มมาแทนที่) */
    public function actionMatrixSave($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findBoard((int) $id);

        $raw = Yii::$app->request->post('matrix');
        $data = is_string($raw) ? json_decode($raw, true) : $raw;
        if (!is_array($data)) {
            return ['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง'];
        }
        $tf = ['quick-win', 'short-term', 'medium-term', 'long-term'];
        $pr = ['high', 'medium', 'low'];

        if ($model->isSoar()) {
            // SOAR: รายการริเริ่มเชิงกลยุทธ์ (S+O → A → R) ไม่จับคู่ข้ามช่องแบบ TOWS
            $items = [];
            foreach ((array) ($data['initiatives'] ?? []) as $item) {
                $title = trim((string) ($item['title'] ?? ''));
                if ($title === '') {
                    continue;
                }
                $items[] = [
                    'id' => (string) ($item['id'] ?? uniqid('so', true)),
                    'title' => mb_substr($title, 0, 500),
                    'description' => mb_substr(trim((string) ($item['description'] ?? '')), 0, 1000),
                    'aspiration' => mb_substr(trim((string) ($item['aspiration'] ?? '')), 0, 500),
                    'metric' => mb_substr(trim((string) ($item['metric'] ?? '')), 0, 300),
                    'timeframe' => in_array($item['timeframe'] ?? '', $tf, true) ? $item['timeframe'] : '',
                    'priority' => in_array($item['priority'] ?? '', $pr, true) ? $item['priority'] : 'medium',
                ];
            }
            $clean = ['initiatives' => $items];
            $model->soar_matrix = $clean;
        } else {
            // SWOT: TOWS Matrix จับคู่ 4 ช่อง
            $cells = ['so', 'wo', 'st', 'wt'];
            $clean = [];
            foreach ($cells as $cell) {
                $clean[$cell] = [];
                foreach ((array) ($data[$cell] ?? []) as $item) {
                    $title = trim((string) ($item['title'] ?? ''));
                    if ($title === '') {
                        continue;
                    }
                    $clean[$cell][] = [
                        'id' => (string) ($item['id'] ?? uniqid('st', true)),
                        'title' => mb_substr($title, 0, 500),
                        'description' => mb_substr(trim((string) ($item['description'] ?? '')), 0, 1000),
                        'timeframe' => in_array($item['timeframe'] ?? '', $tf, true) ? $item['timeframe'] : '',
                        'priority' => in_array($item['priority'] ?? '', $pr, true) ? $item['priority'] : 'medium',
                    ];
                }
            }
            $model->tows_matrix = $clean;
        }

        if ($model->save(false)) {
            return ['success' => true, 'matrix' => $clean];
        }
        return ['success' => false, 'message' => implode(' ', $model->getFirstErrors())];
    }

    /** บันทึกหัวเรื่อง/วัตถุประสงค์/กรอบคิด/สถานะ */
    public function actionUpdateBoard($id)
    {
        $model = $this->findBoard((int) $id);
        $model->load(Yii::$app->request->post());

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรื่องแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['board', 'id' => $model->id]);
    }

    /** ลบเรื่องวิเคราะห์ (ลบโน้ตทั้งหมดตาม FK cascade) */
    public function actionDelete($id)
    {
        $model = $this->findBoard((int) $id);
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบเรื่องวิเคราะห์แล้ว');
        return $this->redirect(['index']);
    }

    /** [ajax] เพิ่ม/แก้ โพสต์อิท */
    public function actionNoteSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request;

        $boardId = (int) $req->post('board_id');
        $board = SwotBoard::findOne($boardId);
        if (!$board) {
            return ['success' => false, 'message' => 'ไม่พบกระดาน'];
        }

        $noteId = (int) $req->post('id');
        if ($noteId > 0) {
            $note = SwotNote::findOne(['id' => $noteId, 'board_id' => $boardId]);
            if (!$note) {
                return ['success' => false, 'message' => 'ไม่พบโน้ต'];
            }
        } else {
            $note = new SwotNote(['board_id' => $boardId]);
        }

        $note->quadrant = (string) $req->post('quadrant', $note->quadrant);
        $note->content = trim((string) $req->post('content'));
        $note->category = $req->post('category') ?: null;
        $note->color = (string) $req->post('color', $note->color ?: 'yellow');
        $note->priority = (string) $req->post('priority', $note->priority ?: 'medium');
        $note->weight = (int) $req->post('weight', $note->weight ?: 3);
        if ($note->isNewRecord && !$note->author) {
            $note->author = $this->currentPersonName();
        }

        if ($note->save()) {
            $this->touchBoard($board);
            return ['success' => true, 'note' => $note->toArray()];
        }
        return ['success' => false, 'message' => implode(' ', $note->getFirstErrors())];
    }

    /** [ajax] ลบโพสต์อิท */
    public function actionNoteDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = (int) Yii::$app->request->post('id');
        $note = SwotNote::findOne($id);
        if (!$note) {
            return ['success' => false, 'message' => 'ไม่พบโน้ต'];
        }
        $board = $note->board;
        $note->delete();
        if ($board) {
            $this->touchBoard($board);
        }
        return ['success' => true];
    }

    /** [ajax] ย้ายโน้ตข้ามช่อง (ลากวาง) */
    public function actionNoteMove()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request;
        $note = SwotNote::findOne((int) $req->post('id'));
        if (!$note) {
            return ['success' => false, 'message' => 'ไม่พบโน้ต'];
        }
        $note->quadrant = (string) $req->post('quadrant', $note->quadrant);
        if ($note->save(true, ['quadrant', 'updated_at', 'updated_by'])) {
            if ($note->board) {
                $this->touchBoard($note->board);
            }
            return ['success' => true];
        }
        return ['success' => false, 'message' => implode(' ', $note->getFirstErrors())];
    }

    // ---------- helpers ----------

    private function findBoard(int $id): SwotBoard
    {
        $model = SwotBoard::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบเรื่องวิเคราะห์นี้');
        }
        return $model;
    }

    private function touchBoard(SwotBoard $board): void
    {
        $board->updateAttributes(['updated_at' => date('Y-m-d H:i:s')]);
    }

    private function currentEmployee(): ?Employees
    {
        return Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
    }

    private function currentDepartmentId(): ?int
    {
        $emp = $this->currentEmployee();
        return $emp && $emp->department ? (int) $emp->department : null;
    }

    private function currentPersonName(): string
    {
        $emp = $this->currentEmployee();
        if ($emp && trim((string) $emp->fullname) !== '') {
            return trim((string) $emp->fullname);
        }
        return 'ผู้ใช้งาน';
    }
}
