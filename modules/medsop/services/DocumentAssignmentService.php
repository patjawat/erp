<?php

namespace app\modules\medsop\services;

use app\modules\medsop\models\Document;
use app\modules\medsop\models\DocumentAssignment;
use app\modules\medsop\models\DocumentAudience;
use app\modules\medsop\models\DocumentReadLog;
use Yii;

class DocumentAssignmentService
{
    private $resolver;

    public function __construct(?AudienceResolverService $resolver = null)
    {
        $this->resolver = $resolver ?: new AudienceResolverService();
    }

    /**
     * Creates an immutable recipient snapshot for a published revision.
     * Existing rows are retained, so acknowledgement evidence is never erased.
     */
    public function assignPublishedRevision(Document $document, ?string $dueDate = null): int
    {
        if ($document->status !== Document::STATUS_PUBLISHED) {
            throw new \DomainException('สร้างรายการผู้รับได้เฉพาะเอกสารที่เผยแพร่แล้ว');
        }

        $audiences = DocumentAudience::find()
            ->where(['document_id' => (int) $document->id])
            ->orderBy(['id' => SORT_ASC])
            ->all();
        $recipients = $this->resolver->resolve($audiences);
        $now = date('Y-m-d H:i:s');
        $userId = Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id;
        $created = 0;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($recipients as $employeeId => $recipient) {
                $assignment = DocumentAssignment::findOne([
                    'document_id' => (int) $document->id,
                    'revision_no' => (int) $document->current_revision,
                    'employee_id' => (int) $employeeId,
                ]);
                if ($assignment !== null) {
                    continue;
                }

                $assignment = new DocumentAssignment([
                    'document_id' => (int) $document->id,
                    'revision_no' => (int) $document->current_revision,
                    'employee_id' => (int) $employeeId,
                    'required' => (bool) $recipient['required'],
                    'source_json' => json_encode($recipient['sources'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'due_date' => $dueDate,
                    'status' => DocumentAssignment::STATUS_UNREAD,
                    'assigned_at' => $now,
                    'assigned_by' => $userId,
                ]);
                if (!$assignment->save()) {
                    throw new \RuntimeException('ไม่สามารถสร้างรายการผู้รับเอกสารได้');
                }
                $this->writeLog($assignment, DocumentReadLog::EVENT_ASSIGNED, $userId, null, null);
                $created++;
            }
            $transaction->commit();
            return $created;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function writeLog(DocumentAssignment $assignment, string $event, ?int $userId, ?string $ip, ?string $userAgent): void
    {
        $log = new DocumentReadLog([
            'assignment_id' => (int) $assignment->id,
            'document_id' => (int) $assignment->document_id,
            'revision_no' => (int) $assignment->revision_no,
            'employee_id' => (int) $assignment->employee_id,
            'user_id' => $userId,
            'event_type' => $event,
            'event_at' => date('Y-m-d H:i:s'),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
        if (!$log->save()) {
            throw new \RuntimeException('ไม่สามารถบันทึกประวัติการมอบหมายเอกสารได้');
        }
    }
}
