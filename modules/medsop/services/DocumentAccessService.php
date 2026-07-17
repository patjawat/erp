<?php

namespace app\modules\medsop\services;

use app\modules\hr\models\Employees;
use app\modules\medsop\models\Document;
use Yii;
use yii\db\ActiveQuery;

class DocumentAccessService
{
    public const PERMISSION_ADMIN = 'medsop.admin';
    public const PERMISSION_REVIEW = 'medsop.review';
    public const PERMISSION_VIEW_ALL = 'medsop.viewAll';
    public const PERMISSION_VIEW_PUBLISHED = 'medsop.viewPublished';

    private $employee;

    public function currentEmployee(): ?Employees
    {
        if ($this->employee === null && !Yii::$app->user->isGuest) {
            $this->employee = Employees::find()->where(['user_id' => Yii::$app->user->id])->one() ?: false;
        }
        return $this->employee ?: null;
    }

    public function isAdmin(): bool
    {
        return !Yii::$app->user->isGuest && Yii::$app->user->can(self::PERMISSION_ADMIN);
    }

    public function canReview(): bool
    {
        return !Yii::$app->user->isGuest && Yii::$app->user->can(self::PERMISSION_REVIEW);
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
        return $employee !== null
            && $document->status === Document::STATUS_PUBLISHED
            && (int) $document->organization_id === (int) $employee->department;
    }

    public function applyVisibleScope(ActiveQuery $query): void
    {
        if ($this->canViewAll()) {
            return;
        }
        $employee = $this->currentEmployee();
        $query->andWhere([
            'd.status' => Document::STATUS_PUBLISHED,
            'd.organization_id' => $employee ? (int) $employee->department : -1,
        ]);
    }
}
