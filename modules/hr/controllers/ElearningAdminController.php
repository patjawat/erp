<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use app\modules\hr\models\ElearningCourse;
use app\modules\hr\models\ElearningMaterial;
use app\modules\hr\models\ElearningQuestion;
use app\modules\hr\models\ElearningAnswer;
use app\modules\hr\models\ElearningProgress;
use app\modules\hr\models\ElearningAttempt;
use app\modules\hr\models\Employees;

/**
 * ElearningAdminController implements the CRUD and analytics actions for E-learning admin.
 */
class ElearningAdminController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'delete-material' => ['POST'],
                    'delete-question' => ['POST'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            // เช็คสิทธิ์การเข้าใช้งานเฉพาะ HR, Admin หรือหัวหน้ากลุ่มงาน/หัวหน้างาน
            $isAdminOrHR = Yii::$app->user->can('hr') || Yii::$app->user->can('admin');
            if ($isAdminOrHR) {
                return true;
            }

            $employee = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
            if ($employee) {
                $isLeader = \app\modules\hr\models\Organization::find()
                    ->where(new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.leader1')) = :empId", [':empId' => (string)$employee->id]))
                    ->orWhere(new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.leader2')) = :empId", [':empId' => (string)$employee->id]))
                    ->exists();
                if ($isLeader) {
                    return true;
                }
            }

            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าใช้งานระบบจัดการ E-learning');
        }
        return false;
    }

    /**
     * รายชื่อหลักสูตรทั้งหมด
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ElearningCourse::find()->orderBy(['id' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * ดูรายละเอียดหลักสูตร สื่อการสอน ข้อสอบ และสถิติผู้เรียน
     */
    public function actionView($id)
    {
        $model = $this->findCourse($id);

        $materialsProvider = new ActiveDataProvider([
            'query' => ElearningMaterial::find()->where(['course_id' => $id])->orderBy(['sort_order' => SORT_ASC]),
        ]);

        $questionsProvider = new ActiveDataProvider([
            'query' => ElearningQuestion::find()->where(['course_id' => $id])->orderBy(['sort_order' => SORT_ASC]),
        ]);

        // สถิติความคืบหน้าของผู้เรียนในหลักสูตรนี้
        $progressProvider = new ActiveDataProvider([
            'query' => ElearningProgress::find()->where(['course_id' => $id])->with('employee'),
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $this->render('view', [
            'model' => $model,
            'materialsProvider' => $materialsProvider,
            'questionsProvider' => $questionsProvider,
            'progressProvider' => $progressProvider,
        ]);
    }

    /**
     * สร้างหลักสูตรใหม่
     */
    public function actionCreate()
    {
        $model = new ElearningCourse();

        if ($model->load(Yii::$app->request->post())) {
            // แปลงแผนกจาก array เป็น JSON string เพื่อจัดเก็บ
            $departments = Yii::$app->request->post('ElearningCourse')['target_departments'] ?? null;
            if (is_array($departments)) {
                $model->target_departments = json_encode($departments);
            } else {
                $model->target_departments = json_encode([]);
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'สร้างหลักสูตรเรียบร้อยแล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * แก้ไขหลักสูตร
     */
    public function actionUpdate($id)
    {
        $model = $this->findCourse($id);

        if ($model->load(Yii::$app->request->post())) {
            $departments = Yii::$app->request->post('ElearningCourse')['target_departments'] ?? null;
            if (is_array($departments)) {
                $model->target_departments = json_encode($departments);
            } else {
                $model->target_departments = json_encode([]);
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกข้อมูลหลักสูตรเรียบร้อยแล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        // แปลง JSON string กลับเป็น array เพื่อแสดงผลใน Select2
        $model->target_departments = json_decode($model->target_departments, true) ?: [];

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * ลบหลักสูตร
     */
    public function actionDelete($id)
    {
        $this->findCourse($id)->delete();
        Yii::$app->session->setFlash('success', 'ลบหลักสูตรเรียบร้อยแล้ว');
        return $this->redirect(['index']);
    }

    /**
     * เพิ่มสื่อการเรียนรู้
     */
    public function actionAddMaterial($course_id)
    {
        $course = $this->findCourse($course_id);
        $model = new ElearningMaterial();
        $model->course_id = $course_id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'เพิ่มสื่อการเรียนรู้เรียบร้อยแล้ว');
            return $this->redirect(['view', 'id' => $course_id]);
        }

        return $this->render('add_material', [
            'model' => $model,
            'course' => $course,
        ]);
    }

    /**
     * แก้ไขสื่อการเรียนรู้
     */
    public function actionUpdateMaterial($id)
    {
        $model = ElearningMaterial::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบสื่อการสอนที่ระบุ');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกข้อมูลสื่อการเรียนรู้เรียบร้อยแล้ว');
            return $this->redirect(['view', 'id' => $model->course_id]);
        }

        return $this->render('update_material', [
            'model' => $model,
            'course' => $model->course,
        ]);
    }

    /**
     * ลบสื่อการเรียนรู้
     */
    public function actionDeleteMaterial($id)
    {
        $model = ElearningMaterial::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบสื่อการสอนที่ระบุ');
        }
        $course_id = $model->course_id;
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบสื่อการเรียนรู้เรียบร้อยแล้ว');
        return $this->redirect(['view', 'id' => $course_id]);
    }

    /**
     * เพิ่มคำถามข้อสอบและตัวเลือกตอบ
     */
    public function actionAddQuestion($course_id)
    {
        $course = $this->findCourse($course_id);
        $model = new ElearningQuestion();
        $model->course_id = $course_id;

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $model->question_text = $post['ElearningQuestion']['question_text'];
            $model->sort_order = $post['ElearningQuestion']['sort_order'] ?? 0;

            if ($model->save()) {
                // บันทึกตัวเลือกตอบ 4 ตัว
                $options = $post['Answers'] ?? [];
                $correctIndex = $post['correct_answer'] ?? 0;

                foreach ($options as $index => $optionText) {
                    if (trim($optionText) !== '') {
                        $answer = new ElearningAnswer();
                        $answer->question_id = $model->id;
                        $answer->answer_text = $optionText;
                        $answer->is_correct = ($index == $correctIndex) ? 1 : 0;
                        $answer->save();
                    }
                }

                Yii::$app->session->setFlash('success', 'เพิ่มโจทย์ข้อสอบเรียบร้อยแล้ว');
                return $this->redirect(['view', 'id' => $course_id]);
            }
        }

        return $this->render('add_question', [
            'model' => $model,
            'course' => $course,
        ]);
    }

    /**
     * แก้ไขข้อสอบและตัวเลือกตอบ
     */
    public function actionUpdateQuestion($id)
    {
        $model = ElearningQuestion::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบโจทย์ข้อถามที่ระบุ');
        }

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $model->question_text = $post['ElearningQuestion']['question_text'];
            $model->sort_order = $post['ElearningQuestion']['sort_order'] ?? 0;

            if ($model->save()) {
                // ลบตัวเลือกคำตอบเดิมทั้งหมดและสร้างใหม่
                ElearningAnswer::deleteAll(['question_id' => $model->id]);
                
                $options = $post['Answers'] ?? [];
                $correctIndex = $post['correct_answer'] ?? 0;

                foreach ($options as $index => $optionText) {
                    if (trim($optionText) !== '') {
                        $answer = new ElearningAnswer();
                        $answer->question_id = $model->id;
                        $answer->answer_text = $optionText;
                        $answer->is_correct = ($index == $correctIndex) ? 1 : 0;
                        $answer->save();
                    }
                }

                Yii::$app->session->setFlash('success', 'บันทึกโจทย์ข้อสอบเรียบร้อยแล้ว');
                return $this->redirect(['view', 'id' => $model->course_id]);
            }
        }

        $answers = $model->answers;

        return $this->render('update_question', [
            'model' => $model,
            'course' => $model->course,
            'answers' => $answers,
        ]);
    }

    /**
     * ลบข้อสอบ
     */
    public function actionDeleteQuestion($id)
    {
        $model = ElearningQuestion::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบโจทย์ข้อถามที่ระบุ');
        }
        $course_id = $model->course_id;
        $model->delete(); // On Delete Cascade ในฐานข้อมูลจะลบเฉลยอัตโนมัติ
        Yii::$app->session->setFlash('success', 'ลบโจทย์ข้อสอบเรียบร้อยแล้ว');
        return $this->redirect(['view', 'id' => $course_id]);
    }

    /**
     * แดชบอร์ดสรุปสถิติ E-learning ภาพรวมของโรงพยาบาล
     */
    public function actionDashboard()
    {
        // 1. สรุปยอดสะสม (KPI Cards)
        $totalCourses = ElearningCourse::find()->count();
        $totalEnrolled = ElearningProgress::find()->count();
        $totalCompleted = ElearningProgress::find()->where(['status' => 'completed'])->count();
        $completionRate = $totalEnrolled > 0 ? round(($totalCompleted / $totalEnrolled) * 100, 2) : 0;

        $totalAttempts = ElearningAttempt::find()->count();
        $totalPassedAttempts = ElearningAttempt::find()->where(['is_passed' => 1])->count();
        $averagePassRate = $totalAttempts > 0 ? round(($totalPassedAttempts / $totalAttempts) * 100, 2) : 0;

        // 2. ข้อมูลสถิติแบ่งตามหลักสูตร
        $coursesData = ElearningCourse::find()->all();
        $courseStats = [];
        foreach ($coursesData as $course) {
            $enrolled = ElearningProgress::find()->where(['course_id' => $course->id])->count();
            $completed = ElearningProgress::find()->where(['course_id' => $course->id, 'status' => 'completed'])->count();
            $courseAttempts = ElearningAttempt::find()->where(['course_id' => $course->id])->count();
            $coursePassed = ElearningAttempt::find()->where(['course_id' => $course->id, 'is_passed' => 1])->count();
            $avgScore = ElearningAttempt::find()->where(['course_id' => $course->id])->average('percentage') ?: 0;

            $courseStats[] = [
                'id' => $course->id,
                'title' => $course->title,
                'enrolled' => $enrolled,
                'completed' => $completed,
                'completion_rate' => $enrolled > 0 ? round(($completed / $enrolled) * 100, 2) : 0,
                'attempts' => $courseAttempts,
                'pass_rate' => $courseAttempts > 0 ? round(($coursePassed / $courseAttempts) * 100, 2) : 0,
                'avg_score' => round($avgScore, 2),
            ];
        }

        // 3. ข้อมูลรายงานผลการเรียนรายบุคคลล่าสุด
        $attemptsDataProvider = new ActiveDataProvider([
            'query' => ElearningAttempt::find()->orderBy(['created_at' => SORT_DESC])->with(['employee', 'course']),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('dashboard', [
            'totalCourses' => $totalCourses,
            'totalEnrolled' => $totalEnrolled,
            'totalCompleted' => $totalCompleted,
            'completionRate' => $completionRate,
            'averagePassRate' => $averagePassRate,
            'courseStats' => $courseStats,
            'attemptsDataProvider' => $attemptsDataProvider,
        ]);
    }

    /**
     * ค้นหาหลักสูตร
     */
    protected function findCourse($id)
    {
        if (($model = ElearningCourse::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('ไม่พบหลักสูตรที่ระบุ');
    }
}
