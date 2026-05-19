<?php

namespace app\modules\hr\controllers;

use app\modules\hr\models\EmployeePosition;
use app\modules\hr\models\EmployeePositionGroup;
use app\modules\hr\models\EmployeeType;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\data\DataProviderInterface;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class EmployeeMasterController extends Controller
{
    private const DEFINITIONS = [
        'type' => [
            'label' => 'ประเภทพนักงาน (ใหม่)',
            'shortLabel' => 'ประเภทพนักงาน',
            'description' => 'ใช้กำหนดกลุ่มหลักของบุคลากร เช่น ข้าราชการ และลูกจ้างชั่วคราว',
            'icon' => 'fa-user-tag',
            'modelClass' => EmployeeType::class,
            'container' => '#employee-master-type-container',
            'modalSize' => 'modal-md',
        ],
        'group' => [
            'label' => 'กลุ่มตำแหน่งพนักงาน (ใหม่)',
            'shortLabel' => 'กลุ่มตำแหน่ง',
            'description' => 'ใช้เป็น master กลางสำหรับจัดกลุ่มตำแหน่ง โดยไม่ผูกกับประเภทพนักงาน',
            'icon' => 'fa-layer-group',
            'modelClass' => EmployeePositionGroup::class,
            'container' => '#employee-master-group-container',
            'modalSize' => 'modal-md',
        ],
        'position' => [
            'label' => 'ตำแหน่งพนักงาน (ใหม่)',
            'shortLabel' => 'ตำแหน่ง',
            'description' => 'ใช้กำหนดชื่อจริงของตำแหน่งสำหรับอ้างอิงในทะเบียนบุคลากร และเลือกกลุ่มตำแหน่งเพื่อใช้สรุปข้อมูลบุคลากร',
            'icon' => 'fa-briefcase',
            'modelClass' => EmployeePosition::class,
            'container' => '#employee-master-position-container',
            'modalSize' => 'modal-lg',
        ],
    ];

    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function beforeAction($action)
    {
        if (!Yii::$app->user->can('hr') && !Yii::$app->user->can('admin')) {
            throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าหน้านี้');
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $filters = $this->filters();
        $masters = [];
        foreach (array_keys(self::DEFINITIONS) as $type) {
            $definition = $this->definition($type);
            $definition['dataProvider'] = $this->buildDataProvider($type, $filters);
            $definition['count'] = (int) $definition['dataProvider']->getTotalCount();
            $masters[$type] = $definition;
        }

        return $this->render('index', [
            'masters' => $masters,
            'activeTab' => $this->request->get('tab', 'type'),
            'filters' => $filters,
        ]);
    }

    public function actionCreate($type)
    {
        $definition = $this->definition($type);
        $model = $this->newModel($type);
        $this->applyDefaultValues($model, $type);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->save()) {
                if ($this->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'success',
                        'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว',
                        'container' => $definition['container'],
                    ];
                }

                return $this->redirect(['index', 'tab' => $type]);
            }

            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $model->hasErrors()
                    ? ActiveForm::validate($model)
                    : [
                        'status' => 'error',
                        'message' => 'ไม่สามารถบันทึกข้อมูลได้',
                    ];
            }
        }

        return $this->renderFormResponse('create', $type, $model);
    }

    public function actionUpdate($type, $id)
    {
        $definition = $this->definition($type);
        $model = $this->findModel($type, $id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->save()) {
                if ($this->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'success',
                        'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว',
                        'container' => $definition['container'],
                    ];
                }

                return $this->redirect(['index', 'tab' => $type]);
            }

            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return $model->hasErrors()
                    ? ActiveForm::validate($model)
                    : [
                        'status' => 'error',
                        'message' => 'ไม่สามารถบันทึกข้อมูลได้',
                    ];
            }
        }

        return $this->renderFormResponse('update', $type, $model);
    }

    public function actionDelete($type, $id)
    {
        $definition = $this->definition($type);
        $model = $this->findModel($type, $id);

        try {
            $result = $model->delete();
            if ($result === false) {
                throw new \RuntimeException('delete returned false');
            }
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'error',
                'msg' => 'ไม่สามารถลบรายการนี้ได้ เนื่องจากมีข้อมูลอ้างอิงอยู่',
            ];
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'container' => $definition['container'],
                'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
            ];
        }

        return $this->redirect(['index', 'tab' => $type]);
    }

    public function actionGroupOptions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $options = EmployeePositionGroup::listItems();

        $html = Html::tag('option', 'เลือกกลุ่มตำแหน่ง...', ['value' => '']);
        $html .= Html::renderSelectOptions(null, $options);

        return [
            'status' => 'success',
            'options_html' => $html,
        ];
    }

    private function buildDataProvider(string $type, array $filters = []): DataProviderInterface
    {
        $q = trim((string) ($filters['q'] ?? ''));
        $active = $filters['active'] ?? '';
        $pageParam = sprintf('employee-master-%s-page', $type);
        $paginationParams = [
            'tab' => $filters['tab'] ?? $type,
        ];
        $activeColumn = null;

        if ($q !== '') {
            $paginationParams['q'] = $q;
        }

        if ($active !== '' && $active !== null) {
            $paginationParams['active'] = $active;
        }

        switch ($type) {
            case 'type':
                $query = EmployeeType::find()
                    ->with(['employeePositions'])
                    ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
                $activeColumn = EmployeeType::tableName() . '.active';

                if ($q !== '') {
                    $query->andFilterWhere(['like', EmployeeType::tableName() . '.title', $q]);
                }
                break;

            case 'group':
                $query = EmployeePositionGroup::find()
                    ->with(['employeePositions'])
                    ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
                $activeColumn = EmployeePositionGroup::tableName() . '.active';

                if ($q !== '') {
                    $query->andFilterWhere(['like', EmployeePositionGroup::tableName() . '.title', $q]);
                }
                break;

            case 'position':
                $query = EmployeePosition::find()
                    ->with(['employeePositionGroup'])
                    ->joinWith(['employeePositionGroup pg'])
                    ->orderBy(['pg.sort' => SORT_ASC, 'sort' => SORT_ASC, 'id' => SORT_ASC]);
                $activeColumn = EmployeePosition::tableName() . '.active';

                if ($q !== '') {
                    $query->andWhere([
                        'or',
                        ['like', EmployeePosition::tableName() . '.title', $q],
                        ['like', EmployeePosition::tableName() . '.legacy_code', $q],
                        ['like', 'pg.title', $q],
                    ]);
                }

                $models = $query->all();
                $models = $this->uniqueEmployeePositionModels($models);

                return new ArrayDataProvider([
                    'allModels' => $models,
                    'pagination' => [
                        'pageSize' => 10,
                        'pageParam' => $pageParam,
                        'params' => $paginationParams,
                    ],
                    'sort' => [
                        'params' => $paginationParams,
                        'attributes' => [
                            'employee_position_group_id' => [
                                'asc' => ['employee_position_group_id' => SORT_ASC, 'id' => SORT_ASC],
                                'desc' => ['employee_position_group_id' => SORT_DESC, 'id' => SORT_DESC],
                            ],
                            'title' => [
                                'asc' => ['title' => SORT_ASC, 'id' => SORT_ASC],
                                'desc' => ['title' => SORT_DESC, 'id' => SORT_DESC],
                            ],
                            'sort' => [
                                'asc' => ['sort' => SORT_ASC, 'id' => SORT_ASC],
                                'desc' => ['sort' => SORT_DESC, 'id' => SORT_DESC],
                            ],
                            'active' => [
                                'asc' => ['active' => SORT_ASC, 'id' => SORT_ASC],
                                'desc' => ['active' => SORT_DESC, 'id' => SORT_DESC],
                            ],
                            'id' => [
                                'asc' => ['id' => SORT_ASC],
                                'desc' => ['id' => SORT_DESC],
                            ],
                        ],
                        'defaultOrder' => [
                            'sort' => SORT_ASC,
                            'id' => SORT_ASC,
                        ],
                    ],
                ]);

            default:
                throw new NotFoundHttpException('ไม่พบข้อมูลที่ร้องขอ');
        }

        if ($active !== '' && $active !== null) {
            $query->andWhere([$activeColumn ?? 'active' => (int) $active]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
                'pageParam' => $pageParam,
                'params' => $paginationParams,
            ],
            'sort' => [
                'params' => $paginationParams,
                'defaultOrder' => [
                    'sort' => SORT_ASC,
                    'id' => SORT_ASC,
                ],
            ],
        ]);
    }

    /**
     * @param array<int, EmployeePosition> $models
     * @return array<int, EmployeePosition>
     */
    private function uniqueEmployeePositionModels(array $models): array
    {
        if (count($models) < 2) {
            return $models;
        }

        $usageCounts = $this->loadEmployeePositionUsageCounts();
        $grouped = [];
        foreach ($models as $model) {
            if (!$model instanceof EmployeePosition) {
                continue;
            }

            $key = $this->normalizeEmployeePositionTitleKey($model->title ?? '');
            if ($key === '') {
                continue;
            }

            $grouped[$key][] = $model;
        }

        $uniqueModels = [];
        foreach ($grouped as $items) {
            usort($items, function (EmployeePosition $left, EmployeePosition $right) use ($usageCounts): int {
                return $this->compareEmployeePositionModels($left, $right, $usageCounts);
            });

            $canonical = $items[0] ?? null;
            if ($canonical instanceof EmployeePosition) {
                $uniqueModels[] = $canonical;
            }
        }

        return $uniqueModels;
    }

    /**
     * @param array<int, int> $usageCounts
     */
    private function compareEmployeePositionModels(EmployeePosition $left, EmployeePosition $right, array $usageCounts): int
    {
        $scoreDiff = $this->employeePositionScore($right, $usageCounts) <=> $this->employeePositionScore($left, $usageCounts);
        if ($scoreDiff !== 0) {
            return $scoreDiff;
        }

        $leftGroupSort = (int) ($left->employeePositionGroup->sort ?? PHP_INT_MAX);
        $rightGroupSort = (int) ($right->employeePositionGroup->sort ?? PHP_INT_MAX);
        $groupSortDiff = $leftGroupSort <=> $rightGroupSort;
        if ($groupSortDiff !== 0) {
            return $groupSortDiff;
        }

        $sortDiff = (int) ($left->sort ?? 0) <=> (int) ($right->sort ?? 0);
        if ($sortDiff !== 0) {
            return $sortDiff;
        }

        return (int) ($left->id ?? 0) <=> (int) ($right->id ?? 0);
    }

    /**
     * @param array<int, int> $usageCounts
     */
    private function employeePositionScore(EmployeePosition $model, array $usageCounts): int
    {
        $score = 0;
        $usageCount = (int) ($usageCounts[(string) ($model->id ?? 0)] ?? 0);

        if ($usageCount > 0) {
            $score += $usageCount * 1000;
        }

        if ((int) ($model->active ?? 0) === 1) {
            $score += 8;
        }

        if ($this->normalizeLegacyCode($model->legacy_code ?? null) !== null) {
            $score += 4;
        }

        if ((int) ($model->employee_position_group_id ?? 0) > 0) {
            $score += 2;
        }

        if ((int) ($model->employee_type_id ?? 0) > 0) {
            $score += 1;
        }

        return $score;
    }

    /**
     * @return array<int, int>
     */
    private function loadEmployeePositionUsageCounts(): array
    {
        if (Yii::$app->db->getTableSchema('{{%employees}}', true) === null) {
            return [];
        }

        $rows = EmployeePosition::find()
            ->alias('p')
            ->select([
                'p.id',
                'usage_count' => 'COUNT(e.id)',
            ])
            ->leftJoin('{{%employees}} e', 'e.employee_position_id = p.id')
            ->groupBy(['p.id'])
            ->asArray()
            ->all();

        $counts = [];
        foreach ($rows as $row) {
            $positionId = (int) ($row['id'] ?? 0);
            if ($positionId <= 0) {
                continue;
            }

            $counts[(string) $positionId] = (int) ($row['usage_count'] ?? 0);
        }

        return $counts;
    }

    private function normalizeLegacyCode($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '' || $value === '-') {
            return null;
        }

        return $value;
    }

    private function normalizeEmployeePositionTitleKey($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/\s+/u', ' ', $value);
        if ($value === null) {
            return '';
        }

        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    private function definition(string $type): array
    {
        if (!isset(self::DEFINITIONS[$type])) {
            throw new NotFoundHttpException('ไม่พบข้อมูลที่ร้องขอ');
        }

        return array_merge(['type' => $type], self::DEFINITIONS[$type]);
    }

    private function newModel(string $type)
    {
        $definition = $this->definition($type);
        $class = $definition['modelClass'];

        return new $class();
    }

    private function findModel(string $type, $id)
    {
        $definition = $this->definition($type);
        $class = $definition['modelClass'];

        if (($model = $class::findOne((int) $id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('ไม่พบข้อมูลที่ร้องขอ');
    }

    private function applyDefaultValues($model, string $type): void
    {
        if (!$model->isNewRecord) {
            return;
        }

        if ($model->hasAttribute('active') && (int) $model->active === 0) {
            $model->active = 1;
        }

        if ($model->hasAttribute('sort') && ((int) $model->sort === 0 || $model->sort === null)) {
            $model->sort = $this->nextSort($type);
        }

        $employeeTypeId = (int) Yii::$app->request->get('employee_type_id', 0);
        if ($employeeTypeId > 0 && $model->hasAttribute('employee_type_id') && empty($model->employee_type_id)) {
            $model->employee_type_id = $employeeTypeId;
        }

        $employeePositionGroupId = (int) Yii::$app->request->get('employee_position_group_id', 0);
        if ($employeePositionGroupId > 0 && $model->hasAttribute('employee_position_group_id') && empty($model->employee_position_group_id)) {
            $model->employee_position_group_id = $employeePositionGroupId;
        }
    }

    private function nextSort(string $type): int
    {
        $definition = $this->definition($type);
        $class = $definition['modelClass'];
        $max = (int) $class::find()->max('sort');

        return $max + 1;
    }

    private function renderFormResponse(string $mode, string $type, $model)
    {
        $definition = $this->definition($type);
        $params = [
            'model' => $model,
            'type' => $type,
            'mode' => $mode,
            'config' => $definition,
        ];

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $mode === 'create'
                    ? '<i class="fa-solid fa-circle-plus me-1"></i> สร้าง' . $definition['shortLabel']
                    : '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข' . $definition['shortLabel'],
                'content' => $this->renderAjax('_form', $params),
            ];
        }

        return $this->render('_form', $params);
    }

    private function filters(): array
    {
        return [
            'q' => trim((string) $this->request->get('q', '')),
            'active' => $this->request->get('active', ''),
            'tab' => $this->request->get('tab', 'type'),
        ];
    }
}
