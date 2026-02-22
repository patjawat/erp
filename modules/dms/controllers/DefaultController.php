<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\components\OllamaSummarizer;

/**
 * Default controller for the `dms` module
 */
class DefaultController extends Controller
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
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * ตั้งค่าความยาวการสรุป AI (DMS)
     */
    public function actionAiSummarySettings()
    {
        $file = Yii::getAlias('@runtime/dms-ai-summary-settings.json');
        $current = 'medium';
        if (is_file($file) && is_readable($file)) {
            $data = @json_decode(file_get_contents($file), true);
            if (!empty($data['summary_length'])) {
                $current = $data['summary_length'];
            }
        }

        if (Yii::$app->request->isPost) {
            $length = Yii::$app->request->post('summary_length', 'medium');
            $allowed = array_keys(OllamaSummarizer::getSummaryLengthOptions());
            if (in_array($length, $allowed, true)) {
                $dir = dirname($file);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                if (file_put_contents($file, json_encode(['summary_length' => $length], JSON_UNESCAPED_UNICODE)) !== false) {
                    Yii::$app->session->setFlash('success', 'บันทึกการตั้งค่าความยาวการสรุปเรียบร้อยแล้ว');
                } else {
                    Yii::$app->session->setFlash('error', 'บันทึกไฟล์ไม่สำเร็จ');
                }
            }
            return $this->redirect(['ai-summary-settings']);
        }

        return $this->render('ai-summary-settings', [
            'currentLength' => $current,
            'options' => OllamaSummarizer::getSummaryLengthOptions(),
        ]);
    }
}
