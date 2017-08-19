<?php
/*
 *          ┌─┐       ┌─┐
 *       ┌──┘ ┴───────┘ ┴──┐
 *       │                 │
 *       │       ───       │
 *       │  ─┬┘       └┬─  │
 *       │                 │
 *       │       ─┴─       │
 *       └───┐         ┌───┘
 *           │         └──────────────┐
 *           │                        ├─┐
 *           │                        ┌─┘
 *           │                        │
 *           └─┐  ┐  ┌───────┬──┐  ┌──┘
 *             │ ─┤ ─┤       │ ─┤ ─┤
 *             └──┴──┘       └──┴──┘
 *        @Author Ethan <ethan.lu@qq.com>
 */

namespace brayun\ucenter\models;

use yii\base\Model;
use yii\db\Expression;
use yii\web\UserEvent;

class LoginForm extends Model
{

    const LOGIN_TYPE_MOBILE = 'mobile';
    const LOGIN_TYPE_USERNAME = 'username';
    const LOGIN_TYPE_EMAIL = 'email';

    const EVENT_AFTER_LOGIN = 'after_login';

    public $account;

    public $password;

    private $_loginType;

    private $_user = null;

    /**
     * @return string
     */
    public function formName()
    {
        return'';
    }

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return [
            [['account', 'password'], 'required'],
            ['account', function ($attribute) {
                if (preg_match('/^1[3|5|7|8]\d{9}$/', $this->account)) {
                    $this->_loginType = self::LOGIN_TYPE_MOBILE;
                } elseif (preg_match('/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/', $this->account)) {
                    $this->_loginType = self::LOGIN_TYPE_EMAIL;
                } else {
                    $this->_loginType = self::LOGIN_TYPE_USERNAME;
                }
            }],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '账号或密码错误');
            }
        }
    }


    /**
     * 用户登录
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();

            if (\Yii::$app->user->login($user, 3600 * 24 * 30)) {

                $event = new UserEvent();
                $event->identity = $user->toArray(['id','username']);
                $this->trigger(self::EVENT_AFTER_LOGIN, $event);
                return $event->identity;
            }
        }
        return false;
    }


    /**
     * 获取用户对象
     */
    protected function getUser()
    {
        if ($this->_user == null) {
            switch ($this->_loginType) {
                case self::LOGIN_TYPE_MOBILE:
                    $this->_user = User::findByMobile($this->account);
                    break;
                case self::LOGIN_TYPE_USERNAME:
                    $this->_user = User::findByUsername($this->account);
                    break;
                case self::LOGIN_TYPE_EMAIL:
                    $this->_user = User::findByEmail($this->account);
                    break;
                default:
                    $this->_user = User::findIdentity($this->account);
            }
        }
        return $this->_user;
    }
}