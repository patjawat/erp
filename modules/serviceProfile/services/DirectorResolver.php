<?php

namespace app\modules\serviceProfile\services;

use app\components\SiteHelper;
use app\modules\hr\models\Employees;
use Yii;

/** Resolves the hospital director from the organization setting first. */
class DirectorResolver
{
    public function resolve(): ?Employees
    {
        $configured = SiteHelper::getInfo()['director'] ?? null;
        if ($configured instanceof Employees) {
            // A selected director without a linked login is a setup error. Do
            // not silently route the document to an administrator instead.
            return $configured->user_id ? $configured : null;
        }

        // Backward-compatible fallback for installations that have not set the
        // director in settings/company yet.
        foreach (Employees::find()->andWhere(['not', ['user_id' => null]])->each(100) as $employee) {
            if (Yii::$app->authManager->checkAccess((int) $employee->user_id, 'serviceProfileDirectorApprove')) {
                return $employee;
            }
        }
        return null;
    }

    public function configured(): ?Employees
    {
        $employee = SiteHelper::getInfo()['director'] ?? null;
        return $employee instanceof Employees && $employee->user_id ? $employee : null;
    }

    public function isConfiguredDirector(?Employees $employee): bool
    {
        $configured = $this->configured();
        return $configured && $employee && (int) $configured->id === (int) $employee->id;
    }
}
