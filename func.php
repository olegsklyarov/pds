<?php

function HaveAdmin()
{
	require_once "./connect.php";

	##Нет проверки на ошибку
	$res = @mysql_query("SELECT * FROM {$GLOBALS['admin_table']} LIMIT 1");
	for ($data = array(); $row = mysql_fetch_assoc($res); $data[]=$row);
	$res = count($data);

	if ($res == 0)
	{
		return false;
	}
	else if ($res == 1)
	{
		$GLOBALS['md5_admin_login'] = $data[0]['Login'];
		$GLOBALS['md5_admin_password'] = $data[0]['Password'];
		return true;
	}
	else
	{
		die("Initial error: more than one record in admin table!");
	}
}


function gen_pass()
{
	$len = 7;
	$base='ABCDEFGHKLMNOPQRSTWXYZabcdefghjkmnpqrstwxyz123456789';
	$max=strlen($base)-1;
	$activatecode='';
	mt_srand((double)microtime()*1000000);
	while (strlen($activatecode)<$len+1) $activatecode.=$base{mt_rand(0,$max)};

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
	if( ereg("^[0-9]+$", $param) )
	{
		return true;
	}
	else
	{
		return false;
	}
}

function translit($st)
{
	// Сначала заменяем "односимвольные" фонемы.
	$st=strtr($st,"абвгдеёзийклмнопрстуфхъыэ_", "abvgdeeziyklmnoprstufh'iei");
	$st=strtr($st,"АБВГДЕЁЗИЙКЛМНОПРСТУФХЪЫЭ_", "ABVGDEEZIYKLMNOPRSTUFH'IEI");

	// Затем - "многосимвольные".
	$st=strtr($st,
		array(
			"ж"=>"zh", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh",
			"щ"=>"shch","ь"=>"", "ю"=>"yu", "я"=>"ya",
			"Ж"=>"ZH", "Ц"=>"TS", "Ч"=>"CH", "Ш"=>"SH",
			"Щ"=>"SHCH","Ь"=>"", "Ю"=>"YU", "Я"=>"YA",
			"ї"=>"i", "Ї"=>"Yi", "є"=>"ie", "Є"=>"Ye"
		)
	);

	// Возвращаем результат.
	return $st;
}

function check_mail($email)
{
	if (!eregi("^[\._a-zA-Z0-9-]+@[\.a-zA-Z0-9-]+\.[a-z]{2,6}$", $email)) return false;
	return true;
}
?>