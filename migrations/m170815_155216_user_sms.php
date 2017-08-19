<?php

use yii\db\Migration;

class m170815_155216_user_sms extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // 用户
        $this->createTable('{{%user_sms}}', [
            'id' => $this->primaryKey(),
            'mobile' => $this->string(11)->comment('手机号码'),
            'captcha' => $this->string(6)->notNull()->comment('短信验证码'),
            'ip' => $this->string(50)->comment('ip地址'),
            'msg_content' => $this->string(255)->comment('短信内容'),
            'type' => $this->string(50)->comment('短信类型'),
            'generate_time' => $this->integer()->notNull()->comment('生成时间'),
            'apply_time' => $this->integer()->comment('使用时间'),
            'status' => $this->boolean()->notNull()->defaultValue(0)->comment('状态'),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%user_sms}}');
    }
}
