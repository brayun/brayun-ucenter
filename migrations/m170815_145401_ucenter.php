<?php

use yii\db\Migration;

class m170815_145401_ucenter extends Migration
{

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // 用户
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique()->comment('用户名'),
            'nickname' => $this->string(100)->unique()->comment('用户昵称'),
            'mobile' => $this->string(11)->unique()->comment('手机号码'),
            'mobile_bind' => $this->boolean()->comment('用户是否绑定 0:未绑定, 1:绑定'),
            'auth_key' => $this->string(32)->notNull()->comment('记住密码授权key'),
            'password_hash' => $this->string()->notNull()->comment('密码'),
            'password_reset_token' => $this->string()->unique()->comment('重置密码Token'),
            'email' => $this->string()->unique()->comment('邮箱'),
            'email_bind' => $this->boolean()->comment('邮箱是否绑定 0:未绑定, 1:绑定'),
            'avatar' => $this->string(255)->comment('头像'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('用户状态'),
            'created_at' => $this->integer()->notNull()->comment('注册时间'),
            'updated_at' => $this->integer()->notNull()->comment('更新时间'),
        ], $tableOptions);

    }

    public function down()
    {
        $this->dropTable('{{%user}}');
    }
}
