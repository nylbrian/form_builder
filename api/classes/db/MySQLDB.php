<?php

class MySQLDB extends DBBase {
  private $config, $link, $resource;

  public function __construct(Array $params) {
    $this->config = $params;
  }

  public function setConfig($config) {
    if (!(is_array($config) && isset($config['server']) && isset($config['username']) &&
      isset($config['password']) && isset($config['database']))) {
      $this->reportError('Invalid database config');
    }
    $this->config = $config;

    return $this;
  }

  public function connect() {
    $this->link = mysql_connect(
      $this->config['server'],
      $this->config['username'],
      $this->config['password']
    );

    if (!$this->link) {
      $this->reportError('Could not connect to server ' . $this->config['server']);
    }

    $dbSelected = mysql_select_db($this->config['database'], $this->link);

    if (!$dbSelected) {
      $this->reportError('Could not connect to database ' . $this->config['database']);
    }
  }

  public function query($sql) {
    $this->resource = mysql_query($sql, $this->link);

    if (!$this->resource) {
      $this->reportError(mysql_error());
    }

    return $this;
  }

  public function fetch() {
    if (!$this->resource) {
      $this->reportError('Invalid usage for fetch');
    }

    $rows = array();

    while ($row = mysql_fetch_assoc($this->resource)) {
      $rows[] = $row;
    }

    return $rows;
  }

  public function numRows() {
    if (!$this->resource) {
      $this->reportError('Invalid usage for numRows');
    }

    return mysql_num_rows($this->resource);
  }

  public function insertId() {
    if (!$this->resource) {
      $this->reportError('Invalid usage for insertID');
    }

    return mysql_insert_id($this->link);
  }

  public function affectedRows() {
    return mysql_affected_rows($this->link);
  }

  public function startTransaction() {
    mysql_query('START TRANSACTION', $this->link);
  }

  public function commit() {
    mysql_query('COMMIT', $this->link);
  }

  public function rollback() {
    mysql_query('ROLLBACK', $this->link);
  }
}
