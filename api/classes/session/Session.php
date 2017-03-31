<?php

class Session {
  public function __construct() {
    session_name('FORM_BUILDER');
    session_set_save_handler(
      array($this, '_open'),
      array($this, '_close'),
      array($this, '_read'),
      array($this, '_write'),
      array($this, '_destroy'),
      array($this, '_gc')
    );
    session_start();
  }

  public function _open() {
    return true;
  }

  public function _close() {
    return true;
  }

  public function _read($id) {
    $result = DB::queryMaster('SELECT `data` from `sessions` WHERE `id` = %d', array($id));

    if ($result->numRows() <= 0) {
      return '';
    }

    $result = $result->fetch();

    return $result[0]['data'];
  }

  public function _write($id, $data) {
    $access = time();
    $result = DB::queryMaster('
      REPLACE INTO `sessions` VALUES("%s", "%s", "%s")',
      array($id, $access, $data)
    );

    return $result->affectedRows() <= 0 ? false : true;
  }

  public function _destroy($id) {
    $result = DB::queryMaster('DELETE FROM `sessions` WHERE id = %d', array($id));

    if ($result->affectedRows() <= 0) {
      return false;
    }

    return true;
  }

  public function _gc($max) {
    $old = time() - $max;

    $result = DB::queryMaster('DELETE FROM `sessions` WHERE `access` < %d', array($old));

    if ($result->affectedRows() <= 0) {
      return false;
    }

    return true;
  }
}
