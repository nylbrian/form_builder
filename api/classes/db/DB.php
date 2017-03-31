<?php

class DB {
  const DB_MYSQL = 'mysql';
  const DB_MSSQL = 'mssql';

  private static $_master;
  private static $_slave;

  public static function initialize($dbParams = array(), $engine = self::DB_MYSQL) {
    switch ($engine) {
      case self::DB_MSSQL:
        self::$_master = new MSSQLDB($dbParams['master']);
        self::$_slave = new MSSQLDB($dbParams['slave']);
      break;
      default:
        self::$_master = new MySQLDB($dbParams['master']);
        self::$_slave = new MySQLDB($dbParams['slave']);
    }

    self::$_master->connect();
    self::$_slave->connect();
  }

  public static function sanitize($sql = '', $params = array()) {
    foreach ($params as &$param) {
      $param = addslashes($param);
    }
    return vsprintf($sql, $params);
  }

  public static function queryMaster($sql = '', $params = array()) {
    $query = self::sanitize($sql, $params);
    return self::$_master->query($query);
  }

  public static function querySlave($sql = '', $params = array()) {
    $query = self::sanitize($sql, $params);
    return self::$_slave->query($query);
  }

  public static function startTransaction() {
    return self::$_master->startTransaction();
  }

  public static function commit() {
    return self::$_master->commit();
  }

  public static function rollback() {
    return self::$_master->rollback();
  }
}
