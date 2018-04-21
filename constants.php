<?php


class Constants
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
            self::DATABASE_PROPERTY_USER => 'homestead',
            self::DATABASE_PROPERTY_PASSWORD => 'secret',
            self::DATABASE_PROPERTY_NAME => 'homestead',
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
}
