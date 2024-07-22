<?php

namespace app\modules\usermanager\models;

use app\modules\hr\models\Employees;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class User extends ActiveRecord implements IdentityInterface {

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public $password;
    public $confirm_password;
    public $roles;

    public $role;
    public $q;
    public $old_password;
    public $fullname_en;
    public $fullname;
    public $doctor_id;
    public $github;
    public $phone;

    public function scenarios() {
        $scenarios = parent::scenarios();
        $scenarios['registration'] = ['username', 'email'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::className(),
        ];
    }


    public function afterSave($insert, $changedAttributes)
    {
         parent::afterSave($insert, $changedAttributes);
        //  $checkEmp = Employees::findOne(['user_id' => $this->id]);
        //  if(!$checkEmp){
        //         $emp = new Employees([
        //             'user_id' => $this->id,
        //             'fname' => $this->fname,
        //             'lname' => $this->lname,
        //             'phone' => $this->phone
        //         ]);
        //         $emp->save();
        //  }
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            // ['phone', 'validateCountry'],
            ['phone', 'checkOwner'],
            //['status', 'required'],
            ['line_id', 'string'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\app\modules\usermanager\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['email', 'filter', 'filter' => 'trim'],
            //  ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => '\app\modules\usermanager\models\User', 'message' => 'This email address has already been taken.'],
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            ['doctor_id', 'unique'],
            ['doctor_id', 'string', 'max' => 5],
            [['doctor_id'], 'trim'],
            // ['phone', 'required'],
            ['confirm_password', 'required'],
            ['confirm_password', 'string', 'min' => 6],
            ['confirm_password', 'compare', 'compareAttribute' => 'password'],
            [['roles', 'doctor_id', 'fullname','fullname_en','q', 'old_password','phone'], 'safe'],
            // ['phone', 'unique', 'targetClass' => 'app\modules\hr\models\Employees', 'message' => 'เบอร์โทรศัพท์ถูกใช้แล้ว'],
        ];
    }

    public function validateCountry()
    {
        $this->addError('phone', 'ไม่พบชื่อในระบบ');
        // if (!in_array($this->$attribute, ['USA', 'Indonesia'])) {
        //     $this->addError($attribute, 'The country must be either "USA" or "Indonesia".');
        // }
    }

    public function checkOwner()
    {
        $model = Employees::find()->where(['phone' => $this->phone])->one();
        if ($this->phone == '0909748044') {
            $this->addError('phone', 'ไม่พบชื่อในระบบ');
        }
    }
    public function attributeLabels() {
        return [
            'doctor_id' => 'รหัสแพทย์',
            'fullname' => 'ชื่อ-สกุล',
            'username' => 'ชื่อเข้าใช้งาน',
            'password' => 'รหัสผ่าน',
            'confirm_password' => 'ยืนยันรหัสผ่าน',
            // 'license_number' => 'เลขใบประกอบฯ',
            'fullname_en' => 'ชื่อ - สกุลแพทย์(อังกฤษ)'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id) {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username) {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token) {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
                    'password_reset_token' => $token,
                    'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token) {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId() {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey() {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey() {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken() {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken() {
        $this->password_reset_token = null;
    }

    public function getItemStatus() {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_DELETED => 'Deleted'
        ];
    }

    public function getStatusName() {
        $items = $this->getItemStatus();
        return array_key_exists($this->status, $items) ? $items[$this->status] : '';
    }

    public function getAllRoles() {
        $auth = $auth = Yii::$app->authManager;
        return ArrayHelper::map($auth->getRoles(), 'name', 'name');
    }

    public function getRoleByUser() {
        $auth = Yii::$app->authManager;
        $rolesUser = $auth->getRolesByUser($this->id);
        $roleItems = $this->getAllRoles();
        $roleSelect = [];

        foreach ($roleItems as $key => $roleName) {
            foreach ($rolesUser as $role) {
                if ($key == $role->name) {
                    $roleSelect[$key] = $roleName;
                }
            }
        }
        $this->roles = $roleSelect;
    }

    // public function assignment() {
    //     $auth = Yii::$app->authManager;
    //     $roleUser = $auth->getRolesByUser($this->id);
    //     $auth->revokeAll($this->id);
    //     foreach ($this->roles as $key => $roleName) {
    //         $auth->assign($auth->getRole($roleName), $this->id);
    //     }
    // }


    public function assignment()
    {
        $auth = Yii::$app->authManager;
        $roleUser = $auth->getRolesByUser($this->id);
        $auth->revokeAll($this->id);
        if ($this->roles) {
            foreach ($this->roles as $key => $roleName) {
                $auth->assign($auth->getRole($roleName), $this->id);
            }
        } else {
            $auth->assign($auth->getRole('user'), $this->id);
        }
    }

    public function assignmentRegis()
    {
        $auth = Yii::$app->authManager;
        $roleUser = $auth->getRolesByUser($this->id);
        $auth->revokeAll($this->id);
        if ($this->roles) {
            foreach ($this->roles as $key => $roleName) {
                $auth->assign($auth->getRole($roleName), $this->id);
            }
        } else {
            $auth->assign($auth->getRole($this->role), $this->id);
        }
    }



    public function getEmployee()
    {
        return $this->hasOne(Employees::className(), ['user_id' => 'id']);
    }


}
