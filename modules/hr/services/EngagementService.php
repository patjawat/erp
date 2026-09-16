<?php
namespace app\modules\hr\services;

use Yii;
use yii\db\Connection;
use yii\db\Query;
use DomainException;

/** All writes and scoring are server-side. No response payload enters audit records. */
class EngagementService
{
    private Connection $db;
    public function __construct(?Connection $db = null) { $this->db = $db ?? Yii::$app->db; }
    public function table(string $name): string { return '{{%hr_engagement_'.$name.'}}'; }
    public function query(string $name): Query { return (new Query())->from($this->table($name)); }
    public function ready(): bool { return $this->db->getTableSchema($this->table('round')) !== null; }
    private function now(): string { return gmdate('Y-m-d H:i:s'); }
    public static function localDate(?string $utc): string { return $utc ? (new \DateTimeImmutable($utc,new \DateTimeZone('UTC')))->setTimezone(new \DateTimeZone('Asia/Bangkok'))->format('d/m/Y H:i') : '—'; }
    public static function statusLabel(string $status): string { return ['draft'=>'ร่าง','open'=>'เปิดรับคำตอบ','closed'=>'ปิดรอบ รอเผยแพร่ผล','published'=>'เผยแพร่แล้ว','pending'=>'รอเริ่ม','doing'=>'กำลังดำเนินการ','done'=>'เสร็จแล้ว','cancelled'=>'ยกเลิก','submitted'=>'ตอบแล้ว','ok'=>'แสดงผลได้','suppressed'=>'ปกปิดกลุ่มเล็ก','no_data'=>'ยังไม่มีข้อมูล'][$status]??$status; }
    private function insert(string $name, array $data): int { $this->db->createCommand()->insert($this->table($name),$data)->execute(); return (int)$this->db->getLastInsertID(); }
    private function update(string $name, array $values, array $where): void { $this->db->createCommand()->update($this->table($name),$values,$where)->execute(); }
    private function audit(string $event, int $actor, ?int $round = null): void { $this->insert('audit',['event'=>$event,'actor_id'=>$actor,'round_id'=>$round,'occurred_at'=>$this->now()]); }
    public function row(string $name, int $id, bool $lock = false): array
    {
        $command=$this->query($name)->where(['id'=>$id])->createCommand($this->db);
        if ($lock && $this->db->driverName !== 'sqlite') {
            // setSql() clears bindings; preserve the query parameters on the new command.
            $command=$this->db->createCommand($command->getSql().' FOR UPDATE',$command->params);
        }
        $row=$command->queryOne();
        if (!$row) throw new DomainException('ไม่พบรายการ');
        return $row;
    }
    public function versions(): array
    {
        return (new Query())->select(['v.*','t.title'])->from(['v'=>$this->table('version')])->innerJoin(['t'=>$this->table('template')],'t.id=v.template_id')->orderBy(['v.id'=>SORT_DESC])->all($this->db);
    }
    public function definition(int $version): array
    {
        $dimensions=$this->query('dimension')->where(['version_id'=>$version])->orderBy('sequence')->all($this->db);
        foreach($dimensions as &$dimension) {
            $dimension['questions']=$this->query('question')->where(['dimension_id'=>$dimension['id']])->orderBy('sequence')->all($this->db);
            foreach($dimension['questions'] as &$question) $question['options']=$this->query('option')->where(['question_id'=>$question['id']])->orderBy('id')->all($this->db);
            unset($question);
        }
        unset($dimension);
        return $dimensions;
    }
    public function saveTemplate(string $title, array $dimensions, int $actor, ?int $versionId = null): int
    {
        $title=trim($title);
        if ($title==='' || mb_strlen($title)>255) throw new DomainException('กรุณาระบุชื่อแบบสำรวจไม่เกิน 255 ตัวอักษร');
        if (!$dimensions || count($dimensions)>12) throw new DomainException('แบบสำรวจต้องมี 1–12 ด้าน');
        $codes=[]; $outcomes=0; $total=0;
        foreach($dimensions as $dimension) {
            if (!preg_match('/^[a-z][a-z0-9_]{0,40}$/D',$dimension['code'] ?? '') || isset($codes[$dimension['code']]) || trim($dimension['title'] ?? '')==='' || mb_strlen($dimension['title'])>255) throw new DomainException('รหัสหรือชื่อด้านไม่ถูกต้อง');
            $codes[$dimension['code']]=true;
            if (!in_array($dimension['kind'] ?? '',['outcome','driver'],true)) throw new DomainException('ประเภทด้านไม่ถูกต้อง');
            if ($dimension['kind']==='outcome') $outcomes++;
            if (empty($dimension['questions'])) throw new DomainException('แต่ละด้านต้องมีคำถาม');
            foreach($dimension['questions'] as $question) {
                if (trim($question['prompt'] ?? '')==='' || mb_strlen($question['prompt'])>2000) throw new DomainException('กรุณาระบุคำถามไม่เกิน 2,000 ตัวอักษร');
                $total++;
            }
        }
        if (!$outcomes || $total>80) throw new DomainException('ต้องมีด้านผลความผูกพัน และรวมไม่เกิน 80 ข้อ');
        return $this->db->transaction(function() use($title,$dimensions,$actor,$versionId) {
            if ($versionId) {
                $version=$this->row('version',$versionId,true);
                if ($version['status']!=='draft') throw new DomainException('แบบเผยแพร่แล้ว กรุณาสร้างเวอร์ชันใหม่');
                $questionIds=$this->query('question')->select('id')->where(['version_id'=>$versionId])->column($this->db);
                $this->db->createCommand()->delete($this->table('option'),['question_id'=>$questionIds])->execute();
                $this->db->createCommand()->delete($this->table('question'),['version_id'=>$versionId])->execute();
                $this->db->createCommand()->delete($this->table('dimension'),['version_id'=>$versionId])->execute();
                // The template title is shared by published versions, so leave it immutable.
            } else {
                $template=$this->insert('template',['code'=>'eng_'.bin2hex(random_bytes(8)),'title'=>$title]);
                $versionId=$this->insert('version',['template_id'=>$template,'version_no'=>1,'status'=>'draft','comparability_key'=>'draft','scoring_policy_json'=>json_encode(['scale'=>5,'minimum_completion'=>0.8,'dimension_weights'=>'equal','outcome_requires_all'=>true])]);
            }
            $this->writeDefinition($versionId,$dimensions);
            $this->audit('template_saved',$actor);
            return $versionId;
        });
    }
    private function writeDefinition(int $version, array $dimensions): void
    {
        foreach(array_values($dimensions) as $i=>$d) {
            $dim=$this->insert('dimension',['version_id'=>$version,'code'=>$d['code'],'title'=>$d['title'],'kind'=>$d['kind'],'sequence'=>$i]);
            foreach(array_values($d['questions']) as $j=>$q) {
                $id=$this->insert('question',['version_id'=>$version,'dimension_id'=>$dim,'code'=>$d['code'].'_'.($j+1),'prompt'=>trim($q['prompt']),'reverse_scored'=>(int)!empty($q['reverse_scored']),'sequence'=>$j]);
                foreach([1=>'ไม่เห็นด้วยอย่างยิ่ง',2=>'ไม่เห็นด้วย',3=>'ปานกลาง',4=>'เห็นด้วย',5=>'เห็นด้วยอย่างยิ่ง','na'=>'ไม่เกี่ยวข้อง / ไม่ประสงค์ตอบ'] as $code=>$label) $this->insert('option',['question_id'=>$id,'code'=>(string)$code,'label'=>$label,'score'=>$code==='na'?null:$code,'is_missing'=>(int)($code==='na')]);
            }
        }
    }
    public function cloneVersion(int $id, int $actor): int
    {
        return $this->db->transaction(function() use($id,$actor) {
            $v=$this->row('version',$id);
            $this->row('template',(int)$v['template_id'],true);
            $next=(int)$this->query('version')->where(['template_id'=>$v['template_id']])->max('version_no',$this->db)+1;
            $copy=$this->insert('version',['template_id'=>$v['template_id'],'version_no'=>$next,'status'=>'draft','scoring_policy_json'=>$v['scoring_policy_json'],'comparability_key'=>'draft']);
            $this->writeDefinition($copy,$this->definition($id));
            $this->audit('template_cloned',$actor);
            return $copy;
        });
    }
    public function publishVersion(int $id, int $actor): void
    {
        $this->db->transaction(function() use($id,$actor) {
            $v=$this->row('version',$id,true);
            if ($v['status']!=='draft') throw new DomainException('เวอร์ชันนี้เผยแพร่แล้ว');
            $definition=$this->definition($id); $canonical=[]; $outcomes=0;
            foreach($definition as $d) {
                if (!$d['questions']) throw new DomainException('ยังไม่มีคำถาม');
                if ($d['kind']==='outcome') $outcomes++;
                $canonical[]=[$d['code'],$d['title'],$d['kind'],array_map(static function($q){ return [$q['prompt'],(bool)$q['reverse_scored']]; },$d['questions'])];
            }
            if (!$outcomes) throw new DomainException('ต้องมีด้านผลความผูกพัน');
            $this->update('version',['status'=>'published','published_at'=>$this->now(),'published_by'=>$actor,'comparability_key'=>hash('sha256',json_encode([$canonical,$v['scoring_policy_json']],JSON_UNESCAPED_UNICODE))],['id'=>$id]);
            $this->audit('template_published',$actor);
        });
    }
    public static function localToUtc(string $value): string
    {
        $date=\DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$value,new \DateTimeZone('Asia/Bangkok'));
        if (!$date || $date->format('Y-m-d\TH:i')!==$value) throw new DomainException('วันเวลาไม่ถูกต้อง');
        return $date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }
    public function createRound(array $input, int $actor, ?int $roundId = null): int
    {
        $v=$this->row('version',(int)($input['version_id']??0));
        if ($v['status']!=='published') throw new DomainException('เลือกแบบสำรวจที่เผยแพร่แล้ว');
        $open=self::localToUtc((string)($input['open_at']??'')); $close=self::localToUtc((string)($input['close_at']??''));
        $title=trim((string)($input['title']??'')); $notice=trim((string)($input['privacy_notice']??''));
        $year=filter_var($input['fiscal_year']??null,FILTER_VALIDATE_INT); $k=filter_var($input['minimum_group_size']??null,FILTER_VALIDATE_INT); $days=filter_var($input['retention_days']??null,FILTER_VALIDATE_INT);
        if (!$title || mb_strlen($title)>255 || mb_strlen($notice)<30 || mb_strlen($notice)>5000 || $close<=$open || $close<=$this->now() || $year<2500 || $year>2700 || $k<5 || $k>100 || $days<30 || $days>3650) throw new DomainException('ตรวจชื่อ รอบเวลา ปีงบประมาณ กลุ่มขั้นต่ำ 5–100 คน อายุข้อมูล 30–3,650 วัน และคำชี้แจงความลับ');
        return $this->db->transaction(function() use($input,$actor,$v,$open,$close,$title,$notice,$year,$k,$days,$roundId) {
            $values=['title'=>$title,'version_id'=>$v['id'],'fiscal_year'=>$year,'open_at'=>$open,'close_at'=>$close,'privacy_notice'=>$notice,'retention_days'=>$days,'minimum_group_size'=>$k,'policy_confirmed'=>(int)(($input['policy_confirmed']??'')==='1')];
            if ($roundId) {
                if ($this->row('round',$roundId,true)['status']!=='draft') throw new DomainException('แก้ได้เฉพาะร่างรอบสำรวจ');
                $this->update('round',$values,['id'=>$roundId]); $id=$roundId;
            } else $id=$this->insert('round',array_merge($values,['code'=>'round_'.bin2hex(random_bytes(8)),'status'=>'draft','created_by'=>$actor,'created_at'=>$this->now()]));
            $this->audit($roundId?'round_updated':'round_created',$actor,$id); return $id;
        });
    }
    /** Cohort is frozen at opening, not reconstructed using today's department later. */
    public function eligibleEmployees(): array
    {
        $sql=WorkforceSnapshotService::sql(); $active=WorkforceSnapshotService::inServiceWhereSql('e');
        return $this->db->createCommand("SELECT e.id,e.department,COALESCE(t.name,'ไม่ระบุหน่วยงาน') department_name FROM {$sql} e LEFT JOIN tree t ON t.id=e.department AND t.tb_name='diagram' WHERE e.branch='MAIN' AND e.id<>1 AND (e.status IS NULL OR e.status NOT IN ('CANCEL','19')) AND {$active} ORDER BY e.id",[':as_of'=>(new \DateTimeImmutable('now',new \DateTimeZone('Asia/Bangkok')))->format('Y-m-d')])->queryAll();
    }
    public function openRound(int $id, int $actor): void
    {
        $this->db->transaction(function() use($id,$actor) {
            $round=$this->row('round',$id,true);
            if ($round['status']!=='draft' || !$round['policy_confirmed'] || $round['close_at']<=$this->now()) throw new DomainException('เปิดได้เฉพาะร่างรอบที่ยังไม่หมดเวลาและยืนยันนโยบายแล้ว');
            $employees=$this->eligibleEmployees();
            if (!$employees) throw new DomainException('ไม่พบผู้มีสิทธิ์ตอบ');
            // First release reports the whole frozen cohort only; no intersecting demographic filters.
            $cohort=$this->insert('cohort',['round_id'=>$id,'group_code'=>'all','group_label'=>'ภาพรวมผู้มีสิทธิ์ในรอบ','eligible_count'=>count($employees)]);
            foreach($employees as $e) $this->insert('invitation',['round_id'=>$id,'emp_id'=>$e['id'],'respondent_key'=>bin2hex(random_bytes(24)),'cohort_id'=>$cohort,'department_id_snapshot'=>$e['department'],'department_name_snapshot'=>$e['department_name'],'completion_status'=>'pending']);
            $this->update('round',['status'=>'open','cohort_at'=>$this->now()],['id'=>$id]); $this->audit('round_opened',$actor,$id);
        });
    }
    public function closeRound(int $id, int $actor): void
    {
        $this->db->transaction(function() use($id,$actor) {
            $r=$this->row('round',$id,true);
            if ($r['status']!=='open') throw new DomainException('รอบนี้ไม่ได้เปิดรับคำตอบ');
            $this->update('round',['status'=>'closed'],['id'=>$id]); $this->audit('round_closed',$actor,$id);
        });
    }
    public function invitation(int $round, int $employee): ?array
    {
        return $this->query('invitation')->where(['round_id'=>$round,'emp_id'=>$employee])->one($this->db) ?: null;
    }
    public function myRounds(int $employee): array
    {
        return (new Query())->select(['r.*','i.completion_status'])->from(['r'=>$this->table('round')])->innerJoin(['i'=>$this->table('invitation')],'i.round_id=r.id')->where(['i.emp_id'=>$employee])->orderBy(['r.id'=>SORT_DESC])->all($this->db);
    }
    public function submit(int $roundId, int $employee, array $answers, string $key): void
    {
        if (!preg_match('/^[a-zA-Z0-9_-]{16,64}$/D',$key)) throw new DomainException('รหัสการส่งไม่ถูกต้อง กรุณาโหลดแบบสำรวจใหม่');
        $this->db->transaction(function() use($roundId,$employee,$answers,$key) {
            $round=$this->row('round',$roundId,true);
            $invitation=$this->invitation($roundId,$employee);
            if (!$invitation) throw new DomainException('คุณไม่มีสิทธิ์ตอบในรอบนี้');
            if ($invitation['completion_status']==='submitted') return; // Repeat submission cannot alter answers.
            if ($round['status']!=='open' || $round['open_at']>$this->now() || $round['close_at']<=$this->now()) throw new DomainException('รอบนี้ไม่ได้เปิดรับคำตอบ');
            $valid=[];
            foreach($this->definition((int)$round['version_id']) as $d) foreach($d['questions'] as $q) {
                $value=$answers[$q['id']]??null;
                if (!is_scalar($value) || !ctype_digit((string)$value)) throw new DomainException('กรุณาตอบทุกข้อ หรือเลือกไม่ประสงค์ตอบ');
                $options=array_column($q['options'],null,'id');
                if (!isset($options[(int)$value])) throw new DomainException('ตัวเลือกไม่ตรงกับคำถาม');
                $valid[(int)$q['id']]=(int)$value;
            }
            if (count($valid)!==count($answers)) throw new DomainException('คำถามไม่ตรงกับเวอร์ชันของรอบ');
            if ($this->query('response')->where(['round_id'=>$roundId,'idempotency_key'=>$key])->exists($this->db)) throw new DomainException('รหัสการส่งซ้ำ กรุณาโหลดแบบสำรวจใหม่');
            $response=$this->insert('response',['round_id'=>$roundId,'respondent_key'=>$invitation['respondent_key'],'cohort_id'=>$invitation['cohort_id'],'idempotency_key'=>$key,'status'=>'submitted','submission_day'=>gmdate('Y-m-d')]);
            foreach($valid as $question=>$option) $this->insert('answer',['response_id'=>$response,'question_id'=>$question,'option_id'=>$option]);
            $this->update('invitation',['completion_status'=>'submitted'],['id'=>$invitation['id']]);
            // Never audit actor, response id, timestamps or answers together.
        });
    }
    /** Pure calculation: each respondent has equal weight, NA is not zero. */
    public static function score(array $definition, array $responses): array
    {
        $result=[]; $outcomeCodes=[]; $personScores=[];
        foreach($definition as $d) {
            $means=[]; $positive=0; $answerCount=0;
            if ($d['kind']==='outcome') $outcomeCodes[]=$d['code'];
            foreach($responses as $person=>$answers) {
                $values=[];
                foreach($d['questions'] as $q) {
                    $value=$answers[$q['id']]??null;
                    if ($value===null) continue;
                    $value=(float)$value;
                    if ($value<1 || $value>5) throw new DomainException('คะแนนนอกช่วง');
                    $values[]=!empty($q['reverse_scored']) ? 6-$value : $value;
                }
                // Favorable answers count all valid answers, independently of person-mean eligibility.
                foreach($values as $value) { $answerCount++; if ($value>=4) $positive++; }
                if (count($values)<ceil(count($d['questions'])*0.8)) continue;
                $means[]=array_sum($values)/count($values);
                $personScores[$person][$d['code']]=end($means);
            }
            $mean=$means ? array_sum($means)/count($means) : null;
            $result[$d['code']]=['title'=>$d['title'],'kind'=>$d['kind'],'valid_n'=>count($means),'mean'=>$mean,'index'=>$mean===null?null:($mean-1)*25,'favorable'=>$answerCount?$positive*100/$answerCount:null];
        }
        $overall=[];
        foreach($personScores as $scores) {
            if (count(array_intersect($outcomeCodes,array_keys($scores)))!==count($outcomeCodes) || !$outcomeCodes) continue;
            $overall[]=array_sum(array_intersect_key($scores,array_flip($outcomeCodes)))/count($outcomeCodes);
        }
        $mean=$overall?array_sum($overall)/count($overall):null;
        $result['_overall']=['title'=>'ความผูกพันรวม','kind'=>'overall','valid_n'=>count($overall),'mean'=>$mean,'index'=>$mean===null?null:($mean-1)*25,'favorable'=>null];
        return $result;
    }
    public function publishResults(int $id, int $actor): void
    {
        $this->db->transaction(function() use($id,$actor) {
            $round=$this->row('round',$id,true);
            if ($round['status']!=='closed') throw new DomainException('ต้องปิดรับคำตอบก่อนเผยแพร่ผล');
            if ($this->query('audit')->where(['round_id'=>$id,'event'=>'raw_data_purged'])->exists($this->db)) throw new DomainException('ข้อมูลดิบครบอายุและถูกลบแล้ว ไม่สามารถคำนวณผลใหม่ได้');
            $cohort=$this->query('cohort')->where(['round_id'=>$id,'group_code'=>'all'])->one($this->db);
            $people=$this->query('response')->where(['round_id'=>$id,'status'=>'submitted'])->all($this->db);
            $responses=[];
            foreach($people as $person) $responses[$person['id']]=[];
            $rows=(new Query())->select(['a.response_id','a.question_id','o.score','o.is_missing'])->from(['a'=>$this->table('answer')])->innerJoin(['o'=>$this->table('option')],'o.id=a.option_id')->where(['a.response_id'=>array_keys($responses)])->all($this->db);
            foreach($rows as $row) $responses[$row['response_id']][$row['question_id']]=$row['is_missing']?null:(float)$row['score'];
            $revision=(int)$round['result_revision']+1;
            foreach(self::score($this->definition((int)$round['version_id']),$responses) as $code=>$metric) {
                $suppressed=(int)$cohort['eligible_count']<(int)$round['minimum_group_size'] || $metric['valid_n']<(int)$round['minimum_group_size'];
                $status=!count($people)?'no_data':($suppressed?'suppressed':($metric['mean']===null?'no_data':'ok'));
                $this->insert('result',['round_id'=>$id,'cohort_id'=>$cohort['id'],'dimension_code'=>$code,'dimension_title'=>$metric['title'],'kind'=>$metric['kind'],'result_revision'=>$revision,'eligible_n'=>$suppressed?null:$cohort['eligible_count'],'respondent_n'=>$suppressed?null:count($people),'valid_n'=>$suppressed?null:$metric['valid_n'],'mean_score'=>$suppressed?null:$metric['mean'],'index_score'=>$suppressed?null:$metric['index'],'favorable_percent'=>$suppressed?null:$metric['favorable'],'status'=>$status,'computed_at'=>$this->now()]);
            }
            $this->update('round',['status'=>'published','result_revision'=>$revision],['id'=>$id]); $this->audit('results_published',$actor,$id);
        });
    }
    public function report(int $id): array
    {
        $round=$this->row('round',$id);
        if ($round['status']!=='published') return ['round'=>$round,'status'=>'no_data','metrics'=>[]];
        return ['round'=>$round,'status'=>'ok','metrics'=>$this->query('result')->where(['round_id'=>$id,'result_revision'=>$round['result_revision']])->orderBy('id')->all($this->db)];
    }
    public function saveAction(array $input, int $actor, ?int $id=null): int
    {
        return $this->db->transaction(function() use($input,$actor,$id) {
            if ($id) {
                $existing=$this->row('action',$id,true);
                if (!in_array($input['status']??'', ['pending','doing','done','cancelled'],true)) throw new DomainException('สถานะแผนไม่ถูกต้อง');
                $note=trim((string)($input['effectiveness_note']??''));
                if ($input['status']==='done' && $note==='') throw new DomainException('กรุณาบันทึกผลติดตามก่อนปิดแผน');
                if (mb_strlen($note)>3000) throw new DomainException('ผลติดตามยาวเกินกำหนด');
                $this->update('action',['status'=>$input['status'],'effectiveness_note'=>$note,'completed_at'=>$input['status']==='done'?($existing['completed_at']??$this->now()):null],['id'=>$id]);
                $this->audit('action_updated',$actor,(int)$existing['round_id']); return $id;
            }
            $round=$this->row('round',(int)($input['round_id']??0));
            $metric=$this->query('result')->where(['round_id'=>$round['id'],'dimension_code'=>$input['dimension_code']??'','status'=>'ok','result_revision'=>$round['result_revision']])->one($this->db);
            if ($round['status']!=='published' || !$metric) throw new DomainException('เลือกประเด็นจากผลที่เผยแพร่และแสดงได้');
            $issue=trim((string)($input['issue_summary']??'')); $target=trim((string)($input['target']??'')); $due=(string)($input['due_date']??''); $owner=(int)($input['owner_emp_id']??0);
            $date=\DateTimeImmutable::createFromFormat('!Y-m-d',$due);
            if (!$issue || !$target || mb_strlen($issue)>3000 || mb_strlen($target)>3000 || !$date || $date->format('Y-m-d')!==$due || !(new Query())->from('{{%employees}}')->where(['id'=>$owner])->exists($this->db)) throw new DomainException('ตรวจประเด็น เป้าหมาย ผู้รับผิดชอบ และกำหนดเสร็จ');
            $id=$this->insert('action',['round_id'=>$round['id'],'cohort_id'=>$metric['cohort_id'],'dimension_code'=>$metric['dimension_code'],'issue_summary'=>$issue,'target'=>$target,'owner_emp_id'=>$owner,'due_date'=>$due,'status'=>'pending','baseline_revision'=>$round['result_revision'],'created_by'=>$actor]);
            $this->audit('action_created',$actor,(int)$round['id']); return $id;
        });
    }
    public function dashboard(int $year, ?int $roundId): array
    {
        if (!$this->ready()) return ['status'=>'not_connected','rounds'=>[],'report'=>null];
        $rounds=$this->query('round')->select(['id','title'])->where(['fiscal_year'=>$year,'status'=>'published'])->orderBy(['id'=>SORT_DESC])->all($this->db);
        if (!$roundId && count($rounds)===1) $roundId=(int)$rounds[0]['id'];
        $allowed=array_map('intval',array_column($rounds,'id'));
        return ['status'=>$rounds?'ok':'no_data','rounds'=>$rounds,'report'=>$roundId && in_array($roundId,$allowed,true)?$this->report($roundId):null];
    }
}
