<?php

use yii\db\Migration;

/**
 * Class m230806_142411_add_user_table
 */
class m230806_142411_add_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $time = time();
        $password_hash = Yii::$app->getSecurity()->generatePasswordHash('112233');
        $auth_key = Yii::$app->security->generateRandomString();



        // สร้าง user admin
        $this->insert('user', [
            'username' => 'admin',
            'email' => 'admin@local.com',
            'password_hash' => $password_hash,
            'auth_key' => $auth_key,
            'status' => 10,
            'created_at' => $time,
            'updated_at' => $time

        ]);
        $id = Yii::$app->db->getLastInsertID();
        $this->insert(
            'profile',
            [
                'user_id' => $id,
                'name' => 'Administrator',
                'public_email' => 'admin@local.com'
            ]
        );
        $this->insert(
            'employees',
            [
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(),10),
                'user_id' => $id,
                'fname' => 'Administrator',
                'lname' => 'Lastname',
                'phone' => '0909748044',
                'status' => 1,
                'position_name' => 1,
                'position_number' => 88888888,
                'position_type' => 1,
                'position_level' => 1,
                'salary' => 60000,
            ]
        );

        // กำหนดกลุ่มใช้งาน
        $this->insert('auth_item', ['name' => 'admin', 'type' => 1, 'created_at' => $time]);
        $this->insert('auth_item', ['name' => 'user', 'type' => 1, 'created_at' => $time]);

        // กำหนด route
        $this->insert('auth_item', ['name' => '*', 'type' => 2]);
        $this->insert('auth_item', ['name' => '/site/*', 'type' => 2]);
        $this->insert('auth_item', ['name' => '/depdrop/*', 'type' => 2]);
        $this->insert('auth_item', ['name' => '/profile/*', 'type' => 2]);
        $this->insert('auth_item', ['name' => '/employees/*', 'type' => 2]);
        $this->insert('auth_item', ['name' => '/settings/*', 'type' => 2]);


        // จัด route ลง  ในแต่ละหลุ่ม
        // จัดกลุ่ม admin
        $this->insert('auth_assignment', ['item_name' => 'admin', 'user_id' => $id, 'created_at' => $time]);
        $this->insert('auth_item_child', ['parent' => 'admin', 'child' => '*']);
        $this->insert('auth_item_child', ['parent' => 'admin', 'child' => '/employees/*']); // จัดการจข้อมูล พนักงานได้ทั้งหมด
        $this->insert('auth_item_child', ['parent' => 'admin', 'child' => 'user']); // สารถเข้า ที่อย่างที่ user เข้าได้
        $this->insert('auth_item_child', ['parent' => 'admin', 'child' => '/settings/*']); // การตั้งค่า


        // จัดกลุ่มของ user
        $this->insert('auth_item_child', ['parent' => 'user', 'child' => '/site/*']);
        $this->insert('auth_item_child', ['parent' => 'user', 'child' => '/profile/*']);
        $this->insert('auth_item_child', ['parent' => 'user', 'child' => '/depdrop/*']);

        // clear hotfix
        $this->execute("DELETE FROM `migration` WHERE `migration`.`version` = 'm200409_110543_rbac_update_mssql_trigger'");
        $this->execute("DELETE FROM `migration` WHERE `migration`.`version` = 'm180523_151638_rbac_updates_indexes_without_prefix'");
        $this->execute("DELETE FROM `migration` WHERE `migration`.`version` = 'm170907_052038_rbac_add_index_on_auth_assignment_user_id'");
        $this->execute("DELETE FROM `migration` WHERE `migration`.`version` = 'm140506_102106_rbac_init'");
        // End clear hotfic

        // insert thai province

        $sqlProvinces = file_get_contents(__DIR__ . '/provinces.sql');
        Yii::$app->db->open();
        Yii::$app->db->pdo->exec($sqlProvinces);





    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if (Yii::$app->db->schema->getTableSchema('{{%amphures}}')) {
            $this->dropTable('{{%amphures}}');
        }

        if (Yii::$app->db->schema->getTableSchema('{{%districts}}')) {
            $this->dropTable('{{%districts}}');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%geographies}}')) {
            $this->dropTable('{{%geographies}}');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%provinces}}')) {
            $this->dropTable('{{%provinces}}');
        }

    }

}