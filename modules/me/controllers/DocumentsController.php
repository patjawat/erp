<?php

namespace app\modules\me\controllers;

use app\components\AppHelper;
use app\components\DateFilterHelper;
use app\components\DocumentAccessPolicy;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\models\Categorise;
use app\models\Uploads;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentsDetail;
use app\modules\dms\models\DocumentsDetailSearch;
use app\modules\dms\models\DocumentSearch;
use app\modules\filemanager\components\FileManagerHelper;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DocumentsController extends \yii\web\Controller
{
    /**
     * ชุดชื่อ `documents_detail` ที่ควรเช็กเวลาไล่หาหนังสือของผู้ใช้
     * ถ้ามี name ใหม่เพิ่มเข้ามา ให้แก้ list ตรงนี้ก่อนเสมอ
     */
    private const INDEX_EMPLOYEE_DETAIL_NAMES = [
        'comment_emp',
        'tags',
        'employee_tag',
        'employee',
        'req_approve',
    ];

    private const INDEX_DEPARTMENT_DETAIL_NAMES = [
        'comment_dept',
        'department',
    ];

    private const HOME_DETAIL_NAMES = [
        'comment_emp',
        'comment_dept',
        'department',
        'tags',
        'employee_tag',
        'employee',
        'req_approve',
    ];

    /**
     * แปลงชื่อ detail เป็น SQL `IN (...)` แบบปลอดภัย
     */
    private function quotedDetailNames(array $names): string
    {
        return implode(', ', array_map(static fn ($name) => Yii::$app->db->quoteValue($name), $names));
    }

    /**
     * นับเอกสารแบบไม่ซ้ำ แม้ query จะ join หลายแถวต่อ 1 เอกสาร
     */
    private function countDistinctDocuments(\yii\db\ActiveQuery $query): int
    {
        $countQuery = clone $query;
        $countQuery->groupBy(null);
        $countQuery->orderBy([]);
        $countQuery->limit(null);
        $countQuery->offset(null);

        return (int) $countQuery->count('DISTINCT [[documents]].[[id]]');
    }

    /**
     * ฐาน query ของหน้า index สำหรับใช้ทั้งรายการและ KPI
     * หมายเหตุ: ไม่ใส่เงื่อนไขค้นหาเข้ามาในตัว helper นี้
     */
    private function applyDocumentsIndexBaseQuery(
        \yii\db\ActiveQuery $query,
        int $empId,
        int $depId,
        ?array $employeeDetailNames = null,
        ?array $departmentDetailNames = null
    ): \yii\db\ActiveQuery
    {
        $employeeDetailNames = $employeeDetailNames ?? self::INDEX_EMPLOYEE_DETAIL_NAMES;
        $departmentDetailNames = $departmentDetailNames ?? self::INDEX_DEPARTMENT_DETAIL_NAMES;

        $employeeDetailNames = $this->quotedDetailNames($employeeDetailNames);
        $departmentDetailNames = $this->quotedDetailNames($departmentDetailNames);

        // ห้ามใช้ 0 เป็น sentinel เพราะข้อมูล legacy อาจมี documents_detail.to_id = 0
        // และจะทำให้ผู้ใช้ที่ยังไม่ได้ผูกพนักงาน/หน่วยงานเห็นหนังสือผิดสิทธิ
        $employeeRouteId = $empId > 0 ? $empId : -1;

        $query->leftJoin(
            ['te' => 'documents_detail'],
            "te.document_id = documents.id AND te.name IN ({$employeeDetailNames}) AND te.to_id = :empId",
            [
                ':empId' => (string) $employeeRouteId,
            ]
        );

        $departmentRouteId = DocumentAccessPolicy::canUseDepartmentRoute($depId, $empId) ? $depId : -1;

        $query->leftJoin(
            ['td' => 'documents_detail'],
            "td.document_id = documents.id AND td.name IN ({$departmentDetailNames}) AND td.to_id = :depId",
            [
                ':depId' => (string) $departmentRouteId,
            ]
        );

        $query->leftJoin(
            ['tr' => 'documents_detail'],
            'tr.document_id = documents.id AND tr.name = :readName AND tr.to_id = :empId',
            [
                ':readName' => 'read',
                ':empId' => (string) $employeeRouteId,
            ]
        );

        $query->addSelect([
            'documents.*',
            new \yii\db\Expression('COALESCE(MIN(te.id), MIN(td.id)) AS detail_id'),
            new \yii\db\Expression('MIN(tr.id) AS read_id'),
            new \yii\db\Expression('MIN(tr.doc_read) AS doc_read'),
        ]);

        $query->groupBy(['documents.id']);
        $query->andWhere([
            'or',
            ['not', ['te.id' => null]],
            ['not', ['td.id' => null]],
        ]);

        return $query;
    }

    /**
     * บันทึกสถานะอ่านของ detail หนึ่งรายการให้ผู้ใช้คนปัจจุบัน
     */
    private function markDetailAsRead(DocumentsDetail $detail, int $empId): bool
    {
        if ($empId <= 0 || !$detail || !$detail->id) {
            return false;
        }

        $checkReading = DocumentsDetail::find()
            ->where([
                'document_id' => $detail->document_id,
                'name' => 'read',
                'to_id' => $empId,
                'from_id' => $detail->id,
            ])
            ->one();

        if (!$checkReading) {
            $reading = new DocumentsDetail();
            $reading->document_id = $detail->document_id;
            $reading->name = 'read';
            $reading->to_id = $empId;
            $reading->from_id = $detail->id;
            $reading->doc_read = date('Y-m-d H:i:s');
            $reading->save(false);
            return true;
        }

        if ($checkReading->doc_read === null) {
            $checkReading->doc_read = date('Y-m-d H:i:s');
            $checkReading->save(false);
        }

        return true;
    }

    /**
     * documents_detail.id ที่ยังไม่ได้อ่านของ "คนนี้" — SQL เดียวกับ actionShowHome / actionShowHomeV2
     */
    private function unreadDetailIdsShowHomeExact($department, $empId): array
    {
        $detailNames = $this->quotedDetailNames(self::HOME_DETAIL_NAMES);
        $sql = "SELECT d.id
                FROM documents_detail d
                LEFT JOIN documents_detail r ON r.from_id = d.id AND r.name = 'read' AND r.to_id = :emp_id
                WHERE d.name IN ({$detailNames}) AND d.to_id = :department AND r.doc_read IS NULL

                UNION

                SELECT d.id
                FROM documents_detail d
                LEFT JOIN documents_detail r ON r.from_id = d.id AND r.name = 'read' AND r.to_id = :emp_id
                WHERE d.name IN ({$detailNames}) AND d.to_id = :emp_id AND r.doc_read IS NULL";

        return Yii::$app->db->createCommand($sql, [
            ':department' => $department,
            ':emp_id' => $empId,
        ])->queryColumn();
    }

    /** เงื่อนไขแถว tags ถึงพนักงาน (รองรับ to_id หลายรูปแบบในฐานข้อมูล) */
    private function tagsToEmployeeCondition(int $empId): array
    {
        return [
            'or',
            ['d_tags.to_id' => (string) $empId],
            ['d_tags.to_id' => $empId],
            new \yii\db\Expression(
                'CAST([[d_tags]].[[to_id]] AS UNSIGNED) = :tagEmpCast',
                [':tagEmpCast' => (int) $empId]
            ),
        ];
    }

    /** แถว read ของพนักงานคนปัจจุบัน — สอดคล้อง actionBookmark (name=read, to_id=พนักงาน, bookmark=Y) */
    private function readRowToEmployeeCondition(int $empId): array
    {
        return [
            'or',
            ['d_read.to_id' => (string) $empId],
            ['d_read.to_id' => $empId],
            new \yii\db\Expression(
                'CAST([[d_read]].[[to_id]] AS UNSIGNED) = :readEmpCast',
                [':readEmpCast' => (int) $empId]
            ),
        ];
    }

    /**
     * สร้าง search model พร้อม hydrate ช่วงวันที่จาก query เพื่อให้หน้า render ใช้ state เดียวกับการค้นหา
     */
    private function createDocumentsSearchModel(): DocumentSearch
    {
        $searchModel = new DocumentSearch([
            'date_filter' => 'today',
        ]);
        $searchModel->load($this->request->queryParams);
        $this->hydrateDocumentSearchDates($searchModel);

        return $searchModel;
    }

    /**
     * เติมค่า date_start/date_end จาก date_filter เมื่อยังไม่มีค่า และคืนช่วงวันที่แบบ Gregorian สำหรับ query
     *
     * @return array{0:?string,1:?string}
     */
    private function hydrateDocumentSearchDates(DocumentSearch $searchModel): array
    {
        $dateStart = $searchModel->date_start ? AppHelper::convertToGregorian($searchModel->date_start) : null;
        $dateEnd = $searchModel->date_end ? AppHelper::convertToGregorian($searchModel->date_end) : null;

        if (($dateStart === null || $dateEnd === null) && is_string($searchModel->date_filter) && $searchModel->date_filter !== '') {
            $range = DateFilterHelper::getRange($searchModel->date_filter);
            if ($range !== null) {
                if ($dateStart === null) {
                    $dateStart = date('Y-m-d', strtotime($range[0]));
                    $searchModel->date_start = AppHelper::convertToThai($dateStart);
                }
                if ($dateEnd === null) {
                    $dateEnd = date('Y-m-d', strtotime($range[1]));
                    $searchModel->date_end = AppHelper::convertToThai($dateEnd);
                }
            }
        }

        return [$dateStart, $dateEnd];
    }

    /**
     * ข้อมูลหน้า index ทะเบียนหนังสือ
     *
     * @return array{
     *   searchModel: DocumentSearch,
     *   dataProvider: \yii\data\ActiveDataProvider,
     *   unreadOpenDetailIdByDocument: array,
     *   unreadOpenDocumentsDetailById: array,
     *   readAtByRoutingId: array,
     *   emp: object
     * }
     */
    

    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();

        $empId = $me->id ?? null;
        $depId = $me->department ?? null;

        $searchModel = new DocumentSearch([
            'date_filter' => 'today',
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);

        $hasSearchCriteria =
            trim((string) ($searchModel->q ?? '')) !== ''
            || !empty($searchModel->q_status)
            || !empty($searchModel->show_reading)
            || !empty($searchModel->doc_speed);

        // ค้นหาด้วยคำ/สถานะไม่ควรถูกล็อกด้วยค่าเริ่มต้น today
        if ($hasSearchCriteria) {
            $searchModel->date_filter = null;
        }

        $q = trim($searchModel->q ?? '');

        [$dateStart, $dateEnd] = $this->hydrateDocumentSearchDates($searchModel);

        $query = $dataProvider->query;
        $this->applyDocumentsIndexBaseQuery(
            $query,
            (int) $empId,
            (int) $depId
        );
        $summaryBaseQuery = $this->applyDocumentsIndexBaseQuery(
            Documents::find(),
            (int) $empId,
            (int) $depId
        );

        $query
            ->andFilterWhere(['>=', 'documents.doc_transactions_date', $dateStart])
            ->andFilterWhere(['<=', 'documents.doc_transactions_date', $dateEnd]);

        $query->andFilterWhere([
            'or',
            ['like', 'topic', $q],
            ['like', 'doc_regis_number', $q],
            ['like', 'doc_number', $q],
            ['like', new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(documents.data_json, '$.des'))"), $q],
        ]);

        $documentSummaryCounts = [
            'total' => $this->countDistinctDocuments(clone $summaryBaseQuery),
            'read' => $this->countDistinctDocuments((clone $summaryBaseQuery)->andWhere(['IS NOT', 'tr.id', null])),
            'unread' => $this->countDistinctDocuments((clone $summaryBaseQuery)->andWhere(['tr.id' => null])),
            'saved' => $this->countDistinctDocuments((clone $summaryBaseQuery)->andWhere(['tr.bookmark' => 'Y'])),
        ];

        if ($searchModel->q_status === 'Y') {
            $query->andWhere(['tr.bookmark' => 'Y']);
        } elseif ($searchModel->q_status === 'read') {
            $query->andWhere(['IS NOT', 'tr.id', null]);
        } elseif ($searchModel->q_status === 'unread') {
            $query->andWhere(['tr.id' => null]);
        } else {
            $query->andFilterWhere(['status' => $searchModel->q_status]);
        }
        $query->orderBy([
            'doc_transactions_date' => SORT_DESC,
            'doc_regis_number' => SORT_DESC,
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'documentSummaryCounts' => $documentSummaryCounts,
        ]);
    }

    /** ดาวน์โหลดเทมเพลต Excel (รอพัฒนาระบบไฟล์) */
    public function actionDownloadTemplate()
    {
        Yii::$app->session->setFlash('info', 'ฟีเจอร์ดาวน์โหลดเทมเพลตสำหรับทะเบียนหนังสืออยู่ระหว่างพัฒนา');
        return $this->redirect(array_merge(['index'], $this->request->getQueryParams()));
    }

    /** ส่งออก Excel (รอเชื่อมต่อรายงาน) */
    public function actionExportExcel()
    {
        Yii::$app->session->setFlash('info', 'ฟีเจอร์ส่งออก Excel อยู่ระหว่างพัฒนา');
        return $this->redirect(array_merge(['index'], $this->request->getQueryParams()));
    }

    /** นำเข้าข้อมูล Excel (รอหน้าอัปโหลด) */
    public function actionImportExcel()
    {
        Yii::$app->session->setFlash('info', 'ฟีเจอร์นำเข้าข้อมูลอยู่ระหว่างพัฒนา');
        return $this->redirect(array_merge(['index'], $this->request->getQueryParams()));
    }


    //แสดงหน้า ถ้าเป็นหัวหน้าหน่วยงานที่ ที่ส่งถึง หรือ tag บุคคล
    public function actionShowHome()
    {
        $me = UserHelper::GetEmployee();
        $empId = $me->id ?? null;
        $depId = $me->department ?? null;

        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $query = $dataProvider->query;
        $this->applyDocumentsIndexBaseQuery(
            $query,
            (int) $empId,
            (int) $depId,
            ['comment_emp', 'tags', 'employee_tag', 'employee', 'req_approve'],
            ['comment_dept', 'department']
        );
        $query->andWhere(['tr.id' => null]);

        $query->orderBy([
            'doc_transactions_date' => SORT_DESC,
            'doc_regis_number' => SORT_DESC,
        ]);

        $debugSql = null;
        if (defined('YII_DEBUG') && YII_DEBUG && $this->request->get('debug_sql')) {
            $debugQuery = clone $query;
            if ($dataProvider->pagination !== false) {
                $debugQuery->limit($dataProvider->pagination->getLimit());
                $debugQuery->offset($dataProvider->pagination->getOffset());
            }
            $debugSql = $debugQuery->createCommand()->getRawSql();
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('tilte'),
                'content' => $this->renderAjax('show_home', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'debugSql' => $debugSql,
                ])
            ];
        } else {
            return $this->render('show_home', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'debugSql' => $debugSql,
            ]);
        }
    }


    public function actionView($id)
    {

        $emp = UserHelper::GetEmployee();
        $detail = DocumentsDetail::findOne($id);
        if (!$detail) {
            throw new NotFoundHttpException('ไม่พบเส้นทางการส่งหนังสือ');
        }
        if (!DocumentAccessPolicy::canRead(
            (int) $detail->document_id,
            (int) ($emp->department ?? 0),
            (int) ($emp->id ?? 0)
        )) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิอ่านหนังสือฉบับนี้');
        }
        $callback = $this->request->get('callback');
        $model = $this->findModel($detail->document_id);

        $this->markDetailAsRead($detail, (int) $emp->id);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->renderAjax('@app/modules/dms/views/documents/view_title', ['model' => $model]),
                'content' => $this->renderAjax('@app/modules/dms/views/documents/view', [
                    'model' => $model,
                    'detail' => $detail
                ])
            ];
        } else {
            return $this->render('@app/modules/dms/views/documents/view', [
                'model' => $model,
                'detail' => $detail,
                'callback' => $callback
            ]);
        }
    }




    //สร้าง bookmark บันทึกหนังสือ
    public function actionBookmark($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $emp = UserHelper::GetEmployee();
        $document = DocumentsDetail::findOne($id);
        // return $document;
        $checkBookmark = DocumentsDetail::findOne(['name' => 'read', 'document_id' => $document->document_id, 'to_id' => $emp->id]);
        // $bookmark = DocumentsDetail::findOne($id);

        if ($checkBookmark) {
            $bookmark = $checkBookmark;
        } else {
            $bookmark = new DocumentsDetail;
            $bookmark->name = 'read';
            $bookmark->document_id = $document->document_id;
            $bookmark->to_id = $emp->id;
        }

        $bookmark->bookmark = ($bookmark->bookmark == 'Y') ? 'N' : 'Y';
        $bookmark->save(false);
        return [
            'action' => 'update',
            'status' => 'success',
            'data' => $bookmark
        ];
    }

    /**
     * อ่านเอกสารหลายรายการที่เลือกไว้
     */
    public function actionBulkRead()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost) {
            return [
                'status' => 'error',
                'message' => 'Invalid request',
            ];
        }

        $emp = UserHelper::GetEmployee();
        if (!$emp || empty($emp->id)) {
            return [
                'status' => 'error',
                'message' => 'ไม่พบข้อมูลผู้ใช้',
            ];
        }

        $markAllUnread = $this->request->post('mark_all_unread') === '1';
        if ($markAllUnread) {
            $unreadQuery = $this->applyDocumentsIndexBaseQuery(
                Documents::find(),
                (int) $emp->id,
                (int) ($emp->department ?? 0)
            );
            $ids = $unreadQuery
                ->andWhere(['tr.id' => null])
                ->select([new \yii\db\Expression('COALESCE(MIN(te.id), MIN(td.id))')])
                ->column();
        } else {
            $ids = $this->request->post('ids', []);
        }
        if (!is_array($ids)) {
            $ids = [];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === [] && !$markAllUnread) {
            return [
                'status' => 'error',
                'message' => 'กรุณาเลือกรายการอย่างน้อย 1 รายการ',
            ];
        }

        if ($ids === []) {
            return [
                'status' => 'success',
                'message' => 'ไม่มีหนังสือที่ยังไม่ได้อ่าน',
                'totalCount' => 0,
            ];
        }

        $count = 0;
        $details = DocumentsDetail::find()->where(['id' => $ids])->indexBy('id')->all();
        foreach ($ids as $detailId) {
            $detail = $details[$detailId] ?? null;
            if (!$detail) {
                continue;
            }
            if ($this->markDetailAsRead($detail, (int) $emp->id)) {
                $count++;
            }
        }

        return [
            'status' => 'success',
            'message' => 'บันทึกอ่านแล้ว ' . $count . ' รายการ',
            'totalCount' => $count,
        ];
    }

    // แสดง File และแสดงความเห็น
    public function actionComment($id)
    {
        $emp = UserHelper::GetEmployee();
        $model = new DocumentsDetail([
            'document_id' => $id,
            'to_id' => $emp->id,
            'name' => 'comment'
        ]);

        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            //## ตรวจสอบสถานะส่งเสนอ ผอ.
            try {
                $director = SiteHelper::getInfo()['director']->id;
                if ($emp->id == $director) {
                    $docStatus =  $model->document;
                    if ($docStatus) {
                        $docStatus->status = 'DS4';
                        $docStatus->save(false);
                    }
                }
                if (is_array($model->tags_employee) && in_array($director, $model->tags_employee)) {
                    $docStatus =  $model->document;
                    if ($docStatus) {
                        $docStatus->status = 'DS3';
                        $docStatus->save(false);
                    }
                }
            } catch (\Throwable $th) {
            }

            if ($model->save()) {
                $this->saveCommentTags($model);
                try {
                    $model->notifyCommentRecipients();
                } catch (\Throwable $th) {
                    Yii::warning('Failed to notify document comment recipients: ' . $th->getMessage(), __METHOD__);
                }
                return ['status' => 'success'];
            }
            return ['status' => 'error', 'errors' => $model->getErrors()];
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/dms/views/documents/_form_comment', [
                    'model' => $model,
                ])
            ];
        }
        return $this->render('@app/modules/dms/views/documents/_form_comment', [
            'model' => $model,
        ]);
    }

    public function actionUpdateComment($id)
    {
        $model = DocumentsDetail::findOne($id);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException();
        }
        if ((int) $model->created_by !== (int) Yii::$app->user->id) {
            throw new \yii\web\ForbiddenHttpException('แก้ไขได้เฉพาะความเห็นของตัวเอง');
        }

        // pre-fill tags จาก children (from_id link)
        $childTags = DocumentsDetail::find()
            ->where(['from_id' => $model->id])
            ->andWhere(['in', 'name', ['comment_emp', 'comment_dept']])
            ->all();
        $tagsEmp = [];
        $tagsDept = [];
        foreach ($childTags as $t) {
            if ($t->name === 'comment_emp') { $tagsEmp[] = $t->to_id; }
            elseif ($t->name === 'comment_dept') { $tagsDept[] = $t->to_id; }
        }
        $model->tags_employee = $tagsEmp;
        $model->tags_department = $tagsDept;

        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->save()) {
                $this->saveCommentTags($model);
                return ['status' => 'success'];
            }
            return ['status' => 'error', 'errors' => $model->getErrors()];
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'แก้ไขความเห็น',
                'content' => $this->renderAjax('@app/modules/dms/views/documents/_form_comment', [
                    'model' => $model,
                ])
            ];
        }
        return $this->render('@app/modules/dms/views/documents/_form_comment', [
            'model' => $model,
        ]);
    }

    public function actionTestNotification($id)
    {
        if (!Yii::$app->user->can('admin')) {
            Yii::$app->response->statusCode = 403;
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'error',
                'message' => 'อนุญาตเฉพาะผู้ใช้สิทธิ admin เท่านั้น',
            ];
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $emp = UserHelper::GetEmployee();
        $empId = $emp ? $emp->id : null;
        $model = new DocumentsDetail([
            'document_id' => $id,
            'to_id' => $empId,
            'name' => 'comment',
        ]);

        if (!$this->request->isPost) {
            return [
                'status' => 'error',
                'message' => 'Invalid request',
            ];
        }

        $model->load($this->request->post());
        if (empty($model->document_id)) {
            $model->document_id = $id;
        }

        return $model->sendTestNotification();
    }

    /**
     * บันทึก tags บุคคล/หน่วยงาน เป็น children rows (link ผ่าน from_id = comment.id)
     * ลบ children เก่าออกก่อน (เฉพาะของผู้แก้) ถ้าไม่อยู่ในรายการใหม่
     */
    protected function saveCommentTags(DocumentsDetail $comment)
    {
        $userId = (int) Yii::$app->user->id;
        $empIds = is_array($comment->tags_employee) ? array_filter(array_map('intval', $comment->tags_employee)) : [];
        $deptIds = is_array($comment->tags_department) ? array_filter(array_map('intval', $comment->tags_department)) : [];

        // กันส่งหน่วยงานซ้ำ — ตัด dept ที่เอกสารนี้ส่งไปแล้ว (รวม department + comment_dept อื่น)
        if (!empty($deptIds)) {
            $alreadyForwarded = DocumentsDetail::find()
                ->select('to_id')
                ->where(['document_id' => $comment->document_id])
                ->andWhere(['in', 'name', ['department', 'comment_dept']])
                ->andWhere(['not', ['from_id' => $comment->id]])
                ->column();
            $alreadyForwarded = array_map('intval', $alreadyForwarded);
            $deptIds = array_values(array_diff($deptIds, $alreadyForwarded));
        }

        // ลบ children ที่ไม่ได้อยู่ใน list ใหม่ (เฉพาะของ user คนนี้)
        DocumentsDetail::deleteAll([
            'and',
            ['from_id' => $comment->id, 'name' => 'comment_emp', 'created_by' => $userId],
            $empIds ? ['not in', 'to_id', $empIds] : [],
        ]);
        DocumentsDetail::deleteAll([
            'and',
            ['from_id' => $comment->id, 'name' => 'comment_dept', 'created_by' => $userId],
            $deptIds ? ['not in', 'to_id', $deptIds] : [],
        ]);

        foreach ($empIds as $empId) {
            $exists = DocumentsDetail::findOne([
                'from_id' => $comment->id,
                'name' => 'comment_emp',
                'to_id' => (string) $empId,
            ]);
            if ($exists) { continue; }
            $row = new DocumentsDetail();
            $row->document_id = $comment->document_id;
            $row->name = 'comment_emp';
            $row->from_id = (string) $comment->id;
            $row->from_type = 'comment';
            $row->to_id = (string) $empId;
            $row->to_type = 'employee';
            $row->save(false);
        }

        foreach ($deptIds as $deptId) {
            $exists = DocumentsDetail::findOne([
                'from_id' => $comment->id,
                'name' => 'comment_dept',
                'to_id' => (string) $deptId,
            ]);
            if ($exists) { continue; }
            $row = new DocumentsDetail();
            $row->document_id = $comment->document_id;
            $row->name = 'comment_dept';
            $row->from_id = (string) $comment->id;
            $row->from_type = 'comment';
            $row->to_id = (string) $deptId;
            $row->to_type = 'department';
            $row->save(false);
        }
    }

    public function actionDeleteComment($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = DocumentsDetail::findOne($id);
        if (!$model) {
            return ['status' => 'error', 'message' => 'ไม่พบรายการ'];
        }
        if ((int) $model->created_by !== (int) Yii::$app->user->id) {
            Yii::$app->response->statusCode = 403;
            return ['status' => 'error', 'message' => 'ลบได้เฉพาะความเห็นของตัวเอง'];
        }
        // cascade: ลบ children (comment_emp, comment_dept) ที่ link ผ่าน from_id
        DocumentsDetail::deleteAll(['from_id' => $model->id, 'name' => ['comment_emp', 'comment_dept']]);
        $model->delete();
        return ['status' => 'success'];
    }

    // แสดง File และแสดงความเห็น
    public function actionListComment($id)
    {

        $model = $this->findModel($id);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<i class="fa-regular fa-comments fs-2"></i> การลงความเห็น',
                'content' => $this->renderAjax('@app/modules/dms/views/documents/_comment_feed', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('@app/modules/dms/views/documents/_comment_feed', [
                'model' => $model,
            ]);
        }
    }



    // แสดง File และแสดงความเห็น
    public function actionFileComment($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('tilte'),
                'content' => $this->renderAjax('file_comment', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('file_comment', [
                'model' => $model,
            ]);
        }
    }

    // แสดง File และแสดงความเห็น
    public function actionShareFile($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<i class="fas fa-share"></i> ส่งต่อ',
                'content' => $this->renderAjax('share_file', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('share_file', [
                'model' => $model,
            ]);
        }
    }

    public function actionShow($ref)
    {
        // $model = $this->findModel($id);
        if (!Yii::$app->user->isGuest) {
            $document = Documents::findOne(['ref' => $ref]);
            $employee = UserHelper::GetEmployee();
            if (!$document || !$employee) {
                throw new NotFoundHttpException('ไม่พบหนังสือ');
            }
            if (!DocumentAccessPolicy::canRead(
                (int) $document->id,
                (int) ($employee->department ?? 0),
                (int) ($employee->id ?? 0)
            )) {
                throw new ForbiddenHttpException('คุณไม่มีสิทธิอ่านหนังสือฉบับนี้');
            }

            $id = Yii::$app->request->get('id');
            $download = Yii::$app->request->get('download');
            $fileUpload = Uploads::findOne(['ref' => $ref]);
            $type = 'pdf';
            if (!$fileUpload) {
                $filepath = Yii::getAlias('@webroot') . '/images/pdf-placeholder.pdf';
            } else {
                $filename = $fileUpload->real_filename;
                $filepath = FileManagerHelper::getUploadPath() . $fileUpload->ref . '/' . $filename;
            }
            if (!$fileUpload && !file_exists($filepath)) {
                $filepath = Yii::getAlias('@webroot') . '/images/pdf-placeholder.pdf';
            }

            if ((string) $download === '1') {
                return Yii::$app->response->sendFile($filepath, basename($filepath));
            }

            return Yii::$app->response->sendFile($filepath, basename($filepath), ['inline' => true]);
        } else {
            return false;
        }
    }

    protected function setHttpHeaders($type)
    {
        \Yii::$app->response->format = yii\web\Response::FORMAT_RAW;
        if ($type == 'png') {
            \Yii::$app->response->headers->add('content-type', 'image/png');
        }

        if ($type == 'pdf') {
            \Yii::$app->response->headers->add('content-type', 'application/pdf');
        }
    }

    public function actionListCommentTemplate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        $data  = Categorise::find()->where(['name' => 'comment_template', 'emp_id' => $me->id])->all();
        return [
            'title' => '',
            'totalCount' => count($data),
            'content' =>  $this->renderAjax('list_comment_template', [
                'data' => $data
            ])
        ];
    }
    //บันทึกข้อความที่ใช้บ่อย
    public function actionSaveCommentTemplate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        $text = $this->request->post('text');
        if ($text !== "") {

            $checkTemplate = Categorise::find()->where(['name' => 'comment_template', 'title' => $text, 'emp_id' => $me->id])->one();
            if (!$checkTemplate) {
                $model = new Categorise;
                $model->name = 'comment_template';
                $model->title = $text;
                $model->emp_id = $me->id;
                $model->save(false);
                return $model;
            } else {
                return $checkTemplate;
            }
        }
    }


    public function actionDeleteCommentTemplate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        $id = $this->request->post('id');
        $model = Categorise::findOne($id);
        if ($model) {
            $model->delete(false);
            return [
                'status' => 'success'
            ];
        } else {
            return [
                'status' => 'error'
            ];
        }
    }



    /**
     * Finds the Documents model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Documents the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Documents::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
