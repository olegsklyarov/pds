<?php

namespace App;

final class Connection
{
    private static ?Connection $instance = null;
    private \mysqli $mysqli;

    private function __construct()
    {
        $this->mysqli = new \mysqli(
            Constants::getDatabaseHost(),
            Constants::getDatabaseUser(),
            Constants::getDatabasePassword(),
            Constants::getDatabaseName()
        );

        if ($this->mysqli->connect_error) {
            die("Could not connect: {$this->mysqli->connect_error}");
        }

        if (!$this->mysqli->set_charset("utf8")) {
            die("Failed to set charset: {$this->mysqli->error}");
        }
    }

    public function __destruct()
    {
        $this->mysqli->close();
    }

    public static function getInstance(): Connection
    {
        if (self::$instance === null) {
            self::$instance = new Connection();
        }

        return self::$instance;
    }

    public function select(string $query): array
    {
        $result = $this->mysqli->query($query) or die("MySQL error during SELECT: {$this->mysqli->error}");
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }


    public function update(string $query)
    {
        $this->mysqli->query($query) or die("MySQL error during UPDATE: {$this->mysqli->error}");
    }


    public function multi_query(string $query)
    {
        if ($this->mysqli->multi_query($query)) {
            do {
                if ($result = $this->mysqli->store_result()) {
                    $result->free();
                }
            } while ($this->mysqli->more_results() && $this->mysqli->next_result());
        }
    }
}
