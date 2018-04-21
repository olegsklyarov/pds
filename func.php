<?php

require_once 'constants.php';
require_once 'connect.php';


/**
 * @param string $query
 * @param string $table
 *
 * @return string
 */
function sql_query_table(string $query, string $table)
{
    return str_replace('%s', $table, $query);
}


function HaveAdmin()
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


function gen_pass()
{
    $len = 7;
    $base = 'ABCDEFGHKLMNOPQRSTWXYZabcdefghjkmnpqrstwxyz123456789';
    $max = strlen($base) - 1;
    $activatecode = '';
    mt_srand((double)microtime() * 1000000);
    while (strlen($activatecode) < $len + 1) {
        $activatecode .= $base{mt_rand(0, $max)};
    }

    return $activatecode;
}


function redirect($url, $message)
{
    require_once "./class.TemplatePower.inc.php";
    $t = new TemplatePower("./html/redirect.htm");
    $t->prepare();
    $t->assign("url", $url);
    $t->assign("message", $message);
    $t->printToScreen();
    die("");
}


function check_id($param)
{
    if (preg_match("/^[0-9]+$/", $param)) {
        return true;
    } else {
        return false;
    }
}

function translit($st)
{
    // Сначала заменяем "односимвольные" фонемы.
    $st = strtr($st, "абвгдеёзийклмнопрстуфхъыэ_", "abvgdeeziyklmnoprstufh'iei");
    $st = strtr($st, "АБВГДЕЁЗИЙКЛМНОПРСТУФХЪЫЭ_", "ABVGDEEZIYKLMNOPRSTUFH'IEI");

    // Затем - "многосимвольные".
    $st = strtr($st,
        array(
            "ж" => "zh",
            "ц" => "ts",
            "ч" => "ch",
            "ш" => "sh",
            "щ" => "shch",
            "ь" => "",
            "ю" => "yu",
            "я" => "ya",
            "Ж" => "ZH",
            "Ц" => "TS",
            "Ч" => "CH",
            "Ш" => "SH",
            "Щ" => "SHCH",
            "Ь" => "",
            "Ю" => "YU",
            "Я" => "YA",
            "ї" => "i",
            "Ї" => "Yi",
            "є" => "ie",
            "Є" => "Ye"
        )
    );

    return $st;
}

function check_mail($email)
{
    return !!filter_var($email, FILTER_VALIDATE_EMAIL);
}
