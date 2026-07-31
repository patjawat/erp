<?php

namespace app\modules\pm\controllers;

use Yii;
use app\modules\pm\models\PmSetting;
use app\modules\pm\models\Projects;
use app\modules\hr\models\Organization;
use app\modules\medsop\models\OrganizationSetting;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * ตั้งค่าโมดูลโครงการ — รูปแบบรหัสโครงการ + รหัสย่อหน่วยงาน
 */
class SettingsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['index' => ['get', 'post']],
            ],
        ];
    }

    public function actionIndex()
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            // 1) รูปแบบรหัสโครงการ
            $pattern = trim((string) ($post['code_pattern'] ?? ''));
            if ($pattern !== '') {
                PmSetting::setValue(PmSetting::CODE_PATTERN, $pattern);
            }

            // 2) รหัสย่อหน่วยงาน -> medsop_organization_setting.code
            $errors = [];
            foreach ((array) ($post['org_code'] ?? []) as $orgId => $code) {
                $orgId = (int) $orgId;
                $code = strtoupper(trim((string) $code));
                $setting = OrganizationSetting::findOne($orgId)
                    ?: new OrganizationSetting(['organization_id' => $orgId, 'active' => 1]);
                $newCode = $code === '' ? null : $code;
                if ((string) $setting->code === (string) $newCode) {
                    continue; // ไม่เปลี่ยน
                }
                $setting->code = $newCode;
                $setting->updated_by = Yii::$app->user->id;
                $setting->updated_at = date('Y-m-d H:i:s');
                if (!isset($setting->created_at)) {
                    $setting->created_at = date('Y-m-d H:i:s');
                }
                if (!$setting->save()) {
                    $name = Organization::findOne($orgId)->name ?? ('#' . $orgId);
                    $errors[] = $name . ': ' . implode(', ', $setting->getFirstErrors());
                }
            }

            if ($errors) {
                Yii::$app->session->setFlash('warning', 'บันทึกบางส่วนไม่สำเร็จ: ' . implode(' | ', $errors));
            } else {
                Yii::$app->session->setFlash('success', 'บันทึกการตั้งค่าเรียบร้อย');
            }
            return $this->redirect(['index']);
        }

        $pattern = PmSetting::value(PmSetting::CODE_PATTERN, 'P-{org}-{yy}{sequence}');

        $orgs = Organization::find()
            ->where(['active' => 1])
            ->andWhere(['>', 'lvl', 0])
            ->orderBy(['lft' => SORT_ASC])
            ->all();

        $codes = [];
        foreach (OrganizationSetting::find()->all() as $s) {
            $codes[$s->organization_id] = $s->code;
        }

        // ตัวอย่างรหัสจากหน่วยงานแรกที่มีรหัสย่อ
        $sampleOrgId = null;
        foreach ($codes as $oid => $c) {
            if ($c) { $sampleOrgId = $oid; break; }
        }
        $sample = Projects::generateCode($sampleOrgId, (int) (date('Y') + 543));

        return $this->render('index', [
            'pattern' => $pattern,
            'orgs' => $orgs,
            'codes' => $codes,
            'sample' => $sample,
        ]);
    }
}
