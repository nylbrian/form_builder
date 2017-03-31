<?php

abstract class DBBase {
  public function reportError($message) {
    trigger_error($message);
    throw new DBException($message);
  }

  abstract public function setConfig($config);
  abstract public function connect();
  abstract public function query($sql);
  abstract public function fetch();
  abstract public function numRows();
  abstract public function insertId();
  abstract public function startTransaction();
  abstract public function commit();
  abstract public function rollback();
}
