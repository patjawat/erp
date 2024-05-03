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
    ROUND(IF(x3.days = 0,0,(x3.year_price/12)),2) as price_month,
    IF((x3.price - total_price) < 1,1,ROUND((x3.price - total_price),2)) as total
    FROM(
    SELECT x2.*,
     IF(x2.count_days > 15, x2.count_days,0) as days,
     (x2.price / x2.service_life) as year_price,
      IF(x2.count_days > 15, ROUND(x2.date_number * ((x2.price / x2.service_life)/12),2),0) as total_price
    FROM(
    SELECT x1.*,
    IF(x1.date_number = 1, DATEDIFF(x1.end_date,receive_date),x1.days_of_month) as count_days
    
    FROM(
    select 
    (TIMESTAMPDIFF(MONTH, (SELECT receive_date FROM asset WHERE id = :id) ,LAST_DAY(m1))+1)  as date_number,
        (SELECT receive_date FROM asset WHERE id = :id) as receive_date,
        DATE_FORMAT(m1, '%Y-%m-%d') as start_date,
        LAST_DAY(m1) as end_date,
         DAYOFMONTH(LAST_DAY(DATE_FORMAT(m1, '%Y-%m-%d'))) as days_of_month,
    IF(DATE_FORMAT(LAST_DAY(m1),'%Y-%m') = DATE_FORMAT(now(),'%Y-%m'), 'Y', 'N') as active,
    DATEDIFF(DATE_FORMAT(DATE_FORMAT(m1, '%Y-%m-%d') + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d'),DATE_FORMAT(m1, '%Y-%m-%d')) AS all_days,
    DATE_FORMAT(DATE_FORMAT((SELECT receive_date FROM asset WHERE id = :id) + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d') + INTERVAL -1 MONTH,'%Y-%m-%d') as begin_date,
        (SELECT price FROM asset where id =:id) as price,
        (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) as service_life,
        (SELECT CAST(data_json->'$.depreciation' as UNSIGNED) FROM asset WHERE id = :id) as dep
        
    
    from
    (
    select ((SELECT receive_date FROM asset WHERE id = :id) - INTERVAL DAYOFMONTH((SELECT receive_date FROM asset WHERE id = :id))-1 DAY) + INTERVAL m MONTH as m1
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
    where m1<=DATE_FORMAT(DATE_FORMAT((SELECT receive_date FROM asset WHERE id = :id) + INTERVAL (SELECT data_json->'$.service_life' FROM asset WHERE id = :id) YEAR,'%Y-%m-%d') + INTERVAL -1 MONTH,'%Y-%m-%d')
    order by m1) as x1) as x2) as x3 where x3.date_number <= :number";

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
