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
     * ตั้งค่า AI สรุป (DMS): ใช้ Ollama เท่านั้น + ความยาว default
     */
    public function actionAiSummarySettings()
    {
        $file = Yii::getAlias('@runtime/dms-ai-summary-settings.json');
        $data = ['summary_length' => 'medium'];
        if (is_file($file) && is_readable($file)) {
            $loaded = @json_decode(file_get_contents($file), true);
            if (is_array($loaded) && isset($loaded['summary_length'])) {
                $data['summary_length'] = $loaded['summary_length'];
            }
        }
        $lengthAllowed = array_keys(OllamaSummarizer::getSummaryLengthOptions());
        $currentLength = isset($data['summary_length']) && in_array($data['summary_length'], $lengthAllowed, true)
            ? $data['summary_length'] : 'medium';

        if (Yii::$app->request->isPost) {
            $length = Yii::$app->request->post('summary_length', 'medium');
            if (!in_array($length, $lengthAllowed, true)) {
                $length = 'medium';
            }
            $data = ['summary_length' => $length];
            $dir = dirname($file);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            if (file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE)) !== false) {
                Yii::$app->session->setFlash('success', 'บันทึกการตั้งค่าเรียบร้อยแล้ว');
            } else {
                Yii::$app->session->setFlash('error', 'บันทึกไฟล์ไม่สำเร็จ');
            }
            return $this->redirect(['ai-summary-settings']);
        }

        return $this->render('ai-summary-settings', [
            'currentLength' => $currentLength,
            'options' => OllamaSummarizer::getSummaryLengthOptions(),
        ]);
    }
}
