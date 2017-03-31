<?php

class UsersDAO {
  private $_hashOptions = array(
    'cost' => 12
  );
  //password_verify

  public function create($data) {
    DB::startTransaction();

    $sql = 'INSERT INTO `users`(username, password, salt, roles_id, name, enabled)
      VALUES("%s", "%s", "%s", %d, "%s", %d)';

    $salt = String::randomString();
    $password = sha1($data['password'].$salt);

    $result = DB::queryMaster($sql, array(
      $data['username'],
      $password,
      $salt,
      $data['role'],
      $data['name'],
      $data['enabled'] === 'true' ? 1 : 0,
    ));

    $userId = $result->insertId();

    if (!$userId) {
      DB::rollback();
      return false;
    }

    DB::commit();
    return $userId;
  }

  public function update($id, $data) {
    DB::startTransaction();

    $sql = 'UPDATE `users` SET `username` = "%s", `enabled` = %d, `roles_id` = %d, `name` = "%s" WHERE `id` = %d';
    $result = DB::queryMaster($sql, array(
      $data['username'],
      $data['enabled'] === 'true' ? 1 : 0,
      $data['role'],
      $data['name'],
      $id,
    ));

    if (isset($data['password'])) {
      $salt = String::randomString();
      $password = sha1($data['password'].$salt);

      $sql = 'UPDATE `users` SET `password` = "%s", `salt` = "%s" WHERE `id` = %d';
      $result = DB::queryMaster($sql, array(
        (string)$password,
        $salt,
        $id,
      ));
    }

    DB::commit();
    return true;
  }

  public function getTableView($page = 1, $itemsPerPage = 10, $sortBy = 'id', $sortOrder = 'asc', $search = '') {
    if ($search !== '') {
      $where = ' WHERE `users`.`name` LIKE "%%' . $search . '%%" OR `roles`.`name` LIKE "%%' . $search . '%%" OR `users`.`username` LIKE "%%' . $search . '%%"';
    } else {
      $where = '';
    }

    $sql = 'SELECT count(`users`.`id`) as cnt from `users` INNER JOIN `roles` ON `roles`.`id` = `users`.`roles_id`' . $where;
    $result = DB::querySlave($sql);

    if ($result->numRows() <= 0) {
      return null;
    }

    $count = $result->fetch();
    $count = $count[0];

    if ($page > ceil($count['cnt'] / $itemsPerPage)) {
      $page = 1;
    }

    $offset = ($page - 1) * $itemsPerPage;
    $sql = 'SELECT `users`.`id`, `users`.`username`, `users`.`name` as full_name,
      `roles`.`name` as role_name, `users`.`enabled` FROM `users`
      INNER JOIN `roles`
        ON `roles`.`id` = `users`.`roles_id` ' . $where . '
      ORDER by `%s` %s
      LIMIT %d, %d';

    $result = DB::querySlave($sql, array(
      $sortBy,
      $sortOrder,
      $offset,
      $itemsPerPage
    ));

    if ($result->numRows() <= 0) {
      return null;
    }

    return array(
      'rows' => $result->fetch(),
      'count' => (int) $count['cnt']
    );
  }

  public function getById($id) {
    $sql = 'SELECT `id`, `enabled`, `name`, `username`, `roles_id` as role FROM `users` WHERE id = %d';
    $result = DB::querySlave($sql, array($id));

    if (!$result || $result->numRows() <= 0) {
      return null;
    }

    $result = $result->fetch();
    $result[0]['enabled'] = (bool) $result[0]['enabled'];
    return $result[0];
  }

  public function updateSettings($id, $data) {
    DB::startTransaction();

    $sql = 'UPDATE `users` SET `name` = "%s" WHERE `id` = %d';
    $result = DB::queryMaster($sql, array($data['name'], $id));

    if ($result->affectedRows() > 0) {
      $_SESSION['realName'] = $data['name'];
    }

    if (isset($data['oldPassword'])) {
      $result = DB::queryMaster('SELECT `password`, `salt` FROM `users` WHERE `id` = "%d"', array($id));

      if ($result->numRows() <= 0) {
        DB::rollback();
        return array('error' => 'User doesn\'t exist');
      }

      $result = $result->fetch();
      if (sha1($data['oldPassword'].$result[0]['salt']) != $result[0]['password']) {
        DB::rollback();
        return array('error' => 'Invalid old password');
      }

      $salt = String::randomString();
      $password = sha1($data['password'].$salt);

      $sql = 'UPDATE `users` SET `password` = "%s", `salt` = "%s" WHERE `id` = %d';

      $result = DB::queryMaster($sql, array(
        $password,
        $salt,
        $id,
      ));
    }

    DB::commit();
    return true;
  }
}
