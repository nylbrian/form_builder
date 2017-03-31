<?php

class Auth {
  const COOKIE_AUTH = 'COOKIE_AUTH';
  const COOKIE_TIME = 2592000;
  private $userId;
  private $userName;
  private $userRole;
  private $realName;
  private static $instance;

  protected function __construct() {
    $this->checkLoggedIn();
  }

  public static function getInstance() {
    if (!self::$instance) {
      self::$instance = new Auth();
      self::$instance->__construct();
    }

    return self::$instance;
  }

  private function checkLoggedIn() {
    if ($this->userId && $this->userName && $this->userRole) {
      return true;
    }

    if (isset($_SESSION['userId']) && isset($_SESSION['userName']) && isset($_SESSION['userRole'])) {
      $this->userId = $_SESSION['userId'];
      $this->userName = $_SESSION['userName'];
      $this->userRole = $_SESSION['userRole'];
      $this->realName = $_SESSION['realName'];
      return true;
    }

    if (isset($_COOKIE[self::COOKIE_AUTH])) {
      parse_str($_COOKIE[self::COOKIE_AUTH]);

      $result = DB::queryMaster('SELECT * from `users` WHERE `id` = %d AND `enabled` = 1', array($user_id));

      if ($result->numRows() > 0) {
        $result = $result->fetch();
        if ($hash === sha1($result[0]['password'])) {
          $this->userId = $_SESSION['userId'] = $user_id;
          $this->userName = $_SESSION['userName'] = $result[0]['username'];
          $this->userRole = $_SESSION['userRole'] = $result[0]['roles_id'];
          $this->realName = $_SESSION['realName'] = $result[0]['name'];
          return true;
        }
      }
    }

    return false;
  }

  public function login($data) {
    if ($this->checkLoggedIn()) {
      return true;
    }

    $result = DB::queryMaster('SELECT * from `users` WHERE `username` = "%s" AND `enabled` = 1', array($data['username']));

    if ($result->numRows() <= 0) {
      return false;
    }

    $result = $result->fetch();
    if (sha1($data['password'].$result[0]['salt']) != $result[0]['password']) {
      return false;
    }

    $this->setLoggedUser($result[0]);

    if (isset($data['remember']) && $data['remember'] === 'true') {
      setcookie(
        self::COOKIE_AUTH,
        'user_id=' . $result[0]['id'] . '&hash=' . sha1($result[0]['password']),
        time() + self::COOKIE_TIME,
        '/'
      );
    }

    return true;
  }

  public function logout() {
    unset($_COOKIE[self::COOKIE_AUTH]);
    setcookie(self::COOKIE_AUTH, null, -1, '/');
    session_unset();
    session_destroy();
    return true;
  }

  public function setLoggedUser($data) {
    $this->userId = $_SESSION['userId'] = $data['id'];
    $this->userName = $_SESSION['userName'] = $data['username'];
    $this->userRole = $_SESSION['userRole'] = $data['roles_id'];
    $this->realName = $_SESSION['realName'] = $data['name'];
  }

  public function getLoggedUser() {
    if ($this->checkLoggedIn() === true) {
      return array(
        'userId' => $this->userId,
        'userName' => $this->userName,
        'userRole' => $this->userRole,
        'realName' => $this->realName,
      );
    }

    return false;
  }
}
