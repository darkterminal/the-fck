<?php

namespace Fckin\core\db;

use Fckin\core\Application;
use PDO;

class QueryBuilder
{
    private $pdo;

    private $table;

    private $select = '*';

    private $where = '';

    private $params = [];

    public function __construct()
    {
        $this->pdo = Application::$app->db->pdo;
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
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement;
    }
}
