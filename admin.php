<?php
require_once "consts.php";

if(!on_server) {
	ini_set("session.use_trans_sid", true);
	ini_set("session.use_cookies", true);
}

require_once "func.php";
require_once "./class.TemplatePower.inc.php";

session_start();
if ( isset($_GET['logout']) ) {
	$_SESSION = array();
	## @unset($_COOKIE[session_name()]);
	session_destroy();
	redirect($_SERVER['SCRIPT_NAME'], "Good bye!");
}





##Fill $GLOBALS['md5_admin_login'] ,  $GLOBALS['md5_admin_password']
if (!HaveAdmin())
{
	redirect("./install.php", "Run <b>Install Script</b> first");
}



if( isset($_SESSION['admin']) )
{
	if( strcmp($_SESSION['md5md5login'], md5( $GLOBALS['md5_admin_login'] ))==0 &&
	    strcmp($_SESSION['md5md5password'], md5( $GLOBALS['md5_admin_password'] ))==0)
	{
		require_once "class.students.inc.php";
		require_once "class.problems.inc.php";

		$tpl = new TemplatePower("./html/admin.htm");
		$tpl->assignInclude("head", "./html/head_css.htm");
		$tpl->assignInclude("foot", "./html/foot.htm");
		$tpl->prepare();
		$tpl->newBlock("logged");
		$tpl->assign("admin_login", $_SESSION['username']);

		$tpl->newBlock("menu");
		$tpl->assign("url", $_SERVER['SCRIPT_NAME']); $tpl->assign("paramstr", student_get_students); $tpl->assign("caption", "Edit students");

		$tpl->newBlock("menu");
		$tpl->assign("url", $_SERVER['SCRIPT_NAME']); $tpl->assign("paramstr", "problems"); $tpl->assign("caption", "Edit problems");

		$tpl->newBlock("menu");
		$tpl->assign("url", $_SERVER['SCRIPT_NAME']); $tpl->assign("paramstr", "edit_admin"); $tpl->assign("caption", "Edit admin");

		$tpl->newBlock("menu");
		$tpl->assign("url", $_SERVER['SCRIPT_NAME']); $tpl->assign("paramstr", "logout"); $tpl->assign("caption", "Logout");









		if( isset($_POST['admin_edit']))
		{
			if( strcmp(md5($_POST['oldpassword']), $GLOBALS['md5_admin_password']) == 0 && strcmp($_POST['newpassword'], $_POST['confirmpassword']) == 0)
			{
				require_once "./connect.php";
				mysql_query("UPDATE {$GLOBALS['admin_table']} SET Login = '". md5($_POST['newlogin']) ."', Password = '". md5($_POST['newpassword']) ."' WHERE id=1 LIMIT 1") or die("MySQL error: ". mysql_error());
				redirect($_SERVER['SCRIPT_NAME'] ."?logout", "Администратор изменен");
			}
			else
			{
				redirect($_SERVER['SCRIPT_NAME'], "Ошибка");
			}
		}


		## POST: Studentss
		if( isset($_POST[student_post_add]) )
		{
			$pass = gen_pass();
			$students = new Students();
			$students->add($_POST['f'], $_POST['i'], $_POST['o'], $_POST['g'], $_POST['mail'], $pass);
			redirect($_SERVER['SCRIPT_NAME'] ."?". student_get_students, "Студент добавлен");
		}

		if( isset($_POST[student_post_edit]) )
		{
			$students = new Students();
			$students->edit($_POST['id'], $_POST['f'], $_POST['i'], $_POST['o'], $_POST['g'], $_POST['mail']);
			redirect($_SERVER['SCRIPT_NAME'] ."?". student_get_students, "Изменения сохранены");
		}









		## POST: Problems
		if( isset($_POST[problem_post_add]) )
		{
			$problems = new Problems();
			$problems->add($_POST['Caption'], $_POST['FAQ'], $_POST['FullDiscription'], $_POST['TeamSize']);
			redirect($_SERVER['SCRIPT_NAME'] ."?". problem_get_problems, "Задача добавлена");
		}


		if( isset($_POST[problem_post_edit]) )
		{
			$problems = new Problems();
			$problems->update($_POST['id'], $_POST['Caption'], $_POST['FAQ'], $_POST['FullDiscription'], $_POST['TeamSize']);
			redirect($_SERVER['SCRIPT_NAME'] ."?". problem_get_problems, "Задача изменена");
		}





		## Students
		if( isset($_GET[student_get_students]) )
		{
			$students = new Students();
			if( isset($_GET[student_get_delete]) )
			{
				$students->delete($_GET[student_get_delete]);
				redirect($_SERVER['SCRIPT_NAME'] ."?". student_get_students, "Студент удален");
			}

			if( isset($_GET[student_get_success]) )
			{
				$students->success( $_GET[student_get_success] );
				redirect($_SERVER['SCRIPT_NAME'] ."?". student_get_students, "Зачет поставлен");
			}

			if( isset($_GET[student_get_not_success]) )
			{
				$students->not_success( $_GET[student_get_not_success] );
				redirect($_SERVER['SCRIPT_NAME'] ."?". student_get_students, "Незачет поставлен");
			}


			if( isset($_GET['mail_to']) )
			{
				if( check_id($_GET['mail_to']) )
				{
					$id = $_GET['mail_to'];

					$students = new Students();
					$pass = gen_pass();
					$students->edit_pass($id, $pass);

					$cur_student = $students->get_by_id($id);
					mail(
						$cur_student['Mail'],
						"Your password to access PDS",
						"Hello, ". translit($cur_student['I']). "!\nWelcome to Project Distribution System!\nYour password: $pass\n\n--\nwww.software.unn.ru:8888/ts/",
						join("\r\n", array(
							"From: oleg.skljarov@gmail.com",
							"Reply-To: oleg.skljarov@gmail.com"
						))
					);
					redirect($_SERVER['SCRIPT_NAME'] ."?". student_get_students, "Пароль изменен");
				}
				else
				{
					redirect($_SERVER['SCRIPT_NAME'] ."?". student_get_students, "Wrong argument");
				}
			}

			if( isset($_GET['mail_to_all']) )
			{
				$students = new Students();
				$lines = $students->get_all();

				foreach ($lines as $s)
				{
					$pass = gen_pass();
					$students->edit_pass($s['id'], $pass);
					mail(
						$s['Mail'],
						"Your password to access PDS",
						"Hello, ". translit($s['I']). "!\nWelcome to Project Distribution System!\nYour password: $pass\n\n--\nwww.software.unn.ru:8888/ts/",
						join("\r\n", array(
							"From: oleg.skljarov@gmail.com",
							"Reply-To: oleg.skljarov@gmail.com"
						))
					);
				}
				redirect($_SERVER['SCRIPT_NAME'] ."?". student_get_students, "Почта отправлена");
			}

			$tpl->newBlock("students");

			$tpl->newBlock("menu_students");
			$tpl->assign("url", $_SERVER['SCRIPT_NAME']); $tpl->assign("paramstr", student_get_students ."&". student_get_add ); $tpl->assign("caption", "Добавить студента");

			$tpl->newBlock("menu_students");
			$tpl->assign("url", $_SERVER['SCRIPT_NAME']); $tpl->assign("paramstr", student_get_students ."&mail_to_all"); $tpl->assign("caption", "Выслать пароли");

			$lines = $students->get_all();

			if (count($lines)>0)
			{
				$tpl->newBlock("table_students");
				if (isset($_GET[student_get_add]))
				{
					$tpl->newBlock("edit_row");
					$tpl->assign("script", $_SERVER['SCRIPT_NAME']);
					$tpl->assign("f", "f");
					$tpl->assign("i", "i");
					$tpl->assign("o", "o");
					$tpl->assign("g", "g");
					$tpl->assign("mail", "mail");
					$tpl->assign("post_name", student_post_add);
				}
				for ($i=0; $i < count($lines); $i++)
				{
					if( isset($_GET[student_get_edit]) && $lines[$i]['id'] == $_GET[student_get_edit])
					{
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

						$tpl->assign("post_name", student_post_edit);
					}
					else
					{
						$tpl->newBlock("row_students");
						$tpl->assign("no", $i+1);
						$tpl->assign("class", ($i%2)?"x":"y");
						$tpl->assign("f", $lines[$i]['F']);
						$tpl->assign("i", $lines[$i]['I']);
						$tpl->assign("o", $lines[$i]['O']);
						$tpl->assign("g", $lines[$i]['G']);
						$tpl->assign("chpass", "<a href=\"". $_SERVER['SCRIPT_NAME'] ."?". student_get_students ."&mail_to=". $lines[$i]['id'] ."\">chpass</a>");
						$tpl->assign("edit_row", "<a href=\"". $_SERVER['SCRIPT_NAME'] ."?". student_get_students ."&". student_get_edit. "=". $lines[$i]['id'] ."\">Edit</a>");
						$tpl->assign("final", ($lines[$i]['Success']==0)?"<font color=\"red\">Незачет</font> <a href=\"" .$_SERVER['SCRIPT_NAME'] ."?". student_get_students ."&". student_get_success ."=". $lines[$i]['id']. "\">Ok</a>":"<font color=\"green\">Зачет</font> <a href=\"" .$_SERVER['SCRIPT_NAME'] ."?". student_get_students ."&". student_get_not_success ."=". $lines[$i]['id']. "\">X</a>");
						$tpl->assign("delete", "<a href=\"". $_SERVER['SCRIPT_NAME'] ."?". student_get_students ."&". student_get_delete. "=". $lines[$i]['id'] ."\">Delete</a>");
					}
				}
			}
			else
			{
				$tpl->newBlock("no_table_students");

				if (isset($_GET[student_get_add]))
				{
					$tpl->newBlock("table_students");
					$tpl->newBlock("edit_row");
					$tpl->assign("script", $_SERVER['SCRIPT_NAME']);
					$tpl->assign("f", "f");
					$tpl->assign("i", "i");
					$tpl->assign("o", "o");
					$tpl->assign("g", "g");
					$tpl->assign("mail", "mail");
					$tpl->assign("post_name", student_post_add);
				}
			}
		}







		## Problems
		else if( isset($_GET[problem_get_problems]) )
		{
			$problems = new Problems();
			$tpl->newBlock("problems");

			$tpl->newBlock("menu_problems");
			$tpl->assign("url", $_SERVER['SCRIPT_NAME']); $tpl->assign("paramstr", problem_get_problems ."&". problem_get_add ); $tpl->assign("caption", "Добавить задачу");

			if( isset($_GET[problem_get_add]) )
			{
				$tpl->newBlock("add_edit");
				$tpl->assign("script", $_SERVER['SCRIPT_NAME']);
				$tpl->assign("caption", "Caption");
				$tpl->assign("teamsize", "TeamSize");
				$tpl->assign("faq", "FAQ");
				$tpl->assign("fulldiscription", "FullDiscription");
				$tpl->assign("post_name", problem_post_add);
			}
			else if( isset($_GET[problem_get_edit]) )
			{
				if (check_id($_GET[problem_get_edit]))
				{
					$line = $problems->get_by_id($_GET[problem_get_edit]);
					$tpl->newBlock("add_edit");
					$tpl->assign("script", $_SERVER['SCRIPT_NAME']);
					$tpl->assign("caption", "Caption");
					$tpl->assign("teamsize", "TeamSize");
					$tpl->assign("faq", "FAQ");
					$tpl->assign("fulldiscription", "FullDiscription");

					$tpl->assign("post_name", problem_post_edit);
					$tpl->assign("caption_val", $line['Caption']);
					$tpl->assign("faq_val", $line['FAQ']);
					$tpl->assign("fulldiscription_val", $line['FullDiscription']);
					$tpl->assign("selected" .$line['TeamSize'], "selected");
					$tpl->assign("id", "id");
					$tpl->assign("id_val", $line['id']);
				}
				else
				{
					redirect($_SERVER['SCRIPT_NAME'] ."?". problem_get_problems, "Wrong argument");
				}
			}
			else if( isset($_GET[problem_get_id]) )
			{
				if( check_id($_GET[problem_get_id]) )
				{
					$line = $problems->get_by_id($_GET[problem_get_id]);
					$tpl->newBlock("more");
					$tpl->assign("Caption", $line['Caption']);
					$tpl->assign("FAQ", $line['FAQ']);
					$tpl->assign("FullDiscription", $line['FullDiscription']);
					$tpl->assign("TeamSize", $line['TeamSize']);

					$team = $problems->get_team($_GET[problem_get_more]);
					$teammembers = "";
					foreach($team as $i=>$line)
					{
						$teammembers = $teammembers . $line['F'] ." ". $line['O'] ." ". $line['O'] ."<br>";
					}
					$tpl->assign("TeamMembers", $teammembers);
				}
				else
				{
					redirect($_SERVER['SCRIPT_NAME'] ."?". problem_get_problems, "Wrong argument");
				}
			}
			else if( isset($_GET[problem_get_delete]) )
			{
				if ( check_id($_GET[problem_get_delete]) )
				{
					$problems->delete($_GET[problem_get_delete]);
					redirect($_SERVER['SCRIPT_NAME'] ."?". problem_get_problems, "Задача удалена");
				}
				else
				{
					redirect($_SERVER['SCRIPT_NAME'] ."?". problem_get_problems, "Wrong argument");
				}
			}
			else
			{
				$lines = $problems->get_all();
				if( count($lines) > 0)
				{
					$tpl->newBlock("table");
					foreach($lines as $i=>$line)
					{
						$tpl->newBlock("row");
						$tpl->assign("no", $i+1);
						$tpl->assign("Caption", $line['Caption']);
						$tpl->assign("TeamSize", $line['TeamSize']);
						$tpl->assign("FAQ", $line['FAQ']);
						$tpl->assign("url_more", $_SERVER['SCRIPT_NAME'] ."?". problem_get_problems ."&". problem_get_id ."=". $line['id']);
						$tpl->assign("url_edit", $_SERVER['SCRIPT_NAME'] ."?". problem_get_problems ."&". problem_get_edit ."=". $line['id']);
						$tpl->assign("url_delete", $_SERVER['SCRIPT_NAME'] ."?". problem_get_problems ."&". problem_get_delete ."=". $line['id']);
						$tpl->assign("FullDiscription", $line['FullDiscription']);
					}
				}
				else
				{
					$tpl->newBlock("no_table");
				}
			}



		}








		## Admin Edit
		else if( isset($_GET['edit_admin']))
		{
			$tpl->newBlock("edit_admin");
			$tpl->assign("script", $_SERVER['SCRIPT_NAME']);
		}
		$tpl->printToScreen();
		die("");
	}
	else
	{
		## Не тестировалось!
		redirect();
		$t = new TemplatePower("./html/redirect.htm");
		$t->prepare("{$_SERVER['SCRIPT_NAME']}?logout", "Bad cookies");
	}
}







## Authorization
if( !isset($_POST['admin_login']) )
{
	$t = new TemplatePower("./html/admin.htm");
	$t->assignInclude("head", "./html/head.htm");
	$t->assignInclude("foot", "./html/foot.htm");
	$t->prepare();
	$t->newBlock("unknown_user");
	$t->printToScreen();
	die("");
}
else
{
	$t = new TemplatePower("./html/redirect.htm");
	$t->prepare();
	$t->assign("url", "{$_SERVER['SCRIPT_NAME']}");

	if ( strcmp($GLOBALS['md5_admin_login'], md5($_POST['login']))==0 && strcmp($GLOBALS['md5_admin_password'], md5($_POST['password'])) == 0)
	{
		$_SESSION['admin'] = true;
		$_SESSION['username'] = $_POST['login'];
		$_SESSION['md5md5login'] = md5( $GLOBALS['md5_admin_login'] );
		$_SESSION['md5md5password'] = md5( $GLOBALS['md5_admin_password'] );
		$t->assign("message", "Please, wait");
	}
	else
	{
		$t->assign("message", "Wrong login or password");
	}
	$t->printToScreen();
	die("");
}

?>