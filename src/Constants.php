<?php

namespace App;

final class Constants
{
    const ENVIRONMENT_DEVELOPMENT = 'environment_development';
    const ENVIRONMENT_PRODUCTION = 'environment_production';

    const DATABASE_TABLE_STUDENT = 'student';
    const DATABASE_TABLE_PROBLEM = 'problem';
    const DATABASE_TABLE_ADMIN = 'admin';

    const DATABASE_PROPERTY_HOST = 'database_property_host';
    const DATABASE_PROPERTY_USER = 'database_property_user';
    const DATABASE_PROPERTY_PASSWORD = 'database_property_password';
    const DATABASE_PROPERTY_NAME = 'database_property_name';
    const DATABASE_LOCALHOST = 'localhost';

    const DATABASE_SETTINGS = [
        self::ENVIRONMENT_DEVELOPMENT => [
            self::DATABASE_PROPERTY_USER => 'task_select',
            self::DATABASE_PROPERTY_PASSWORD => 'task_select',
            self::DATABASE_PROPERTY_NAME => 'task_select',
            self::DATABASE_PROPERTY_HOST => self::DATABASE_LOCALHOST,
        ],
        self::ENVIRONMENT_PRODUCTION => [
            self::DATABASE_PROPERTY_USER => 'task_select',
            self::DATABASE_PROPERTY_PASSWORD => 'hy2md12j',
            self::DATABASE_PROPERTY_NAME => 'task_select',
            self::DATABASE_PROPERTY_HOST => self::DATABASE_LOCALHOST,
        ]
    ];

    private static function getEnvironmentType()
    {
        return self::ENVIRONMENT_DEVELOPMENT;
    }

    public static function isProductionEnvironment()
    {
        return self::getEnvironmentType() === self::ENVIRONMENT_PRODUCTION;
    }

    public static function getDatabaseUser()
    {
        return self::DATABASE_SETTINGS[self::getEnvironmentType()][self::DATABASE_PROPERTY_USER];
    }

    public static function getDatabasePassword()
    {
        return self::DATABASE_SETTINGS[self::getEnvironmentType()][self::DATABASE_PROPERTY_PASSWORD];
    }

    public static function getDatabaseName()
    {
        return self::DATABASE_SETTINGS[self::getEnvironmentType()][self::DATABASE_PROPERTY_NAME];
    }

    public static function getDatabaseHost()
    {
        return self::DATABASE_SETTINGS[self::getEnvironmentType()][self::DATABASE_PROPERTY_HOST];
    }

    // Пользователь просит показать данные странички "Edit students"
    public const student_get_students = "students";

    // Передаются значения полей формы "Поставить зачет"
    public const student_post_success = "sps";

    // Указание на вывод формы "Поставить зачет"
    public const student_get_success = "sgs";

    // Пользователь отправил значения формы "Поставить незачет"
    public const student_post_not_success = "spns";
    // Пользователь попросил вывести форму "Поставить незачет"
    public const student_get_not_success = "sgns";

    // Пользователь попросил вывести форму для добавления студента
    public const student_get_add = "sga";
    // Пришли данные от формы "Добавить студента"
    public const student_post_add = "spa";

    public const student_get_delete = "sgd";
    public const student_post_delete = "spd";

    public const student_get_edit = "sge";
    public const student_post_edit = "spe";

    public const problem_get_problems = "problems";
    public const problem_get_add = "pga";
    public const problem_post_add = "ppa";
    public const problem_get_id = "pgi";
    public const problem_get_edit = "pge";
    public const problem_post_edit = "ppe";
    public const problem_get_delete = "pgd";
}
