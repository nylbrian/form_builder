<?php

class LanguagesDAO {

  public function getById($id) {
    $sql = 'SELECT * from `languages` WHERE `id` = %d';
    $result = DB::querySlave($sql, array($id));

    if (!$result || $result->numRows() <= 0) {
      return;
    }

    $result = $result->fetch();

    return $result[0];
  }

  public function getAll() {
    $sql = 'SELECT * from `languages`';
    $result = DB::querySlave($sql);

    if (!$result || $result->numRows() <= 0) {
      return;
    }

    return $result->fetch();
  }

}
