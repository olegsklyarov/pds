<?php

require_once 'connect.php';
require_once 'constants.php';
require_once 'func.php';
require_once "./class.TemplatePower.inc.php";

$table_name_admin = Constants::DATABASE_TABLE_ADMIN;
$table_name_student = Constants::DATABASE_TABLE_STUDENT;
$table_name_problem = Constants::DATABASE_TABLE_PROBLEM;

$form_install = 'install';
$form_login = 'login';
$form_password = 'password';

$recreate_tables = "
DROP TABLE IF EXISTS `$table_name_admin`;
DROP TABLE IF EXISTS `$table_name_student`;
DROP TABLE IF EXISTS `$table_name_problem`;

CREATE TABLE IF NOT EXISTS `$table_name_admin` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Login` VARCHAR(32) NOT NULL DEFAULT '',
  `Password` VARCHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `$table_name_student` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `F` VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'Фамилия',
  `I` VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'Имя',
  `O` VARCHAR(50) DEFAULT NULL COMMENT 'Отчество',
  `G` VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'Группа',
  `Mail` VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'E-mail',
  `Password` VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'Пароль',
  `Success` INT(11) NOT NULL DEFAULT '0' COMMENT 'Задание зачтено',
  `Task_id` INT(10) NOT NULL DEFAULT '-1' COMMENT 'Задача',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Mail` (`Mail`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `$table_name_problem` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `Caption` VARCHAR(250) NOT NULL DEFAULT '' COMMENT 'Название',
  `FAQ` TEXT,
  `FullDescription` TEXT NOT NULL,
  `TeamSize` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";


$t = new TemplatePower("./html/install.htm");
$t->assignInclude("head", "./html/head.htm");
$t->assignInclude("foot", "./html/foot.htm");
$t->prepare();

if (isset($_POST[$form_install])) {
    Connection::getInstance()->multi_query($recreate_tables);

    $login = md5(trim($_POST[$form_login]));
    $password = md5(trim($_POST[$form_password]));

    Connection::getInstance()->update(sql_query_table(
        "INSERT INTO %s (Login, Password) VALUES ('$login', '$password')",
        Constants::DATABASE_TABLE_ADMIN));

    $t->newBlock("installed");
    $t->printToScreen();
    unlink(__FILE__);
    die;
}

$t->newBlock('install');
$t->assign('admin_login', $form_login);
$t->assign('admin_password', $form_password);
$t->assign('admin_install', $form_install);
$t->printToScreen();
