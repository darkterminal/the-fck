<?php

namespace Fckin\core\db;

use Exception;
use Fckin\core\Application;
use Fckin\core\exceptions\DatabaseException;
use InvalidArgumentException;
use PDO;
use PDOStatement;

class QueryBuilder
{
    private $pdo;

    private $table;

    private $select = '*';

    private $join = '';

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
        $this->where .= empty($this->where) ? "WHERE $column $operator ?" : " AND $column $operator ?";
        $this->params[] = $value;
        return $this;
    }

    public function andWhere(string $column, string $operator, mixed $value)
    {
        return $this->where($column, $operator, $value);
    }

    public function orWhere(string $column, string $operator, mixed $value)
    {
        $this->where .= empty($this->where) ? "WHERE $column $operator ?" : " OR $column $operator ?";
        $this->params[] = $value;
        return $this;
    }

    public function whereIsNull(string $column)
    {
        $this->where .= empty($this->where) ? "WHERE $column IS NULL" : " AND $column IS NULL";
        return $this;
    }

    public function whereNot(string $column, mixed $value)
    {
        return $this->where($column, '<>', $value);
    }

    public function whereIn(string $column, array $values)
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->where .= empty($this->where) ? "WHERE $column IN ($placeholders)" : " AND $column IN ($placeholders)";
        $this->params = array_merge($this->params, $values);
        return $this;
    }

    public function whereNotIn(string $column, array $values)
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->where .= empty($this->where) ? "WHERE $column NOT IN ($placeholders)" : " AND $column NOT IN ($placeholders)";
        $this->params = array_merge($this->params, $values);
        return $this;
    }

    public function whereNotNull(string $column)
    {
        $this->where .= empty($this->where) ? "WHERE $column IS NOT NULL" : " AND $column IS NOT NULL";
        return $this;
    }

    public function whereGroup(callable $callback)
    {
        $this->where .= empty($this->where) ? 'WHERE (' : ' AND (';
        $callback($this);
        $this->where .= ')';
        return $this;
    }

    public function join(string $table, string $column1, string $operator, string $column2)
    {
        $this->join .= " JOIN $table ON $column1 $operator $column2";
        return $this;
    }

    public function innerJoin(string $table, string $column1, string $operator, string $column2)
    {
        $this->join .= " INNER JOIN $table ON $column1 $operator $column2";
        return $this;
    }

    public function leftJoin(string $table, string $column1, string $operator, string $column2)
    {
        $this->join .= " LEFT JOIN $table ON $column1 $operator $column2";
        return $this;
    }

    public function rightJoin(string $table, string $column1, string $operator, string $column2)
    {
        $this->join .= " RIGHT JOIN $table ON $column1 $operator $column2";
        return $this;
    }

    public function groupBy(string $column)
    {
        $this->where .= " GROUP BY $column";
        return $this;
    }

    public function orderBy(string|array $columns, string $direction = 'asc')
    {
        if (is_array($columns)) {
            $orderBy = [];
            foreach ($columns as $column => $dir) {
                $orderBy[] = "$column $dir";
            }
            $orderBy = implode(', ', $orderBy);
        } else {
            $orderBy = "$columns $direction";
        }
        $this->where .= " ORDER BY $orderBy";
        return $this;
    }

    public function limit(int $limit, ?int $offset = null)
    {
        if ($offset !== null) {
            $this->where .= " LIMIT $offset, $limit";
        } else {
            $this->where .= " LIMIT $limit";
        }
        return $this;
    }

    public function page(int $page, int $perPage)
    {
        $offset = ($page - 1) * $perPage;
        return $this->limit($perPage, $offset);
    }

    public function count($field = null)
    {
        try {
            $field = $field ?? '*';
            $this->select = "COUNT($field)";
            return $this->get()->count ?? 0;
        } catch (DatabaseException $e) {
            throw new Exception("Database Exception: " . $e->getMessage() . " (Query: " . $e->getQuery() . ")");
        }
    }

    public function sum($field)
    {
        try {
            $this->select = "SUM($field)";
            return $this->get()->sum ?? 0;
        } catch (DatabaseException $e) {
            throw new Exception("Database Exception: " . $e->getMessage() . " (Query: " . $e->getQuery() . ")");
        }
    }

    public function min($field)
    {
        try {
            $this->select = "MIN($field)";
            return $this->get()->min ?? null;
        } catch (DatabaseException $e) {
            throw new Exception("Database Exception: " . $e->getMessage() . " (Query: " . $e->getQuery() . ")");
        }
    }

    public function max($field)
    {
        try {
            $this->select = "MAX($field)";
            return $this->get()->max ?? null;
        } catch (DatabaseException $e) {
            throw new Exception("Database Exception: " . $e->getMessage() . " (Query: " . $e->getQuery() . ")");
        }
    }

    public function avg($field)
    {
        try {
            $this->select = "AVG($field)";
            return $this->get()->avg ?? 0;
        } catch (DatabaseException $e) {
            throw new Exception("Database Exception: " . $e->getMessage() . " (Query: " . $e->getQuery() . ")");
        }
    }

    public function exists()
    {
        try {
            $this->select = "EXISTS(SELECT 1" . ($this->where ? " FROM $this->table $this->where" : "") . ") AS `exists`";
            $result = $this->get();
            return (bool)($result ? $result->exists : false);
        } catch (DatabaseException $e) {
            throw new Exception("Database Exception: " . $e->getMessage() . " (Query: " . $e->getQuery() . ")");
        }
    }

    public function insert(array $data): int
    {
        try {
            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_fill(0, count($data), '?'));

            $sql = "INSERT INTO $this->table ($columns) VALUES ($values)";
            $statement = $this->pdo->prepare($sql);
            $statement->execute(array_values($data));

            return $this->pdo->lastInsertId();
        } catch (DatabaseException $e) {
            throw new Exception("Database Exception: " . $e->getMessage() . " (Query: " . $e->getQuery() . ")");
        }
    }

    public function update(array $data): int
    {
        try {
            $setClause = implode(', ', array_map(function ($key) {
                return "$key = ?";
            }, array_keys($data)));

            $sql = "UPDATE $this->table SET $setClause $this->where";
            $params = array_merge(array_values($data), $this->params);
            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);

            return $statement->rowCount();
        } catch (DatabaseException $e) {
            throw new Exception("Database Exception: " . $e->getMessage() . " (Query: " . $e->getQuery() . ")");
        }
    }

    public function delete(): int
    {
        try {
            $sql = "DELETE FROM $this->table $this->where";
            $statement = $this->pdo->prepare($sql);
            $statement->execute($this->params);

            return $statement->rowCount();
        } catch (DatabaseException $e) {
            throw new Exception("Database Exception: " . $e->getMessage() . " (Query: " . $e->getQuery() . ")");
        }
    }

    public function first(): ?object
    {
        return $this->get();
    }

    public function all(): array
    {
        return $this->getAll();
    }

    public function get(): ?object
    {
        try {
            $sql = "SELECT $this->select FROM $this->table $this->join $this->where";
            $statement = $this->pdo->prepare($sql);
            $statement->execute($this->params);

            $result = $statement->fetch(PDO::FETCH_OBJ);

            return $result !== false ? $result : null;
        } catch (DatabaseException $e) {
            throw new Exception("Database Exception: " . $e->getMessage() . " (Query: " . $e->getQuery() . ")");
        }
    }

    public function getAll(): array
    {
        try {
            $sql = "SELECT $this->select FROM $this->table $this->join $this->where";
            $statement = $this->pdo->prepare($sql);
            $statement->execute($this->params);

            return $statement->fetchAll(PDO::FETCH_OBJ);
        } catch (DatabaseException $e) {
            throw new Exception("Database Exception: " . $e->getMessage() . " (Query: " . $e->getQuery() . ")");
        }
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);

            return $statement;
        } catch (DatabaseException $e) {
            throw new Exception("Database Exception: " . $e->getMessage() . " (Query: " . $e->getQuery() . ")");
        }
    }

    public function execQueryTable($sql, $validColumns, $search, $column, $order_by, $offset, $limit): PDOStatement
    {
        $dir_statements = $this->sql_statement_path . \str_replace('.', '/', $sql);
        $statement = \file_get_contents($dir_statements . '.sql');

        $column = $this->validateColumn($column, $validColumns);
        $order_by = $this->validateOrder($order_by);

        $statement = str_replace(':column', $column, $statement);
        $statement = str_replace(':order_by', $order_by, $statement);
        $statement = str_replace(':search', "%$search%", $statement);
        $statement = str_replace(':offset', $offset, $statement);
        $statement = str_replace(':limit', $limit, $statement);

        return $this->query($statement);
    }

    public function executeQuery($sql_filename, $params = []): PDOStatement
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
        } catch (DatabaseException $e) {
            throw new Exception("Database Exception: " . $e->getMessage() . " (Query: " . $e->getQuery() . ")");
        }
    }

    private function validateColumn($column, $validColumns)
    {
        if (!in_array($column, $validColumns)) {
            throw new InvalidArgumentException("Invalid column name.");
        }
        return $column;
    }

    private function validateOrder($order)
    {
        $order = strtoupper($order);
        if ($order !== 'ASC' && $order !== 'DESC') {
            throw new InvalidArgumentException("Invalid order direction.");
        }
        return $order;
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
