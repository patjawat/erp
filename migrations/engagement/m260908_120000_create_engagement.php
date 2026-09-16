<?php

use yii\db\Migration;

/** Additive, independently deployable: yii migrate --migrationPath=@app/migrations/engagement */
class m260908_120000_create_engagement extends Migration
{
    public function safeUp()
    {
        $tables = [
            'template' => ['code'=>$this->string(64)->notNull(), 'title'=>$this->string(255)->notNull()],
            'version' => ['template_id'=>$this->integer()->notNull(), 'version_no'=>$this->integer()->notNull(), 'status'=>$this->string(20)->notNull()->defaultValue('draft'), 'scoring_policy_json'=>$this->text()->notNull(), 'comparability_key'=>$this->string(64)->notNull(), 'published_at'=>$this->dateTime(), 'published_by'=>$this->integer()],
            'dimension' => ['version_id'=>$this->integer()->notNull(), 'code'=>$this->string(64)->notNull(), 'title'=>$this->string(255)->notNull(), 'kind'=>$this->string(20)->notNull(), 'sequence'=>$this->integer()->notNull()],
            'question' => ['version_id'=>$this->integer()->notNull(), 'dimension_id'=>$this->integer()->notNull(), 'code'=>$this->string(64)->notNull(), 'prompt'=>$this->text()->notNull(), 'reverse_scored'=>$this->boolean()->notNull()->defaultValue(false), 'sequence'=>$this->integer()->notNull()],
            'option' => ['question_id'=>$this->integer()->notNull(), 'code'=>$this->string(16)->notNull(), 'label'=>$this->string(255)->notNull(), 'score'=>$this->integer(), 'is_missing'=>$this->boolean()->notNull()->defaultValue(false)],
            'round' => ['code'=>$this->string(64)->notNull(), 'title'=>$this->string(255)->notNull(), 'version_id'=>$this->integer()->notNull(), 'fiscal_year'=>$this->integer()->notNull(), 'open_at'=>$this->dateTime()->notNull(), 'close_at'=>$this->dateTime()->notNull(), 'cohort_at'=>$this->dateTime(), 'status'=>$this->string(20)->notNull()->defaultValue('draft'), 'privacy_notice'=>$this->text()->notNull(), 'retention_days'=>$this->integer()->notNull(), 'minimum_group_size'=>$this->integer()->notNull()->defaultValue(5), 'policy_confirmed'=>$this->boolean()->notNull()->defaultValue(false), 'result_revision'=>$this->integer()->notNull()->defaultValue(0), 'created_by'=>$this->integer(), 'created_at'=>$this->dateTime()->notNull()],
            'cohort' => ['round_id'=>$this->integer()->notNull(), 'group_code'=>$this->string(64)->notNull(), 'group_label'=>$this->string(255)->notNull(), 'eligible_count'=>$this->integer()->notNull()],
            'invitation' => ['round_id'=>$this->integer()->notNull(), 'emp_id'=>$this->integer()->notNull(), 'respondent_key'=>$this->string(64)->notNull(), 'cohort_id'=>$this->integer()->notNull(), 'department_id_snapshot'=>$this->bigInteger(), 'department_name_snapshot'=>$this->string(255), 'completion_status'=>$this->string(20)->notNull()->defaultValue('pending')],
            // Intentionally no blameable fields or precise timestamps on responses/answers.
            'response' => ['round_id'=>$this->integer()->notNull(), 'respondent_key'=>$this->string(64)->notNull(), 'idempotency_key'=>$this->string(64)->notNull(), 'cohort_id'=>$this->integer()->notNull(), 'status'=>$this->string(20)->notNull()->defaultValue('submitted'), 'submission_day'=>$this->date()->notNull()],
            'answer' => ['response_id'=>$this->integer()->notNull(), 'question_id'=>$this->integer()->notNull(), 'option_id'=>$this->integer()->notNull()],
            'result' => ['round_id'=>$this->integer()->notNull(), 'cohort_id'=>$this->integer()->notNull(), 'dimension_code'=>$this->string(64)->notNull(), 'dimension_title'=>$this->string(255)->notNull(), 'kind'=>$this->string(20)->notNull(), 'result_revision'=>$this->integer()->notNull(), 'eligible_n'=>$this->integer(), 'respondent_n'=>$this->integer(), 'valid_n'=>$this->integer(), 'mean_score'=>$this->decimal(8,4), 'index_score'=>$this->decimal(8,4), 'favorable_percent'=>$this->decimal(8,4), 'status'=>$this->string(20)->notNull(), 'computed_at'=>$this->dateTime()->notNull()],
            'action' => ['round_id'=>$this->integer()->notNull(), 'cohort_id'=>$this->integer()->notNull(), 'dimension_code'=>$this->string(64)->notNull(), 'issue_summary'=>$this->text()->notNull(), 'owner_emp_id'=>$this->integer()->notNull(), 'due_date'=>$this->date()->notNull(), 'target'=>$this->text()->notNull(), 'status'=>$this->string(20)->notNull()->defaultValue('pending'), 'effectiveness_note'=>$this->text(), 'baseline_revision'=>$this->integer()->notNull(), 'completed_at'=>$this->dateTime(), 'created_by'=>$this->integer()->notNull()],
            'audit' => ['round_id'=>$this->integer(), 'event'=>$this->string(64)->notNull(), 'actor_id'=>$this->integer(), 'reason'=>$this->string(255), 'occurred_at'=>$this->dateTime()->notNull()],
        ];
        $options = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB' : null;
        foreach ($tables as $name=>$columns) $this->createTable($this->table($name), array_merge(['id'=>$this->primaryKey()],$columns),$options);
        $unique = ['template'=>[['code']], 'version'=>[['template_id','version_no']], 'dimension'=>[['version_id','code']], 'question'=>[['version_id','code']], 'option'=>[['question_id','code']], 'round'=>[['code']], 'cohort'=>[['round_id','group_code']], 'invitation'=>[['round_id','emp_id'],['respondent_key']], 'response'=>[['round_id','respondent_key'],['round_id','idempotency_key']], 'answer'=>[['response_id','question_id']], 'result'=>[['round_id','cohort_id','dimension_code','result_revision']]];
        foreach ($unique as $table=>$keys) foreach($keys as $i=>$columns) $this->createIndex('uq-eng-'.$table.'-'.$i,$this->table($table),$columns,true);
        $relations = ['version'=>['template_id'=>'template'], 'dimension'=>['version_id'=>'version'], 'question'=>['version_id'=>'version','dimension_id'=>'dimension'], 'option'=>['question_id'=>'question'], 'round'=>['version_id'=>'version'], 'cohort'=>['round_id'=>'round'], 'invitation'=>['round_id'=>'round','cohort_id'=>'cohort'], 'response'=>['round_id'=>'round','cohort_id'=>'cohort'], 'answer'=>['response_id'=>'response','question_id'=>'question','option_id'=>'option'], 'result'=>['round_id'=>'round','cohort_id'=>'cohort'], 'action'=>['round_id'=>'round','cohort_id'=>'cohort']];
        // SQLite fixtures enforce the same ownership invariants in service tests.
        if ($this->db->driverName !== 'sqlite') foreach($relations as $child=>$refs) foreach($refs as $field=>$parent) $this->addForeignKey('fk-eng-'.$child.'-'.$field,$this->table($child),$field,$this->table($parent),'id','RESTRICT','RESTRICT');
        $this->createIndex('idx-eng-response-round',$this->table('response'),['round_id','status']);
        $this->createIndex('idx-eng-action-owner',$this->table('action'),['owner_emp_id','status','due_date']);
        $auth = Yii::$app->has('authManager') ? Yii::$app->authManager : null;
        if ($auth) foreach (['engagementManageTemplate'=>'จัดการแบบสำรวจความผูกพัน','engagementManageRound'=>'จัดการรอบสำรวจความผูกพัน','engagementViewAnalytics'=>'ดูผลรวมความผูกพันทั้งองค์กร','engagementManageAction'=>'จัดการแผนปรับปรุงความผูกพัน','engagementExportAggregate'=>'ส่งออกผลรวมความผูกพัน'] as $name=>$label) {
            if (!$auth->getPermission($name)) { $p=$auth->createPermission($name); $p->description=$label; $auth->add($p); }
        }
        // No automatic grants to HR or employees, no published sample survey.
    }

    private function table($name) { return '{{%hr_engagement_'.$name.'}}'; }
    public function safeDown()
    {
        echo "Engagement contains confidential records. Use a reviewed retention/forward migration; automatic drop is disabled.\n";
        return false;
    }
}
