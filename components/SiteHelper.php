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
                // หากยังไม่ได้ตั้งค่า "site" ในตาราง categorise ให้คืนค่า default เพื่อไม่ให้ระบบพัง
                if (!$model) {
                        $defaultLayout = 'vertical';
                        return [
                                'layout' => $defaultLayout,
                                'director' => null,
                                'logo' => null,
                                'company_name' => null,
                                'hoscode' => null,
                                'doc_number' => null,
                                'director_name' => null,
                                'director_position' => null,
                                'director_type' => null,
                                'leader' => null,
                                'leader_fullname' => null,
                                'leader_position' => '',
                                'leader_signature_path' => null,
                                'address' => null,
                                'province' => null,
                                'phone' => null,
                                'website' => '-',
                                'line_liff_home' => null,
                                'line_liff_profile' => null,
                                'line_liff_login' => null,
                                'line_liff_register' => null,
                                'line_liff_user_connect' => null,
                                'line_qrcode' => null,
                                'pdpa_url' => null,
                                'active_pdpa' => 0,
                                'manual' => 0,
                        ];
                }

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
                //หัวหน้าเจ้าหน้าที่
                $leader = isset($model->data_json['leader']) ? Employees::findOne($model->data_json['leader']) : new Employees();

                return [
                        'layout' => $_layout,
                        'director' => $director,
                        'logo' => $model->logo() ?? null,
                        'company_name' => isset($model->data_json['company_name']) ? $model->data_json['company_name'] : null,
                        'hoscode' => isset($model->data_json['hoscode']) ? $model->data_json['hoscode'] : null,
                        'doc_number' => isset($model->data_json['doc_number']) ? $model->data_json['doc_number'] : null, //เลขที่หนังสือ
                        'director_name' => isset($model->data_json['director_name']) ? $model->data_json['director_name'] : null,
                        'director_position' => isset($model->data_json['director_position']) ? $model->data_json['director_position'] : null,
                        'director_type' => isset($model->data_json['director_type']) ? $model->data_json['director_type'] : null,
                        'leader' => isset($model->data_json['leader']) ? $model->data_json['leader'] : null,
                        'leader_fullname' => isset($model->data_json['leader_fullname']) ? $model->data_json['leader_fullname'] : null,
                        'leader_position' => isset($leader->data_json['position_name_text']) ? ($leader->data_json['position_name_text'].$leader->data_json['position_level_text']) : '',
                        'leader_signature_path' => $leader?->SignatureFilePath(),
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

        /**
         * ตรวจว่า emp_id นี้เป็นผู้อำนวยการตามตั้งค่าองค์กร (settings/company) หรือไม่
         * อิงจาก data_json['director_name'] ที่เก็บเป็น id
         */
        public static function isDirectorFromSettings($empId)
        {
                $id = self::getInfo()['director_name'] ?? null;
                return $id && (int) $id === (int) $empId;
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
                                'position_name' => $employee->positionName(),
                                'signature' => $employee->SignatureFilePath()
                        ];
                } catch (\Throwable $th) {
                        return [
                                'id' => '',
                                'avatar' => '',
                                'department' => '',
                                'fullname' => '',
                                'position_name' => '',
                                'signature' => '',

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

        /**
         * ข้อความและรูปแบบตัวอักษรบนแถบ Header จากตั้งค่าองค์กร (data_json)
         * เว้น header_brand_line1 / line2 ว่าง = แสดง HOSPITAL / ERP SYSTEM
         *
         * @param  array|\ArrayAccess|null $dataJson
         * @return array{line1: string, line2: string, line1_style: string, line2_style: string, google_font_href: ?string}
         */
        public static function resolveHeaderBrand($dataJson): array
        {
                $d = is_array($dataJson) ? $dataJson : [];
                $line1 = trim((string) ($d['header_brand_line1'] ?? ''));
                if ($line1 === '') {
                        $line1 = 'HOSPITAL';
                }
                $line2 = trim((string) ($d['header_brand_line2'] ?? ''));
                if ($line2 === '') {
                        $line2 = 'ERP SYSTEM';
                }
                $googleFont = self::sanitizeHeaderGoogleFontName($d['header_brand_google_font'] ?? '');
                $fontStack = $googleFont !== '' ? "'" . str_replace("'", '', $googleFont) . "', sans-serif" : null;

                $l1Size = self::sanitizeHeaderCssFontSize($d['header_brand_line1_size'] ?? '', '1.35rem');
                $l1Color = self::sanitizeHeaderCssColor($d['header_brand_line1_color'] ?? '', '#ffffff');
                $l1Weight = self::sanitizeHeaderFontWeight($d['header_brand_line1_weight'] ?? '', '700');

                $l2Size = self::sanitizeHeaderCssFontSize($d['header_brand_line2_size'] ?? '', '11px');
                $l2Color = self::sanitizeHeaderCssColor($d['header_brand_line2_color'] ?? '', 'rgba(255,255,255,0.5)');
                $l2Weight = self::sanitizeHeaderFontWeight($d['header_brand_line2_weight'] ?? '', '500');

                $fontCss = $fontStack ? 'font-family:' . $fontStack . ';' : '';
                $line1Style = 'line-height:1;letter-spacing:0.5px;font-size:' . $l1Size . ';color:' . $l1Color . ';font-weight:' . $l1Weight . ';' . $fontCss;
                $line2Style = 'line-height:1;letter-spacing:1px;font-size:' . $l2Size . ';color:' . $l2Color . ';font-weight:' . $l2Weight . ';' . $fontCss;

                $googleFontHref = null;
                if ($googleFont !== '') {
                        $qf = str_replace(' ', '+', $googleFont);
                        $googleFontHref = 'https://fonts.googleapis.com/css2?family=' . $qf . ':wght@300;400;500;600;700;800&display=swap';
                }

                return [
                        'line1' => $line1,
                        'line2' => $line2,
                        'line1_style' => $line1Style,
                        'line2_style' => $line2Style,
                        'google_font_href' => $googleFontHref,
                ];
        }

        private static function sanitizeHeaderGoogleFontName($name): string
        {
                $name = trim((string) $name);
                if ($name === '') {
                        return '';
                }
                if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\s\-]{0,62}$/', $name)) {
                        return '';
                }

                return $name;
        }

        private static function sanitizeHeaderCssFontSize($value, string $default): string
        {
                $value = trim((string) $value);
                if ($value === '') {
                        return $default;
                }
                if (preg_match('/^\d+(\.\d+)?(px|rem|em|%)$/', $value)) {
                        return $value;
                }

                return $default;
        }

        private static function sanitizeHeaderCssColor($value, string $default): string
        {
                $value = trim((string) $value);
                if ($value === '') {
                        return $default;
                }
                if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value)) {
                        return $value;
                }
                if (preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(\s*,\s*[\d.]+\s*)?\)$/', $value)) {
                        return $value;
                }

                return $default;
        }

        private static function sanitizeHeaderFontWeight($value, string $default): string
        {
                $value = strtolower(trim((string) $value));
                if ($value === '') {
                        return $default;
                }
                if (preg_match('/^(normal|bold|bolder|lighter|[1-9]00)$/', $value)) {
                        return $value;
                }

                return $default;
        }
}
