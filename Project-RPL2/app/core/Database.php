<?php
/**
 * Class Database Wrapper PDO
 * Menyediakan method query, bind, execute, resultSet, dan single
 */

class Database {
    private string $host = DB_HOST;
    private string $user = DB_USER;
    private string $pass = DB_PASS;
    private string $dbname = DB_NAME;
    private int $port = DB_PORT;

    private ?PDO $dbh = null;
    private $stmt;
    private ?string $error = null;

    public function __construct() {
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function getError(): ?string {
        return $this->error;
    }

    public function isConnected(): bool {
        return $this->dbh !== null;
    }

    public function query(string $query): void {
        if (!$this->dbh) return;
        $this->stmt = $this->dbh->prepare($query);
    }

    public function bind($param, $value, $type = null): void {
        if (!$this->stmt) return;

        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute(): bool {
        if (!$this->stmt) return false;
        return $this->stmt->execute();
    }

    public function resultSet(): array {
        if (!$this->execute()) return [];
        return $this->stmt->fetchAll();
    }

    public function single(): ?array {
        if (!$this->execute()) return null;
        $result = $this->stmt->fetch();
        return $result ?: null;
    }

    public function rowCount(): int {
        return $this->stmt ? $this->stmt->rowCount() : 0;
    }

    public function lastInsertId(): string {
        return $this->dbh ? $this->dbh->lastInsertId() : '0';
    }

    /**
     * Memulai transaksi database
     */
    public function beginTransaction(): bool {
        if (!$this->dbh || $this->dbh->inTransaction()) {
            return false;
        }
        return $this->dbh->beginTransaction();
    }

    /**
     * Menyimpan dan menerapkan semua perubahan transaksi
     */
    public function commit(): bool {
        if (!$this->dbh || !$this->dbh->inTransaction()) {
            return false;
        }
        return $this->dbh->commit();
    }

    /**
     * Membatalkan transaksi database
     */
    public function rollBack(): bool {
        if (!$this->dbh || !$this->dbh->inTransaction()) {
            return false;
        }
        return $this->dbh->rollBack();
    }

    /**
     * Memeriksa apakah saat ini sedang dalam transaksi aktif
     */
    public function inTransaction(): bool {
        return $this->dbh ? $this->dbh->inTransaction() : false;
    }
}
