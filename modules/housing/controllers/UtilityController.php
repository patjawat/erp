<?php
declare(strict_types=1);
namespace app\modules\housing\controllers;

use app\modules\housing\models\AssetAssignment;
use app\modules\housing\models\BillingPeriod;
use app\modules\housing\models\Building;
use app\modules\housing\models\ChargeType;
use app\modules\housing\models\HousingRate;
use app\modules\housing\models\Meter;
use app\modules\housing\models\MeterReading;
use app\modules\housing\models\MonthlyAccount;
use app\modules\housing\models\MonthlyAccountItem;
use app\modules\housing\models\Occupancy;
use app\modules\housing\models\Room;
use app\modules\housing\models\Unit;
use Yii;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

final class UtilityController extends BaseController
{
    public function behaviors():array{return array_merge(parent::behaviors(),['verbs'=>['class'=>VerbFilter::class,'actions'=>['confirm-reading'=>['POST'],'generate-month'=>['POST'],'close-period'=>['POST']]]]);}
    public function actionIndex(){return $this->redirect(['monthly']);}
    public function actionCreateRate(){return $this->save(new HousingRate(),'rate');}
    public function actionUpdateRate(int $id){return $this->save($this->find(HousingRate::class,$id),'rate');}
    public function actionCreateMeter(){return $this->save(new Meter(['status'=>'active']),'meter');}
    public function actionUpdateMeter(int $id){return $this->save($this->find(Meter::class,$id),'meter');}
    public function actionCreateChargeType(){return $this->save(new ChargeType(['status'=>'active']),'charge-type');}
    public function actionUpdateChargeType(int $id){return $this->save($this->find(ChargeType::class,$id),'charge-type');}
    public function actionCreatePeriod(){return $this->save(new BillingPeriod(['status'=>'open']),'period');}
    public function actionUpdatePeriod(int $id){return $this->save($this->find(BillingPeriod::class,$id),'period');}

    public function actionChargeTypes()
    {
        return $this->render('charge-types',['models'=>ChargeType::find()->orderBy(['sort_order'=>SORT_ASC,'name'=>SORT_ASC])->all()]);
    }

    public function actionMonthly(?int $period_id=null)
    {
        $periods=BillingPeriod::find()->orderBy(['start_date'=>SORT_DESC])->all();
        $period_id??=$periods[0]->id??null;
        $period=$period_id?$this->find(BillingPeriod::class,$period_id):null;
        $accounts=$period?MonthlyAccount::find()->where(['billing_period_id'=>$period->id])->with('items.chargeType')
            ->orderBy(['building_name'=>SORT_ASC,'unit_name'=>SORT_ASC,'room_name'=>SORT_ASC,'payer_name'=>SORT_ASC])->all():[];
        $summary=['total'=>0.0,'paid'=>0.0,'balance'=>0.0,'pending'=>0,'unpaid'=>0,'electric'=>0.0,'water'=>0.0];
        foreach($accounts as $account){$summary['total']+=(float)$account->total_amount;$summary['paid']+=(float)$account->paid_amount;$summary['balance']+=(float)$account->balance_amount;if($account->status===MonthlyAccount::STATUS_PENDING)$summary['pending']++;if($account->payment_status!==MonthlyAccount::PAYMENT_PAID)$summary['unpaid']++;foreach($account->items as $item){if($item->chargeType?->code==='ELECTRIC')$summary['electric']+=(float)$item->amount;if($item->chargeType?->code==='WATER')$summary['water']+=(float)$item->amount;}}
        return $this->render('monthly',compact('periods','period','accounts','summary'));
    }

    public function actionGenerateMonth(int $period_id)
    {
        $period=$this->find(BillingPeriod::class,$period_id);
        if($period->status!=='open')throw new BadRequestHttpException('รอบเดือนนี้ปิดแล้ว');
        $transaction=Yii::$app->db->beginTransaction();
        try{
            foreach(Building::find()->where(['status'=>Building::STATUS_ACTIVE])->orderBy(['sort_order'=>SORT_ASC])->all() as $building){
                $units=$building->getUnits()->with('rooms')->all();
                if($units===[]){$this->createAccount($period,$building,null,null,null);continue;}
                foreach($units as $unit){
                    $occupancies=Occupancy::find()->with(['employee','residents','room'])->where(['unit_id'=>$unit->id,'status'=>[Occupancy::STATUS_ALLOCATED,Occupancy::STATUS_ACTIVE]])->all();
                    $wholeUnit=array_values(array_filter($occupancies,static fn(Occupancy $o):bool=>$o->room_id===null));
                    if($wholeUnit!==[]){foreach($wholeUnit as $occupancy)$this->createAccount($period,$building,$unit,null,$occupancy);continue;}
                    if($unit->rooms!==[]){
                        foreach($unit->rooms as $room){$roomOccupancies=array_values(array_filter($occupancies,static fn(Occupancy $o):bool=>(int)$o->room_id===(int)$room->id));if($roomOccupancies===[])$this->createAccount($period,$building,$unit,$room,null);else foreach($roomOccupancies as $occupancy)$this->createAccount($period,$building,$unit,$room,$occupancy);}
                    }elseif($occupancies===[])$this->createAccount($period,$building,$unit,null,null);else foreach($occupancies as $occupancy)$this->createAccount($period,$building,$unit,null,$occupancy);
                }
            }
            if(!$period->prepared_at)$period->updateAttributes(['prepared_at'=>date('Y-m-d H:i:s'),'prepared_by'=>Yii::$app->user->id?:null]);
            $transaction->commit();Yii::$app->session->setFlash('success','สร้างรายการบ้านพักประจำเดือนเรียบร้อยแล้ว');
        }catch(\Throwable $e){$transaction->rollBack();throw $e;}
        return $this->redirect(['monthly','period_id'=>$period->id]);
    }

    public function actionEditAccount(int $id)
    {
        $account=$this->find(MonthlyAccount::class,$id);
        $isLocked=$account->period->status!=='open';
        $types=ChargeType::find()->where(['status'=>'active'])->orderBy(['sort_order'=>SORT_ASC,'name'=>SORT_ASC])->all();
        $existing=MonthlyAccountItem::find()->where(['account_id'=>$account->id])->indexBy('charge_type_id')->all();
        if(Yii::$app->request->isPost){
            if($isLocked)throw new BadRequestHttpException('รอบเดือนนี้ปิดแล้ว ไม่สามารถแก้ไขได้');
            $posted=Yii::$app->request->post('items',[]);$paid=max(0,(float)Yii::$app->request->post('paid_amount',0));$transaction=Yii::$app->db->beginTransaction();
            try{$total=0.0;foreach($types as $type){$amount=max(0,round((float)($posted[$type->id]['amount']??0),2));$total+=$amount;$item=$existing[$type->id]??new MonthlyAccountItem(['account_id'=>$account->id,'charge_type_id'=>$type->id]);$item->description=$type->name;$item->amount=$amount;$item->note=trim((string)($posted[$type->id]['note']??''))?:null;$item->sort_order=$type->sort_order;if(!$item->save())throw new \RuntimeException(implode(' ',$item->getFirstErrors()));}
                if($paid>$total)throw new BadRequestHttpException('ยอดชำระต้องไม่มากกว่าค่าใช้จ่ายรวม');
                $balance=round($total-$paid,2);
                $payment=$total<=0
                    ? MonthlyAccount::PAYMENT_PAID
                    : ($paid<=0?MonthlyAccount::PAYMENT_UNPAID:($balance>0?MonthlyAccount::PAYMENT_PARTIAL:MonthlyAccount::PAYMENT_PAID));
                $account->updateAttributes(['total_amount'=>$total,'paid_amount'=>$paid,'balance_amount'=>$balance,'payment_status'=>$payment,'status'=>MonthlyAccount::STATUS_SAVED,'note'=>trim((string)Yii::$app->request->post('note'))?:null]);
                $transaction->commit();Yii::$app->response->format=Response::FORMAT_JSON;return ['status'=>'success','redirect'=>Url::to(['monthly','period_id'=>$account->billing_period_id])];
            }catch(\Throwable $e){$transaction->rollBack();throw $e;}
        }
        $defaults=[];foreach($types as $type)$defaults[$type->id]=$this->defaultAmount($account,$type);
        Yii::$app->response->format=Response::FORMAT_JSON;
        return ['title'=>$isLocked?'รายละเอียดค่าใช้จ่าย':'ลงค่าใช้จ่ายประจำเดือน','content'=>$this->renderAjax('_account_form',compact('account','types','existing','defaults','isLocked'))];
    }

    public function actionClosePeriod(int $id)
    {
        $period=$this->find(BillingPeriod::class,$id);
        $pending=(int)MonthlyAccount::find()->where(['billing_period_id'=>$id,'status'=>MonthlyAccount::STATUS_PENDING])->count();
        if($pending>0){Yii::$app->session->setFlash('error',"ยังมี {$pending} รายการที่ยังไม่ได้บันทึกค่าใช้จ่าย");return $this->redirect(['monthly','period_id'=>$id]);}
        if(!MonthlyAccount::find()->where(['billing_period_id'=>$id])->exists()){Yii::$app->session->setFlash('error','ยังไม่มีรายการประจำเดือน');return $this->redirect(['monthly','period_id'=>$id]);}
        $period->updateAttributes(['status'=>'closed','closed_at'=>date('Y-m-d H:i:s'),'closed_by'=>Yii::$app->user->id?:null,'closed_by_name'=>(string)(Yii::$app->user->identity->username??'ผู้ใช้งานระบบ')]);
        Yii::$app->session->setFlash('success','ปิดค่าใช้จ่ายประจำเดือนแล้ว ข้อมูลถูกล็อกไม่ให้แก้ไข');
        return $this->redirect(['monthly','period_id'=>$id]);
    }

    private function createAccount(BillingPeriod $period,Building $building,?Unit $unit,?Room $room,?Occupancy $occupancy):void
    {
        $key=$occupancy?'O-'.$occupancy->id:($room?'R-'.$room->id:($unit?'U-'.$unit->id:'B-'.$building->id));
        if(MonthlyAccount::find()->where(['billing_period_id'=>$period->id,'subject_key'=>$key])->exists())return;
        $employee=$occupancy?->employee;$over15=$occupancy?1:0;
        if($occupancy){$cutoff=date('Y-m-d',strtotime($period->end_date.' -15 years'));foreach($occupancy->residents as $resident)if($resident->status==='active'&&$resident->count_for_charge&&$resident->birth_date&&$resident->birth_date<=$cutoff)$over15++;}
        $model=new MonthlyAccount(['billing_period_id'=>$period->id,'building_id'=>$building->id,'unit_id'=>$unit?->id,'room_id'=>$room?->id,'occupancy_id'=>$occupancy?->id,'payer_emp_id'=>$occupancy?->payer_emp_id,'subject_key'=>$key,'building_name'=>$building->name,'unit_name'=>$unit?->name,'room_name'=>$room?->name,'electric_account_no'=>$unit?->electric_account_no?:$building->electric_account_no,'payer_name'=>$employee?->fullname(),'position_name'=>$employee?->positionName(),'occupants_over_15'=>$over15,'status'=>MonthlyAccount::STATUS_PENDING,'payment_status'=>MonthlyAccount::PAYMENT_UNPAID]);
        if(!$model->save())throw new \RuntimeException(implode(' ',$model->getFirstErrors()));
    }

    private function defaultAmount(MonthlyAccount $account,ChargeType $type):float
    {
        if($type->calculation_method===ChargeType::METHOD_EQUIPMENT&&$account->unit_id)return (float)AssetAssignment::find()->where(['unit_id'=>$account->unit_id,'room_id'=>$account->room_id,'is_active'=>1])->sum(new Expression('quantity * monthly_rent'));
        $rate=HousingRate::find()->where(['charge_type_id'=>$type->id,'status'=>'active'])->andWhere(['<=','effective_from',$account->period->start_date])->andWhere(['or',['effective_to'=>null],['>=','effective_to',$account->period->start_date]])->orderBy(['unit_id'=>SORT_DESC,'building_id'=>SORT_DESC,'effective_from'=>SORT_DESC])->one();
        // อัตราเฉพาะ (ราย อาคาร/ห้อง) มาก่อน ถ้าไม่มีให้ใช้อัตราตั้งต้นที่ตั้งไว้บนประเภทค่าใช้จ่าย
        $value=(float)($rate?->rate??$type->default_rate??0);
        return $type->calculation_method===ChargeType::METHOD_PER_PERSON?$value*$account->occupants_over_15:$value;
    }

    private function save($model,string $kind)
    {
        if($model->load(Yii::$app->request->post())&&$model->save()){$redirect=$kind==='charge-type'?['charge-types']:($kind==='period'?['monthly','period_id'=>$model->id]:['monthly']);if(Yii::$app->request->isAjax){Yii::$app->response->format=Response::FORMAT_JSON;return ['status'=>'success','redirect'=>Url::to($redirect)];}return $this->redirect($redirect);}
        if(Yii::$app->request->isPost&&Yii::$app->request->isAjax){Yii::$app->response->format=Response::FORMAT_JSON;return ['errors'=>ActiveForm::validate($model)];}
        $params=['model'=>$model,'kind'=>$kind,'chargeTypes'=>ArrayHelper::map(ChargeType::find()->where(['status'=>'active'])->all(),'id','name'),'buildings'=>ArrayHelper::map(Building::find()->all(),'id','name'),'units'=>ArrayHelper::map(Unit::find()->all(),'id','name'),'metersList'=>ArrayHelper::map(Meter::find()->all(),'id','name'),'periods'=>ArrayHelper::map(BillingPeriod::find()->all(),'id','name')];
        if(Yii::$app->request->isAjax){Yii::$app->response->format=Response::FORMAT_JSON;return ['title'=>'บันทึกข้อมูล','content'=>$this->renderAjax('_form',$params)];}
        return $this->render('_form',$params);
    }
    private function find(string $class,int $id){if(($m=$class::findOne($id))===null)throw new NotFoundHttpException('ไม่พบข้อมูล');return $m;}
}
