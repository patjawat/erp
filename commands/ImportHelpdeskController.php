<?php

/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\modules\helpdesk\models\Helpdesk;
use yii\console\Controller;
use Yii;

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
        $sql = "SELECT
                a.ARTICLE_NUM as code,
                ('repair') as name,
                i.YEAR_ID as year_budget,
                CAST(e.id as UNSIGNED) as empoyee_id,
                concat(e.fname,' ',e.lname) as create_name,
                person.HR_CID,i.DATE_TIME_REQUEST,i.YEAR_ID,
                IF(a.ARTICLE_NUM > 1, 'asset','general') as send_type,
                i.SYMPTOM as title,
                i.REPAIR_COMMENT as repair_note,
                i.TECH_RECEIVE_ID,
                i.TECH_RECEIVE_NAME as accept_name,
                concat(i.TECH_RECEIVE_DATE,' ',i.TECH_RECEIVE_TIME) as accept_time,
                concat(i.REPAIR_DATE,' ',i.REPAIR_TIME) as repair_date,
                i.DEPARTMENT_SUB_ID,
                dep.HR_DEPARTMENT_SUB_SUB_NAME as location,
                i.STATUS,
                IF(i.REPAIR_SCORE IS NULL,1,i.REPAIR_SCORE) as urgency 
                FROM backoffice.informrepair_index i

                LEFT JOIN backoffice.asset_article a ON a.ARTICLE_ID = i.ARTICLE_ID
                LEFT JOIN backoffice.hrd_person person ON person.ID = i.USER_REQUEST_ID
                LEFT JOIN backoffice.hrd_department_sub_sub dep ON dep.HR_DEPARTMENT_SUB_SUB_ID = i.DEPARTMENT_SUB_ID
                LEFT JOIN employees e ON e.cid = backoffice.person.HR_CID
                WHERE a.ARTICLE_NUM IS NOT NULL
                AND i.STATUS = 'SUCCESS'
                LIMIT 100000";

        $querys = Yii::$app->db->createCommand($sql)->queryAll();

        foreach ($querys as $item) {
            // ตรวจสอบว่าเป็นครุภัณ์หรืแไม่
            $sqlCheckAssetType = "SELECT asset_item.title,asset_type.title,asset_type.code FROM asset a INNER JOIN categorise asset_item ON asset_item.code = a.asset_item AND asset_item.name = 'asset_item' INNER JOIN categorise asset_type ON asset_type.code = asset_item.category_id AND asset_type.name = 'asset_type' WHERE a.code = :code;";
            $checkAssetType = Yii::$app
                ->db
                ->createCommand($sqlCheckAssetType)
                ->bindValue(':code', $item['code'])
                ->queryOne();

            try {
                if (isset($checkAssetType) && $checkAssetType['code'] == 11) {
                    $repair_group = 3;
                } elseif ($checkAssetType['code'] == 12) {
                    $repair_group = 2;
                } else {
                    $repair_group = 1;
                }

                $model = new Helpdesk();
                $model->code = $item['code'];
                $model->name = 'repair';
                $model->year_budget = $item['year_budget'];
                $model->status = 4;
                $model->repair_group = $repair_group;
                $model->created_by = $item['empoyee_id'];
                $model->data_json = [
                    'note' => 'เพิ่มเติม',
                    'price' => '10000',
                    'title' => $item['title'],
                    'end_job' => '1',
                    'urgency' => $item['urgency'] == 5 ? 1 : $item['urgency'],
                    'location' => $item['location'],
                    'send_type' => 'asset',
                    'start_job' => '1',
                    'accept_name' => $item['accept_name'],
                    'accept_time' => $item['accept_time'],
                    'create_name' => $item['create_name'],
                    'repair_note' => $item['repair_note'],
                    'repair_type' => 'ซ่อมภายใน',
                    'end_job_date' => '21/05/2024 14:34:42',
                    'start_job_date' => '09/05/2024 14:34:36'
                ];

                $model->save(false);
                // code...
            } catch (\Throwable $th) {
                $repair_group = '';
            }
        }
        echo "Hello Inpoert \n";
    }
}
