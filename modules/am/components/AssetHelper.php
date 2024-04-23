<?php

namespace app\modules\am\components;

use Yii;
use app\modules\am\models\Fsn;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class AssetHelper extends Component
{

   

    public static function FsnGroup()
    {
        return Fsn::find()->where(['name' => 'asset_group','active' => 1])->all();
    }

    //ค่าเสื่อมราคาตามวันที่ 1 รายการ
    public static function Depreciation($id,$number)
    {
       
    $sql = "SELECT x3.*,
    (x3.total_days * price_days) as total_price,
    (x3.days_x2 * price_days) as total_price2,
    ROUND(x3.price-(x3.days_x2 * price_days),2) as total
    FROM
    (
    SELECT x2.*,DATEDIFF(date,receive_date) as days_x2,
    IF(x2.date_number = 1, DATEDIFF(date,receive_date),x2.days_of_month) as total_days,
    ROUND((x2.price / x2.all_days),2) as price_days
    FROM (
    select x1.*
        from (select LAST_DAY(m1) as date,
        IF(DATE_FORMAT(LAST_DAY(m1),'%Y-%m') = DATE_FORMAT(now(),'%Y-%m'), 'Y', 'N') as active,
        (TIMESTAMPDIFF(MONTH, (SELECT receive_date FROM asset WHERE id = :id) ,LAST_DAY(m1))+1)  as date_number,
        DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
        DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS all_days,
        ((select price FROM asset where id =:id)-1) as price,
        (SELECT code FROM asset WHERE id = :id) as  item,
        (SELECT receive_date FROM asset WHERE id = :id) as receive_date,
        (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) as service_life,
        (SELECT (price/CAST(data_json->'$.service_life' as UNSIGNED) / 12) FROM asset WHERE id = :id) as month_price,
        (SELECT CAST(data_json->'$.depreciation' as UNSIGNED) FROM asset WHERE id = :id) as depreciation
        from
        (
        select ((SELECT receive_date FROM asset WHERE id = :id) - INTERVAL DAYOFMONTH((SELECT receive_date FROM asset WHERE id = :id))-1 DAY) 
        +INTERVAL m MONTH as m1
        from
        (
        select @rownum:=@rownum+1 as m from
        (select 1 union select 2 union select 3 union select 4) t1,
        (select 1 union select 2 union select 3 union select 4) t2,
        (select 1 union select 2 union select 3 union select 4) t3,
        (select 1 union select 2 union select 3 union select 4) t4,
        (select @rownum:=-1) t0
        ) d1
        ) d2 
        where m1<= DATE_FORMAT(DATE_FORMAT((SELECT receive_date FROM asset WHERE id = :id) + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d') + INTERVAL -1 MONTH,'%Y-%m-%d')
        order by m1)as x1) as x2) as x3 WHERE x3.date_number <= 10";

    $querys = Yii::$app->db->createCommand($sql)
    ->bindValue(':id', $id)
    // ->bindValue(':number', $number)
    ->queryAll();
      
    if($querys){
        return $querys;
    }else{
        return [];
    }
    }

    



}
