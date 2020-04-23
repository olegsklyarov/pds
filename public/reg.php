<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Students;
use App\TemplatePower;
use App\Utils;

$tpl = new TemplatePower('reg.htm');
$tpl->assignInclude("head", __DIR__ . '/../src/templates/head_css.htm');
$tpl->assignInclude("foot", __DIR__ . '/../src/templates/foot.htm');
$tpl->prepare();

$students = new Students();

$lines = $students->get_all();

if (isset($_POST['add'])) {
    $ok = true;
    $new = $_POST;

    foreach ($lines as $rec) {
        if (strcmp($new['F'], $rec['F']) == 0 &&
            strcmp($new['I'], $rec['I']) == 0 &&
            strcmp($new['O'], $rec['O']) == 0) {
            $ok = false;
        }

        if (strcmp($new['Mail'], $rec['Mail']) == 0) {
            $ok = false;
        }
    }

    if (empty($new['F']) || empty($new['I']) || empty($new['O']) || empty($new['G']) || empty($new['Mail'])) {
        Utils::redirect($_SERVER['SCRIPT_NAME'], "Пустое поле");
    }
    if (!Utils::check_mail($new['Mail'])) {
        Utils::redirect($_SERVER['SCRIPT_NAME'], "Некорректный e-mail");
    }

    if ($ok) {
        $pass = Utils::gen_pass();
        $students->add($new['F'], $new['I'], $new['O'], $new['G'], $new['Mail'], $pass);
        mail(
            $new['Mail'],
            "Your password to access PDS",
            "Hello, " . Utils::translit($new['I']) . "!\nWelcome to Project Distribution System!\nYour password: $pass\n\n--\nwww.software.unn.ru:8888/ts/",
            join("\r\n", array(
                "From: oleg.skljarov@gmail.com",
                "Reply-To: oleg.skljarov@gmail.com"
            ))
        );
        Utils::redirect($_SERVER['SCRIPT_NAME'], "Студент добавлен");
    } else {
        Utils::redirect($_SERVER['SCRIPT_NAME'], "Ошибка: дублирование информации");
    }
}


if (count($lines) > 0) {
    $tpl->newBlock("table_students");

    if (isset($_GET['add'])) {
        $tpl->newBlock("edit_row");
        $tpl->assign("script", $_SERVER['SCRIPT_NAME']);
        $tpl->assign("F", "F");
        $tpl->assign("I", "I");
        $tpl->assign("O", "O");
        $tpl->assign("G", "G");
        $tpl->assign("Mail", "Mail");
        $tpl->assign("post_name", "add");
    }

    foreach ($lines as $i => $line) {
        $tpl->newBlock("row_students");
        $tpl->assign("no", $i + 1);
        $tpl->assign("F", $line['F']);
        $tpl->assign("I", $line['I']);
        $tpl->assign("O", $line['O']);
        $tpl->assign("G", $line['G']);
        $tpl->assign("Mail", $line['Mail']);
        if (!($i % 2)) {
            $tpl->assign("class", "x");
        } else {
            $tpl->assign("class", "y");
        }
        $tpl->assign("edit", "<a href=\"" . $_SERVER['SCRIPT_NAME'] . "?edit=" . $line['id'] . "\">Edit</a>");
        $tpl->assign("delete", "<a href=\"" . $_SERVER['SCRIPT_NAME'] . "?delete=" . $line['id'] . "\">Delete</a>");
    }
} else {
    $tpl->newBlock("no_table_students");

    if (isset($_GET['add'])) {
        $tpl->newBlock("table_students");
        $tpl->newBlock("edit_row");
        $tpl->assign("script", $_SERVER['SCRIPT_NAME']);
        $tpl->assign("F", "F");
        $tpl->assign("I", "I");
        $tpl->assign("O", "O");
        $tpl->assign("G", "G");
        $tpl->assign("Mail", "Mail");
        $tpl->assign("post_name", "add");
    }
}
$tpl->printToScreen();
