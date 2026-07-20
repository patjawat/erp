<?php

namespace app\components;

use Yii;
use yii\base\Component;
use app\models\Province;
// นำเข้า model ต่างๆ
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Organization;

// ใช้งานเกี่ยวกับทรัพย์สิน
class AssetHelper extends Component
{

    /**
     * ข้อความอายุการรับเข้า เช่น "ผ่านมา 2 ปี 3 เดือน" / "รับวันนี้" / "ยังไม่ถึงวันที่รับ"
     * ใช้ร่วมกันระหว่างหน้า list และ quick-update เพื่อไม่ให้ logic ซ้ำ
     */
    public static function receiveAgeText($receiveDate): string
    {
        if (empty($receiveDate)) {
            return '';
        }

        try {
            $dateText = substr((string) $receiveDate, 0, 10);
            $receivedAt = \DateTimeImmutable::createFromFormat('!Y-m-d', $dateText) ?: new \DateTimeImmutable((string) $receiveDate);
            $today = new \DateTimeImmutable('today');
        } catch (\Throwable $e) {
            return '';
        }

        if ($receivedAt > $today) {
            return 'ยังไม่ถึงวันที่รับ';
        }

        $diff = $receivedAt->diff($today);
        if ($diff->y === 0 && $diff->m === 0 && $diff->d === 0) {
            return 'รับวันนี้';
        }

        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y . ' ปี';
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' เดือน';
        }
        if ($diff->d > 0) {
            $parts[] = $diff->d . ' วัน';
        }

        return 'ผ่านมา ' . implode(' ', $parts);
    }

        //คำนวนรหัสครุภัณฑ์ใหม่
    public static function nextAssetCode($fsn_number)
    {
        $sql = "SELECT x.* FROM(SELECT 
                CONCAT(:fsn,'/', 
                    SUBSTRING_INDEX(SUBSTRING_INDEX(code, '/', -1), '.', 1), 
                    '.', 
                    LPAD(MAX(CAST(SUBSTRING_INDEX(code, '.', -1) AS UNSIGNED)) + 1, 2, '0')
                ) AS next_code
                    FROM asset 
                    WHERE code LIKE CONCAT(:fsn, '/%')
                    GROUP BY SUBSTRING_INDEX(SUBSTRING_INDEX(code, '/', -1), '.', 1)) as x
                    order BY next_code DESC limit 1;";
        $query = Yii::$app->db->createCommand($sql)
            ->bindValue(':fsn', $fsn_number)
            ->queryOne();

        $newCode  = $fsn_number . '/' . substr(AppHelper::YearBudget(), -2) . '.01';
        return $query['next_code'] ?? $newCode;
    }
    

    //
    public static function CheckAssetItem($typeName,$code, $title)
    {
        // try {
            $fsnNum = explode('/', $code);
            $catgorise = Categorise::find()->where(['name' => 'asset_item', 'code' => $fsnNum[0]])->one();
            $getType = Categorise::find()->where(['name' => 'asset_type', 'title' =>$typeName])->one();

            if (!$catgorise) {
                $model = new Categorise;
            }else{
                $model = $catgorise;
            }

            $model->title = $title;
            $model->code = $fsnNum[0];
            $model->category_id = $getType->code ?? 0; 
            $model->name = 'asset_item';
            $model->active = 1;
            $model->save();
            return $model;

        // } catch (\Throwable $th) {
        //     return false;
        // }
    }

    public static function findByName($name,$title)
    {
        $catgorise = Categorise::find()->where(['name' => $name, 'title' => $title])->one();
        if($catgorise){
            return $catgorise->code ?? 0;
        }else{
            return 0;
        }
    }

    public static function getCode($name,$code)
    {
        $catgorise = Categorise::find()->where(['name' => $name, 'code' => $code])->one();
        if($catgorise){
            return $catgorise;
        }else{
            return false;
        }
    }
}
