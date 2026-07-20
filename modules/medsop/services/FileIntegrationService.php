<?php

namespace app\modules\medsop\services;

use app\modules\filemanager\models\Uploads;

class FileIntegrationService
{
    public function createRevisionRef(int $documentId, int $revisionNo): string
    {
        return sprintf('medsop/%d/revision-%d-%s', $documentId, $revisionNo, bin2hex(random_bytes(6)));
    }

    public function findByRef(string $ref): array
    {
        return Uploads::find()->where(['ref' => $ref])->orderBy(['id' => SORT_ASC])->all();
    }

    public function isReferenced(string $ref): bool
    {
        return \app\modules\medsop\models\DocumentRevision::find()->where(['file_ref' => $ref])->exists();
    }

    /**
     * MedSOP intentionally does not delete uploads because filemanager performs hard delete.
     */
    public function canDelete(string $ref): bool
    {
        return !$this->isReferenced($ref);
    }
}
