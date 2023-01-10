<?php

// データベースを操作するための関数
class DB{
    private static $dbInstance;
    private $dbh;

    private const DB = 'mysql:host=localhost;dbname=sample_db';
    private const USER = 'root';
    private const PASSWORD = 'yesterday';
    private const OPT = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // MySQLからのエラーを取得する
        PDO::ATTR_EMULATE_PREPARES => false,
        // セキュリティ的な意味。PREPARESという関数があって、それを展開しない、という設定。
        
        // PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // PDO::ATTR_EMULATE_PREPARES => false,この2行は必須
        
        // マルチクエリを不可に。セキュリティ的な目的。
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
    ];
    
    private function __construct(){
        // データベースに接続する。
        $this->dbh = new PDO(self::DB, self::USER, self::PASSWORD, self::OPT);  
    }

    // dbhを取得する
    public function getDbh(){
        return $this->dbh;
    }

    // DBクラスのインスタンスを取得する
    static function getDbInstance() {
        return self::$dbInstance ?? self::$dbInstance = New DB;
    }


}