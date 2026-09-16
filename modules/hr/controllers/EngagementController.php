<?php
namespace app\modules\hr\controllers;

use Yii;
use DomainException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\ServiceUnavailableHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use app\modules\hr\services\EngagementService;

class EngagementController extends Controller
{
    private EngagementService $service;
    public function init() { parent::init(); $this->service=new EngagementService(); }
    public function behaviors()
    {
        return [
            'access'=>['class'=>AccessControl::class,'rules'=>[['allow'=>true,'roles'=>['@']]]],
            'verbs'=>['class'=>VerbFilter::class,'actions'=>['publish-template'=>['POST'],'clone-template'=>['POST'],'transition'=>['POST']]],
        ];
    }
    public function beforeAction($action)
    {
        if ($action->id==='respond') {
            \app\modules\hr\services\EngagementPrivacy::protect();
        }
        Yii::$app->response->headers->set('Cache-Control','no-store, private');
        if (!parent::beforeAction($action)) return false;
        if (!in_array($action->id,['index','mine'],true) && !$this->service->ready()) throw new ServiceUnavailableHttpException('ยังไม่ได้ติดตั้งตารางความผูกพัน');
        return true;
    }
    private function allowed(string $permission): bool { return Yii::$app->user->can($permission) || Yii::$app->user->can('admin'); }
    private function permit(string $permission): void { if (!$this->allowed($permission)) throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ดำเนินการส่วนนี้'); }
    private function actor(): int { return (int)Yii::$app->user->id; }
    private function employee(): int
    {
        $id=(new Query())->from('{{%employees}}')->select('id')->where(['user_id'=>Yii::$app->user->id])->scalar();
        if (!$id) throw new ForbiddenHttpException('บัญชีนี้ยังไม่ได้ผูกกับทะเบียนบุคลากร');
        return (int)$id;
    }
    public function actionIndex()
    {
        $this->permit('engagementManageRound');
        if (!$this->service->ready()) return $this->render('unavailable');
        return $this->render('index',['provider'=>new ActiveDataProvider(['query'=>$this->service->query('round')->orderBy(['id'=>SORT_DESC]),'pagination'=>['pageSize'=>20]]),'versions'=>$this->service->versions()]);
    }
    public function actionTemplates()
    {
        $this->permit('engagementManageTemplate');
        return $this->render('templates',['versions'=>$this->service->versions()]);
    }
    public function actionTemplate($id=null)
    {
        $this->permit('engagementManageTemplate');
        $version=$id ? $this->service->row('version',(int)$id) : null;
        $title=$version ? $this->service->row('template',(int)$version['template_id'])['title'] : '';
        $dimensions=$version ? $this->service->definition((int)$id) : [];
        if (Yii::$app->request->isPost) {
            try {
                $raw=Yii::$app->request->post('dimensions',[]); $parsed=[];
                if (!is_array($raw)) throw new DomainException('รูปแบบคำถามไม่ถูกต้อง');
                foreach($raw as $code=>$d) {
                    if (!is_array($d)) throw new DomainException('รูปแบบคำถามไม่ถูกต้อง');
                    $lines=array_values(array_filter(array_map('trim',preg_split('/\R/u',(string)($d['questions']??''))),static fn($line)=>$line!==''));
                    if (!$lines) continue;
                    $parsed[]=['code'=>$code,'title'=>(string)($d['title']??''),'kind'=>$code==='engagement'?'outcome':'driver','questions'=>array_map(static fn($line)=>['prompt'=>$line,'reverse_scored'=>false],$lines)];
                }
                $this->service->saveTemplate($version ? $title : (string)Yii::$app->request->post('title',''),$parsed,$this->actor(),$version?(int)$id:null);
                Yii::$app->session->setFlash('success','บันทึกแบบร่างแล้ว กรุณาตรวจสอบก่อนเผยแพร่');
                return $this->redirect(['templates']);
            } catch (DomainException $e) { Yii::$app->session->setFlash('error',$e->getMessage()); }
        }
        return $this->render('template',['version'=>$version,'title'=>$title,'dimensions'=>$dimensions]);
    }
    public function actionPublishTemplate($id)
    {
        $this->permit('engagementManageTemplate');
        try { $this->service->publishVersion((int)$id,$this->actor()); Yii::$app->session->setFlash('success','เผยแพร่แบบแล้ว ข้อคำถามและสูตรถูกล็อก'); }
        catch(DomainException $e) { Yii::$app->session->setFlash('error',$e->getMessage()); }
        return $this->redirect(['templates']);
    }
    public function actionCloneTemplate($id)
    {
        $this->permit('engagementManageTemplate');
        $copy=$this->service->cloneVersion((int)$id,$this->actor());
        return $this->redirect(['template','id'=>$copy]);
    }
    public function actionCreate($id=null)
    {
        $this->permit('engagementManageRound');
        $round=$id?$this->service->row('round',(int)$id):null;
        if ($round && $round['status']!=='draft') throw new ForbiddenHttpException('แก้ได้เฉพาะร่างรอบ');
        if (Yii::$app->request->isPost) {
            try { $savedId=$this->service->createRound(Yii::$app->request->post(),$this->actor(),$round?(int)$id:null); return $this->redirect(['round','id'=>$savedId]); }
            catch(DomainException $e) { Yii::$app->session->setFlash('error',$e->getMessage()); }
        }
        return $this->render('create',['versions'=>array_filter($this->service->versions(),static fn($v)=>$v['status']==='published'),'round'=>$round]);
    }
    public function actionRound($id)
    {
        $this->permit('engagementManageRound');
        $round=$this->service->row('round',(int)$id);
        $total=$this->service->query('invitation')->where(['round_id'=>$id])->count();
        $submitted=$this->service->query('invitation')->where(['round_id'=>$id,'completion_status'=>'submitted'])->count();
        $preview=[];
        if ($round['status']==='draft') foreach($this->service->eligibleEmployees() as $e) $preview[$e['department_name']]=($preview[$e['department_name']]??0)+1;
        return $this->render('round',['round'=>$round,'total'=>$total,'submitted'=>$submitted,'preview'=>$preview,'purged'=>$this->service->query('audit')->where(['round_id'=>$id,'event'=>'raw_data_purged'])->exists()]);
    }
    public function actionTransition($id)
    {
        $operation=Yii::$app->request->post('operation');
        $this->permit($operation==='publish'?'engagementViewAnalytics':'engagementManageRound');
        try {
            if ($operation==='open') $this->service->openRound((int)$id,$this->actor());
            elseif ($operation==='close') $this->service->closeRound((int)$id,$this->actor());
            elseif ($operation==='publish') $this->service->publishResults((int)$id,$this->actor());
            else throw new DomainException('คำสั่งไม่ถูกต้อง');
            Yii::$app->session->setFlash('success','ดำเนินการแล้ว');
        } catch(DomainException $e) { Yii::$app->session->setFlash('error',$e->getMessage()); }
        return $this->redirect($operation==='publish'?['report','id'=>$id]:['round','id'=>$id]);
    }
    public function actionMine()
    {
        if (!$this->service->ready()) return $this->render('unavailable');
        return $this->render('mine',['rounds'=>$this->service->myRounds($this->employee())]);
    }
    public function actionRespond($id)
    {
        $employee=$this->employee(); $round=$this->service->row('round',(int)$id); $invitation=$this->service->invitation((int)$id,$employee);
        if (!$invitation) throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ตอบรอบนี้');
        if ($invitation['completion_status']==='submitted') return $this->render('thanks');
        if (Yii::$app->request->isPost) {
            try {
                if (Yii::$app->request->post('acknowledge')!=='1') throw new DomainException('กรุณายืนยันว่าอ่านคำชี้แจงแล้ว');
                $answers=Yii::$app->request->post('answers',[]);
                if (!is_array($answers)) throw new DomainException('รูปแบบคำตอบไม่ถูกต้อง');
                $this->service->submit((int)$id,$employee,$answers,(string)Yii::$app->request->post('submission_key',''));
                return $this->redirect(['respond','id'=>$id]);
            } catch(DomainException $e) { Yii::$app->session->setFlash('error',$e->getMessage()); }
            catch(\Throwable $e) { Yii::error('Engagement submission failed; payload omitted','engagement'); Yii::$app->session->setFlash('error','ยังส่งไม่สำเร็จ กรุณาลองใหม่ ข้อมูลไม่ได้ถูกส่งซ้ำ'); }
        }
        $available=$round['status']==='open' && $round['open_at']<=gmdate('Y-m-d H:i:s') && $round['close_at']>gmdate('Y-m-d H:i:s');
        return $this->render('respond',['round'=>$round,'dimensions'=>$available?$this->service->definition((int)$round['version_id']):[],'available'=>$available]);
    }
    public function actionReport($id=null)
    {
        $this->permit('engagementViewAnalytics');
        $rounds=$this->service->query('round')->where(['status'=>['closed','published']])->orderBy(['id'=>SORT_DESC])->all();
        $selected=$id ? (int)$id : ($rounds ? (int)$rounds[0]['id'] : null);
        $report=$selected ? $this->service->report($selected) : null;
        return $this->render('report',['rounds'=>$rounds,'report'=>$report,'trend'=>$selected?$this->trend($selected):[]]);
    }
    private function trend(int $id): array
    {
        $round=$this->service->row('round',$id); $version=$this->service->row('version',(int)$round['version_id']);
        $rows=(new Query())->select(['r.id','r.title','r.close_at','x.index_score','x.status'])->from(['r'=>$this->service->table('round')])->innerJoin(['v'=>$this->service->table('version')],'v.id=r.version_id')->innerJoin(['x'=>$this->service->table('result')],"x.round_id=r.id AND x.result_revision=r.result_revision AND x.dimension_code='_overall'")->where(['r.status'=>'published','v.comparability_key'=>$version['comparability_key']])->andWhere(['<=','r.close_at',$round['close_at']])->orderBy(['r.close_at'=>SORT_DESC,'r.id'=>SORT_DESC])->limit(12)->all();
        return array_reverse($rows);
    }
    public function actionExport($id)
    {
        $this->permit('engagementViewAnalytics'); $this->permit('engagementExportAggregate');
        $report=$this->service->report((int)$id);
        $stream=fopen('php://temp','w+'); fwrite($stream,"\xEF\xBB\xBF"); fputcsv($stream,['รอบสำรวจ','ด้าน','สถานะ','ผู้ตอบที่ใช้คำนวณ','ค่าเฉลี่ย 1–5','ดัชนี 0–100']);
        foreach($report['metrics'] as $m) {
            $safe=static fn($value)=>preg_match('/^[=+@\-\t\r]/u',(string)$value)?"'".$value:$value;
            fputcsv($stream,[$safe($report['round']['title']),$safe($m['dimension_title']),$m['status'],$m['valid_n'],$m['mean_score'],$m['index_score']]);
        }
        rewind($stream); $csv=stream_get_contents($stream); fclose($stream);
        return Yii::$app->response->sendContentAsFile($csv,'engagement-summary.csv',['mimeType'=>'text/csv']);
    }
    public function actionActions($round_id=null)
    {
        $this->permit('engagementManageAction');
        $query=$this->service->query('action')->orderBy(['due_date'=>SORT_ASC]);
        if ($round_id) $query->andWhere(['round_id'=>(int)$round_id]);
        return $this->render('actions',['provider'=>new ActiveDataProvider(['query'=>$query,'pagination'=>['pageSize'=>20]])]);
    }
    public function actionAction($round_id=null,$id=null)
    {
        $this->permit('engagementManageAction');
        $action=$id?$this->service->row('action',(int)$id):null;
        $roundId=$action?(int)$action['round_id']:(int)$round_id;
        if (Yii::$app->request->isPost) {
            try { $input=Yii::$app->request->post(); $input['round_id']=$roundId; $this->service->saveAction($input,$this->actor(),$action?(int)$id:null); return $this->redirect(['actions','round_id'=>$roundId]); }
            catch(DomainException $e) { Yii::$app->session->setFlash('error',$e->getMessage()); }
        }
        $metrics=$this->service->query('result')->where(['round_id'=>$roundId,'status'=>'ok'])->orderBy(['id'=>SORT_DESC])->all();
        $owners=(new Query())->select(['id','fname','lname'])->from('{{%employees}}')->where(['branch'=>'MAIN'])->andWhere(['<>','id',1])->orderBy('fname')->all();
        return $this->render('action',['action'=>$action,'metrics'=>$metrics,'owners'=>$owners,'roundId'=>$roundId]);
    }
}
