<?php
define("problem_get_problems", "problems");
define("problem_get_add", "pga");
define("problem_post_add", "ppa");
define("problem_get_id", "pgi");
define("problem_get_edit", "pge");
define("problem_post_edit", "ppe");
define("problem_get_delete", "pgd");


class Problems
{
	private function mysqlselect($str)
	{
		require_once "./connect.php";
		$res = mysql_query( sprintf($str, $GLOBALS['problems_table']) ) or die("MySQL error in Problems class select: ". mysql_error());
		for ($data = array(); $row = mysql_fetch_assoc($res); $data[]=$row);
		return $data;
	}


	private function mysqlupdate($str)
	{
		require_once "./connect.php";
		mysql_query( sprintf($str, $GLOBALS['problems_table']) ) or die("MySQL error in Problems class update: ". mysql_error());
	}


	public function add($Caption, $FAQ, $FullDiscription, $TeamSize)
	{
		$this->mysqlupdate("INSERT INTO %s (id, Caption, FAQ, FullDiscription, TeamSize) VALUES ('', '$Caption', '$FAQ', '$FullDiscription', $TeamSize)");
	}


	public function get_all()
	{
		return $this->mysqlselect("SELECT * FROM %s");
	}



	public function get_by_id($id)
	{
		$res = $this->mysqlselect("SELECT * FROM %s WHERE id = $id LIMIT 1");
		return $res[0];
	}



	public function update($id, $Caption, $FAQ, $FullDiscription, $TeamSize)
	{
		$this->mysqlupdate("UPDATE %s SET Caption = '$Caption', FAQ = '$FAQ', FullDiscription = '$FullDiscription', TeamSize = '$TeamSize' WHERE id = $id LIMIT 1");
	}



	public function get_team($id)
	{
		require_once "./connect.php";
		$table = $GLOBALS['students_table'];
		$res = mysql_query("SELECT * FROM $table WHERE id = '$id' ORDER BY F") or die("MySQL error in Problems class select: ". mysql_error());
		for ($data = array(); $row = mysql_fetch_assoc($res); $data[]=$row);
		return $data;
	}




	public function delete($id)
	{
		$this->mysqlupdate("DELETE FROM %s WHERE id = $id LIMIT 1");
	}
}