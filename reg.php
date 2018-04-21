<?php
require_once "./constants.php";
require_once "./func.php";
require_once "./class.TemplatePower.inc.php";
require_once "./class.students.inc.php";

$tpl = new TemplatePower("./html/reg.htm");
$tpl->assignInclude("head", "./html/head_css.htm");
$tpl->assignInclude("foot", "./html/foot.htm");
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
        redirect($_SERVER['SCRIPT_NAME'], "Пустое поле");
    }
    if (!check_mail($new['Mail'])) {
        redirect($_SERVER['SCRIPT_NAME'], "Некорректный e-mail");
    }

    if ($ok) {
        $pass = gen_pass();
        $students->add($new['F'], $new['I'], $new['O'], $new['G'], $new['Mail'], $pass);
        mail(
            $new['Mail'],
            "Your password to access PDS",
            "Hello, " . translit($new['I']) . "!\nWelcome to Project Distribution System!\nYour password: $pass\n\n--\nwww.software.unn.ru:8888/ts/",
            join("\r\n", array(
                "From: oleg.skljarov@gmail.com",
                "Reply-To: oleg.skljarov@gmail.com"
            ))
        );
        redirect($_SERVER['SCRIPT_NAME'], "Студент добавлен");
    } else {
        redirect($_SERVER['SCRIPT_NAME'], "Ошибка: дублирование информации");
    }
}

/*
if( isset($_POST['edit']) )
{
	$ok = true;
	$new = $_POST;

	foreach($lines as $rec)
	{
		if($rec['id'] == $new['id']) continue;
		if(strcmp($new['F'], $rec['F']) == 0 &&
		   strcmp($new['I'], $rec['O']) == 0 &&
		   strcmp($new['O'], $rec['O']) == 0) $ok = false;

		if(strcmp($new['Mail'], $rec['Mail'])==0) $ok = false;
	}

	if( empty($new['F']) || empty($new['I']) || empty($new['O']) || empty($new['G']) || empty($new['Mail']) ) redirect($_SERVER['SCRIPT_NAME'], "Пустое поле");
	if( !check_mail($new['Mail']) ) redirect($_SERVER['SCRIPT_NAME'], "Некорректный e-mail");

	if(!check_id($new['id']))$ok = false;

	if($ok)
	{
		$students->edit($new['id'], $new['F'], $new['I'], $new['O'], $new['G'], $new['Mail']);
		redirect($_SERVER['SCRIPT_NAME'], "Сохранено");
	}
	else
	{
		redirect($_SERVER['SCRIPT_NAME'], "Ошибка: дублирование информации");
	}
}
*/

if (count($lines) > 0) {
    $tpl->newBlock("table_students");
    /*
    if( isset($_GET['delete']) )
    {
        if ( check_id($_GET['delete']) )
        {
            $students->delete($_GET['delete']);
            redirect($_SERVER['SCRIPT_NAME'], "Студент удалён");
        }
        else
        {
            redirect($_SERVER['SCRIPT_NAME'], "Bad request");
        }
    }
    */
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
        /*
        if( isset($_GET['edit']) && check_id($_GET['edit']) && $line['id'] == $_GET['edit'])
        {
            $tpl->newBlock("edit_row");
            $tpl->assign("script", $_SERVER['SCRIPT_NAME']);
            $tpl->assign("F", "F");
            $tpl->assign("I", "I");
            $tpl->assign("O", "O");
            $tpl->assign("G", "G");
            $tpl->assign("Mail", "Mail");
            $tpl->assign("id", "id");

            $tpl->assign("F_val", $line['F']);
            $tpl->assign("I_val", $line['I']);
            $tpl->assign("O_val", $line['O']);
            $tpl->assign("G_val", $line['G']);
            $tpl->assign("Mail_val", $line['Mail']);
            $tpl->assign("id_val", $line['id']);
            $tpl->assign("post_name", "edit");
        }
        else
        */
        {
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
