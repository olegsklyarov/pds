<?php
	require_once "consts.php";

	$host="localhost";

	if(on_server)
	{
		$user="task_select";
		$pass="hy2md12j";
		$db="task_select";
	}
	else
	{
		$user="root";
		$pass="";
		$db="task_select";
	}

	$GLOBALS['students_table'] = "students";
	$GLOBALS['problems_table'] = "problems";
	$GLOBALS['admin_table'] = "admin";

	mysql_connect($host,$user,$pass) or die("Could not connect: ". mysql_error());
	mysql_select_db($db) or die("Could not connect: ". mysql_error());
?>