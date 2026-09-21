<?php

namespace app\modules\usermanager\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/** Reusable RBAC role bundles. A template is an RBAC parent role named template.*. */
class TemplateController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['admin']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['create' => ['GET', 'POST'], 'update' => ['GET', 'POST']],
            ],
        ];
    }

    public function actionIndex()
    {
        $auth = Yii::$app->authManager;
        $templates = [];
        foreach ($auth->getRoles() as $role) {
            if (strncmp($role->name, 'template.', 9) !== 0) {
                continue;
            }
            $children = array_filter($auth->getChildren($role->name), static fn ($child) => $child->type === 1);
            $templates[] = ['role' => $role, 'children' => $children];
        }
        usort($templates, static fn ($a, $b) => strcmp($a['role']->name, $b['role']->name));
        return $this->render('index', ['templates' => $templates]);
    }

    public function actionCreate()
    {
        return $this->edit(null);
    }

    public function actionUpdate($id)
    {
        if (strncmp((string) $id, 'template.', 9) !== 0 || Yii::$app->authManager->getRole($id) === null) {
            throw new NotFoundHttpException('ไม่พบ template');
        }
        return $this->edit($id);
    }

    private function edit(?string $id)
    {
        $auth = Yii::$app->authManager;
        $template = $id === null ? null : $auth->getRole($id);
        $slug = $template ? substr($template->name, 9) : '';
        $description = $template->description ?? '';
        $roleNames = $template ? array_keys(array_filter($auth->getChildren($template->name), static fn ($child) => $child->type === 1)) : [];
        $errors = [];

        if (Yii::$app->request->isPost) {
            $slug = trim((string) Yii::$app->request->post('slug', ''));
            $description = trim((string) Yii::$app->request->post('description', ''));
            $roleNames = array_values(array_unique(array_filter((array) Yii::$app->request->post('roles', []), 'is_string')));
            if (!preg_match('/^[a-z][a-z0-9_-]{1,49}$/', $slug)) {
                $errors[] = 'รหัสใช้ตัวอักษรอังกฤษตัวเล็กขึ้นต้น ตามด้วยตัวเลข ขีดกลาง หรือขีดล่าง รวม 2–50 ตัว';
            }
            if ($description === '') {
                $errors[] = 'กรุณาระบุชื่อ template';
            }
            if (!$roleNames) {
                $errors[] = 'เลือกบทบาทอย่างน้อยหนึ่งรายการ';
            }
            foreach ($roleNames as $name) {
                if (strncmp($name, 'template.', 9) === 0 || $auth->getRole($name) === null) {
                    $errors[] = 'มีบทบาทที่เลือกไม่ถูกต้อง';
                    break;
                }
            }
            if ($template === null && $auth->getRole('template.' . $slug) !== null) {
                $errors[] = 'รหัส template นี้ถูกใช้งานแล้ว';
            }
            if ($template !== null && $template->name !== 'template.' . $slug) {
                $errors[] = 'ไม่สามารถเปลี่ยนรหัส template หลังสร้างแล้ว';
            }
            if (!$errors) {
                Yii::$app->db->transaction(function () use ($auth, $template, $slug, $description, $roleNames) {
                    if ($template === null) {
                        $template = $auth->createRole('template.' . $slug);
                        $template->description = $description;
                        $auth->add($template);
                    } else {
                        $template->description = $description;
                        $auth->update($template->name, $template);
                        foreach ($auth->getChildren($template->name) as $child) {
                            $auth->removeChild($template, $child);
                        }
                    }
                    foreach ($roleNames as $name) {
                        $auth->addChild($template, $auth->getRole($name));
                    }
                    $auth->invalidateCache();
                });
                Yii::$app->session->setFlash('success', 'บันทึก template แล้ว');
                return $this->redirect(['index']);
            }
        }

        $roles = array_filter($auth->getRoles(), static fn ($role) => strncmp($role->name, 'template.', 9) !== 0);
        uasort($roles, static fn ($a, $b) => strcasecmp($a->name, $b->name));
        return $this->render('form', compact('template', 'slug', 'description', 'roleNames', 'roles', 'errors'));
    }
}
