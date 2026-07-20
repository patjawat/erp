<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\modules\am\models\Asset;
use app\modules\am\models\DepreciationProfile;
use app\modules\am\models\AssetDepreciationChange;
use app\modules\am\services\AssetDepreciationChangeService;

/**
 * เปลี่ยนเกณฑ์ค่าเสื่อมของทรัพย์สิน + ประวัติการเปลี่ยนแปลง (screens 7, 9)
 */
class AssetDepreciationChangeController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['change' => ['POST']],
            ],
        ]);
    }

    /**
     * ฟอร์มเปลี่ยนเกณฑ์ (screen 7)
     */
    public function actionForm($asset_id = null)
    {
        $asset = $asset_id ? Asset::findOne($asset_id) : null;
        $profiles = DepreciationProfile::find()
            ->where(['status' => DepreciationProfile::STATUS_ACTIVE])
            ->orderBy(['code' => SORT_ASC])
            ->all();

        return $this->render('form', [
            'asset' => $asset,
            'assetId' => $asset_id,
            'profiles' => $profiles,
            'scopeOptions' => AssetDepreciationChange::scopeOptions(),
        ]);
    }

    public function actionChange()
    {
        $req = Yii::$app->request;
        $asset = Asset::findOne((int) $req->post('asset_id'));
        $profile = DepreciationProfile::findOne((int) $req->post('new_depreciation_profile_id'));

        if (!$asset || !$profile) {
            Yii::$app->session->setFlash('error', 'ไม่พบทรัพย์สินหรือเกณฑ์');
            return $this->redirect(['form', 'asset_id' => $req->post('asset_id')]);
        }

        $res = (new AssetDepreciationChangeService())->changeProfile(
            $asset,
            $profile,
            $req->post('effective_date') ?: date('Y-m-d'),
            $req->post('change_scope'),
            $req->post('reason'),
            $req->post('document_reference'),
            null,
            Yii::$app->user->id
        );
        Yii::$app->session->setFlash($res['success'] ? 'success' : 'error', $res['message']);
        return $this->redirect(['form', 'asset_id' => $asset->id]);
    }

    /**
     * ประวัติการเปลี่ยนเกณฑ์ (screen 9)
     */
    public function actionHistory($asset_id = null)
    {
        $query = AssetDepreciationChange::find()->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
        if ($asset_id) {
            $query->andWhere(['asset_id' => $asset_id]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
        ]);

        return $this->render('history', [
            'dataProvider' => $dataProvider,
            'assetId' => $asset_id,
        ]);
    }
}
