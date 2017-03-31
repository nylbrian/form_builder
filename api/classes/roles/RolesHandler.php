<?php

class RolesHandler {
  private $dao;

  public function __construct() {
    $this->dao = new RolesDAO();
  }

  public function getAll() {
    try {
      return array('data' => $this->dao->getAll());
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }

  public function getById($id) {
    try {
      return array('data' => $this->dao->getById($id));
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }
}
