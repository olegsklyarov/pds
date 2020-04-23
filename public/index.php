<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Constants;
use App\Problems;
use App\TemplatePower;
use App\Students;
use App\Utils;

if (!Constants::isProductionEnvironment()) {
    ini_set("session.use_trans_sid", true);
    ini_set("session.use_cookies", true);
}

session_start();
if (isset($_GET['logout'])) {
    $_SESSION = array();
    ## @unset($_COOKIE[session_name()]);
    session_destroy();
    Utils::redirect($_SERVER['SCRIPT_NAME'], "Good bye!");
}

if (isset($_SESSION['logged'])) {
    if (Utils::check_id($_SESSION['id'])) {
        $s = new Students;
        $GLOBALS['cur_student'] = $s->get_by_id($_SESSION['id']);
        unset($s);

        if (strcmp($GLOBALS['cur_student']['Password'], $_SESSION['Password']) == 0) {

            $tpl = new TemplatePower('index.htm');
            $tpl->assignInclude('head', __DIR__ . '/../src/templates/head.htm');
            $tpl->assignInclude('foot', __DIR__ . '/../src/templates/foot.htm');
            $tpl->prepare();

            $tpl->newBlock("logged");
            $tpl->assign("username", $GLOBALS['cur_student']['I'] . " " . $GLOBALS['cur_student']['F']);
            $tpl->assign("url_exit", $_SERVER['SCRIPT_NAME'] . "?logout");
            if ($GLOBALS['cur_student']['Task_id'] == -1) {
                $tpl->assign("project", "Не выбран");
            } else {
                $p = new Problems;
                $cur_task = $p->get_by_id($GLOBALS['cur_student']['Task_id']);
                $tpl->assign("project",
                    "<a href={$_SERVER['SCRIPT_NAME']}?more={$cur_task['id']}>{$cur_task['Caption']}</a>");
            }
            if ($GLOBALS['cur_student']['Success'] == 1 && $GLOBALS['cur_student']['Task_id'] != -1) {
                $tpl->newBlock("success");
            } else {
                $tpl->newBlock("not_success");
            }


            if (isset($_GET['join'])) {
                if (Utils::check_id($_GET['join'])) {
                    $p = new Problems;
                    $s = new Students;

                    $cur_problem = $p->get_by_id($_GET['join']);
                    $team = $s->get_by_task($cur_problem['id']);

                    if (($cur_problem['TeamSize'] - count($team) > 0) && ($GLOBALS['cur_student']['Success'] != 1)) {
                        $s->chtask($GLOBALS['cur_student']['id'], $cur_problem['id']);
                        Utils::redirect($_SERVER['SCRIPT_NAME'], "Задача выбрана");
                    } else {
                        Utils::redirect($_SERVER['SCRIPT_NAME'], "Уже нельзя");
                    }
                } else {
                    Utils::redirect($_SERVER['SCRIPT_NAME'], "Wrong argument");
                }
            } else if (isset($_GET['more'])) {
                if (Utils::check_id($_GET['more'])) {
                    $p = new Problems;
                    $s = new Students;

                    $cur_problem = $p->get_by_id($_GET['more']);

                    $tpl->newBlock("more");
                    $tpl->assign("Caption", $cur_problem['Caption']);
                    $tpl->assign("FAQ", $cur_problem['FAQ']);
                    $tpl->assign("FullDescription", $cur_problem['FullDescription']);

                    $tpl->assign("TeamSize", $cur_problem['TeamSize']);

                    $team = $s->get_by_task($cur_problem['id']);
                    $names = "";
                    foreach ($team as $j => $member) {
                        $names = $names . $member['I'] . " " . $member['F'] . "<br>";
                    }

                    $tpl->assign("team", $names);
                    $tpl->assign("need", $cur_problem['TeamSize'] - count($team));

                    $tpl->assign("url_back", $_SERVER['SCRIPT_NAME']);

                    if ($cur_problem['TeamSize'] - count($team) > 0 && $GLOBALS['cur_student']['Success'] != 1) {
                        $tpl->newBlock("join");
                        $tpl->assign("url_join", $_SERVER['SCRIPT_NAME'] . "?join=" . $cur_problem['id']);
                    }
                } else {
                    Utils::redirect($_SERVER['SCRIPT_NAME'], "Wrong argument");
                }
            } else {
                $tpl->newBlock("table");
                $p = new Problems;
                $s = new Students;

                $lines = $p->get_all();
                foreach ($lines as $i => $line) {
                    $tpl->newBlock("row");
                    $tpl->assign("no", $i + 1);
                    $tpl->assign("Caption", $line['Caption']);
                    $tpl->assign("TeamSize", $line['TeamSize']);
                    $team = $s->get_by_task($line['id']);
                    $names = "";
                    foreach ($team as $j => $member) {
                        $names = $names . $member['I'] . " " . $member['F'] . "<br>";
                    }
                    $tpl->assign("team", $names);
                    $need = $line['TeamSize'] - count($team);
                    $tpl->assign("need", $need);
                    $tpl->assign("FAQ", $line['FAQ']);
                    $tpl->assign("url_more", $_SERVER['SCRIPT_NAME'] . "?more=" . $line['id']);
                }
            }
            $tpl->printToScreen();
        } else {
            Utils::redirect($_SERVER['SCRIPT_NAME'] . "?logout", "Bad cookies");
        }
    }
} else if (isset($_POST['login'])) ## Authorization
{
    $t = new TemplatePower('redirect.htm');
    $t->prepare();
    $t->assign("url", $_SERVER['SCRIPT_NAME']);

    $s = new Students;

    if (Utils::check_id($_POST['id'])) {
        $GLOBALS['cur_student'] = $s->get_by_id($_POST['id']);

        if (strcmp($GLOBALS['cur_student']['Password'], md5($_POST['Password'])) == 0) {
            $_SESSION['logged'] = true;
            $_SESSION['id'] = $_POST['id'];
            $_SESSION['Password'] = $GLOBALS['cur_student']['Password'];

            $t->assign("message", "Please, wait");
        } else {
            $t->assign("message", "Wrong login or password");
        }
        $t->printToScreen();
    } else {
        Utils::redirect($_SERVER['SCRIPT_NAME'], "Bad request");
    }
} else {
    $t = new TemplatePower('index.htm');
    $t->assignInclude('head', __DIR__ . '/../src/templates/head.htm');
    $t->assignInclude('foot', __DIR__ . '/../src/templates/foot.htm');
    $t->prepare();

    $s = new Students;
    $students = $s->get_all();
    unset($s);

    $t->newBlock("unknown_user");
    $t->assign("id", "id");
    foreach ($students as $i => $line) {
        $t->newBlock("next_mail");

        $t->assign("id_val", $line['id']);
        $t->assign("Mail", $line['F'] . " " . $line['I']);
    }
    $t->printToScreen();
}
