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

use brayun\skeleton\traits\ModelTrait;
use yii\base\Model;

class RegistrationForm extends Model
{
    use ModelTrait;

    const SCENARIO_REG_MOBILE = 'mobile';
    const SCENARIO_REG_USERNAME = 'username';
    const SCENARIO_REG_EMAIL = 'email';

    public $mobile;
    public $password;
    public $sms_code;
    public $is_read;

    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_REG_MOBILE] = ['mobile', 'password', 'sms_code', 'is_read'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['mobile', 'required'],
            ['mobile', 'filter', 'filter' => 'trim'],
            ['mobile', 'match', 'pattern' => '/^1[3|4|5|7|8]\d{9}$/'],
            ['mobile', 'unique', 'targetClass' => User::className(), 'message' => '手机号码已存在', 'on' => self::SCENARIO_REG_MOBILE],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],

//            ['sms_code', 'required'],
//            ['sms_code', 'validateSmsCode'],

            ['is_read', 'required', 'message' => '请同意服务协议'],
            ['is_read', 'boolean', 'trueValue' => 1, 'message' => '请先阅读服务条款'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'mobile' => '手机号码',
            'password' => '密码',
            'sms_code' => '短信验证码',
            'is_read' => '服务条款',
        ];
    }

//    public function validateSmsCode($attribute, $params)
//    {
//        if ($sms = SmsLog::find()->where('mobile = :mobile AND captcha = :captcha', [':mobile' => $this->mobile, ':captcha' => $this->$attribute])->one()) {
//
//            if ($sms['add_time'] > time()-600) {
//                return true;
//            }
//
//            $this->addError('sms_code', '验证码超时');
//            return false;
//        }
//        $this->addError('sms_code', '验证码错误');
//        return false;
//    }


    /**
     * 手机号码注册
     * @return null|User
     */
    public function regMobile()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->mobile = $this->mobile;
        $user->username = $this->mobile;
        $user->bindMobile();
        $user->setPassword($this->password);
        $user->generateAuthKey();

        return $user->save() ? $user : null;

    }
}