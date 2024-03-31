<?php

namespace Fckin\core\db;

use Fckin\core\Application;
use PDO;
use PDOException;

class QueryBuilder
{
    private $pdo;

    private $table;

    private $select = '*';

    private $where = '';

    private $params = [];

    private $sql_statement_path;

    public function __construct()
    {
        $this->pdo = Application::$app->db->pdo;
        $this->sql_statement_path = Application::$ROOT_DIR . \DIRECTORY_SEPARATOR . 'models' . \DIRECTORY_SEPARATOR . 'statements' . \DIRECTORY_SEPARATOR;
    }

    public function table(string $table)
    {
        $this->table = $table;
        return $this;
    }

    public function select(string|array $columns)
    {
        $this->select = is_array($columns) ? implode(', ', $columns) : $columns;
        return $this;
    }

    public function where(string $column, string $operator, mixed $value)
    {
        $this->where = "WHERE $column $operator ?";
        $this->params[] = $value;
        return $this;
    }

    public function insert(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO $this->table ($columns) VALUES ($values)";
        $statement = $this->pdo->prepare($sql);
        $statement->execute(array_values($data));

        return $this->pdo->lastInsertId();
    }

    public function update(array $data): int
    {
        $setClause = implode(', ', array_map(function ($key) {
            return "$key = ?";
        }, array_keys($data)));

        $sql = "UPDATE $this->table SET $setClause $this->where";
        $statement = $this->pdo->prepare($sql);
        $statement->execute(array_merge(array_values($data), $this->params));

        return $statement->rowCount();
    }

    public function delete(): int
    {
        $sql = "DELETE FROM $this->table $this->where";
        $statement = $this->pdo->prepare($sql);
        $statement->execute($this->params);

        return $statement->rowCount();
    }

    public function get(): ?object
    {
        $sql = "SELECT $this->select FROM $this->table $this->where";
        $statement = $this->pdo->prepare($sql);
        $statement->execute($this->params);

        $result = $statement->fetch(PDO::FETCH_OBJ);

        return $result !== false ? $result : null;
    }

    public function getAll(): array
    {
        $sql = "SELECT $this->select FROM $this->table $this->where";
        $statement = $this->pdo->prepare($sql);
        $statement->execute($this->params);

        return $statement->fetchAll(PDO::FETCH_OBJ);
    }

    public function query(string $sql, array $params = []): mixed
    {
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);

            return $statement;
        } catch (PDOException $e) {
            die('Query failed: ' . $e->getMessage());
        }
    }

    public function executeQuery($sql_filename, $params = [])
    {
        try {
            $dir_statements = $this->sql_statement_path . \str_replace('.', '/', $sql_filename);
            $statement = \file_get_contents($dir_statements . '.sql');
            $stmt = $this->pdo->prepare($statement);
            foreach ($params as $param => &$value) {
                $stmt->bindParam($param, $value, $this->getParamType($value));
            }
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            die('Query failed: ' . $e->getMessage());
        }
    }

    private function getParamType($value)
    {
        if (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            return PDO::PARAM_NULL;
        } elseif (is_int($value)) {
            return PDO::PARAM_INT;
        } elseif (is_string($value)) {
            return PDO::PARAM_STR;
        } else {
            return PDO::PARAM_STR;
        }
    }
}
