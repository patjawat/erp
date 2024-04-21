<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

// นำเข้า model ต่างๆ
use app\modules\hr\models\Organization;
use app\models\Categorise;
use app\models\Province;

// ใช้ง
// ใช้งานเกี่ยวกับ user
class CategoriseHelper extends Component
{

    public static function Categorise($name)
    {
        return Categorise::find()->where(['name' => $name])->all();
    }

    //หาตำแหน่ง กลุ่ม ประเภท ด้วย ID
    public static function TreeById($id)
    {
        // $sql = "SELECT concat(e.fname,' ',e.lname) as fullname,
        // t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.id as position_id,t1.name as position_name, t2.id as position_group,t2.name as position_group_name,t3.id as position_type,t3.name as position_type_name
        // FROM employees e
        // JOIN tree t1 ON t1.id = e.position_name
        // JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
        // JOIN tree t3 ON t2.lft BETWEEN t3.lft AND t3.rgt AND t2.lvl = t3.lvl + 1
        // WHERE t1.id = :id";

        $sql = "SELECT
        t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, 
        t1.id as position_id,
        t1.name as position_name, 
        t2.id as position_group_id,
        t2.name as position_group_name,
        t3.id as position_type_id,
        t3.name as position_type_name
        FROM tree t1 
        JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
        JOIN tree t3 ON t2.lft BETWEEN t3.lft AND t3.rgt AND t2.lvl = t3.lvl + 1
        WHERE t1.id = :id";

        $query = Yii::$app->db->createCommand($sql)
        ->bindValue(':id', $id)
        ->queryOne();
        return $query;
    }

    //หาตำแหน่ง กลุ่ม ประเภท ด้วย ID
    public static function TreeByName($name)
    {

        $sql = "SELECT
        t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, 
        t1.id as position_id,
        t1.name as position_name, 
        t2.id as position_group_id,
        t2.name as position_group_name,
        t3.id as position_type_id,
        t3.name as position_type_name
        FROM tree t1 
        JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1
        JOIN tree t3 ON t2.lft BETWEEN t3.lft AND t3.rgt AND t2.lvl = t3.lvl + 1
        WHERE t3.name = :name";

        $query = Yii::$app->db->createCommand($sql)
        ->bindValue(':name', $name)
        ->queryAll();
        return $query;
    }




    public static function CategoriseByCodeName($code, $name)
    {
        return Categorise::findOne(['code' => $code, 'name' => $name]);
    }

//เพศ
    public static function Gender()
    {
        return ArrayHelper::map(self::Categorise('gender'), 'title', 'title');
    }

    //ครอบครัว
    public static function FamilyRelation()
    {
        return ArrayHelper::map(self::Categorise('family_relation'), 'title', 'title');
    }

//คำนำหน้าชื่อ EN
    public static function PrefixTh()
    {
        return ArrayHelper::map(self::Categorise('prefix_th'), 'title', 'title');
    }

// ภูมิลำเนาเดิม
    public static function Born()
    {
        return ArrayHelper::map(Province::find()->all(), 'name_th', 'name_th');
    }

//คำนำหน้าชื่อ EN
    public static function PrefixEn()
    {
        return ArrayHelper::map(self::Categorise('prefix_en'), 'title', 'title');
    }

//เชื้อชาติ/สัญชาติ
    public static function Nationality()
    {
        return ArrayHelper::map(self::Categorise('nationality'), 'title', 'title');
    }

//เสถานภาพสมรส
    public static function Marry()
    {
        return ArrayHelper::map(self::Categorise('marry'), 'title', 'title');
    }

//ศาสนา
    public static function Religion()
    {
        return ArrayHelper::map(self::Categorise('religion'), 'title', 'title');
    }

//หมู่โลหิต
    public static function Blood()
    {
        return ArrayHelper::map(self::Categorise('blood'), 'title', 'title');
    }

//การศึกษา **

//ระดับการศึกษา
    public static function Education()
    {
        return ArrayHelper::map(self::Categorise('education'), 'code', 'title');
    }

//วิชาเอก
    // public static function Major()
    // {
    //     return ArrayHelper::map(self::Categorise('major'), 'title', 'title');
    // }

// จบจากสถาบัน
    // public static function Institute()
    // {
    //     return ArrayHelper::map(self::Categorise('institute'), 'title', 'title');
    // }

    // การเปลี่ยนชื่อ
    public static function RenameType()
    {
        return ArrayHelper::map(self::Categorise('rename_type'), 'title', 'title');
    }

    // ประเภทบุคลากร Tree Table
    // public static function PositionType()
    // {
    //     $sql = "SELECT t1.id, t1.root, t1.lft, t1.rgt, t1.lvl, t1.id as position_id,
    //     t1.name as position_name, 
    //     t2.id as position_group,
    //     t2.name as position_group_name 
    //     FROM tree t1 
    //     JOIN tree t2 ON t1.lft BETWEEN t2.lft AND t2.rgt AND t1.lvl = t2.lvl + 1 WHERE t2.name = 'ตำแหน่งสายงาน'";
    //      $model = Yii::$app->db->createCommand($sql)
    //     ->queryAll();
    //     return ArrayHelper::map($model, 'id', 'position_name');
    // }


    public static function PositionType()
    {
        return ArrayHelper::map(self::Categorise('position_type'), 'code', 'title');
    }


    // ระดับตำแหน่ง
    public static function PositionLevel()
    {
        return ArrayHelper::map(self::Categorise('position_level'), 'code', 'title');
    }

    // กลุ่มงาน
    public static function PositionGroup()
    {
        return ArrayHelper::map(self::Categorise('position_group'), 'code', 'title');
    }

    // ชื่อตำแหน่ง
    public static function PositionName()
    {
        return ArrayHelper::map(self::Categorise('position_name'), 'code', 'title');
    }
 // ชื่อตำแหน่ง แบบ ajax emplate
    public static function PositionNameAjaxTemplate()
    {
        return ArrayHelper::map(self::Categorise('position_name'), 'id', function($model){
            // return '';
            $this->render('@app/modules/hr/views/position/poaition_ajax_template',['model' => $model]);
        });
    }


    

        // ความเชี่ยวชาญ
        public static function Expertise()
        {
            return ArrayHelper::map(self::Categorise('expertise'), 'code', 'title');
        }
        // ตำแหน่งบริหาร
        public static function PositionManage()
        {
            $data = Categorise::find()->where(['name' => 'position_name'])->all();
            return ArrayHelper::map($data,'code',function($model){
                return isset($model->data_json['sub_title']) ? $model->data_json['sub_title'] : '-';
            });
        }

        
    // สถานะของบุคลากร
    public static function EmpStatus()
    {
        return ArrayHelper::map(self::Categorise('emp_status'), 'code', function ($model) {
            return $model->title;
        }); 
    }


    // กลุ่มงาน
    public static function Workgroup()
    {
        return ArrayHelper::map(self::Categorise('workgroup'), 'code', 'title');
    }

    // แผนก
    public static function Department()
    {
        $model = Organization::find()->where(['lvl' => 2])->all();
        return ArrayHelper::map($model, 'id', 'name');
    }
    //ชื่อแผนก
    public static function DepartmentName($id)
    {
        $model = Organization::find()->where(['lvl' => 2,'id' => $id])->one();
        if($model){
            return $model->name;
        }else{
            return null;
        }
    }

    //รายการ สัมมนา ฝึกอบรม ดูงาน ศึกษาต่อ และข้อมูลรายงาน
    public static function Develop()
    {
        return ArrayHelper::map(self::Categorise('develop'), 'code', 'title');
    }

    //ลักษณะการไป
    public static function Followby()
    {
        return ArrayHelper::map(self::Categorise('followby'), 'code', 'title');
    }

    //ผู้แทนจำหน่าย
    public static function Vendor()
    {
        return ArrayHelper::map(self::Categorise('vendor'), 'code', 'title');
    }

    //ค้นหาด้วย title
    public static function Title($title)
    {
        return Categorise::find()->where(['title' => $title]);
    }

        //ค้นหาด้วย title
    public static function TitleAndName($title,$name)
    {
        return Categorise::find()->where(["name" => $name,'title' => $title]);
    }

    public static function Id($id)
    {
        return Categorise::find()->where(["id" => $id]);
    }

    public static function CategoryAndName($code, $name)
    {
        return Categorise::find()->where(["category_id" => $code, 'name'=>$name])->orderBy(['id' => SORT_DESC]);
    }
    
    // code ตัวสุดท้าย
    public static function CodePurchase()
    {
        $sql = "SELECT (CAST(code as UNSIGNED)+1) as code FROM `categorise` WHERE `name` = 'purchase' ORDER BY code DESC LIMIT 1";
        return  Yii::$app->db->createCommand($sql)->queryScalar();
        return Categorise::find()
        ->andWhere(['name' => 'purchase'])
        ->orderBy(['code' => SORT_DESC])
        ->one();
    }
}
