<?php

namespace app\modules\usermanager\controllers;

use Yii;
use yii\web\Controller;
use app\models\VisitCounter;
use app\modules\usermanager\models\User;
use app\modules\usermanager\models\Session;
use app\modules\usermanager\models\UserSearch;

/**
 * Default controller for the `usermanager` module
 */
class DefaultController extends Controller
{
    /**
     * จุดเข้าโมดูล — ไปหน้า dashboard
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        return $this->redirect(['/usermanager/default/dashboard']);
    }

    /**
     * Dashboard ภาพรวมระบบจัดการผู้ใช้งาน
     * @return string
     */
    public function actionDashboard()
    {
        $totalUsers = User::find()->count();
        $activeUsers = User::find()->where(['status' => User::STATUS_ACTIVE])->count();
        $inactiveUsers = User::find()->where(['status' => User::STATUS_DELETED])->count();
        $authManager = Yii::$app->authManager;
        $roles = $authManager ? $authManager->getRoles() : [];
        $rolesCount = count($roles);
        $roleNames = array_keys($roles);
        $usersPerRole = array_fill_keys($roleNames, 0);
        if ($rolesCount > 0 && Yii::$app->db->getSchema()->getTableSchema('auth_assignment', true) !== null) {
            $rows = (new \yii\db\Query())
                ->select(['item_name', 'COUNT(*) as cnt'])
                ->from('auth_assignment')
                ->where(['item_name' => $roleNames])
                ->groupBy('item_name')
                ->all();
            foreach ($rows as $r) {
                $usersPerRole[$r['item_name']] = (int) $r['cnt'];
            }
        }
        // รายละเอียดบทบาท: ชื่อ role => [description, count] สำหรับแสดง "รายละเอียด (ชื่อ role)"
        $roleDetails = [];
        foreach ($roles as $name => $role) {
            $roleDetails[$name] = [
                'description' => $role->description ?? $name,
                'count' => $usersPerRole[$name] ?? 0,
            ];
        }
        $activeSessions = 0;
        $onlineUsers = [];
        try {
            if (Yii::$app->db->getSchema()->getTableSchema(Session::tableName(), true) !== null) {
                $activeSessions = Session::find()->where(['>', 'expire', time()])->count();
                $schema = Yii::$app->db->getSchema()->getTableSchema(Session::tableName(), true);
                if ($schema && $schema->getColumn('user_id') !== null) {
                    $onlineUserIds = Session::find()
                        ->select('user_id')
                        ->where(['>', 'expire', time()])
                        ->andWhere(['not', ['user_id' => null]])
                        ->distinct()
                        ->column();
                    if (!empty($onlineUserIds)) {
                        $onlineUsers = User::find()
                            ->where([User::tableName() . '.id' => array_unique($onlineUserIds)])
                            ->joinWith(['employee'])
                            ->limit(10)
                            ->all();
                    }
                }
            }
        } catch (\Throwable $e) {
            // ตาราง session อาจไม่มี (ใช้ session แบบ file/cache) — แสดง 0
        }

        // สถิติการเข้าใช้งานเว็บจากตาราง visit_counter
        $visitSummary = [
            'daily' => 0,
            'month' => 0,
            'lastMonth' => 0,
            'total' => 0,
        ];
        $visitChart = [
            'labels' => [],
            'series' => [],
        ];
        try {
            if (Yii::$app->db->getSchema()->getTableSchema('visit_counter', true) !== null) {
                $today = date('Y-m-d');
                $thisMonth = date('Y-m');
                $lastMonth = date('Y-m', strtotime('-1 month'));

                $visitSummary['daily'] = (int) VisitCounter::find()->where(['vdate' => $today])->sum('counter');
                $visitSummary['month'] = (int) VisitCounter::find()
                    ->where(['like', 'vdate', $thisMonth . '%', false])
                    ->sum('counter');
                $visitSummary['lastMonth'] = (int) VisitCounter::find()
                    ->where(['like', 'vdate', $lastMonth . '%', false])
                    ->sum('counter');
                $visitSummary['total'] = (int) VisitCounter::find()->sum('counter');

                $rows = VisitCounter::find()
                    ->select(['vdate', 'SUM(counter) AS c'])
                    ->groupBy('vdate')
                    ->orderBy(['vdate' => SORT_DESC])
                    ->limit(14)
                    ->asArray()
                    ->all();
                $rows = array_reverse($rows);
                foreach ($rows as $row) {
                    $visitChart['labels'][] = $row['vdate'];
                    $visitChart['series'][] = (int) $row['c'];
                }
            }
        } catch (\Throwable $e) {
            // ถ้าไม่มีตารางหรือ query error ให้ใช้ค่าเริ่มต้น (0 / ว่าง)
        }

        $searchModel = new UserSearch();
        $recentProvider = $searchModel->search([]);
        $recentProvider->query->joinWith(['employee']);
        $recentProvider->query->orderBy([User::tableName() . '.updated_at' => SORT_DESC]);
        $recentProvider->pagination = ['pageSize' => 8, 'pageParam' => 'recent-page'];

        return $this->render('dashboard', [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'inactiveUsers' => $inactiveUsers,
            'rolesCount' => $rolesCount,
            'activeSessions' => $activeSessions,
            'onlineUsers' => $onlineUsers,
            'visitSummary' => $visitSummary,
            'visitChart' => $visitChart,
            'usersPerRole' => $usersPerRole,
            'roleDetails' => $roleDetails,
            'recentProvider' => $recentProvider,
        ]);
    }

    /**
     * แสดงรายชื่อผู้ใช้ที่ได้สิทธิในแต่ละบทบาท (หรือบทบาทที่เลือก)
     * @param string|null $role ชื่อบทบาท ถ้าไม่ระบุจะแสดงทุกบทบาท
     * @return string
     */
    public function actionUsersByRole($role = null)
    {
        $authManager = Yii::$app->authManager;
        $roles = $authManager ? $authManager->getRoles() : [];
        $roleDetails = [];
        $roleUsers = [];

        if (Yii::$app->db->getSchema()->getTableSchema('auth_assignment', true) === null) {
            return $this->render('users-by-role', [
                'role' => $role,
                'roleDetails' => [],
                'roleUsers' => [],
                'roles' => [],
            ]);
        }

        $roleNames = $role !== null && isset($roles[$role]) ? [$role] : array_keys($roles);

        foreach ($roleNames as $name) {
            $roleObj = $roles[$name] ?? null;
            $roleDetails[$name] = [
                'description' => $roleObj && $roleObj->description ? $roleObj->description : $name,
            ];
            $userIds = (new \yii\db\Query())
                ->select('user_id')
                ->from('auth_assignment')
                ->where(['item_name' => $name])
                ->column();
            $roleUsers[$name] = [];
            if (!empty($userIds)) {
                $roleUsers[$name] = User::find()
                    ->where([User::tableName() . '.id' => $userIds])
                    ->joinWith(['employee'])
                    ->orderBy([User::tableName() . '.username' => SORT_ASC])
                    ->all();
            }
        }

        return $this->render('users-by-role', [
            'role' => $role,
            'roleDetails' => $roleDetails,
            'roleUsers' => $roleUsers,
            'roles' => $roles,
        ]);
    }
}
