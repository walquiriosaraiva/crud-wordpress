<?php

class Conexao
{
    private static $conexao;

    /**
     * Conexao constructor.
     */
    private function __construct()
    {
    }

    /**
     * @return PDO
     */
    public static function getInstance()
    {
        if (is_null(self::$conexao)) {
            self::$conexao = new \PDO('mysql:host='.DB_HOST.';port=3306;dbname='.DB_NAME.'', DB_USER, DB_PASSWORD);
            self::$conexao->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$conexao->exec('set names '.DB_CHARSET);
        }
        return self::$conexao;
    }
}