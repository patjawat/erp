<?php

namespace app\modules\medsop\services;

use app\modules\medsop\models\Document;
use app\modules\medsop\models\DocumentRevision;
use app\modules\medsop\models\DocumentStep;
use Yii;

class DocumentService
{
    private $files;

    public function __construct(?FileIntegrationService $files = null)
    {
        $this->files = $files ?: new FileIntegrationService();
    }

    public function save(Document $document, array $stepRows): bool
    {
        $steps = $this->normalizeSteps($stepRows);
        if ($steps === []) {
            $document->addError('title', 'กรุณาเพิ่มขั้นตอนอย่างน้อย 1 ขั้นตอน');
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$document->save()) {
                $transaction->rollBack();
                return false;
            }
            DocumentStep::deleteAll(['document_id' => $document->id]);
            foreach ($steps as $index => $row) {
                $step = new DocumentStep([
                    'document_id' => $document->id,
                    'step_order' => $index + 1,
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'caution' => $row['caution'],
                ]);
                if (!$step->save()) {
                    throw new \RuntimeException('ไม่สามารถบันทึกขั้นตอนปฏิบัติงานได้');
                }
            }
            $this->snapshot($document, $steps);
            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($e, __METHOD__);
            $document->addError('title', 'ไม่สามารถบันทึกเอกสารได้ กรุณาลองใหม่');
            return false;
        }
    }

    private function snapshot(Document $document, array $steps): void
    {
        $revision = DocumentRevision::findOne([
            'document_id' => $document->id,
            'revision_no' => $document->current_revision,
        ]);
        if ($revision === null) {
            $revision = new DocumentRevision([
                'document_id' => $document->id,
                'revision_no' => $document->current_revision,
                'file_ref' => $this->files->createRevisionRef((int) $document->id, (int) $document->current_revision),
                'created_emp_id' => $document->created_emp_id,
                'created_by' => $document->created_by,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        $revision->approval_status = $document->status;
        $revision->snapshot_json = json_encode([
            'document_no' => $document->document_no,
            'title' => $document->title,
            'document_type' => $document->document_type,
            'organization_id' => (int) $document->organization_id,
            'objective' => $document->objective,
            'scope' => $document->scope,
            'steps' => $steps,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!$revision->save()) {
            throw new \RuntimeException('ไม่สามารถบันทึก Revision ได้');
        }
    }

    private function normalizeSteps(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $result[] = [
                'title' => $title,
                'description' => trim((string) ($row['description'] ?? '')),
                'caution' => trim((string) ($row['caution'] ?? '')),
            ];
        }
        return $result;
    }
}
