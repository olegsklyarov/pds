<?php

namespace App;

final class Problems
{
    private function mysqlselect($str)
    {
        return Connection::getInstance()->select(Utils::sql_query_table($str, Constants::DATABASE_TABLE_PROBLEM));
    }


    private function mysqlupdate($str)
    {
        Connection::getInstance()->update(Utils::sql_query_table(
            $str,
            Constants::DATABASE_TABLE_PROBLEM));
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
        $table = Constants::DATABASE_TABLE_STUDENT;

        return Connection::getInstance()->select("SELECT * FROM $table WHERE Task_id = '$id' ORDER BY F");
    }


    public function delete($id)
    {
        $this->mysqlupdate("DELETE FROM %s WHERE id = $id LIMIT 1");
    }
}
