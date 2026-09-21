<?php
namespace app\modules\accounting\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\modules\accounting\models\AccountingJournalDraft;
use app\modules\accounting\models\AccountingJournalLine;

class LedgerController extends Controller
{
    public function behaviors(){return array_merge(parent::behaviors(),['access'=>['class'=>AccessControl::class,'rules'=>[['allow'=>true,'roles'=>['accountingView']]]]]);}
    public function actionIndex($year=null,$account=null)
    {
        $year=(int)($year?:543+(int)date('Y')+(date('n')>=10?1:0));
        $query=AccountingJournalLine::find()->alias('l')->joinWith(['journal j'])
            ->andWhere(['j.status'=>AccountingJournalDraft::STATUS_POSTED,'j.fiscal_year'=>$year]);
        if($account!=='')$query->andWhere(['like','l.account_code_snapshot',trim((string)$account)]);
        $total=(clone $query)->select(['debit'=>'SUM(l.debit_amount)','credit'=>'SUM(l.credit_amount)'])->asArray()->one();
        return $this->render('index',['year'=>$year,'account'=>$account,'total'=>$total,'dataProvider'=>new ActiveDataProvider(['query'=>$query->orderBy(['j.document_date'=>SORT_ASC,'j.id'=>SORT_ASC,'l.sequence'=>SORT_ASC]),'pagination'=>['pageSize'=>50]])]);
    }
}
