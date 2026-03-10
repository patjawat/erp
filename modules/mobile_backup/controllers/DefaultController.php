<?php

namespace app\modules\mobile\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * Default controller for the `mobile` module.
 * Actions: index (dashboard), news, services, scan, profile.
 * ต้องล็อกอินก่อนเข้าหน้าใดๆ
 */
class DefaultController extends Controller
{
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
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    Yii::$app->user->setReturnUrl(Yii::$app->request->url);
                    return Yii::$app->response->redirect(['/mobile/auth/login']);
                },
            ],
        ];
    }

    /**
     * Dashboard with summary cards.
     */
    public function actionIndex()
    {
        $this->view->title = 'บริการออนไลน์';
        return $this->render('index', [
            'current_page' => 'home',
        ]);
    }

    /**
     * Vertical news feed (ข่าวสารและประกาศ).
     */
    public function actionNews()
    {
        $this->view->title = 'ข่าวสาร';
        return $this->render('news', [
            'current_page' => 'news',
        ]);
    }

    /**
     * ดูรายละเอียดข่าวสาร (ตัวอย่าง).
     */
    public function actionNewsView($id)
    {
        $this->view->title = 'รายละเอียดข่าว';
        return $this->render('news-view', [
            'current_page' => 'news',
            'id' => (int) $id,
        ]);
    }

    /**
     * รายการการแจ้งเตือนทั้งหมด.
     */
    public function actionNotifications()
    {
        $this->view->title = 'การแจ้งเตือน';
        return $this->render('notifications', [
            'current_page' => 'home',
        ]);
    }

    /**
     * 3x3 grid menu for tools.
     */
    public function actionServices()
    {
        $this->view->title = 'บริการ';
        return $this->render('services', [
            'current_page' => 'services',
        ]);
    }

    /**
     * Scan page (camera-UI mockup).
     */
    public function actionScan()
    {
        $this->view->title = 'สแกน';
        return $this->render('scan', [
            'current_page' => 'scan',
        ]);
    }

    /**
     * User profile and settings list.
     */
    public function actionProfile()
    {
        $this->view->title = 'ส่วนตัว';
        return $this->render('profile', [
            'current_page' => 'profile',
        ]);
    }

    /**
     * จองรถราชการ (mobile-first form).
     */
    public function actionBookingVehicle()
    {
        $this->view->title = 'จองรถราชการ';
        return $this->render('booking-vehicle', [
            'current_page' => 'services',
        ]);
    }

    /**
     * จองห้องประชุม (mobile-first: calendar, rooms, form).
     */
    public function actionBookingMeeting()
    {
        $this->view->title = 'จองห้องประชุม';
        return $this->render('booking-meeting', [
            'current_page' => 'services',
        ]);
    }

    /**
     * ขอลาออนไลน์ (mobile-first: balance, workflow, form with attachment).
     */
    public function actionLeaveRequest()
    {
        $this->view->title = 'ขอลาออนไลน์';
        return $this->render('leave-request', [
            'current_page' => 'services',
        ]);
    }

    /**
     * แจ้งซ่อม (mobile-first: type, location, description, camera/gallery upload, QR asset).
     */
    public function actionMaintenanceRequest()
    {
        $this->view->title = 'แจ้งซ่อม';
        return $this->render('maintenance-request', [
            'current_page' => 'services',
        ]);
    }

    /**
     * ดูข้อมูลครุภัณฑ์ (จาก QR หรือเปิดโดย id).
     */
    public function actionAsset($id = null)
    {
        $this->view->title = 'ข้อมูลครุภัณฑ์';
        return $this->render('asset', [
            'current_page' => 'services',
            'id' => $id,
        ]);
    }
}
