<?php

namespace app\modules\medsop\services;

use app\modules\hr\models\Employees;
use app\modules\medsop\models\Document;
use app\modules\medsop\models\DocumentAudience;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;

class DocumentAccessService
{
    public const PERMISSION_ADMIN = 'medsop.admin';
    public const PERMISSION_REVIEW = 'medsop.review';
    public const PERMISSION_VIEW_ALL = 'medsop.viewAll';
    public const PERMISSION_VIEW_PUBLISHED = 'medsop.viewPublished';

    private $employee;
    private $audienceDocumentIds;

    public function currentEmployee(): ?Employees
    {
        if ($this->employee === null && !Yii::$app->user->isGuest) {
            $this->employee = Employees::find()->where(['user_id' => Yii::$app->user->id])->one() ?: false;
        }
        return $this->employee ?: null;
    }

    public function isAdmin(): bool
    {
        return !Yii::$app->user->isGuest
            && (Yii::$app->user->can(self::PERMISSION_ADMIN) || Yii::$app->user->can('admin'));
    }

    public function canReview(): bool
    {
        return !Yii::$app->user->isGuest
            && (
                Yii::$app->user->can(self::PERMISSION_REVIEW)
                || Yii::$app->user->can('director')
                || Yii::$app->user->can('hr')
            );
    }

    public function canViewAll(): bool
    {
        return $this->isAdmin() || $this->canReview() || (!Yii::$app->user->isGuest && Yii::$app->user->can(self::PERMISSION_VIEW_ALL));
    }

    public function canCreate(): bool
    {
        return $this->isAdmin();
    }

    public function canUpdate(Document $document): bool
    {
        return $this->isAdmin() && $document->isEditable();
    }

    public function canView(Document $document): bool
    {
        if ($this->canViewAll()) {
            return true;
        }
        $employee = $this->currentEmployee();
        if ($employee === null || $document->status !== Document::STATUS_PUBLISHED) {
            return false;
        }

        $audiences = DocumentAudience::find()
            ->where(['document_id' => (int) $document->id])
            ->orderBy(['id' => SORT_ASC])
            ->all();
        if ($audiences !== []) {
            return isset((new AudienceResolverService())->resolve($audiences)[(int) $employee->id]);
        }

        return (int) $document->organization_id === (int) $employee->department;
    }

    public function applyVisibleScope(ActiveQuery $query): void
    {
        if ($this->canViewAll()) {
            return;
        }
        $employee = $this->currentEmployee();
        if ($employee === null) {
            $query->andWhere(['d.status' => Document::STATUS_PUBLISHED, 'd.id' => -1]);
            return;
        }

        $query->andWhere(['d.status' => Document::STATUS_PUBLISHED])
            ->andWhere(['or',
                ['d.id' => $this->audienceDocumentIds((int) $employee->id)],
                ['and',
                    ['d.organization_id' => (int) $employee->department],
                    new Expression('NOT EXISTS (SELECT 1 FROM {{%medsop_document_audience}} audience WHERE audience.document_id = d.id)'),
                ],
            ]);
    }

    private function audienceDocumentIds(int $employeeId): array
    {
        if ($this->audienceDocumentIds !== null) {
            return $this->audienceDocumentIds;
        }

        $grouped = [];
        foreach (DocumentAudience::find()->orderBy(['document_id' => SORT_ASC, 'id' => SORT_ASC])->all() as $audience) {
            $grouped[(int) $audience->document_id][] = $audience;
        }

        $resolver = new AudienceResolverService();
        $documentIds = [];
        foreach ($grouped as $documentId => $audiences) {
            if (isset($resolver->resolve($audiences)[$employeeId])) {
                $documentIds[] = (int) $documentId;
            }
        }

        return $this->audienceDocumentIds = $documentIds ?: [-1];
    }
}
