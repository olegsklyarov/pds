<?php

namespace App;

final class Utils
{
    public static function sql_query_table(string $query, string $table): string
    {
        return str_replace('%s', $table, $query);
    }


    public static function HaveAdmin()
    {
        $data = Connection::getInstance()->select(sprintf(
                "SELECT * FROM %s LIMIT 1",
                Constants::DATABASE_TABLE_ADMIN)
        );
        $res = count($data);

        if ($res == 0) {
            return false;
        } else if ($res == 1) {
            $GLOBALS['md5_admin_login'] = $data[0]['Login'];
            $GLOBALS['md5_admin_password'] = $data[0]['Password'];

            return true;
        } else {
            die("Initial error: more than one record in admin table!");
        }
    }


    public static function gen_pass()
    {
        $len = 7;
        $base = 'ABCDEFGHKLMNOPQRSTWXYZabcdefghjkmnpqrstwxyz123456789';
        $max = strlen($base) - 1;
        $activatecode = '';
        mt_srand((double)microtime() * 1000000);
        while (strlen($activatecode) < $len + 1) {
            $activatecode .= $base[mt_rand(0, $max)];
        }

        return $activatecode;
    }


    public static function redirect($url, $message)
    {
        $t = new TemplatePower('redirect.htm');
        $t->prepare();
        $t->assign("url", $url);
        $t->assign("message", $message);
        $t->printToScreen();
        die("");
    }

    public static function check_id($param)
    {
        if (preg_match("/^[0-9]+$/", $param)) {
            return true;
        } else {
            return false;
        }
    }

    public static function translit($st)
    {
        return transliterator_transliterate('Cyrillic-Latin', $st);
    }

    public static function check_mail($email)
    {
        return !!filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
