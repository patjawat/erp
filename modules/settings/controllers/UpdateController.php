<?php

namespace app\modules\settings\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * อัปเดตระบบ - แสดงเวอร์ชันและรัน migration / update-table จากเว็บ
 */
class UpdateController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'run-migrate' => ['post'],
                    'run-update-table' => ['post'],
                    'run-docker-pull' => ['post'],
                    'ajax-docker-pull' => ['post'],
                    'ajax-migrate' => ['post'],
                    'ajax-update-table' => ['post'],
                ],
            ],
        ];
    }

    /**
     * แสดงหน้าข้อมูลเวอร์ชันและขั้นตอนอัปเดต
     * @return string
     */
    public function actionIndex()
    {
        $version = Yii::$app->version;
        $dockerConfig = Yii::$app->params['dockerUpdate'] ?? [];
        return $this->render('index', [
            'version' => $version,
            'dockerConfig' => $dockerConfig,
        ]);
    }

    /**
     * รัน migration จากเว็บ (เฉพาะ admin)
     * @return \yii\web\Response
     */
    public function actionRunMigrate()
    {
        if (!Yii::$app->user->can('admin')) {
            Yii::$app->session->setFlash('error', 'ไม่มีสิทธิ์รัน Migration');
            return $this->redirect(['index']);
        }

        $yii = Yii::getAlias('@app/yii');
        if (!is_file($yii)) {
            Yii::$app->session->setFlash('error', 'ไม่พบไฟล์ yii สำหรับรันคำสั่ง');
            return $this->redirect(['index']);
        }

        $output = [];
        $returnVar = -1;
        exec('php ' . escapeshellarg($yii) . ' migrate --interactive=0 2>&1', $output, $returnVar);
        $message = implode("\n", $output);

        if ($returnVar === 0) {
            Yii::$app->session->setFlash('success', 'รัน Migration สำเร็จ');
        } else {
            Yii::$app->session->setFlash('error', 'รัน Migration ไม่สำเร็จ: ' . ($message ?: "รหัสออก $returnVar"));
        }
        return $this->redirect(['index']);
    }

    /**
     * รัน update-table (อัปเดต route / auth_item) จากเว็บ (เฉพาะ admin)
     * @return \yii\web\Response
     */
    public function actionRunUpdateTable()
    {
        if (!Yii::$app->user->can('admin')) {
            Yii::$app->session->setFlash('error', 'ไม่มีสิทธิ์รันอัปเดต Route');
            return $this->redirect(['index']);
        }

        $yii = Yii::getAlias('@app/yii');
        if (!is_file($yii)) {
            Yii::$app->session->setFlash('error', 'ไม่พบไฟล์ yii สำหรับรันคำสั่ง');
            return $this->redirect(['index']);
        }

        $output = [];
        $returnVar = -1;
        exec('php ' . escapeshellarg($yii) . ' update-table 2>&1', $output, $returnVar);
        $message = implode("\n", $output);

        if ($returnVar === 0) {
            Yii::$app->session->setFlash('success', 'อัปเดต Route สำเร็จ');
        } else {
            Yii::$app->session->setFlash('error', 'อัปเดต Route ไม่สำเร็จ: ' . ($message ?: "รหัสออก $returnVar"));
        }
        return $this->redirect(['index']);
    }

    /**
     * ดึง image และรีสตาร์ท container: docker pull + docker-compose up -d --no-deps --force-recreate (เฉพาะ admin)
     * ใช้ได้เมื่อ PHP รันบน host ที่มี docker หรือ container mount /var/run/docker.sock
     * @return \yii\web\Response
     */
    public function actionRunDockerPull()
    {
        if (!Yii::$app->user->can('admin')) {
            Yii::$app->session->setFlash('error', 'ไม่มีสิทธิ์รัน Docker อัปเดต');
            return $this->redirect(['index']);
        }

        $config = Yii::$app->params['dockerUpdate'] ?? [];
        $image = $config['image'] ?? 'patjawat/erp:latest';
        $composePath = isset($config['composePath']) ? rtrim($config['composePath'], '/') : null;
        $serviceName = $config['serviceName'] ?? 'app';

        if (empty($composePath) || !is_dir($composePath)) {
            Yii::$app->session->setFlash('error', 'กรุณาตั้งค่า params[dockerUpdate][composePath] ใน config/params.php เป็นโฟลเดอร์ที่มี docker-compose.yml');
            return $this->redirect(['index']);
        }

        $logs = [];
        $allOk = true;

        // 1) docker pull patjawat/erp:latest
        $cmd1 = 'docker pull ' . escapeshellarg($image) . ' 2>&1';
        exec($cmd1, $out1, $ret1);
        $logs[] = 'docker pull: ' . ($ret1 === 0 ? 'OK' : 'FAIL') . "\n" . implode("\n", $out1);
        if ($ret1 !== 0) {
            $allOk = false;
        }

        // 2) docker-compose up -d --no-deps --force-recreate <service>
        $composeDir = escapeshellarg($composePath);
        $cmd2 = 'cd ' . $composeDir . ' && docker-compose up -d --no-deps --force-recreate ' . escapeshellarg($serviceName) . ' 2>&1';
        exec($cmd2, $out2, $ret2);
        $logs[] = 'docker-compose up: ' . ($ret2 === 0 ? 'OK' : 'FAIL') . "\n" . implode("\n", $out2);
        if ($ret2 !== 0) {
            $allOk = false;
        }

        $message = implode("\n---\n", $logs);
        if ($allOk) {
            Yii::$app->session->setFlash('success', 'ดึง Image และรีสตาร์ท Container สำเร็จ แนะนำให้กด "รัน Migration" หลังจากนี้');
        } else {
            Yii::$app->session->setFlash('error', 'Docker อัปเดตมีข้อผิดพลาด (อาจเป็นสิทธิ์หรือไม่มี docker บนเครื่อง): ' . $message);
        }
        return $this->redirect(['index']);
    }

    /**
     * AJAX: ดึง image + recreate container คืนค่า JSON (เฉพาะ admin)
     */
    public function actionAjaxDockerPull()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!Yii::$app->user->can('admin')) {
            return ['success' => false, 'message' => 'ไม่มีสิทธิ์', 'output' => ''];
        }
        $config = Yii::$app->params['dockerUpdate'] ?? [];
        $image = $config['image'] ?? 'patjawat/erp:latest';
        $composePath = isset($config['composePath']) ? rtrim($config['composePath'], '/') : null;
        $serviceName = $config['serviceName'] ?? 'app';
        if (empty($composePath) || !is_dir($composePath)) {
            return ['success' => false, 'message' => 'ไม่ได้ตั้งค่า composePath', 'output' => ''];
        }
        $logs = [];
        $allOk = true;
        exec('docker pull ' . escapeshellarg($image) . ' 2>&1', $out1, $ret1);
        $logs[] = implode("\n", $out1);
        if ($ret1 !== 0) $allOk = false;
        $composeDir = escapeshellarg($composePath);
        exec('cd ' . $composeDir . ' && docker-compose up -d --no-deps --force-recreate ' . escapeshellarg($serviceName) . ' 2>&1', $out2, $ret2);
        $logs[] = implode("\n", $out2);
        if ($ret2 !== 0) $allOk = false;
        return ['success' => $allOk, 'message' => $allOk ? 'สำเร็จ' : 'มีข้อผิดพลาด', 'output' => implode("\n---\n", $logs)];
    }

    /**
     * AJAX: รัน migration คืนค่า JSON (เฉพาะ admin)
     */
    public function actionAjaxMigrate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!Yii::$app->user->can('admin')) {
            return ['success' => false, 'message' => 'ไม่มีสิทธิ์', 'output' => ''];
        }
        $yii = Yii::getAlias('@app/yii');
        if (!is_file($yii)) {
            return ['success' => false, 'message' => 'ไม่พบไฟล์ yii', 'output' => ''];
        }
        $output = [];
        $returnVar = -1;
        exec('php ' . escapeshellarg($yii) . ' migrate --interactive=0 2>&1', $output, $returnVar);
        $message = implode("\n", $output);
        return ['success' => $returnVar === 0, 'message' => $returnVar === 0 ? 'สำเร็จ' : 'ไม่สำเร็จ', 'output' => $message];
    }

    /**
     * AJAX: รัน update-table คืนค่า JSON (เฉพาะ admin)
     */
    public function actionAjaxUpdateTable()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!Yii::$app->user->can('admin')) {
            return ['success' => false, 'message' => 'ไม่มีสิทธิ์', 'output' => ''];
        }
        $yii = Yii::getAlias('@app/yii');
        if (!is_file($yii)) {
            return ['success' => false, 'message' => 'ไม่พบไฟล์ yii', 'output' => ''];
        }
        $output = [];
        $returnVar = -1;
        exec('php ' . escapeshellarg($yii) . ' update-table 2>&1', $output, $returnVar);
        $message = implode("\n", $output);
        return ['success' => $returnVar === 0, 'message' => $returnVar === 0 ? 'สำเร็จ' : 'ไม่สำเร็จ', 'output' => $message];
    }
}
