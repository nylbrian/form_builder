<?php

class InputTypesHandler {
  private $dao;

  public function __construct() {
    $this->dao = new InputTypesDAO();
  }

  public function getAll() {
    try {
      return array('data' => $this->dao->getAll());
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }

}
