<?php
require_once "constants.php";


class Connection
{
    /** @var Connection */
    private static $instance;

    /** @var mysqli */
    private $mysqli;

    private function __construct()
    {
        $this->mysqli = new mysqli(
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

    /**
     * @return Connection
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Connection();
        }

        return self::$instance;
    }


    /**
     * @param string $query
     *
     * @return array
     */
    public function select(string $query)
    {
        /** @var mysqli_result $result */
        $result = $this->mysqli->query($query) or die("MySQL error during SELECT: {$this->mysqli->error}");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->close();

        return $data;
    }


    /**
     * @param string $query
     */
    public function update(string $query)
    {
        $this->mysqli->query($query) or die("MySQL error during UPDATE: {$this->mysqli->error}");
    }
}
