<?php

declare(strict_types=1);

namespace app\modules\ai\security;

use Yii;
use yii\web\ForbiddenHttpException;

class PermissionChecker
{
    public function requirePermission(string $permission): void
    {
        if (!$this->can($permission)) {
            throw new ForbiddenHttpException("Permission '{$permission}' is required.");
        }
    }

    public function can(string $permission): bool
    {
        if ($permission === '') {
            return false;
        }

        if ($permission === '@authenticated') {
            return $this->isAuthenticated();
        }

        if (!isset(Yii::$app->user) || Yii::$app->user->isGuest) {
            return false;
        }

        return Yii::$app->user->can($permission);
    }

    /**
     * @param array<int, string> $permissions
     */
    public function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public function isAuthenticated(): bool
    {
        return isset(Yii::$app->user) && !Yii::$app->user->isGuest;
    }

    public function currentUserId(): ?int
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return (int) Yii::$app->user->id;
    }
}
