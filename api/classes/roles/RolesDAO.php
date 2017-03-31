<?php

class RolesDAO {

  public function getById($id) {
    $sql = 'SELECT * from `roles` WHERE `id` = %d';
    $result = DB::querySlave($sql, array($id));

    if (!$result || $result->numRows() <= 0) {
      return;
    }

    $result = $result->fetch();

    return $result[0];
  }

  public function getAll() {
    $sql = 'SELECT * from `roles`';
    $result = DB::querySlave($sql);

    if (!$result || $result->numRows() <= 0) {
      return;
    }

    return $result->fetch();
  }

}
