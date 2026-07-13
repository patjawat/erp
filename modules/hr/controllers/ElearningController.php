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
 * ElearningController handles the employee portal side of E-learning.
 */
class ElearningController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'submit-quiz' => ['POST'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            // เช็คว่าผู้ใช้งานเข้าสู่ระบบหรือยัง
            if (Yii::$app->user->isGuest) {
                return $this->redirect(['/auth/login']);
            }
            return true;
        }
        return false;
    }

    /**
     * หน้าแรกของพนักงาน: รายการหลักสูตรอบรม
     */
    public function actionIndex()
    {
        $employee = $this->getCurrentEmployee();
        if (!$employee) {
            throw new ForbiddenHttpException('ไม่พบประวัติข้อมูลพนักงานของคุณในระบบกรุณาติดต่อฝ่ายบุคคล');
        }

        // ค้นหาหลักสูตรทั้งหมดที่เปิดใช้งาน
        $courses = ElearningCourse::find()->where(['is_active' => 1])->all();

        $mandatoryCourses = []; // หลักสูตรบังคับตามแผนก
        $generalCourses = [];   // หลักสูตรทั่วไป

        foreach ($courses as $course) {
            $targetDeps = json_decode($course->target_departments, true) ?: [];
            
            // หาความคืบหน้าการเรียนปัจจุบัน
            $progress = ElearningProgress::findOne(['emp_id' => $employee->id, 'course_id' => $course->id]);
            
            // ค้นหาประวัติการสอบที่ผ่าน
            $passedAttempt = ElearningAttempt::find()
                ->where(['emp_id' => $employee->id, 'course_id' => $course->id, 'is_passed' => 1])
                ->one();

            $courseData = [
                'model' => $course,
                'progress' => $progress,
                'is_passed' => $passedAttempt ? true : false,
            ];

            if (empty($targetDeps)) {
                $generalCourses[] = $courseData;
            } elseif (in_array($employee->department, $targetDeps)) {
                $mandatoryCourses[] = $courseData;
            } else {
                // พนักงานแผนกอื่นสามารถเรียนรู้เพิ่มเติมได้
                $generalCourses[] = $courseData;
            }
        }

        return $this->render('index', [
            'mandatoryCourses' => $mandatoryCourses,
            'generalCourses' => $generalCourses,
            'employee' => $employee,
        ]);
    }

    /**
     * หน้าห้องเรียน: ดูสื่อการสอนของหลักสูตร
     */
    public function actionView($id)
    {
        $employee = $this->getCurrentEmployee();
        $course = $this->findCourse($id);

        // สร้างรายการบันทึกความคืบหน้าหากเริ่มเรียนเป็นครั้งแรก
        $progress = ElearningProgress::findOne(['emp_id' => $employee->id, 'course_id' => $course->id]);
        if (!$progress) {
            $progress = new ElearningProgress();
            $progress->emp_id = $employee->id;
            $progress->course_id = $course->id;
            $progress->status = 'learning';
            $progress->started_at = date('Y-m-d H:i:s');
            $progress->save();
        }

        $materials = ElearningMaterial::find()
            ->where(['course_id' => $id])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        $attempts = ElearningAttempt::find()
            ->where(['emp_id' => $employee->id, 'course_id' => $id])
            ->orderBy(['attempt_number' => SORT_DESC])
            ->all();

        return $this->render('view', [
            'course' => $course,
            'materials' => $materials,
            'progress' => $progress,
            'attempts' => $attempts,
        ]);
    }

    /**
     * หน้าแสดงสื่อการเรียนรู้รายชิ้น
     */
    public function actionStudyMaterial($id)
    {
        $material = ElearningMaterial::findOne($id);
        if (!$material) {
            throw new NotFoundHttpException('ไม่พบสื่อการเรียนรู้ที่ระบุ');
        }

        return $this->render('study_material', [
            'model' => $material,
            'course' => $material->course,
        ]);
    }

    /**
     * หน้าทำแบบทดสอบหลังเรียน (Post-test)
     */
    public function actionQuiz($id)
    {
        $employee = $this->getCurrentEmployee();
        $course = $this->findCourse($id);

        $questions = ElearningQuestion::find()
            ->where(['course_id' => $id])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        if (empty($questions)) {
            Yii::$app->session->setFlash('warning', 'หลักสูตรนี้ยังไม่ได้กำหนดแบบทดสอบหลังเรียน');
            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->render('quiz', [
            'course' => $course,
            'questions' => $questions,
        ]);
    }

    /**
     * บันทึกคะแนนสอบหลังการเรียน
     */
    public function actionSubmitQuiz($id)
    {
        $employee = $this->getCurrentEmployee();
        $course = $this->findCourse($id);
        
        $answersSubmitted = Yii::$app->request->post('answers') ?: [];
        $questions = ElearningQuestion::find()->where(['course_id' => $id])->all();
        $totalQuestions = count($questions);
        
        if ($totalQuestions === 0) {
            return $this->redirect(['view', 'id' => $id]);
        }

        $score = 0;
        foreach ($questions as $question) {
            $submittedAnsId = $answersSubmitted[$question->id] ?? null;
            if ($submittedAnsId !== null) {
                // ตรวจคำตอบที่ถูก
                $correctAns = ElearningAnswer::findOne([
                    'id' => $submittedAnsId, 
                    'question_id' => $question->id,
                    'is_correct' => 1
                ]);
                if ($correctAns) {
                    $score++;
                }
            }
        }

        $percentage = round(($score / $totalQuestions) * 100, 2);
        $isPassed = ($percentage >= $course->passing_score_percent) ? 1 : 0;

        // ดึงลำดับครั้งที่สอบล่าสุด
        $lastAttempt = ElearningAttempt::find()
            ->where(['emp_id' => $employee->id, 'course_id' => $id])
            ->orderBy(['attempt_number' => SORT_DESC])
            ->one();
        $attemptNum = $lastAttempt ? ($lastAttempt->attempt_number + 1) : 1;

        // บันทึกผลสอบลงในประวัติ
        $attempt = new ElearningAttempt();
        $attempt->emp_id = $employee->id;
        $attempt->course_id = $course->id;
        $attempt->attempt_number = $attemptNum;
        $attempt->score = $score;
        $attempt->total_questions = $totalQuestions;
        $attempt->percentage = $percentage;
        $attempt->is_passed = $isPassed;
        $attempt->created_at = date('Y-m-d H:i:s');

        if ($attempt->save()) {
            // หากสอบผ่าน ปรับปรุงสถานะความคืบหน้าการเรียนเป็นเสร็จสมบูรณ์
            if ($isPassed) {
                $progress = ElearningProgress::findOne(['emp_id' => $employee->id, 'course_id' => $id]);
                if ($progress) {
                    $progress->status = 'completed';
                    $progress->completed_at = date('Y-m-d H:i:s');
                    $progress->save();
                }
            }
            
            Yii::$app->session->setFlash('success', 'ส่งแบบทดสอบเรียบร้อยแล้ว');
            return $this->redirect(['quiz-result', 'id' => $attempt->id]);
        }

        Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาดในการบันทึกคะแนนสอบ');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * หน้าแสดงผลการสอบหลังเรียน
     */
    public function actionQuizResult($id)
    {
        $attempt = ElearningAttempt::findOne($id);
        if (!$attempt) {
            throw new NotFoundHttpException('ไม่พบประวัติการทำแบบทดสอบที่ระบุ');
        }

        // ตรวจสอบสิทธิ์ว่าเจ้าของข้อสอบเป็นคนดู
        $employee = $this->getCurrentEmployee();
        if ($attempt->emp_id !== $employee->id && !Yii::$app->user->can('hr') && !Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ในการดูผลสอบของบุคลากรท่านอื่น');
        }

        return $this->render('quiz_result', [
            'model' => $attempt,
            'course' => $attempt->course,
        ]);
    }

    /**
     * ค้นหาหลักสูตร
     */
    protected function findCourse($id)
    {
        if (($model = ElearningCourse::findOne($id)) !== null && $model->is_active) {
            return $model;
        }

        throw new NotFoundHttpException('ไม่พบหลักสูตรที่เปิดใช้งาน');
    }

    /**
     * ดึงข้อมูลพนักงานของคนที่เข้าสู่ระบบอยู่ปัจจุบัน
     */
    protected function getCurrentEmployee()
    {
        return Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
    }
}
