<?php

use yii\db\Migration;

/**
 * เพิ่มฟิลด์ข้อมูล JD ครบชุดตามมาตรฐาน HR สมัยใหม่ (10 หมวด)
 */
class m260305_000001_add_jd_template_fields extends Migration
{
    public function up()
    {
        // หมวด 1: ข้อมูลพื้นฐานของตำแหน่ง
        $this->addColumn('{{%jd_template}}', 'job_code', $this->string(64)->null()->comment('รหัสตำแหน่ง (Job Code/ID)'));
        $this->addColumn('{{%jd_template}}', 'job_level', $this->string(100)->null()->comment('ระดับตำแหน่ง (Junior/Senior/Manager ฯลฯ)'));
        $this->addColumn('{{%jd_template}}', 'department', $this->string(255)->null()->comment('แผนก/ฝ่าย'));
        $this->addColumn('{{%jd_template}}', 'report_to', $this->string(255)->null()->comment('รายงานตรงต่อตำแหน่ง'));
        $this->addColumn('{{%jd_template}}', 'has_subordinates', $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('มีผู้ใต้บังคับบัญชา (0=ไม่มี, 1=มี)'));

        // หมวด 2: วัตถุประสงค์ของตำแหน่ง
        $this->addColumn('{{%jd_template}}', 'job_purpose', $this->text()->null()->comment('วัตถุประสงค์ของตำแหน่ง (Job Purpose/Summary)'));

        // หมวด 4: คุณสมบัติที่ต้องการ
        $this->addColumn('{{%jd_template}}', 'edu_requirement', $this->text()->null()->comment('ข้อกำหนดด้านการศึกษา'));
        $this->addColumn('{{%jd_template}}', 'exp_years', $this->smallInteger()->null()->comment('จำนวนปีประสบการณ์ขั้นต่ำ'));
        $this->addColumn('{{%jd_template}}', 'exp_detail', $this->text()->null()->comment('รายละเอียดประสบการณ์ที่ต้องการ'));
        $this->addColumn('{{%jd_template}}', 'hard_skills', $this->text()->null()->comment('ทักษะเฉพาะทาง (Hard Skills)'));
        $this->addColumn('{{%jd_template}}', 'soft_skills', $this->text()->null()->comment('ทักษะด้านพฤติกรรม (Soft Skills)'));

        // หมวด 5: สมรรถนะที่ต้องการ
        $this->addColumn('{{%jd_template}}', 'core_competency', $this->text()->null()->comment('Core Competency — สมรรถนะที่ทุกคนในองค์กรต้องมี'));
        $this->addColumn('{{%jd_template}}', 'functional_competency', $this->text()->null()->comment('Functional Competency — สมรรถนะเฉพาะสายงาน'));
        $this->addColumn('{{%jd_template}}', 'leadership_competency', $this->text()->null()->comment('Leadership Competency — สำหรับตำแหน่งระดับหัวหน้า'));

        // หมวด 6: ตัวชี้วัดผลงาน
        $this->addColumn('{{%jd_template}}', 'kpis', $this->text()->null()->comment('ตัวชี้วัดผลงานหลัก (KPIs)'));

        // หมวด 7: โครงสร้างค่าตอบแทน
        $this->addColumn('{{%jd_template}}', 'salary_min', $this->integer()->null()->comment('เงินเดือนต่ำสุด (บาท)'));
        $this->addColumn('{{%jd_template}}', 'salary_max', $this->integer()->null()->comment('เงินเดือนสูงสุด (บาท)'));
        $this->addColumn('{{%jd_template}}', 'benefits', $this->text()->null()->comment('สวัสดิการหลัก'));
        $this->addColumn('{{%jd_template}}', 'variable_pay', $this->text()->null()->comment('ค่าตอบแทนผันแปร (โบนัส/คอมมิชชั่น/OT)'));

        // หมวด 8: สภาพแวดล้อมการทำงาน
        $this->addColumn('{{%jd_template}}', 'work_type', $this->string(20)->null()->comment('รูปแบบการทำงาน (Onsite/Hybrid/Remote)'));
        $this->addColumn('{{%jd_template}}', 'work_location', $this->string(255)->null()->comment('สถานที่ปฏิบัติงาน'));
        $this->addColumn('{{%jd_template}}', 'work_hours', $this->string(255)->null()->comment('เวลาทำงาน / กะ / การเดินทาง'));
        $this->addColumn('{{%jd_template}}', 'work_conditions', $this->text()->null()->comment('สภาพแวดล้อมพิเศษ'));

        // หมวด 9: เส้นทางความก้าวหน้า
        $this->addColumn('{{%jd_template}}', 'career_vertical', $this->text()->null()->comment('เส้นทางอาชีพแนวดิ่ง (Vertical)'));
        $this->addColumn('{{%jd_template}}', 'career_lateral', $this->text()->null()->comment('เส้นทางอาชีพแนวราบ (Lateral)'));

        // หมวด 10: ข้อมูล HR Analytics
        $this->addColumn('{{%jd_template}}', 'employment_type', $this->string(50)->null()->comment('ประเภทการจ้าง (พนักงานประจำ/สัญญาจ้าง/Freelance)'));
        $this->addColumn('{{%jd_template}}', 'headcount', $this->smallInteger()->null()->comment('Headcount ที่ได้รับอนุมัติ (จำนวนอัตรา)'));
        $this->addColumn('{{%jd_template}}', 'jd_approved_by', $this->string(255)->null()->comment('ผู้อนุมัติ JD (Audit Trail)'));
        $this->addColumn('{{%jd_template}}', 'jd_approved_at', $this->dateTime()->null()->comment('วันที่อนุมัติ JD'));
    }

    public function down()
    {
        $columns = [
            'job_code', 'job_level', 'department', 'report_to', 'has_subordinates',
            'job_purpose',
            'edu_requirement', 'exp_years', 'exp_detail', 'hard_skills', 'soft_skills',
            'core_competency', 'functional_competency', 'leadership_competency',
            'kpis',
            'salary_min', 'salary_max', 'benefits', 'variable_pay',
            'work_type', 'work_location', 'work_hours', 'work_conditions',
            'career_vertical', 'career_lateral',
            'employment_type', 'headcount', 'jd_approved_by', 'jd_approved_at',
        ];
        foreach ($columns as $col) {
            $this->dropColumn('{{%jd_template}}', $col);
        }
    }
}
