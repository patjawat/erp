<?php

/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\BaseConsole;
use app\modules\hr\models\Employees;
use app\modules\helpdesk\models\Helpdesk;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 */
class ImportHelpdeskController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     *
     * @return int Exit code
     */
    public function actionIndex()
    {
        $sql = '';
        if (BaseConsole::confirm('ยืนยัน ??')) {
        $this->General();
        $this->Computer();
        $this->Medical();
        $this->UpdateServiceGeneral();
        $this->UpdateServiceComputer();
        }
    }

    // งานซ่อมบำรุง
    public function General()
    {
        $sql = 'SELECT 
                    r.ID,
                    REPAIR_ID,
                    YEAR_ID,
                    REPAIR_NAME,
                    ARTICLE_ID,
                    OTHER_ID,
                    OTHER_NAME,
                    USER_REQUEST_ID,
                    USRE_REQUEST_NAME,
                    DATE_TIME_REQUEST,
                    SYMPTOM,
                    REPAIR_DATE,
                    REPAIR_TIME,
                    LOCATION_SEE_ID,
                    LOCATIONLEVEL_SEE_ID,
                    LOCATIONLEVELROOM_SEE_ID,
                    DEPARTMENT_SUB_ID,
                    DEPARTMENT_ID,
                    DATE_SAVE,
                    REPAIR_STATUS,
                    STATUS,
                    PRIORITY_ID,
                    TECH_RECEIVE_ID,
                    TECH_RECEIVE_NAME,
                    TECH_RECEIVE_DATE,
                    TECH_RECEIVE_TIME,
                    TECH_RECEIVE_COMMENT,
                    TECH_REPAIR_DATE,
                    TECH_REPAIR_TIME,
                    TECH_REPAIR_ID,
                    TECH_REPAIR_NAME,
                    TECH_SUCCESS_DATE,
                    TECH_SUCCESS_TIME,
                    REPAIR_IMG,
                    REPAIR_SUCCESS_IMG,
                    REPAIR_SUCCESS_REMARK,
                    IS_ARTICLE,
                    REPAIR_SCORE,
                    REPAIR_ASSESSMENT,
                    REPAIR_COMMENT,
                    DEPAIR_DEP_SUB_SUB_ID,
                    BOOK_SUB_SUB_NUM,
                    IMAGES,
                    APP_TYPE_SAVE,
                    TECH_WANT_ID,
                    TECH_WANT_NAME,
                    FANCINESS_SCORE,
                    FANCINESS_REMARK,
                    FANCINESS_PERSON_ID,
                    FANCINESS_DATE,
                    CANCEL_COMMENT,
                    CANCEL_USER_EDIT_ID,
                    CANCEL_MANAGER_EDIT_ID,
                    OUTSIDE_ACTIVE,
                    OUTSIDE_COMMENT,
                    OUTSIDE_TOOL,
                    OUTSIDE_SHOP,
                    OUTSIDE_EMP,
                    DEAL_ACTIVE,
                    DEAL_COMMENT,
                    GETBACK_ACTIVE,
                    GETBACK_DATE,
                    GETBACK_PERSON,
                    REPAIR_SYSTEM,s.*,t.* FROM `informrepair_index` r
                LEFT JOIN informrepair_status s ON s.STATUS_ID = r.STATUS
                LEFT JOIN `informrepair_tech` t ON t.REPAIRTECH_ID = r.TECH_RECEIVE_ID';
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        // if (BaseConsole::confirm('งานซ่อมบำรุง ' . count($querys) . ' รายการ ยืนยัน ??')) {
            $num = 1;
            $total = count($querys);
            foreach ($querys as $item) {
                try {
                    $emp = $this->Person($item['USER_REQUEST_ID']);
                    $checkModel = Helpdesk::findOne(['title' => $item['SYMPTOM']]);
                    if (!$checkModel) {
                        $model = new Helpdesk();
                    } else {
                        $model = $checkModel;
                    }
                    $model->repair_group = 1;
                    $model->name = 'repair';
                    $model->emp_id = $emp->id;
                    $model->thai_year = $item['YEAR_ID'];
                    $model->title = $item['SYMPTOM'];
                    $model->created_by = $emp->user_id;
                    $model->status = $item['STATUS_ID'];
                    $model->created_at = $item['DATE_TIME_REQUEST'];
                    $model->date_start = $item['REPAIR_DATE'];
                    $model->date_end = $item['TECH_REPAIR_DATE'];
                    $model->rating = $item['REPAIR_SCORE'];
                    $model->data_json = [
                        'time_start' => $item['REPAIR_TIME'],
                        'end_start' => $item['TECH_REPAIR_TIME'],
                        'status' => $item['STATUS_ID'],
                        'status_name' => $item['STATUS_NAME_TH'],
                        'note' => '',
                        'phone' => '',
                        'urgency' => '1',
                        'location' => 'งานซ่อมบำรุง',
                        'repair_type' => $item['OUTSIDE_ACTIVE'] == true ? 'ซ่อมภายนอก' : 'ซ่อมภายใน',
                        'send_type' => 'general',
                        'accept_emp_id' => '',
                        'accept_name' => '',
                        'accept_time' => $item['TECH_RECEIVE_DATE'] . ' ' . $item['TECH_RECEIVE_TIME'],
                        'create_name' => $emp->fullname,
                        'status_name' => 'ร้องขอ',
                        'location_other' => '',
                        'technician_req' => '0',
                        'technician_name' => '',
                        'old_data' => $item
                    ];
                    if ($model->save(false)) {
                        $percentage = (($num++) / $total) * 100;
                        echo 'ดำเนินการแล้ว : ' . number_format($percentage, 2) . "%\n";
                        $this->TechRepair($model->id, $item['TECH_REPAIR_ID']);
                        $this->serviceItems($model, $item);
                    } else {
                        echo "Save Error \n";
                        print_r($model->getErrors());
                    }
                } catch (\Throwable $th) {
                }
            }
           
        // }
    }

    // งานคอมพิวเตอร์

    public function Computer()
    {
        $sql = 'SELECT 
        REPAIR_ID,
  YEAR_ID,
  ARTICLE_ID,
  ARTICLE_NUM,
  OTHER_NAME,
  USER_REQUEST_ID,
  USRE_REQUEST_NAME,
  REPAIR_NAME,
  SYMPTOM,
  PRIORITY_ID,
  REPAIR_BY_ID,
  DATE_TIME_REQUEST,
  DATE_SAVE,
  DATE_WANT_USES,
  TIME_WANT_USES,
  DATE_TIME_UPDATE,
  LOCATION_SEE_ID,
  LOCATIONLEVEL_SEE_ID,
  LOCATIONLEVELROOM_SEE_ID,
  TECH_REPAIR_ID,
  TECH_REPAIR_NAME,
  REPAIR_STATUS,
  COM_ID,
  RECEIVE_DATE_TIME,
  RECEIVE_BY_ID,
  RECEIVE_BY_NAME,
  RECEIVE_WILL_DATE,
  RECEIVE_WILL_TIME,
  RECEIVE_COMMENT,
  RECEIVE_ADVICE,
  REPAIR_WAIT_DETAIL,
  REPAIR_WAIT_DATETIME,
  REPAIR_ALERT,
  REPAIR_ALERT_DATE_ACCEPT,
  REPAIR_ALERT_HR_ID,
  REPAIR_TYPE_ID,
  REPAIR_DEP_ID,
  DEPAIR_DEP_SUB_ID,
  SERVICE_LIST_ID,
  SERVICE_REPAIR,
  BOOK_SUB_SUB_NUM,
  DEPAIR_DEP_SUB_SUB_ID,
  FANCINESS_SCORE,
  FANCINESS_REMARK,
  FANCINESS_PERSON_ID,
  FANCINESS_DATE,
  updated_at,
  created_at,
  CANCEL_COMMENT,
  CANCEL_USER_EDIT_ID,
  TECH_RECEIVE_DATE,
  TECH_RECEIVE_TIME,
  TECH_RECEIVE_ID,
  TECH_RECEIVE_NAME,
  TECH_RECEIVE_COMMENT,
  TECH_REPAIR_DATE,
  TECH_REPAIR_TIME,
  TECH_SUCCESS_DATE,
  TECH_SUCCESS_TIME,
  REPAIR_DATE,
  REPAIR_TIME,
  REPAIR_COMMENT,
  OUTSIDE_ACTIVE,
  OUTSIDE_COMMENT,
  OUTSIDE_TOOL,
  OUTSIDE_SHOP,
  OUTSIDE_EMP,
  REPAIR_SUCCESS_REMARK,
  DEAL_ACTIVE,
  DEAL_COMMENT,
  GETBACK_ACTIVE,
  GETBACK_DATE,
  GETBACK_PERSON,
  REPAIR_SYSTEM FROM `informcom_repair` r';
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        // if (BaseConsole::confirm('งานซ่อมบำรุง ' . count($querys) . ' รายการ ยืนยัน ??')) {
            $num = 1;
            $total = count($querys);
            foreach ($querys as $item) {
                // try {
                $emp = $this->Person($item['USER_REQUEST_ID']);
                $checkModel = Helpdesk::findOne(['title' => $item['SYMPTOM']]);
                if (!$checkModel) {
                    $model = new Helpdesk();
                } else {
                    $model = $checkModel;
                }
                $model->repair_group = 2;
                $model->name = 'repair';
                $model->emp_id = $emp->id;
                $model->thai_year = $item['YEAR_ID'];
                $model->title = $item['SYMPTOM'];
                $model->created_by = $emp->user_id;
                $model->status = $item['REPAIR_STATUS'];
                $model->created_at = $item['DATE_TIME_REQUEST'];
                $model->date_start = $item['TECH_REPAIR_DATE'];
                $model->date_end = $item['TECH_SUCCESS_DATE'];
                $model->rating = $item['FANCINESS_SCORE'];
                $model->data_json = [
                    'time_start' => $item['REPAIR_TIME'],
                    'end_start' => $item['TECH_REPAIR_TIME'],
                    'status' => $item['REPAIR_STATUS'],
                    'note' => $item['REPAIR_SUCCESS_REMARK'],
                    'phone' => '',
                    'urgency' => '1',
                    'location' => 'งานซ่อมบำรุง',
                    'repair_type' => $item['OUTSIDE_ACTIVE'] == true ? 'ซ่อมภายนอก' : 'ซ่อมภายใน',
                    'send_type' => 'general',
                    'accept_emp_id' => '',
                    'accept_name' => '',
                    'accept_time' => $item['TECH_RECEIVE_DATE'] . ' ' . $item['TECH_RECEIVE_TIME'],
                    'create_name' => $emp->fullname,
                    'status_name' => 'ร้องขอ',
                    'location_other' => '',
                    'technician_req' => '0',
                    'technician_name' => '',
                    'old_data' => $item
                ];
                if ($model->save(false)) {
                    $percentage = (($num++) / $total) * 100;
                    echo 'ดำเนินการแล้ว : ' . number_format($percentage, 2) . "%\n";
                    $this->TechRepair($model->id, $item['TECH_REPAIR_ID']);
                    // $this->serviceItems($model, $item);
                } else {
                    echo "Save Error \n";
                    print_r($model->getErrors());
                }

                // } catch (\Throwable $th) {

                // }
            }
        // }
    }


    public function Medical()
    {
        $sql = 'SELECT * FROM `asset_care_repair`';
        $querys = Yii::$app->db2->createCommand($sql)->queryAll();
        // if (BaseConsole::confirm('งานซ่อมบำรุง ' . count($querys) . ' รายการ ยืนยัน ??')) {
            $num = 1;
            $total = count($querys);
            foreach ($querys as $item) {
                try {
                    $emp = $this->Person($item['USER_REQUEST_ID']);
                    $checkModel = Helpdesk::findOne(['title' => $item['SYMPTOM']]);
                    if (!$checkModel) {
                        $model = new Helpdesk();
                    } else {
                        $model = $checkModel;
                    }
                    $model->repair_group = 3;
                    $model->name = 'repair';
                    $model->code = $item['ARTICLE_NUM'];
                    $model->emp_id = $emp->id;
                    $model->thai_year = $item['YEAR_ID'];
                    $model->title = $item['SYMPTOM'];
                    $model->created_by = $emp->user_id;
                    $model->status = $item['REPAIR_STATUS'];
                    $model->created_at = $item['DATE_TIME_REQUEST'];
                    $model->data_json = [
                    'time_start' => $item['REPAIR_TIME'],
                    'end_start' => $item['TECH_REPAIR_TIME'],
                    'status' => $item['REPAIR_STATUS'],
                    'note' => $item['REPAIR_SUCCESS_REMARK'],
                    'phone' => '',
                    'urgency' => '1',
                    'location' => 'งานเครื่องมือแพทย์',
                    'repair_type' => $item['OUTSIDE_ACTIVE'] == true ? 'ซ่อมภายนอก' : 'ซ่อมภายใน',
                    'send_type' => 'medical',
                    'accept_emp_id' => '',
                    'accept_name' => '',
                    'accept_time' => $item['TECH_RECEIVE_DATE'] . ' ' . $item['TECH_RECEIVE_TIME'],
                    'create_name' => $emp->fullname,
                    'status_name' => 'ร้องขอ',
                    'location_other' => '',
                    'technician_req' => '0',
                    'technician_name' => '',
                    'old_data' => $item
                ];
                    if ($model->save(false)) {
                        $percentage = (($num++) / $total) * 100;
                        echo 'ดำเนินการแล้ว : ' . number_format($percentage, 2) . "%\n";
                        $this->TechRepair($model->id, $item['TECH_REPAIR_ID']);
                        // $this->serviceItems($model, $item);
                    } else {
                        echo "Save Error \n";
                        print_r($model->getErrors());
                    }

                } catch (\Throwable $th) {
                }
            }
        // }
    }

    
    public function TechRepair($id, $person_id)
    {
        try {
            $emp = $this->Person($person_id);
            $checkModel = Helpdesk::findOne(['name' => 'repair_team', 'category_id' => $id]);
            if (!$checkModel) {
                $model = new Helpdesk();
            } else {
                $model = $checkModel;
            }
            $model->emp_id = $emp->id;
            $model->name = 'repair_team';
            $model->category_id = $id;
            $model->data_json = [
                'tech_fullname' => $emp->fullname,
                'tech_position' => $emp->positionName(),
                'tech_department' => $emp->departmentName(),
            ];
            $model->save(false);
            // code...
        } catch (\Throwable $th) {
            // throw $th;
        }
    }

    // งานบริการซ่อมบำรุง
    public function UpdateServiceGeneral()
    {
        try {

        $helpdesk = Helpdesk::find()->where(['name' => 'repair'])->all();
        foreach ($helpdesk as $item) {
            // echo 'ID : ' . $item->data_json['old_data']['ID'] . "\n";
            $sql = 'SELECT * FROM `informrepair_service` WHERE REPAIR_INDEX_ID = :id';
            $id = $item->data_json['old_data']['ID'];
            $querys = Yii::$app->db2->createCommand($sql)->bindValue(':id', $id)->queryAll();
            foreach ($querys as $data) {
                // echo 'Service Items : ' . $data['REPAIR_SERVICE_NAME'] . "\n";
                $check = Helpdesk::findOne(['name' => 'service_items', 'category_id' => $item->id, 'title' => $data['REPAIR_SERVICE_NAME']]);
                if (!$check) {
                    $model = new Helpdesk();
                } else {
                    $model = $check;
                }
                $model->category_id = $item->id;
                $model->title = $data['REPAIR_SERVICE_NAME'];
                $model->name = 'service_items';
                $model->data_json = [
                    'old_data' => $data,
                    'unit_price' => $data['REPAIR_SUM_PRICE'],
                ];
                if ($model->save(false)) {
                    echo 'Service Items : ' . $model->title . "\n";
                } else {
                    echo "Save Error \n";
                    print_r($model->getErrors());
                }
            }
        }

        } catch (\Throwable $th) {

        }
    }

    // งานบริการซ่อมบำรุง

    public function UpdateServiceComputer()
    {
        $helpdesk = Helpdesk::find()->where(['name' => 'repair'])->all();
        foreach ($helpdesk as $item) {
            try {
                $sql = 'SELECT * FROM `informcom_service` WHERE REPAIR_INDEX_ID = :id';
                $id = $item->data_json['old_data']['ID'];
                $querys = Yii::$app->db2->createCommand($sql)->bindValue(':id', $id)->queryAll();
                foreach ($querys as $data) {
                    // echo 'Service Items : ' . $data['REPAIR_SERVICE_NAME'] . "\n";
                    $check = Helpdesk::findOne(['name' => 'service_items', 'category_id' => $item->id, 'title' => $data['REPAIR_SERVICE_NAME']]);
                    if (!$check) {
                        $model = new Helpdesk();
                    } else {
                        $model = $check;
                    }
                    $model->category_id = $item->id;
                    $model->title = $data['REPAIR_SERVICE_NAME'];
                    $model->name = 'service_items';
                    $model->data_json = [
                        'old_data' => $data,
                        'unit_price' => $data['REPAIR_SUM_PRICE'],
                    ];
                    if ($model->save(false)) {
                        echo 'Service Items : ' . $model->title . "\n";
                    } else {
                        echo "Save Error \n";
                        print_r($model->getErrors());
                    }
                }
                // code...
            } catch (\Throwable $th) {
                // throw $th;
            }
        }
    }


    
    public static function Person($id)
    {
        $person = Yii::$app
            ->db2
            ->createCommand('SELECT * FROM `hrd_person` WHERE ID = :id')
            ->bindValue(':id', $id)
            ->queryOne();
        if ($person) {
            $emp = Employees::findOne(['cid' => $person['HR_CID']]);
            return $emp;
        }
    }
}
