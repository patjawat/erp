<?php

namespace app\modules\medsop\services;

use app\modules\medsop\models\Document;
use app\modules\medsop\models\DocumentRevision;
use app\modules\medsop\models\DocumentStep;
use app\modules\medsop\models\DocumentStepMedia;
use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class DocumentService
{
    private $files;

    public function __construct(?FileIntegrationService $files = null)
    {
        $this->files = $files ?: new FileIntegrationService();
    }

    public function save(Document $document, array $stepRows, array $mediaFiles = [], ?UploadedFile $coverFile = null): bool
    {
        try {
            $relatedDocuments = $this->relatedDocumentMap($stepRows);
            $steps = $this->normalizeSteps($stepRows, $relatedDocuments, (int) $document->id);
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            $document->addError('related_links', $e->getMessage());
            return false;
        }
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
            if ($coverFile !== null) {
                $this->saveCover($document, $coverFile);
            }
            $existingSteps = DocumentStep::find()->where(['document_id' => $document->id])->orderBy(['step_order' => SORT_ASC])->indexBy('id')->all();
            $keptStepIds = [];
            if ($existingSteps) {
                DocumentStep::updateAllCounters(['step_order' => 10000], ['document_id' => $document->id]);
                foreach ($existingSteps as $existingStep) {
                    $temporaryOrder = (int) $existingStep->step_order + 10000;
                    $existingStep->step_order = $temporaryOrder;
                    $existingStep->setOldAttribute('step_order', $temporaryOrder);
                }
            }
            foreach ($steps as $index => $row) {
                $requestedId = (int) ($row['id'] ?? 0);
                $step = $requestedId && isset($existingSteps[$requestedId]) ? $existingSteps[$requestedId] : new DocumentStep();
                $step->setAttributes([
                    'document_id' => $document->id,
                    'step_order' => $index + 1,
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'caution' => $row['caution'],
                    'related_links' => $row['related_links'] ? json_encode($row['related_links'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                ]);
                if (!$step->save()) {
                    throw new \RuntimeException('ไม่สามารถบันทึกขั้นตอนปฏิบัติงานได้');
                }
                $keptStepIds[] = (int) $step->id;
                $this->syncMedia($step, $row, $mediaFiles[$row['source_index']] ?? []);
            }
            if ($existingSteps) {
                DocumentStep::deleteAll(['and', ['document_id' => $document->id], ['not in', 'id', $keptStepIds]]);
            }
            $snapshotSteps = array_map(static function (array $row): array {
                return array_intersect_key($row, array_flip(['title', 'description', 'caution', 'related_links']));
            }, $steps);
            $this->snapshot($document, $snapshotSteps);
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
            'category' => $document->category,
            'keywords' => $document->keywords,
            'review_date' => $document->review_date,
            'announcement_status' => $document->announcement_status,
            'cover_image' => $document->cover_image,
            'related_links' => $document->getRelatedLinkItems(),
            'objective' => $document->objective,
            'scope' => $document->scope,
            'steps' => $steps,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!$revision->save()) {
            throw new \RuntimeException('ไม่สามารถบันทึก Revision ได้');
        }
    }

    private function normalizeSteps(array $rows, array $relatedDocuments, int $currentDocumentId): array
    {
        $result = [];
        foreach ($rows as $sourceIndex => $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $result[] = [
                'id' => (int) ($row['id'] ?? 0),
                'source_index' => $sourceIndex,
                'title' => $title,
                'description' => trim((string) ($row['description'] ?? '')),
                'caution' => trim((string) ($row['caution'] ?? '')),
                'related_links' => $this->normalizeRelatedLinks((array) ($row['related_links'] ?? []), $relatedDocuments, $currentDocumentId),
                'keep_media' => array_map('intval', (array) ($row['keep_media'] ?? [])),
            ];
        }
        return $result;
    }

    private function relatedDocumentMap(array $stepRows): array
    {
        $ids = [];
        foreach ($stepRows as $step) {
            foreach ((array) ($step['related_links'] ?? []) as $row) {
                $id = (int) ($row['document_id'] ?? 0);
                if ($id > 0) {
                    $ids[$id] = $id;
                }
            }
        }
        return $ids
            ? Document::find()->where(['id' => array_values($ids), 'deleted_at' => null])->indexBy('id')->all()
            : [];
    }

    private function normalizeRelatedLinks(array $rows, array $documents, int $currentDocumentId): array
    {
        $links = [];
        foreach ($rows as $row) {
            $documentId = (int) ($row['document_id'] ?? 0);
            if ($documentId < 1) {
                continue;
            }
            if ($documentId === $currentDocumentId || !isset($documents[$documentId])) {
                throw new \RuntimeException('ไม่พบ SOP หรือ WI ที่เลือกเป็นเอกสารที่เกี่ยวข้อง');
            }
            $document = $documents[$documentId];
            $links[] = [
                'document_id' => $documentId,
                'label' => $document->document_no . ' · ' . $document->title,
                'url' => \yii\helpers\Url::to(['/medsop/document/view', 'id' => $documentId]),
            ];
        }
        return $links;
    }

    private function saveCover(Document $document, UploadedFile $file): void
    {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $actualMime = FileHelper::getMimeType($file->tempName);
        if (!isset($allowed[$actualMime]) || $file->size > 10 * 1024 * 1024) {
            throw new \RuntimeException('รูปปกต้องเป็นไฟล์ JPG, PNG หรือ WebP และมีขนาดไม่เกิน 10 MB');
        }
        $directory = Yii::getAlias('@webroot/uploads/medsop/' . $document->id . '/cover');
        FileHelper::createDirectory($directory);
        $storedName = bin2hex(random_bytes(16)) . '.' . $allowed[$actualMime];
        if (!$file->saveAs($directory . DIRECTORY_SEPARATOR . $storedName)) {
            throw new \RuntimeException('ไม่สามารถจัดเก็บรูปปกเอกสารได้');
        }
        $oldPath = $document->cover_image;
        $document->cover_image = '/uploads/medsop/' . $document->id . '/cover/' . $storedName;
        if (!$document->save(false, ['cover_image', 'updated_at'])) {
            @unlink($directory . DIRECTORY_SEPARATOR . $storedName);
            throw new \RuntimeException('ไม่สามารถบันทึกรูปปกเอกสารได้');
        }
        if ($oldPath) {
            $oldFile = Yii::getAlias('@webroot') . str_replace('/', DIRECTORY_SEPARATOR, $oldPath);
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }
    }

    /** @param UploadedFile[] $files */
    private function syncMedia(DocumentStep $step, array $row, array $files): void
    {
        $keepIds = $row['keep_media'];
        $removeQuery = DocumentStepMedia::find()->where(['step_id' => $step->id]);
        if ($keepIds) {
            $removeQuery->andWhere(['not in', 'id', $keepIds]);
        }
        foreach ($removeQuery->all() as $media) {
            $media->delete();
        }

        $existingImageCount = (int) DocumentStepMedia::find()->where([
            'step_id' => $step->id,
            'media_type' => DocumentStepMedia::TYPE_IMAGE,
        ])->count();
        $newImageCount = 0;
        foreach ($files as $file) {
            $actualMime = $file instanceof UploadedFile ? FileHelper::getMimeType($file->tempName) : null;
            if (in_array($actualMime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                $newImageCount++;
            }
        }
        if ($existingImageCount + $newImageCount > 5) {
            throw new \RuntimeException('แต่ละขั้นตอนเพิ่มรูปได้สูงสุด 5 ภาพ');
        }

        $allowed = [
            'image/jpeg' => [DocumentStepMedia::TYPE_IMAGE, 'jpg'],
            'image/png' => [DocumentStepMedia::TYPE_IMAGE, 'png'],
            'image/webp' => [DocumentStepMedia::TYPE_IMAGE, 'webp'],
            'video/mp4' => [DocumentStepMedia::TYPE_VIDEO, 'mp4'],
            'video/webm' => [DocumentStepMedia::TYPE_VIDEO, 'webm'],
        ];
        $sortOrder = (int) DocumentStepMedia::find()->where(['step_id' => $step->id])->max('sort_order');
        foreach ($files as $file) {
            $actualMime = $file instanceof UploadedFile ? FileHelper::getMimeType($file->tempName) : null;
            if (!$file instanceof UploadedFile || !isset($allowed[$actualMime])) {
                throw new \RuntimeException('รองรับเฉพาะภาพ JPG, PNG, WebP และวิดีโอ MP4, WebM');
            }
            [$mediaType, $safeExtension] = $allowed[$actualMime];
            $maxSize = $mediaType === DocumentStepMedia::TYPE_IMAGE ? 10 * 1024 * 1024 : 100 * 1024 * 1024;
            if ($file->size > $maxSize) {
                throw new \RuntimeException($mediaType === DocumentStepMedia::TYPE_IMAGE ? 'ภาพต้องมีขนาดไม่เกิน 10 MB' : 'วิดีโอต้องมีขนาดไม่เกิน 100 MB');
            }
            $directory = Yii::getAlias('@webroot/uploads/medsop/' . $step->document_id . '/' . $step->id);
            FileHelper::createDirectory($directory);
            $storedName = bin2hex(random_bytes(16)) . '.' . $safeExtension;
            if (!$file->saveAs($directory . DIRECTORY_SEPARATOR . $storedName)) {
                throw new \RuntimeException('ไม่สามารถจัดเก็บไฟล์สื่อได้');
            }
            $media = new DocumentStepMedia([
                'step_id' => $step->id,
                'media_type' => $mediaType,
                'file_name' => $file->baseName . '.' . $file->extension,
                'file_path' => '/uploads/medsop/' . $step->document_id . '/' . $step->id . '/' . $storedName,
                'mime_type' => $actualMime,
                'file_size' => $file->size,
                'sort_order' => ++$sortOrder,
                'created_by' => Yii::$app->user->id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            if (!$media->save()) {
                @unlink($directory . DIRECTORY_SEPARATOR . $storedName);
                throw new \RuntimeException('ไม่สามารถบันทึกข้อมูลไฟล์สื่อได้');
            }
        }
    }
}
