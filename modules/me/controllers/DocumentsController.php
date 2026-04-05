<?php

namespace app\modules\me\controllers;

use Yii;
use yii\web\Response;
use app\models\Uploads;
use app\models\Categorise;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\UserHelper;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentSearch;
use app\modules\dms\models\DocumentsDetail;
use app\modules\dms\models\DocumentsDetailSearch;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\hr\models\Organization;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class DocumentsController extends \yii\web\Controller
{
    private function isDeptHeadOrDeputy(int $departmentId, int $empId): bool
    {
        if ($departmentId <= 0 || $empId <= 0) {
            return false;
        }

        $org = Organization::findOne($departmentId);
        if (!$org) {
            return false;
        }

        $dataJson = $org->data_json;
        if (is_string($dataJson)) {
            $decoded = json_decode($dataJson, true);
            $dataJson = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($dataJson)) {
            $dataJson = [];
        }

        $leader1 = $dataJson['leader1'] ?? null;
        $leader2 = $dataJson['leader2'] ?? null;

        $allowed = [];
        foreach ([$leader1, $leader2] as $v) {
            if (is_numeric($v) && (int) $v > 0) {
                $allowed[] = (int) $v;
            }
        }

        return in_array($empId, $allowed, true);
    }

    /**
     * documents_detail.id ที่ยังไม่ได้อ่าน — SQL เดียวกับ actionShowHome / actionShowHomeV2 (ห้ามแก้ต่างจากตรงนั้น)
     */
    private function unreadDetailIdsShowHomeExact($department, $empId): array
    {
        $sql = "SELECT d.id
                FROM documents_detail d
                LEFT JOIN documents_detail r ON r.from_id = d.id AND r.name = 'read'
                WHERE d.name = 'department' AND d.to_id = :department AND r.doc_read IS NULL

                UNION

                SELECT d.id
                FROM documents_detail d
                LEFT JOIN documents_detail r ON r.from_id = d.id AND r.name = 'read'
                WHERE d.name = 'tags' AND d.to_id = :emp_id AND r.doc_read IS NULL";

        return Yii::$app->db->createCommand($sql, [
            ':department' => $department,
            ':emp_id' => $empId,
        ])->queryColumn();
    }

    /** แปลงรายการ detail id → document_id ไม่ซ้ำ */
    private function documentIdsFromDetailIds(array $detailIds): array
    {
        $detailIds = array_values(array_unique(array_filter(array_map('intval', $detailIds))));
        if ($detailIds === []) {
            return [];
        }

        return DocumentsDetail::find()
            ->select('document_id')
            ->distinct()
            ->where(['id' => $detailIds])
            ->column();
    }

    /** document_id สำหรับ KPI / กรอง kpi=unread — มาจากชุดเดียวกับ showHome */
    private function unreadDocumentIdsForMeInbox($department, $empId): array
    {
        $docIds = $this->documentIdsFromDetailIds($this->unreadDetailIdsShowHomeExact($department, $empId));

        return array_values(array_unique(array_filter(array_map('intval', $docIds))));
    }

    /**
     * kpi=unread: document_id => documents_detail.id สำหรับลิงก์ view/bookmark
     * ใช้แถว unread จริงจาก SQL เดียวกับ showHome — ไม่ใช้ hasOne documentTags ที่อาจชี้แถว tags คนอื่น/ไม่ใช่แถวที่ยัง unread
     *
     * @return array<int,int>
     */
    private function unreadOpenDetailIdByDocumentMap($department, int $empId): array
    {
        $detailIds = array_values(array_unique(array_filter(array_map('intval', $this->unreadDetailIdsShowHomeExact($department, $empId)))));
        if ($detailIds === []) {
            return [];
        }

        $rows = DocumentsDetail::find()
            ->select(['id', 'document_id', 'name'])
            ->where(['id' => $detailIds])
            ->asArray()
            ->all();

        $byDocument = [];
        foreach ($rows as $row) {
            $docId = (int) $row['document_id'];
            if (!isset($byDocument[$docId])) {
                $byDocument[$docId] = [];
            }
            $byDocument[$docId][] = $row;
        }

        $map = [];
        foreach ($byDocument as $docId => $list) {
            $chosen = null;
            foreach ($list as $row) {
                if (($row['name'] ?? '') === 'tags') {
                    $chosen = $row;
                    break;
                }
            }
            if ($chosen === null) {
                foreach ($list as $row) {
                    if (($row['name'] ?? '') === 'department') {
                        $chosen = $row;
                        break;
                    }
                }
            }
            if ($chosen === null && $list !== []) {
                $chosen = $list[0];
            }
            if ($chosen !== null) {
                $map[(int) $docId] = (int) $chosen['id'];
            }
        }

        return $map;
    }

    /**
     * โหลด DocumentsDetail ตาม unreadOpenDetailIdByDocumentMap สำหรับส่งเข้า view (ลด N+1)
     *
     * @param array<int,int> $openMap document_id => detail_id
     * @return array<int,DocumentsDetail>
     */
    private function unreadOpenDocumentsDetailById(array $openMap): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $openMap))));
        if ($ids === []) {
            return [];
        }

        return DocumentsDetail::find()->where(['id' => $ids])->indexBy('id')->all();
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
     * ข้อมูลหน้า index ทะเบียนหนังสือ (ใช้ทั้ง render เต็มและ Ajax refresh)
     *
     * @return array{
     *   searchModel: DocumentSearch,
     *   dataProvider: \yii\data\ActiveDataProvider,
     *   kpi: string|null,
     *   unreadOpenDetailIdByDocument: array,
     *   unreadOpenDocumentsDetailById: array,
     *   readAtByRoutingId: array,
     *   documentStats: array{total:int,unread:int,bookmarked:int,urgent:int},
     *   emp: object
     * }
     */
    private function buildDocumentsIndexViewData(): array
    {
        $emp = UserHelper::GetEmployee();
        $kpiRaw = $this->request->get('kpi');
        $isUnreadKpi = is_string($kpiRaw) && $kpiRaw === 'unread';

        $searchModel = new DocumentSearch([
            'date_filter' => 'today'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        /** @var \yii\db\ActiveQuery $query */
        $query = $dataProvider->query;
        $query->joinWith([
            'documentTags' => function ($query) {
                $query->alias('d_tags')
                    ->andOnCondition(['d_tags.name' => 'tags']);
            }
        ]);
        $query->joinWith([
            'docRead' => function ($query) {
                $query->alias('d_read')
                    ->andOnCondition(['d_read.name' => 'read']);
            }
        ]);
        $q = trim($searchModel->q ?? '');

        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'topic', $q],
            ['like', 'doc_regis_number', $q],  // Fixed typo here
            ['like', 'doc_number', $q],
            ['like', new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(documents.data_json, '$.des'))"), $q],
        ]);
        // โหมดยังไม่ได้อ่าน: ห้ามใช้ d_read.bookmark — ขัดกับ r.doc_read IS NULL ของนิยาม unread
        if (!$isUnreadKpi && $searchModel->q_status == 'Y') {
            $dataProvider->query->andFilterWhere(['d_read.bookmark' => 'Y']);
            $dataProvider->query->andWhere($this->readRowToEmployeeCondition((int) $emp->id));
        } elseif (!$isUnreadKpi) {
            $dataProvider->query->andFilterWhere(['status' => $searchModel->q_status]);
        } elseif ($searchModel->q_status !== null && $searchModel->q_status !== '' && (string) $searchModel->q_status !== 'Y') {
            $dataProvider->query->andFilterWhere(['status' => $searchModel->q_status]);
        }
        $dataProvider->query->andFilterWhere([
            'between',
            'doc_transactions_date',
            AppHelper::convertToGregorian($searchModel->date_start),
            AppHelper::convertToGregorian($searchModel->date_end)
        ]);

        // JOIN หลายแถวต่อ 1 เอกสาร — ต้อง group ที่ documents.id ไม่เช่นนั้น LIMIT ของ pagination นับแถว join → เห็นแค่ไม่กี่รายการต่อหน้า
        $dataProvider->query->groupBy(['documents.id']);

        // ยังไม่ได้อ่าน (showHome) รวมหนังสือถึงหน่วยงานที่อาจไม่มีแถว tags ถึงตัวพนักงาน — ห้ามบังคับ tags ในการนับ/กรอง unread
        $kpiUnreadBaseQuery = clone $dataProvider->query;
        $kpiInboxBaseQuery = clone $dataProvider->query;
        $kpiInboxBaseQuery->andWhere($this->tagsToEmployeeCondition((int) $emp->id));
        if (!$isUnreadKpi) {
            $dataProvider->query->andWhere($this->tagsToEmployeeCondition((int) $emp->id));
        }

        $dataProvider->setPagination([
            'pageSize' => 20,
            'pageSizeParam' => false,
        ]);

        $dataProvider->setSort(['defaultOrder' => [
            // 'doc_regis_number' => SORT_DESC,
            // 'thai_year' => SORT_DESC,
        ]]);

        $kpi = $this->request->get('kpi');
        if (!is_string($kpi) || !in_array($kpi, ['unread', 'bookmarked', 'urgent', 'total'], true)) {
            $kpi = null;
        }
        switch ($kpi) {
            case 'unread':
                $unreadIds = $this->unreadDocumentIdsForMeInbox($emp->department, $emp->id);
                if ($unreadIds === []) {
                    $dataProvider->query->andWhere('0=1');
                } else {
                    $dataProvider->query->andWhere(['documents.id' => $unreadIds]);
                }
                break;
            case 'bookmarked':
                $dataProvider->query->andWhere(['d_read.bookmark' => 'Y']);
                $dataProvider->query->andWhere($this->readRowToEmployeeCondition((int) $emp->id));
                break;
            case 'urgent':
                $dataProvider->query->andWhere(['documents.doc_speed' => 'ด่วนที่สุด']);
                break;
            case 'total':
            default:
                break;
        }

        // query มี GROUP BY documents.id แล้ว — ใช้ count('*') ไม่ใช้ DISTINCT documents.id (subquery ไม่มี prefix ตาราง)
        $totalList = (int) (clone $kpiInboxBaseQuery)->count('*');
        $bookmarkedCount = (int) (clone $kpiInboxBaseQuery)
            ->andWhere(['d_read.bookmark' => 'Y'])
            ->andWhere($this->readRowToEmployeeCondition((int) $emp->id))
            ->count('*');
        $urgentCount = (int) (clone $kpiInboxBaseQuery)
            ->andWhere(['documents.doc_speed' => 'ด่วนที่สุด'])
            ->count('*');

        $unreadIdsForKpi = $this->unreadDocumentIdsForMeInbox($emp->department, $emp->id);
        if ($unreadIdsForKpi === []) {
            $unreadCount = 0;
        } else {
            $unreadCount = (int) (clone $kpiUnreadBaseQuery)
                ->andWhere(['documents.id' => $unreadIdsForKpi])
                ->count('*');
        }

        $unreadOpenDetailIdByDocument = $isUnreadKpi
            ? $this->unreadOpenDetailIdByDocumentMap($emp->department, (int) $emp->id)
            : [];
        $unreadOpenDocumentsDetailById = $unreadOpenDetailIdByDocument !== []
            ? $this->unreadOpenDocumentsDetailById($unreadOpenDetailIdByDocument)
            : [];

        $routingIdsForReadStatus = [];
        foreach ($dataProvider->getModels() as $m) {
            if ($unreadOpenDetailIdByDocument !== [] && isset($unreadOpenDetailIdByDocument[$m->id])) {
                $routingIdsForReadStatus[] = $unreadOpenDetailIdByDocument[$m->id];
            } else {
                $dt = $m->documentTags ?? $m->documentDepartment ?? null;
                if ($dt !== null) {
                    $routingIdsForReadStatus[] = (int) $dt->id;
                }
            }
        }
        $readAtByRoutingId = $routingIdsForReadStatus !== []
            ? DocumentsDetail::readRecordTimesByRoutingFromIds($routingIdsForReadStatus, (int) $emp->id)
            : [];

        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'kpi' => $kpi,
            'unreadOpenDetailIdByDocument' => $unreadOpenDetailIdByDocument,
            'unreadOpenDocumentsDetailById' => $unreadOpenDocumentsDetailById,
            'readAtByRoutingId' => $readAtByRoutingId,
            'documentStats' => [
                'total' => $totalList,
                'unread' => $unreadCount,
                'bookmarked' => $bookmarkedCount,
                'urgent' => $urgentCount,
            ],
            'emp' => $emp,
        ];
    }

    public function actionIndex()
    {
        $d = $this->buildDocumentsIndexViewData();

        return $this->render('index', [
            'searchModel' => $d['searchModel'],
            'dataProvider' => $d['dataProvider'],
            'action' => 'index',
            'to' => 'ถึง' . $d['emp']->fullname(),
            'activeKpi' => $d['kpi'],
            'unreadOpenDetailIdByDocument' => $d['unreadOpenDetailIdByDocument'],
            'unreadOpenDocumentsDetailById' => $d['unreadOpenDocumentsDetailById'],
            'readAtByRoutingId' => $d['readAtByRoutingId'],
            'documentStats' => $d['documentStats'],
        ]);
    }

    /** Ajax: HTML สำหรับอัปเดต KPI + รายการแบบเรียลไทม์ (หลังเปิดอ่าน / ปักดาว) */
    public function actionAjaxRefresh()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $d = $this->buildDocumentsIndexViewData();
        $isTableView = Yii::$app->request->get('view', 'list') !== 'grid';
        $listPartial = $isTableView ? '_list' : '_grid';
        $listHtml = $this->renderPartial($listPartial, [
            'dataProvider' => $d['dataProvider'],
            'unreadOpenDetailIdByDocument' => $d['unreadOpenDetailIdByDocument'],
            'unreadOpenDocumentsDetailById' => $d['unreadOpenDocumentsDetailById'],
            'readAtByRoutingId' => $d['readAtByRoutingId'],
        ]);
        $kpiHtml = $this->renderPartial('kpi_summary', [
            'documentStats' => $d['documentStats'],
            'activeKpi' => $d['kpi'],
        ]);

        return [
            'success' => true,
            'kpiHtml' => $kpiHtml,
            'listHtml' => $listHtml,
            'documentStats' => $d['documentStats'],
            'totalCount' => (int) $d['dataProvider']->getTotalCount(),
        ];
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

    //ถึงหน่วยงาน
    // public function actionDepartment()
    // {
    //     $emp = UserHelper::GetEmployee();
    //     $department = $emp->department;
    //     if (!$this->isDeptHeadOrDeputy((int) $department, (int) $emp->id)) {
    //         $searchModel = new DocumentSearch([
    //             'date_filter' => 'today'
    //         ]);
    //         $dataProvider = $searchModel->search($this->request->queryParams);
    //         $dataProvider->query->andWhere('1=0');

    //         return $this->render('index', [
    //             'searchModel' => $searchModel,
    //             'dataProvider' => $dataProvider,
    //             'action' => 'department',
    //             'to' => 'ถึงหน่วยงาน',
    //         ]);
    //     }

    //     $searchModel = new DocumentSearch([
    //         'date_filter' => 'today'
    //     ]);

    //     $dataProvider = $searchModel->search($this->request->queryParams);
    //     /** @var \yii\db\ActiveQuery $query */
    //     $query = $dataProvider->query;
    //     $query->joinWith([
    //         'documentDepartment' => function ($query) {
    //             $query->alias('d_department')
    //                 ->andOnCondition(['d_department.name' => 'department']);
    //         }
    //     ]);
    //     $query->joinWith([
    //         'docRead' => function ($query) {
    //             $query->alias('d_read')
    //                 ->andOnCondition(['d_read.name' => 'read']);
    //         }
    //     ]);
    //     $dataProvider->query->andFilterWhere([
    //         'or',
    //         ['like', 'topic', $searchModel->q],
    //         ['like', 'doc_regis_number', $searchModel->q],  // Fixed typo here
    //         ['like', 'doc_number', $searchModel->q],
    //         ['like', new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(documents.data_json, '$.des'))"), $searchModel->q],
    //     ]);
    //     if ($searchModel->q_status == 'Y') {
    //         $dataProvider->query->andFilterWhere(['d_read.bookmark' => 'Y']);
    //     } else {
    //         $dataProvider->query->andFilterWhere(['status' => $searchModel->q_status]);
    //     }

    //     $dataProvider->query->andWhere(['d_department.to_id' => $emp->department]);

    //             $dataProvider->query->andFilterWhere([
    //         'between',
    //         'doc_transactions_date',
    //         AppHelper::convertToGregorian($searchModel->date_start),
    //         AppHelper::convertToGregorian($searchModel->date_end)
    //     ]);



    //     return $this->render('index', [
    //         'searchModel' => $searchModel,
    //         'dataProvider' => $dataProvider,
    //         'action' => 'department',
    //          'to' => 'ถึงหน่วยงาน'
    //     ]);
    // }

    //แสดงหน้า Mydashboard
    public function actionShowHome()
    {

        $emp = UserHelper::GetEmployee();
        $department = $emp->department;

        $ids = $this->unreadDetailIdsShowHomeExact($department, $emp->id);

          $searchModel = new DocumentsDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['IN','id', $ids]);

        


        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('tilte'),
                'content' => $this->renderAjax('show_home', [
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('show_home', [
                'dataProvider' => $dataProvider,
            ]);
        }
    }

     public function actionShowHomeV2()
    {

        $emp = UserHelper::GetEmployee();
        $department = $emp->department;

        $ids = $this->unreadDetailIdsShowHomeExact($department, $emp->id);

          $searchModel = new DocumentsDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['IN','id', $ids]);

        


        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('tilte'),
                'content' => $this->renderAjax('show_home_v2', [
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('show_home_v2', [
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionView($id)
    {

        $emp = UserHelper::GetEmployee();
        $detail = DocumentsDetail::findOne($id);
        // เงื่อนไขเดิม เมื่อส่งถึงหน่วยงาน
        // if ($detail && $detail->name === 'department') {
        //     $deptId = (int) $detail->to_id;
        //     if (!$this->isDeptHeadOrDeputy($deptId, (int) $emp->id)) {
        //         throw new ForbiddenHttpException('ไม่อนุญาตให้เข้าถึงเอกสารของหน่วยงานนี้');
        //     }
        // }
        $callback = $this->request->get('callback');
        $model = $this->findModel($detail->document_id);
        
        $checkReading = DocumentsDetail::find()->where(['document_id' => $detail->document_id, 'name' => 'read', 'to_id' => $emp->id, 'from_id' => $id])->one();
        if (!$checkReading) {
            $reading = new DocumentsDetail;
            $reading->document_id = $detail->document_id;
            $reading->name = 'read';
            $reading->to_id = $emp->id;
            $reading->from_id = $id;
            $reading->doc_read = date('Y-m-d H:i:s');
            $reading->save(false);
        } else {
            if ($checkReading->doc_read == null) {
                $checkReading->doc_read = date('Y-m-d H:i:s');
                $checkReading->save(false);
            }
        }

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
            $director = SiteHelper::getInfo()['director']->id;
            $me = UserHelper::GetEmployee();
            if ($me->id == $director) {
                $docStatus =  $model->document;
                $docStatus->status = 'DS4';
                $docStatus->save(false);
            }

            try {
            //ตรวจว่ามีการ Tags ถึง ผอฬหรือไม่
            if (in_array($director, $model->tags_employee)) {
                $docStatus =  $model->document;
                $docStatus->status = 'DS3';
                $docStatus->save(false);
            }
                        } catch (\Throwable $th) {
            }



            if ($model->save()) {
                $model->UpdateDocumentsDetail();
                return [
                    'status' => 'success'
                ];
                // ส่งข้อมูลกลับไปยังหน้า view เพื่อให้เห็นว่ามีการ comment เข้ามา'
                // return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/dms/views/documents/_form_comment', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('@app/modules/dms/views/documents/_form_comment', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdateComment($id)
    {
        $emp = UserHelper::GetEmployee();
        $model = DocumentsDetail::findOne($id);

        $tags = DocumentsDetail::find()->where(['name' => 'comment', 'document_id' => $model->document_id])->all();
        $list = ArrayHelper::map($tags, 'tag_id', 'tag_id');

        if ($this->request->isPost && $model->load($this->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($model->save()) {
                $model->UpdateDocumentsDetail();
                return [
                    'status' => 'success'
                ];
                // return [
                //     'status' => 'success',
                //     'data' => $model,
                // ];
            }
        }
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => 'xxx',
                'content' => $this->renderAjax('@app/modules/dms/views/documents/_form_comment', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('@app/modules/dms/views/documents/_form_comment', [
                'model' => $model,
            ]);
        }
    }


    public function actionDeleteComment($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = DocumentsDetail::findOne($id);
        if ($model->created_by == Yii::$app->user->id) {

            $model->delete();
            return [
                'status' => 'success',
                'data' => $model,
            ];
        } else {
            return [
                'status' => 'error',
            ];
        }
    }

    // แสดง File และแสดงความเห็น
    public function actionListComment($id)
    {

        $model = $this->findModel($id);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<i class="fa-regular fa-comments fs-2"></i> การลงความเห็น',
                'content' => $this->renderAjax('list_comment', [
                    'model' => $model,

                ])
            ];
        } else {
            return $this->render('list_comment', [
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
            $id = Yii::$app->request->get('id');
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

            $this->setHttpHeaders($type);
            \Yii::$app->response->data = file_get_contents($filepath);
            return \Yii::$app->response;
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
        $data  = Categorise::find()->where(['name' => 'comment_template','emp_id' => $me->id])->all();
        return [
            'title' => '',
            'totalCount' => count($data),
            'content' =>  $this->renderAjax('list_comment_template',[
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
        if($text !==""){

        $checkTemplate = Categorise::find()->where(['name' => 'comment_template','title' => $text,'emp_id' => $me->id])->one();
        if(!$checkTemplate){
            $model = new Categorise;
            $model->name = 'comment_template';
            $model->title = $text;
            $model->emp_id = $me->id;
            $model->save(false);
            return $model;
        }else{
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
        if($model){
            $model->delete(false);
            return [
                'status' => 'success'
            ];
        }else{
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