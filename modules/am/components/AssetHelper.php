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
       
    $sql = "select xx.*,
    ROUND((xx.month_price * date_number),2) as total_month_price,
    ROUND(IF(xx.price -(xx.month_price * date_number) <= 1,1,(xx.price -(xx.month_price * date_number))),2) as total,
     IF(xx.date_number = 1,(DATEDIFF(DATE_FORMAT(date_m,'%Y-%m-%d'),xx.receive_date)),xx.days_of_month) as sum_days,
      ROUND(((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date_m + INTERVAL service_life YEAR,'%Y-%m-%d'),date_m)),2) as price_days,
      ROUND(((((price - 0)-1) /  (DATEDIFF(DATE_FORMAT(date_m + INTERVAL service_life YEAR,'%Y-%m-%d'),date_m))* (IF(xx.date_number = 1,(DATEDIFF(DATE_FORMAT(date_m,'%Y-%m-%d'),xx.receive_date)),xx.days_of_month))) * date_number),2)  as price_of_month,
    receive_date
    from (select 
    LAST_DAY(m1) as date_m,
    IF(DATE_FORMAT(LAST_DAY(m1),'%Y-%m') = DATE_FORMAT(now(),'%Y-%m'), 'Y', 'N') as active,
    (TIMESTAMPDIFF(MONTH, (SELECT receive_date FROM asset WHERE id = :id) ,LAST_DAY(m1))+1)  as date_number,
    DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
    DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS days_of_year,
    (select price FROM asset where id =:id) as price,
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
    order by m1)as xx where xx.date_number <= :number;";

    $querys = Yii::$app->db->createCommand($sql)
    ->bindValue(':id', $id)
    ->bindValue(':number', $number)
    ->queryAll();
      
    if($querys){
        return $querys;
    }else{
        return [];
    }
    }

    



}
