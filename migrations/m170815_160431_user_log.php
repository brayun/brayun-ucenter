<?php

use yii\db\Migration;

class m170815_160431_user_log extends Migration
{

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // 用户登录日志
        $this->createTable('{{%user_log}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->comment('用户ID'),
            'behavior' => $this->string(100)->comment('用户行为'),
            'remark' => $this->string(255)->comment('备注信息'),
            'ip' => $this->string(50)->comment('ip地址'),
            'created_at' => $this->integer()->notNull()->comment('操作时间'),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%user_log}}');
    }
}
