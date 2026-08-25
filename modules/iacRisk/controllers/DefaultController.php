<?php

namespace app\modules\iacRisk\controllers;

use app\modules\iacRisk\models\Activity;
use app\modules\iacRisk\services\AccessService;
use app\modules\iacRisk\services\ContextService;
use app\components\AppHelper;
use app\modules\serviceProfile\models\ServiceProfile;
use app\modules\serviceProfile\services\OwnerDirectoryService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class DefaultController extends Controller
{
    private AccessService $access;

    public function init(): void
    {
        parent::init();
        $this->access = new AccessService();
    }

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
        ]);
    }

    public function beforeAction($action): bool
    {
        if (!$this->access->canEnter()) throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ใช้งาน IAC&Risk');
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $context = (new ContextService())->resolve();
        $activities = [];
        if ($context['hospitalId']) {
            $query = Activity::find()->where(['hospital_id' => $context['hospitalId']]);
            if ($context['fiscalYearId']) $query->andWhere(['fiscal_year_id' => $context['fiscalYearId']]);
            if ($context['orgUnitId']) $query->andWhere(['or', ['org_unit_id' => $context['orgUnitId']], ['org_unit_id' => null]]);
            $activities = $query->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])->limit(8)->all();
        }
        return $this->render('index', ['context' => $context, 'activities' => $activities, 'access' => $this->access]);
    }

    public function actionProcesses() { return $this->placeholder('กระบวนงาน', 'processes'); }
    public function actionServiceProfile()
    {
        $context = (new ContextService())->resolve();
        $fiscalYear = (int) ($context['fiscalYear']?->fiscal_year ?: AppHelper::YearBudget());
        $orgUnitId = (int) $context['orgUnitId'];
        $directory = new OwnerDirectoryService();

        if (!$orgUnitId && !$this->access->canScopeAllUnits()) {
            $employee = $this->access->employee();
            $unit = $directory->orgUnitForDepartment($employee?->department ? (int) $employee->department : null, $fiscalYear);
            $orgUnitId = (int) ($unit?->id ?: 0);
        }

        $query = ServiceProfile::find()->with(['authors.employee', 'approvals.employee'])
            ->where(['fiscal_year' => $fiscalYear]);
        if ($orgUnitId) {
            try {
                $owner = $directory->resolveOwner($orgUnitId, $fiscalYear);
                $query->andWhere(['owner_type' => $owner['owner_type'], 'owner_id' => $owner['owner_id']]);
            } catch (\DomainException $e) {
                $query->andWhere('0=1');
            }
        } elseif (!$this->access->canScopeAllUnits()) {
            $query->andWhere('0=1');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['owner_name_snapshot' => SORT_ASC, 'revision_no' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('service-profile', [
            'context' => $context,
            'dataProvider' => $dataProvider,
            'fiscalYear' => $fiscalYear,
        ]);
    }
    public function actionCsa() { return $this->placeholder('CSA', 'csa'); }
    public function actionRisks() { return $this->placeholder('บัญชีความเสี่ยง', 'risks'); }
    public function actionPk4() { return $this->placeholder('ปค.4', 'pk4'); }
    public function actionPk5() { return $this->placeholder('ปค.5', 'pk5'); }
    public function actionTracking() { return $this->placeholder('ติดตามผล', 'tracking'); }
    public function actionHistory() { return $this->placeholder('ประวัติ', 'history'); }

    private function placeholder(string $title, string $active)
    {
        return $this->render('placeholder', ['context' => (new ContextService())->resolve(), 'title' => $title, 'active' => $active]);
    }
}
