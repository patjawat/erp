<?php

namespace app\modules\finance\controllers;

use app\modules\finance\models\FinanceManualCategory;
use app\modules\finance\models\FinanceManualItem;
use app\modules\finance\models\FinanceManualTopic;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * คู่มือการเงิน (survival guide) — ป็อปอัปเมนู + รายละเอียดเอกสารที่ต้องเตรียม
 *  - อ่าน: เจ้าหน้าที่ทุกคนที่ล็อกอิน
 *  - แก้ไข (เพิ่ม/ลบ/แก้คำ): เฉพาะ financeAdmin
 */
class ManualController extends Controller
{
    public $enableCsrfValidation = true;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['data'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => [
                            'category-save', 'category-delete',
                            'topic-save', 'topic-delete',
                            'item-save', 'item-delete', 'item-move',
                        ],
                        'roles' => ['financeAdmin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'category-save' => ['post'],
                    'category-delete' => ['post'],
                    'topic-save' => ['post'],
                    'topic-delete' => ['post'],
                    'item-save' => ['post'],
                    'item-delete' => ['post'],
                    'item-move' => ['post'],
                ],
            ],
        ]);
    }

    private function canEdit(): bool
    {
        return Yii::$app->user->can('financeAdmin');
    }

    /** โครงคู่มือทั้งหมด (JSON) สำหรับ render ในป็อปอัป */
    public function actionData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $categories = FinanceManualCategory::find()
            ->where(['is_active' => true])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->with(['topics' => function ($q) {
                $q->andWhere(['is_active' => true])->with('items');
            }])
            ->all();

        $out = [];
        foreach ($categories as $cat) {
            $topics = [];
            foreach ($cat->topics as $topic) {
                $items = [];
                foreach ($topic->items as $item) {
                    $items[] = [
                        'id' => (int) $item->id,
                        'content' => $item->content,
                        'kind' => $item->kind,
                    ];
                }
                $topics[] = [
                    'id' => (int) $topic->id,
                    'title' => $topic->title,
                    'intro' => $topic->intro,
                    'note' => $topic->note,
                    'items' => $items,
                ];
            }
            $out[] = [
                'id' => (int) $cat->id,
                'code' => $cat->code,
                'title' => $cat->title,
                'icon' => $cat->icon,
                'description' => $cat->description,
                'topics' => $topics,
            ];
        }

        return [
            'ok' => true,
            'canEdit' => $this->canEdit(),
            'kinds' => FinanceManualItem::kinds(),
            'categories' => $out,
        ];
    }

    // ---- แก้ไข: หมวด --------------------------------------------------------

    public function actionCategorySave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = (int) Yii::$app->request->post('id');
        $model = $id ? $this->findCategory($id) : new FinanceManualCategory();

        $model->title = trim((string) Yii::$app->request->post('title'));
        $icon = trim((string) Yii::$app->request->post('icon'));
        $model->icon = $icon !== '' ? $icon : 'bi-folder2-open';
        $model->description = trim((string) Yii::$app->request->post('description')) ?: null;
        if ($model->isNewRecord) {
            $model->sort_order = (int) FinanceManualCategory::find()->max('sort_order') + 10;
            $model->is_active = true;
        }

        if (!$model->save()) {
            return ['ok' => false, 'errors' => $model->getErrorSummary(true)];
        }
        return ['ok' => true, 'id' => (int) $model->id];
    }

    public function actionCategoryDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findCategory((int) Yii::$app->request->post('id'));
        $model->delete();
        return ['ok' => true];
    }

    // ---- แก้ไข: เรื่อง -------------------------------------------------------

    public function actionTopicSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = (int) Yii::$app->request->post('id');
        $model = $id ? $this->findTopic($id) : new FinanceManualTopic();

        if ($model->isNewRecord) {
            $model->category_id = (int) Yii::$app->request->post('category_id');
            $model->sort_order = (int) FinanceManualTopic::find()
                ->where(['category_id' => $model->category_id])->max('sort_order') + 10;
            $model->is_active = true;
        }
        $model->title = trim((string) Yii::$app->request->post('title'));
        $model->intro = trim((string) Yii::$app->request->post('intro')) ?: null;
        $model->note = trim((string) Yii::$app->request->post('note')) ?: null;

        if (!$model->save()) {
            return ['ok' => false, 'errors' => $model->getErrorSummary(true)];
        }
        return ['ok' => true, 'id' => (int) $model->id];
    }

    public function actionTopicDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findTopic((int) Yii::$app->request->post('id'));
        $model->delete();
        return ['ok' => true];
    }

    // ---- แก้ไข: บรรทัดเอกสาร -------------------------------------------------

    public function actionItemSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = (int) Yii::$app->request->post('id');
        $model = $id ? $this->findItem($id) : new FinanceManualItem();

        if ($model->isNewRecord) {
            $model->topic_id = (int) Yii::$app->request->post('topic_id');
            $model->sort_order = (int) FinanceManualItem::find()
                ->where(['topic_id' => $model->topic_id])->max('sort_order') + 10;
        }
        $model->content = trim((string) Yii::$app->request->post('content'));
        $kind = (string) Yii::$app->request->post('kind');
        if (array_key_exists($kind, FinanceManualItem::kinds())) {
            $model->kind = $kind;
        }

        if (!$model->save()) {
            return ['ok' => false, 'errors' => $model->getErrorSummary(true)];
        }
        return ['ok' => true, 'id' => (int) $model->id];
    }

    public function actionItemDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findItem((int) Yii::$app->request->post('id'));
        $model->delete();
        return ['ok' => true];
    }

    /** สลับลำดับบรรทัดกับเพื่อนบ้าน (dir = up|down) ภายในเรื่องเดียวกัน */
    public function actionItemMove()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findItem((int) Yii::$app->request->post('id'));
        $dir = Yii::$app->request->post('dir') === 'up' ? 'up' : 'down';

        $query = FinanceManualItem::find()->where(['topic_id' => $model->topic_id]);
        if ($dir === 'up') {
            $neighbor = (clone $query)
                ->andWhere(['<', 'sort_order', $model->sort_order])
                ->orderBy(['sort_order' => SORT_DESC])->one();
        } else {
            $neighbor = (clone $query)
                ->andWhere(['>', 'sort_order', $model->sort_order])
                ->orderBy(['sort_order' => SORT_ASC])->one();
        }
        if ($neighbor) {
            [$model->sort_order, $neighbor->sort_order] = [$neighbor->sort_order, $model->sort_order];
            $model->save(false);
            $neighbor->save(false);
        }
        return ['ok' => true];
    }

    // ---- helpers ------------------------------------------------------------

    private function findCategory(int $id): FinanceManualCategory
    {
        if (($m = FinanceManualCategory::findOne($id)) !== null) {
            return $m;
        }
        throw new NotFoundHttpException('ไม่พบหมวดคู่มือ');
    }

    private function findTopic(int $id): FinanceManualTopic
    {
        if (($m = FinanceManualTopic::findOne($id)) !== null) {
            return $m;
        }
        throw new NotFoundHttpException('ไม่พบเรื่องในคู่มือ');
    }

    private function findItem(int $id): FinanceManualItem
    {
        if (($m = FinanceManualItem::findOne($id)) !== null) {
            return $m;
        }
        throw new NotFoundHttpException('ไม่พบรายการเอกสาร');
    }
}
