<?php

class InputTypesDAO {

  public function getById($id) {
    $sql = 'SELECT * from `input_type` WHERE `id` = %d';
    $result = DB::querySlave($sql, array($id));

    if (!$result || $result->numRows() <= 0) {
      return;
    }

    $result = $result->fetch();

    return $result[0];
  }

  public function getAll() {
    $sql = 'SELECT * from `input_type` ORDER BY `name`';
    $result = DB::querySlave($sql);

    if (!$result || $result->numRows() <= 0) {
      return;
    }

    return $result->fetch();
  }

}
