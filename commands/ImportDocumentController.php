<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\db\Expression;
use yii\console\ExitCode;
use app\models\Categorise;
use yii\console\Controller;
use \yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseConsole;
use app\components\AppHelper;
use yii\helpers\BaseFileHelper;
use app\modules\hr\models\Leave;
use app\modules\hr\models\Employees;
use app\modules\dms\models\Documents;
use app\modules\filemanager\models\Uploads;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ImportDocumentController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        $this->Receive();
        $this->Send();
    }

    // หนังสือับ
    public static function Receive()
    {
        $querys = Yii::$app->db2->createCommand("SELECT *
                        FROM gbook_index
                        LEFT JOIN grecord_org ON gbook_index.BOOK_ORG_ID = grecord_org.RECORD_ORG_ID
                        LEFT JOIN hrd_person ON gbook_index.PERSON_SAVE_ID = hrd_person.ID
                        LEFT JOIN hrd_prefix ON hrd_person.HR_PREFIX_ID = hrd_prefix.HR_PREFIX_ID
                        LEFT JOIN gbook_index_send_leader ON gbook_index.BOOK_ID = gbook_index_send_leader.BOOK_LD_ID
                        WHERE gbook_index.BOOK_USE = 'true'
                        -- AND gbook_index.DATE_SAVE BETWEEN '2024-01-01'  AND '2024-01-31'
                        ORDER BY gbook_index.BOOK_NUM_IN DESC")->queryAll();

        $num = 1;
        $total = count($querys);
        foreach ($querys as $key => $item) {
            $checkDoc = Documents::findOne([
                'document_group' => 'receive',
                'topic' => $item['BOOK_NAME'],
                'doc_regis_number' => $item['BOOK_NUM_IN'],
                'doc_number' => $item['BOOK_NUMBER'],
                'thai_year' => $item['BOOK_YEAR_ID'],
                'doc_date' => $item['BOOK_DATE'],
                'doc_receive' => $item['DATE_SAVE'],
                'document_org' => $item['RECORD_ORG_ID'],
            ]);
            $percentage = (($num++) / $total) * 100;
            if ($checkDoc) {
                $model = $checkDoc;
                echo 'update : ' . number_format($percentage, 2) . '% =>' . $item['BOOK_NUMBER'] . "\n";
            } else {
                $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new Documents([
                    'ref' => $ref
                ]);

                echo 'นำเข้า : ' . number_format($percentage, 2) . '% => ' . $item['BOOK_NUMBER'] . "\n";
            }

            switch ($item['BOOK_URGENT_ID']) {
                case '01':
                    $docSpeed = 'ปกติ';
                    break;
                case '02':
                    $docSpeed = 'ด่วน';
                    break;
                case '03':
                    $docSpeed = 'ด่วนมาก';
                    break;
                case '04':
                    $docSpeed = 'ด่วนที่สุด';
                    break;
                default:
                $docSpeed = '';
                    break;
            }

            $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $model->ref = $ref;
            $model->document_group = 'receive';
            $model->doc_speed =  $docSpeed;
            $model->document_type = 'DT' . $item['BOOK_TYPE_ID'];
            $model->doc_number = $item['BOOK_NUMBER'];
            $model->doc_regis_number = $item['BOOK_NUM_IN'];
            $model->topic = $item['BOOK_NAME'];
            $model->doc_receive = $item['DATE_SAVE'];
            $model->doc_date = $item['BOOK_DATE'];
            $model->thai_year = $item['BOOK_YEAR_ID'];
            $model->document_org = $item['RECORD_ORG_ID'];
            $model->data_json = ['filename' => $item['BOOK_FILE_NAME']];

            try {
                $model->save(false);
            } catch (\Throwable $th) {
                echo 'นำเข้าใหม่ => ' . $item['BOOK_NUM_IN'] . ' => ' . $th . "\n";
            }

            // หน่วงานที่รับส่งหนังสือ
            $code = $item['RECORD_ORG_ID'];
            $title = $item['RECORD_ORG_NAME'];

            $categorise = Categorise::findOne(['name' => 'document_org', 'code' => $code, 'title' => $title]);
            if ($categorise) {
                $org = $categorise;
            } else {
                $org = new Categorise();
            }
            $org->name = 'document_org';
            $org->code = $code;
            $org->title = $title;
            $org->save(false);

            // End หน่วงานที่รับส่งหนังสือ
        }
        return ExitCode::OK;
    }

    public static function send()
    {
        $querys = Yii::$app->db2->createCommand("SELECT *
                        FROM gbook_index_inside
                        LEFT JOIN grecord_org ON gbook_index_inside.BOOK_ORG_ID = grecord_org.RECORD_ORG_ID
                        LEFT JOIN hrd_person ON gbook_index_inside.PERSON_SAVE_ID = hrd_person.ID
                        LEFT JOIN hrd_prefix ON hrd_person.HR_PREFIX_ID = hrd_prefix.HR_PREFIX_ID
                        LEFT JOIN gbook_send_inside_leader ON gbook_index_inside.BOOK_ID = gbook_send_inside_leader.BOOK_LD_ID
                        WHERE gbook_index_inside.BOOK_USE = 'true'
                        --   AND gbook_index_inside.DATE_SAVE BETWEEN '2024-01-01'  AND '2024-01-31'
                        ORDER BY gbook_index_inside.BOOK_ID DESC;")->queryAll();

        $num = 1;
        $total = count($querys);
        foreach ($querys as $key => $item) {
            $checkDoc = Documents::findOne([
                // 'ref' => $ref,
                'document_group' => 'send',
                'topic' => $item['BOOK_NAME'],
                'doc_regis_number' => $item['BOOK_NUM_IN'],
                'doc_number' => $item['BOOK_NUMBER'],
                'thai_year' => $item['BOOK_YEAR_ID'],
                'doc_date' => $item['BOOK_DATE'],
                'doc_receive' => $item['DATE_SAVE'],
                'document_org' => $item['RECORD_ORG_ID'],
            ]);
            $percentage = (($num++) / $total) * 100;
            if ($checkDoc) {
                $model = $checkDoc;
                echo 'update : ' . number_format($percentage, 2) . '% =>' . $item['BOOK_NUMBER'] . "\n";
            } else {
                $model = new Documents();

                echo 'นำเข้า : ' . number_format($percentage, 2) . '% => ' . $item['BOOK_NUMBER'] . "\n";
            }

            switch ($item['BOOK_URGENT_ID']) {
                case '01':
                    $docSpeed = 'ปกติ';
                    break;
                case '02':
                    $docSpeed = 'ด่วน';
                    break;
                case '03':
                    $docSpeed = 'ด่วนมาก';
                    break;
                case '04':
                    $docSpeed = 'ด่วนที่สุด';
                    break;
                default:
                $docSpeed = '';
                    break;
            }
            
            $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $model->ref = $ref;
            $model->document_group = 'inside';
            $model->doc_speed =  $docSpeed;
            $model->document_type = 'DT' . $item['BOOK_TYPE_ID'];
            $model->doc_number = $item['BOOK_NUMBER'];
            $model->doc_regis_number = $item['BOOK_NUM_IN'];
            $model->topic = $item['BOOK_NAME'];
            $model->doc_receive = $item['DATE_SAVE'];
            $model->doc_date = $item['BOOK_DATE'];
            $model->thai_year = $item['BOOK_YEAR_ID'];
            $model->document_org = $item['RECORD_ORG_ID'];
            $model->data_json = ['filename' => $item['BOOK_FILE_NAME']];
            $fileName = $item['BOOK_FILE_NAME'];  // ชื่อไฟล์ที่ต้องการตรวจสอบ
            // self::UploadFile($fileName,$item['BOOK_ID']);
            try {
                // กำหนดพาธของไดเรกทอรี

                $model->save(false);
            } catch (\Throwable $th) {
                echo 'นำเข้าใหม่ => ' . $item['BOOK_NUM_IN'] . ' => ' . $th . "\n";
            }

            // หน่วงานที่รับส่งหนังสือ
            $code = $item['RECORD_ORG_ID'];
            $title = $item['RECORD_ORG_NAME'];

            $categorise = Categorise::findOne(['name' => 'document_org', 'code' => $code, 'title' => $title]);
            if ($categorise) {
                $org = $categorise;
            } else {
                $org = new Categorise();
            }
            $org->name = 'document_org';
            $org->code = $code;
            $org->title = $title;
            $org->save(false);

            // End หน่วงานที่รับส่งหนังสือ
        }
        return ExitCode::OK;
    }

    public function actionUploadFile()
    {
        $directory = Yii::getAlias('@app/bookpdf/');

        foreach (Documents::find()->all() as $document) {
            echo 'update => ' . $document->doc_number . "\n";
            $ref = $document->ref;
            $checkFileUpload = Uploads::findOne(['ref' => $ref]);
            if (!$checkFileUpload) {
                $fileName = $document->data_json['filename'];
                $filePath = $directory . $fileName;

                // ตรวจสอบว่าไฟล์มีอยู่หรือไม่
                if (file_exists($filePath) && is_file($filePath)) {
                    $this->CreateDir($ref);

                    $name = $fileName;
                    $destinationPath = Yii::getAlias('@app') . '/modules/filemanager/fileupload/' . $ref . '/' . $fileName;

                    if (copy($filePath, $destinationPath)) {
                        $upload = new Uploads();
                        $upload->ref = $ref;
                        $upload->name = 'document';
                        $upload->file_name = $name;
                        $upload->real_filename = $name;
                        $upload->type = 'pdf';
                        $upload->save(false);
                        echo "ไฟล์ $fileName มีอยู่ในไดเรกทอรี app/bookpdf" . "\n";
                    }
                } else {
                    echo "ไฟล์ $fileName ไม่พบในไดเรกทอรี app/bookpdf ID=" . $fileName . "\n";
                }
            }
        }
        // $filePath = $directory . $fileName;

        // ตรวจสอบว่าไฟล์มีอยู่หรือไม่
        // if (file_exists($filePath) && is_file($filePath)) {
        //     echo "ไฟล์ $fileName มีอยู่ในไดเรกทอรี app/bookpdf". "\n";
        // } else {
        //     echo "ไฟล์ $fileName ไม่พบในไดเรกทอรี app/bookpdf ID=".$id. "\n";
        // }
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

    public static function CreateDir($folderName)
    {
        if ($folderName != null) {
            $basePath = Yii::getAlias('@app') . '/modules/filemanager/fileupload/';
            if (BaseFileHelper::createDirectory($basePath . $folderName, 0777)) {
                BaseFileHelper::createDirectory($basePath . $folderName . '/thumbnail', 0777);
            }
        }
        return;
    }
}
