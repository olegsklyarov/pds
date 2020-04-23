<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Connection;
use App\Constants;
use App\Problems;
use App\Students;
use App\TemplatePower;
use App\Utils;

if (!Constants::isProductionEnvironment()) {
    ini_set("session.use_trans_sid", true);
    ini_set("session.use_cookies", true);
}

session_start();
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    Utils::redirect($_SERVER['SCRIPT_NAME'], "Good bye!");
}


##Fill $GLOBALS['md5_admin_login'] ,  $GLOBALS['md5_admin_password']
if (!Utils::HaveAdmin()) {
    Utils::redirect("./install.php", "Run <b>Install Script</b> first");
}


if (isset($_SESSION['admin'])) {
    if (strcmp($_SESSION['md5md5login'], md5($GLOBALS['md5_admin_login'])) == 0 &&
        strcmp($_SESSION['md5md5password'], md5($GLOBALS['md5_admin_password'])) == 0) {

        $tpl = new TemplatePower('admin.htm');
        $tpl->assignInclude("head", __DIR__ . '/../src/templates/head_css.htm');
        $tpl->assignInclude("foot", __DIR__ . '/../src/templates/foot.htm');
        $tpl->prepare();
        $tpl->newBlock("logged");
        $tpl->assign("admin_login", $_SESSION['username']);

        $tpl->newBlock("menu");
        $tpl->assign("url", $_SERVER['SCRIPT_NAME']);
        $tpl->assign("paramstr", Constants::student_get_students);
        $tpl->assign("caption", "Edit students");

        $tpl->newBlock("menu");
        $tpl->assign("url", $_SERVER['SCRIPT_NAME']);
        $tpl->assign("paramstr", "problems");
        $tpl->assign("caption", "Edit problems");

        $tpl->newBlock("menu");
        $tpl->assign("url", $_SERVER['SCRIPT_NAME']);
        $tpl->assign("paramstr", "edit_admin");
        $tpl->assign("caption", "Edit admin");

        $tpl->newBlock("menu");
        $tpl->assign("url", $_SERVER['SCRIPT_NAME']);
        $tpl->assign("paramstr", "logout");
        $tpl->assign("caption", "Logout");


        if (isset($_POST['admin_edit'])) {
            if (strcmp(md5($_POST['oldpassword']), $GLOBALS['md5_admin_password']) == 0 && strcmp($_POST['newpassword'],
                    $_POST['confirmpassword']) == 0) {
                Connection::getInstance()->update(Utils::sql_query_table(
                    "UPDATE %s SET Login = '" . md5($_POST['newlogin']) . "', PASSWORD = '" . md5($_POST['newpassword']) . "' WHERE id=1 LIMIT 1",
                    Constants::DATABASE_TABLE_ADMIN));
                Utils::redirect($_SERVER['SCRIPT_NAME'] . "?logout", "Администратор изменен");
            } else {
                Utils::redirect($_SERVER['SCRIPT_NAME'], "Ошибка");
            }
        }


        ## POST: Students
        if (isset($_POST[Constants::student_post_add])) {
            $pass = Utils::gen_pass();
            $students = new Students();
            $students->add($_POST['f'], $_POST['i'], $_POST['o'], $_POST['g'], $_POST['mail'], $pass);
            Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::student_get_students, "Студент добавлен");
        }

        if (isset($_POST[Constants::student_post_edit])) {
            $students = new Students();
            $students->edit($_POST['id'], $_POST['f'], $_POST['i'], $_POST['o'], $_POST['g'], $_POST['mail']);
            Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::student_get_students, "Изменения сохранены");
        }


        ## POST: Problems
        if (isset($_POST[Constants::problem_post_add])) {
            $problems = new Problems();
            $problems->add($_POST['Caption'], $_POST['FAQ'], $_POST['FullDescription'], $_POST['TeamSize']);
            Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::problem_get_problems, "Задача добавлена");
        }


        if (isset($_POST[Constants::problem_post_edit])) {
            $problems = new Problems();
            $problems->update($_POST['id'], $_POST['Caption'], $_POST['FAQ'], $_POST['FullDescription'],
                $_POST['TeamSize']);
            Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::problem_get_problems, "Задача изменена");
        }


        ## Students
        if (isset($_GET[Constants::student_get_students])) {
            $students = new Students();
            if (isset($_GET[Constants::student_get_delete])) {
                $students->delete($_GET[Constants::student_get_delete]);
                Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::student_get_students, "Студент удален");
            }

            if (isset($_GET[Constants::student_get_success])) {
                $students->success($_GET[Constants::student_get_success]);
                Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::student_get_students, "Зачет поставлен");
            }

            if (isset($_GET[Constants::student_get_not_success])) {
                $students->not_success($_GET[Constants::student_get_not_success]);
                Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::student_get_students, "Незачет поставлен");
            }


            if (isset($_GET['mail_to'])) {
                if (Utils::check_id($_GET['mail_to'])) {
                    $id = $_GET['mail_to'];

                    $students = new Students();
                    $pass = Utils::gen_pass();
                    $students->edit_pass($id, $pass);

                    $cur_student = $students->get_by_id($id);
                    mail(
                        $cur_student['Mail'],
                        "Your password to access PDS",
                        "Hello, " . Utils::translit($cur_student['I']) . "!\nWelcome to Project Distribution System!\nYour password: $pass\n\n--\nwww.software.unn.ru:8888/ts/",
                        join("\r\n", array(
                            "From: oleg.skljarov@gmail.com",
                            "Reply-To: oleg.skljarov@gmail.com"
                        ))
                    );
                    Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::student_get_students, "Пароль изменен");
                } else {
                    Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::student_get_students, "Wrong argument");
                }
            }

            if (isset($_GET['mail_to_all'])) {
                $students = new Students();
                $lines = $students->get_all();

                foreach ($lines as $s) {
                    $pass = Utils::gen_pass();
                    $students->edit_pass($s['id'], $pass);
                    mail(
                        $s['Mail'],
                        "Your password to access PDS",
                        "Hello, " . Utils::translit($s['I']) . "!\nWelcome to Project Distribution System!\nYour password: $pass\n\n--\nwww.software.unn.ru:8888/ts/",
                        join("\r\n", array(
                            "From: oleg.skljarov@gmail.com",
                            "Reply-To: oleg.skljarov@gmail.com"
                        ))
                    );
                }
                Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::student_get_students, "Почта отправлена");
            }

            $tpl->newBlock("students");

            $tpl->newBlock("menu_students");
            $tpl->assign("url", $_SERVER['SCRIPT_NAME']);
            $tpl->assign("paramstr", Constants::student_get_students . "&" . Constants::student_get_add);
            $tpl->assign("caption", "Добавить студента");

            $tpl->newBlock("menu_students");
            $tpl->assign("url", $_SERVER['SCRIPT_NAME']);
            $tpl->assign("paramstr", Constants::student_get_students . "&mail_to_all");
            $tpl->assign("caption", "Выслать пароли");

            $lines = $students->get_all();

            if (count($lines) > 0) {
                $tpl->newBlock("table_students");
                if (isset($_GET[Constants::student_get_add])) {
                    $tpl->newBlock("edit_row");
                    $tpl->assign("script", $_SERVER['SCRIPT_NAME']);
                    $tpl->assign("f", "f");
                    $tpl->assign("i", "i");
                    $tpl->assign("o", "o");
                    $tpl->assign("g", "g");
                    $tpl->assign("mail", "mail");
                    $tpl->assign("post_name", Constants::student_post_add);
                }
                for ($i = 0; $i < count($lines); $i++) {
                    if (isset($_GET[Constants::student_get_edit]) && $lines[$i]['id'] == $_GET[Constants::student_get_edit]) {
                        $tpl->newBlock("edit_row");
                        $tpl->assign("script", $_SERVER['SCRIPT_NAME']);
                        $tpl->assign("f", "f");
                        $tpl->assign("i", "i");
                        $tpl->assign("o", "o");
                        $tpl->assign("g", "g");
                        $tpl->assign("mail", "mail");
                        $tpl->assign("id", "id");

                        $tpl->assign("f_val", $lines[$i]['F']);
                        $tpl->assign("i_val", $lines[$i]['I']);
                        $tpl->assign("o_val", $lines[$i]['O']);
                        $tpl->assign("g_val", $lines[$i]['G']);
                        $tpl->assign("id_val", $lines[$i]['id']);
                        $tpl->assign("mail_val", $lines[$i]['Mail']);

                        $tpl->assign("post_name", Constants::student_post_edit);
                    } else {
                        $tpl->newBlock("row_students");
                        $tpl->assign("no", $i + 1);
                        $tpl->assign("class", ($i % 2) ? "x" : "y");
                        $tpl->assign("f", $lines[$i]['F']);
                        $tpl->assign("i", $lines[$i]['I']);
                        $tpl->assign("o", $lines[$i]['O']);
                        $tpl->assign("g", $lines[$i]['G']);
                        $tpl->assign("chpass",
                            "<a href=\"" . $_SERVER['SCRIPT_NAME'] . "?" . Constants::student_get_students . "&mail_to=" . $lines[$i]['id'] . "\">chpass</a>");
                        $tpl->assign("edit_row",
                            "<a href=\"" . $_SERVER['SCRIPT_NAME'] . "?" . Constants::student_get_students . "&" . Constants::student_get_edit . "=" . $lines[$i]['id'] . "\">Edit</a>");
                        $tpl->assign("final",
                            ($lines[$i]['Success'] == 0) ? "<font color=\"red\">Незачет</font> <a href=\"" . $_SERVER['SCRIPT_NAME'] . "?" . Constants::student_get_students . "&" . Constants::student_get_success . "=" . $lines[$i]['id'] . "\">Ok</a>" : "<font color=\"green\">Зачет</font> <a href=\"" . $_SERVER['SCRIPT_NAME'] . "?" . Constants::student_get_students . "&" . Constants::student_get_not_success . "=" . $lines[$i]['id'] . "\">X</a>");
                        $tpl->assign("delete",
                            "<a href=\"" . $_SERVER['SCRIPT_NAME'] . "?" . Constants::student_get_students . "&" . Constants::student_get_delete . "=" . $lines[$i]['id'] . "\">Delete</a>");
                    }
                }
            } else {
                $tpl->newBlock("no_table_students");

                if (isset($_GET[Constants::student_get_add])) {
                    $tpl->newBlock("table_students");
                    $tpl->newBlock("edit_row");
                    $tpl->assign("script", $_SERVER['SCRIPT_NAME']);
                    $tpl->assign("f", "f");
                    $tpl->assign("i", "i");
                    $tpl->assign("o", "o");
                    $tpl->assign("g", "g");
                    $tpl->assign("mail", "mail");
                    $tpl->assign("post_name", Constants::student_post_add);
                }
            }
        } ## Problems
        else if (isset($_GET[Constants::problem_get_problems])) {
            $problems = new Problems();
            $tpl->newBlock("problems");

            $tpl->newBlock("menu_problems");
            $tpl->assign("url", $_SERVER['SCRIPT_NAME']);
            $tpl->assign("paramstr", Constants::problem_get_problems . "&" . Constants::problem_get_add);
            $tpl->assign("caption", "Добавить задачу");

            if (isset($_GET[Constants::problem_get_add])) {
                $tpl->newBlock("add_edit");
                $tpl->assign("script", $_SERVER['SCRIPT_NAME']);
                $tpl->assign("caption", "Caption");
                $tpl->assign("teamsize", "TeamSize");
                $tpl->assign("faq", "FAQ");
                $tpl->assign("fulldescription", "FullDescription");
                $tpl->assign("post_name", Constants::problem_post_add);
            } else if (isset($_GET[Constants::problem_get_edit])) {
                if (Utils::check_id($_GET[Constants::problem_get_edit])) {
                    $line = $problems->get_by_id($_GET[Constants::problem_get_edit]);
                    $tpl->newBlock("add_edit");
                    $tpl->assign("script", $_SERVER['SCRIPT_NAME']);
                    $tpl->assign("caption", "Caption");
                    $tpl->assign("teamsize", "TeamSize");
                    $tpl->assign("faq", "FAQ");
                    $tpl->assign("fulldescription", "FullDescription");

                    $tpl->assign("post_name", Constants::problem_post_edit);
                    $tpl->assign("caption_val", $line['Caption']);
                    $tpl->assign("faq_val", $line['FAQ']);
                    $tpl->assign("fulldescription_val", $line['FullDescription']);
                    $tpl->assign("selected" . $line['TeamSize'], "selected");
                    $tpl->assign("id", "id");
                    $tpl->assign("id_val", $line['id']);
                } else {
                    Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::problem_get_problems, "Wrong argument");
                }
            } else if (isset($_GET[Constants::problem_get_id])) {
                if (Utils::check_id($_GET[Constants::problem_get_id])) {
                    $line = $problems->get_by_id($_GET[Constants::problem_get_id]);
                    $tpl->newBlock("more");
                    $tpl->assign("Caption", $line['Caption']);
                    $tpl->assign("FAQ", $line['FAQ']);
                    $tpl->assign("FullDescription", $line['FullDescription']);
                    $tpl->assign("TeamSize", $line['TeamSize']);

                    $team = $problems->get_team($_GET[Constants::problem_get_id]);
                    $teammembers = "";
                    foreach ($team as $i => $line) {
                        $teammembers .= trim($line['F'] . " " . $line['I'] . " " . $line['O']) . "<br>";
                    }
                    $tpl->assign("TeamMembers", $teammembers);
                } else {
                    Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::problem_get_problems, "Wrong argument");
                }
            } else if (isset($_GET[Constants::problem_get_delete])) {
                if (Utils::check_id($_GET[Constants::problem_get_delete])) {
                    $problems->delete($_GET[Constants::problem_get_delete]);
                    Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::problem_get_problems, "Задача удалена");
                } else {
                    Utils::redirect($_SERVER['SCRIPT_NAME'] . "?" . Constants::problem_get_problems, "Wrong argument");
                }
            } else {
                $lines = $problems->get_all();
                if (count($lines) > 0) {
                    $tpl->newBlock("table");
                    foreach ($lines as $i => $line) {
                        $tpl->newBlock("row");
                        $tpl->assign("no", $i + 1);
                        $tpl->assign("Caption", $line['Caption']);
                        $tpl->assign("TeamSize", $line['TeamSize']);
                        $tpl->assign("FAQ", $line['FAQ']);
                        $tpl->assign("url_more",
                            $_SERVER['SCRIPT_NAME'] . "?" . Constants::problem_get_problems . "&" . Constants::problem_get_id . "=" . $line['id']);
                        $tpl->assign("url_edit",
                            $_SERVER['SCRIPT_NAME'] . "?" . Constants::problem_get_problems . "&" . Constants::problem_get_edit . "=" . $line['id']);
                        $tpl->assign("url_delete",
                            $_SERVER['SCRIPT_NAME'] . "?" . Constants::problem_get_problems . "&" . Constants::problem_get_delete . "=" . $line['id']);
                        $tpl->assign("FullDescription", $line['FullDescription']);
                    }
                } else {
                    $tpl->newBlock("no_table");
                }
            }


        } ## Admin Edit
        else if (isset($_GET['edit_admin'])) {
            $tpl->newBlock("edit_admin");
            $tpl->assign("script", $_SERVER['SCRIPT_NAME']);
        }
        $tpl->printToScreen();
        die("");
    } else {
        die('Не тестировалось!');
    }
}


## Authorization
if (!isset($_POST['admin_login'])) {
    $t = new TemplatePower('admin.htm');
    $t->assignInclude("head", __DIR__ . '/../src/templates/head.htm');
    $t->assignInclude("foot", __DIR__ . '/../src/templates/foot.htm');
    $t->prepare();
    $t->newBlock("unknown_user");
    $t->printToScreen();
    die("");
} else {
    $t = new TemplatePower('redirect.htm');
    $t->prepare();
    $t->assign("url", "{$_SERVER['SCRIPT_NAME']}");

    if (strcmp($GLOBALS['md5_admin_login'], md5($_POST['login'])) == 0 && strcmp($GLOBALS['md5_admin_password'],
            md5($_POST['password'])) == 0) {
        $_SESSION['admin'] = true;
        $_SESSION['username'] = $_POST['login'];
        $_SESSION['md5md5login'] = md5($GLOBALS['md5_admin_login']);
        $_SESSION['md5md5password'] = md5($GLOBALS['md5_admin_password']);
        $t->assign("message", "Please, wait");
    } else {
        $t->assign("message", "Wrong login or password");
    }
    $t->printToScreen();
    die("");
}
