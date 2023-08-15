
<?php
class DBConnect {
    private $_conn = NULL;

    public function __construct($servidor, $usuario_db, $pwd_db, $nombre_db) {
        $dsn = 'mysql:dbname=' . $nombre_db . ';host=' . $servidor;
        try {
            $this->_conn = new PDO($dsn, $usuario_db, $pwd_db);
        } catch (PDOException $e) {
            echo 'Falló la conexión: ' . $e->getMessage();
        }
    }

    public function getConexion() {
        return $this->_conn;
    }
}
?>