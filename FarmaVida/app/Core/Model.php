<?php
abstract class Model
{
    protected function db(): PDO { return Config::getDbConnection(); }
    public function beginTransaction(): bool { return $this->db()->beginTransaction(); }
    public function commit(): bool { return $this->db()->commit(); }
    public function rollBack(): bool { return $this->db()->rollBack(); }
    public function inTransaction(): bool { return $this->db()->inTransaction(); }
    public function lastInsertId(): string|false { return $this->db()->lastInsertId(); }
}
