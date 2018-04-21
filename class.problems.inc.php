<?php

require_once 'constants.php';
require_once 'connect.php';
require_once 'func.php';

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
        return Connection::getInstance()->select(sql_query_table($str, Constants::DATABASE_TABLE_PROBLEMS));
    }


    private function mysqlupdate($str)
    {
        Connection::getInstance()->update(sql_query_table(
            $str,
            Constants::DATABASE_TABLE_PROBLEMS));
    }


    public function add($Caption, $FAQ, $FullDescription, $TeamSize)
    {
        $this->mysqlupdate("INSERT INTO %s (Caption, FAQ, FullDescription, TeamSize) VALUES ('$Caption', '$FAQ', '$FullDescription', $TeamSize)");
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


    public function update($id, $Caption, $FAQ, $FullDescription, $TeamSize)
    {
        $this->mysqlupdate("UPDATE %s SET Caption = '$Caption', FAQ = '$FAQ', FullDescription = '$FullDescription', TeamSize = '$TeamSize' WHERE id = $id LIMIT 1");
    }


    public function get_team($id)
    {
        $table = Constants::DATABASE_TABLE_STUDENTS;

        return Connection::getInstance()->select("SELECT * FROM $table WHERE id = '$id' ORDER BY F");
    }


    public function delete($id)
    {
        $this->mysqlupdate("DELETE FROM %s WHERE id = $id LIMIT 1");
    }
}