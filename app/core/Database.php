<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $port = DB_PORT;
    private $dbname = DB_NAME;

    private $dbh;
    private $stmt;
    private $error;

    public function __construct() {
        $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbname;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log('[CloudArena DB] Connection failed: ' . $e->getMessage());
            if (PHP_SAPI !== 'cli' && !headers_sent()) {
                http_response_code(503);
                header('Content-Type: text/html; charset=UTF-8');
            }
            if (PHP_SAPI !== 'cli') {
                echo '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Không khả dụng</title></head><body><p>Hệ thống tạm thời không khả dụng. Vui lòng thử lại sau.</p></body></html>';
            }
            exit(1);
        }
    }

    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    public function bind($param, $value, $type = null) {
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

    public function execute() {
        try {
            $ok = $this->stmt->execute();
            #app_log('info', 'db_query_executed', ['sql' => $this->stmt->queryString]);
            return $ok;
        } catch (PDOException $e) {
            app_log('error', 'db_query_failed', [
                'sql'   => $this->stmt->queryString,
                'error' => $e->getMessage(),
            ]);
            throw $e;   
        }
    }

    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}
