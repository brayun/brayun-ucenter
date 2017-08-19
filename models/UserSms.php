<?php

namespace brayun\ucenter\models;

use Yii;

/**
 * This is the model class for table "{{%user_sms}}".
 *
 * @property integer $id
 * @property string $mobile
 * @property string $captcha
 * @property string $ip
 * @property string $msg_content
 * @property string $type
 * @property integer $generate_time
 * @property integer $apply_time
 * @property integer $status
 */
class UserSms extends \yii\db\ActiveRecord
{
    /** 短信类型 登录 */
    const SMS_TYPE_LOGIN = 'LOGIN';
    /** 注册 */
    const SMS_TYPE_REGISTRATION = 'REGISTRATION';
    /** 找回密码 */
    const SMS_TYPE_FIND_PWD = 'FIND_PWD';
    /** 验证手机号码 */
    const SMS_TYPE_VALIDATE_MOBILE = 'VALIDATE_MOBILE';

    /** 已使用 */
    const STATUS_APPLY = '1';
    /** 未使用 */
    const STATUS_NOT_APPLY = '0';

    /** 发送限制次数 每日6次 */
    const LIMIT_SEND = 6;

    /** 发送频率限制 60秒*/
    const LIMIT_RATE = 60;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_sms}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['captcha', 'generate_time', 'apply_time', 'status'], 'integer'],
            [['mobile'], 'string', 'max' => 11],
            [['ip'], 'string', 'max' => 15],
            [['msg_content'], 'string', 'max' => 255],


            [['status'], 'in', 'range' => [self::STATUS_APPLY, self::STATUS_NOT_APPLY]],
            [['status'], 'default', 'value' => self::STATUS_NOT_APPLY],

            [['type'], 'default', 'value' => self::SMS_TYPE_REGISTRATION],
            [['type'], 'in', 'range' => [self::SMS_TYPE_LOGIN, self::SMS_TYPE_REGISTRATION, self::SMS_TYPE_FIND_PWD, self::SMS_TYPE_VALIDATE_MOBILE]],

            [['captcha'], 'default', 'value' => rand(1000, 9999)],

            [['ip'], 'default', 'value' => Yii::$app->request->getUserIP()],

            [['generate_time'], 'default', 'value' => time()],

            ['mobile', 'required'],
            [['mobile'], 'match', 'pattern' => '/^1[3|4|5|7|8]\d{9}$/'],
            [['mobile'], 'validateLimit'],

            // 判断注册时检查手机号码是否存在,如存在则不发送短信     注册时
            [['mobile'], 'unique', 'targetClass' => User::className(), 'message' => '该号码已注册，请更换手机号码！', 'when' => function ($model) {
                return in_array($model->type, [static::SMS_TYPE_REGISTRATION]);
            }],

            // 登录及找回密码时判断用户是否存在
            [['mobile'], 'exist', 'targetClass' => User::className(), 'message' => '用户不存在', 'when' => function ($model) {
                return in_array($model->type, [static::SMS_TYPE_LOGIN, static::SMS_TYPE_FIND_PWD]);
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => '手机号码',
            'captcha' => '验证码',
            'ip' => '用户IP',
            'msg_content' => '短信内容',
            'type' => '短信类型',
            'generate_time' => '生成时间',
            'apply_time' => '使用时间',
            'status' => '是否使用 0未使用 1使用',
        ];
    }

    /**
     * 限制每天对同一号码发送次数及频率
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function validateLimit($attribute, $params)
    {
        $count = static::find()->where(['type'=> $this->type, 'mobile' => $this->mobile])->andFilterWhere(['between', 'generate_times', strtotime(date('Y-m-d')), strtotime(date('Y-m-d 23:59:59'))])->count();
        if ($count >= static::LIMIT_SEND) {
            $this->addError($attribute, '您已超过每日发送限制'.static::LIMIT_SEND);
            return false;
        }

        if ($lastSendTime = static::find()
            ->where(['type' => $this->type, 'mobile' => $this->mobile])
            ->andWhere(['gt', 'generate_time', time()-60])
            ->orderBy('generate_time DESC')->one()) {
            $this->addError('mobile', 60 - (time() - $lastSendTime['generate_time']).'秒后再试');
            return false;
        }
    }

}
