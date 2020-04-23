<?php

namespace App;

final class Students
{
    private function mysqlselect($str)
    {
        return Connection::getInstance()->select(Utils::sql_query_table(
            $str, Constants::DATABASE_TABLE_STUDENT));
    }

    public function get_all()
    {
        return $this->mysqlselect("SELECT * FROM %s ORDER BY G, F");
    }

    public function get_success()
    {
        return $this->mysqlselect("SELECT * FROM %s WHERE Success = 1 ORDER BY G, F");
    }

    public function get_not_success()
    {
        return $this->mysqlselect("SELECT * FROM %s WHERE Success = 0 ORDER BY G, F");
    }

    public function mysqlupdate($str)
    {
        Connection::getInstance()->update(Utils::sql_query_table(
            $str,
            Constants::DATABASE_TABLE_STUDENT));
    }


    public function add($f, $i, $o, $g, $mail, $pass)
    {
        $p = md5(trim($pass));
        $this->mysqlupdate("INSERT INTO %s (F, I, O, G, Mail, Password, Task_id, Success) VALUES ('$f', '$i', '$o', '$g', '$mail', '$p' , '-1', '0')");
    }


    public function edit($id, $f, $i, $o, $g, $mail)
    {
        $this->mysqlupdate("UPDATE %s SET F = '$f', I = '$i', O = '$o', G = '$g', Mail = '$mail' WHERE id = $id LIMIT 1");
    }

    /*
    public function edit_md5_pass($id, $md5pass)
    {
        $this->mysqlupdate("UPDATE %s SET Password = '$md5pass' WHERE id = $id LIMIT 1");
    }
    */


    public function edit_pass($id, $pass)
    {
        $p = md5(trim($pass));
        $this->mysqlupdate("UPDATE %s SET Password = '$p' WHERE id = $id LIMIT 1");
    }

    public function get_by_task($task_id)
    {
        return $this->mysqlselect("SELECT * FROM %s WHERE Task_id = $task_id ORDER BY F");
    }


    public function get_by_id($id)
    {
        $res = $this->mysqlselect("SELECT * FROM %s WHERE id = $id LIMIT 1");

        return $res[0];
    }


    public function success($id)
    {
        $this->mysqlupdate("UPDATE %s SET Success = '1' WHERE id=$id LIMIT 1");
    }


    public function not_success($id)
    {
        $this->mysqlupdate("UPDATE %s SET Success = '0' WHERE id=$id LIMIT 1");
    }


    public function delete($id)
    {
        $this->mysqlupdate("DELETE FROM %s WHERE id=$id LIMIT 1");
    }


    public function chtask($id, $Task_id)
    {
        $this->mysqlupdate("UPDATE %s SET Task_id = '$Task_id' WHERE id=$id LIMIT 1");
    }
}
