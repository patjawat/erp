<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\bootstrap5\Html;
use app\models\Categorise;
use app\models\SiteSetting;
use app\models\VisitCounter;
use app\modules\hr\models\Employees;
use PHPUnit\Framework\Constraint\IsEmpty;

class SiteHelper extends Component
{
        public static function getInfo()
        {
                $model = Categorise::findOne(['name' => 'site']);
                $siteUrl = isset($model->data_json['website']) ? $model->data_json['website'] : '-';
                $siteName = isset($model->data_json['company_name']) ? $model->data_json['company_name'] : null;
                $director =  Employees::find()->where(['id' => $model->data_json['director_name'] ?? 0])->one();

                try {
                        $leader = Employees::findOne($model->data_json['leader']);
                } catch (\Throwable $th) {
                
                }

                
                try {
                        $layout = Categorise::findOne(['name' => 'layout']);
                        $_layout = isset($layout->data_json['layout']) ? $layout->data_json['layout'] : 'horizontal';

                } catch (\Throwable $th) {
                        $_layout = 'vertical';
                }


                return [
                        'layout' => $_layout,
                        'director' => $director,
                        'logo' => $model->logo() ?? null,
                        'company_name' => isset($model->data_json['company_name']) ? $model->data_json['company_name'] : null,
                        'doc_number' => isset($model->data_json['doc_number']) ? $model->data_json['doc_number'] : null, //เลขที่หนังสือ
                        'director_name' => isset($model->data_json['director_name']) ? $model->data_json['director_name'] : null,
                        'director_position' => isset($model->data_json['director_position']) ? $model->data_json['director_position'] : null,
                        'director_type' => isset($model->data_json['director_type']) ? $model->data_json['director_type'] : null,
                        'leader' => isset($model->data_json['leader']) ? $model->data_json['leader'] : null,
                        'leader_fullname' => isset($model->data_json['leader_fullname']) ? $model->data_json['leader_fullname'] : null,
                        'leader_position' => isset($leader->data_json['position_name_text']) ? ($leader->data_json['position_name_text'].$leader->data_json['position_level_text']) : '',
                        'address' => isset($model->data_json['address']) ? $model->data_json['address'] : null,
                        'province' => isset($model->data_json['province']) ? $model->data_json['province'] : null,
                        'phone' => isset($model->data_json['phone']) ? $model->data_json['phone'] : null,
                        'website' => Html::a($siteName, $siteUrl),
                        'line_liff_home' => isset($model->data_json['line_liff_home']) ? $model->data_json['line_liff_home'] : null,
                        'line_liff_profile' => isset($model->data_json['line_liff_profile']) ? $model->data_json['line_liff_profile'] : null,
                        'line_liff_login' => isset($model->data_json['line_liff_login']) ? $model->data_json['line_liff_login'] : null,
                        'line_liff_register' => isset($model->data_json['line_liff_register']) ? $model->data_json['line_liff_register'] : null,
                        'line_liff_user_connect' => isset($model->data_json['line_liff_user_connect']) ? $model->data_json['line_liff_user_connect'] : null,
                        'line_qrcode' => isset($model->data_json['line_qrcode']) ? $model->data_json['line_qrcode'] : null,
                        'pdpa_url' => isset($model->data_json['pdpa_url']) ? $model->data_json['pdpa_url'] : null,
                        'active_pdpa' => isset($model->data_json['active_pdpa']) ? $model->data_json['active_pdpa'] : 0,
                        'manual' => isset($model->data_json['manual']) ? $model->data_json['manual'] : 0,
                ];


        }

        //ข้อมูลของผู้อำนวยการ
        public static function viewDirector()
        {
                try {
                        $id = self::getInfo()['director_name'];
                        $employee = Employees::find()->where(['id' => $id])->one();

                        return [
                                'id' => $employee->id,
                                'avatar' => $employee->getAvatar(false),
                                'department' => $employee->departmentName(),
                                'fullname' => $employee->fullname,
                                'position_name' => $employee->positionName()
                        ];
                } catch (\Throwable $th) {
                        return [
                                'id' => '',
                                'avatar' => '',
                                'department' => '',
                                'fullname' => '',
                                'position_name' => '',
                        ];
                }
        }


        public static function getCategoriseByname($name)
        {
                return Categorise::findOne(['name' => $name]);
        }

        public static function getVisitCounter()
        {
                return [
                        'daily' => VisitCounter::find()->where(['vdate' => date('Y-m-d')])->sum('counter'),
                        'mount' => VisitCounter::find()->where(['like', 'vdate', date('Y-m') . '%', false])->sum('counter'),
                        'lastmount' => VisitCounter::find()->where(['like', 'vdate', date('Y-m', strtotime('-1 months')) . '%', false])->sum('counter'),
                        'total' => VisitCounter::find()->sum('counter'),
                ];
        }

        public static function visitorCounter()
        {
                $detect = self::detectServerVisitInfo();
                $check = VisitCounter::find()->where(['ip' => $detect['serv_ip'], 'device' => $detect['device'], 'vdate' => date('Y-m-d')])->one();

                if (!$check) {
                        $model = new VisitCounter([
                                'ip' => $detect['serv_ip'],
                                'device' => $detect['device'],
                                'vdate' => date('Y-m-d'),
                                'counter' => 1
                        ]);
                        $model->save(false);
                } else {
                        $check->ip = $detect['serv_ip'];
                        $check->device = $detect['device'];
                        $check->counter = $check->counter + 1;
                        $check->save(false);
                }
        }

        private static function detectServerVisitInfo()
        {
                $request = Yii::$app->request;
                $data = [];
                // Common user data
                $data['serv_ip'] = $request->getUserIP();
                $data['serv_user_agent'] = $request->getUserAgent();
                $data['serv_referer_url'] = $request->getReferrer();
                $data['serv_server_name'] = $request->getServerName();
                $data['serv_auth_user_id'] = \Yii::$app->user->isGuest ? null : Yii::$app->user->identity->id;
                $data['serv_port'] = $request->getPort();
                $data['serv_cookies'] = \yii\helpers\Json::encode($request->getCookies());
                $data['device'] = Yii::getAlias('@device');

                return $data;
        }

        public static function SiteSetting()
        {
                $model = self::getCategoriseByname('site');
                return $model;
        }

        // public static function btnCreate(string $text = null, string $url = null, string $class = null){
        //       return Html::a('<i class="fa fa-plus"></i> '.($text  ? $text : 'Add New'), [ $url ? $url : 'create'], ['class' => $class ? $class : 'btn btn-add-gradient open-modal']);
        // }
        public static function btnSave()
        {
                return Html::submitButton('<i class="fa-regular fa-circle-check"></i> บันทึก', ['class' => 'btn btn-primary submit-btn']);
        }

        public static function btnCancel($url)
        {
                return Html::a('<i class="fa-solid fa-rotate-right"></i> ยกเลิก', [$url], ['class' => 'btn btn-secondary']);
        }

        public static function actionView($model)
        {
                return Html::a('<i class="fa-solid fa-pen-to-square"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) . '&nbsp;'
                        . Html::a('<i class="fa-solid fa-trash"></i> ลบทิ้ง', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger',
                                'data' => [
                                        'confirm' => 'Are you sure you want to delete this item?',
                                        'method' => 'post',
                                ],
                        ]);
        }

        public static function ActionColumn($action, $id)
        {
                return '<div class="dropdown dropdown-action text-center">
            <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
            <div class="dropdown-menu dropdown-menu-right">' . Html::a('<i class="fa fa-pencil m-r-5"></i> Edit', ['update', 'id' => $id], ['class' => 'dropdown-item open-modal'])
                        . Html::a('<i class="fa fa-trash-o m-r-5"></i> delete', ['delete', 'id' => $id], [
                                'class' => 'dropdown-item',
                                'data' => [
                                        'confirm' => 'Are you sure you want to delete this item?',
                                        'method' => 'post',
                                ],
                        ]) . '
            </div>
      </div>';
        }

        public static function setDisplay($data)
        {
                \Yii::$app->session->set('display', $data);
        }

        public static function setDisplayList()
        {
                \Yii::$app->session->set('display', 'list');
        }

        public static function setDisplayGrid()
        {
                \Yii::$app->session->set('display', 'grid');
        }

        public static function getDisplay()
        {
                return \Yii::$app->session->get('display');
        }
}
