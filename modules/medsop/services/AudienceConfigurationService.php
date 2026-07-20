<?php

namespace app\modules\medsop\services;

use app\modules\medsop\models\Document;
use app\modules\medsop\models\DocumentAudience;
use Yii;

class AudienceConfigurationService
{
    private $resolver;

    public function __construct(?AudienceResolverService $resolver = null)
    {
        $this->resolver = $resolver ?: new AudienceResolverService();
    }

    /** @return DocumentAudience[] */
    public function build(Document $document, array $input): array
    {
        $rows = [];
        foreach ((array) ($input['organizations'] ?? []) as $organizationId => $values) {
            if (empty($values['selected'])) {
                continue;
            }
            $rows[] = $this->make($document, DocumentAudience::TYPE_ORGANIZATION, (int) $organizationId, 0, [
                'include_children' => !empty($values['include_children']),
                'required' => !isset($values['required']) || !empty($values['required']),
            ]);
        }

        foreach ((array) ($input['teams'] ?? []) as $appointmentId => $values) {
            if (empty($values['selected'])) {
                continue;
            }
            $rows[] = $this->make(
                $document,
                DocumentAudience::TYPE_TEAM_GROUP,
                (int) ($values['team_group_id'] ?? 0),
                (int) $appointmentId,
                ['required' => !isset($values['required']) || !empty($values['required'])]
            );
        }

        foreach (array_values(array_unique(array_filter(array_map('intval', (array) ($input['employee_ids'] ?? []))))) as $employeeId) {
            $rows[] = $this->make($document, DocumentAudience::TYPE_EMPLOYEE, $employeeId, 0, ['required' => true]);
        }

        foreach ($rows as $row) {
            if (!$row->validate()) {
                throw new \DomainException(implode(' ', $row->getFirstErrors()));
            }
        }
        return $rows;
    }

    public function preview(Document $document, array $input): array
    {
        $rows = $this->build($document, $input);
        $recipients = $this->resolver->resolve($rows);
        return ['rules' => count($rows), 'recipients' => $recipients];
    }

    public function save(Document $document, array $input): int
    {
        $rows = $this->build($document, $input);
        // Resolve first so invalid HR/team references cannot replace a valid saved configuration.
        $this->resolver->resolve($rows);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            DocumentAudience::deleteAll(['document_id' => (int) $document->id]);
            foreach ($rows as $row) {
                $row->created_by = Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id;
                if (!$row->save()) {
                    throw new \RuntimeException(implode(' ', $row->getFirstErrors()));
                }
            }
            $transaction->commit();
            return count($rows);
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function make(Document $document, string $type, int $audienceId, int $versionId, array $attributes): DocumentAudience
    {
        $audience = new DocumentAudience(array_merge([
            'document_id' => (int) $document->id,
            'audience_type' => $type,
            'audience_id' => $audienceId,
            'audience_version_id' => $versionId,
            'include_children' => false,
            'required' => true,
        ], $attributes));
        $audience->scenario = DocumentAudience::SCENARIO_REPLACE;
        return $audience;
    }
}
