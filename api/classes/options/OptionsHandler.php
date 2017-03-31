<?php

class OptionsHandler {
  private $dao;

  public function __construct() {
    $this->dao = new OptionsDAO();
  }

  public function getTableView($page = 1, $itemsPerPage = 10, $sortBy = 'id', $sortOrder = 'asc', $search = '') {
    try {
      $data = $this->dao->getTableView($page, $itemsPerPage, $sortBy, $sortOrder, $search);
      if ($data === null) {
        return array('error' => 'No questionnaires found');
      } else {
        return array('data' => $data);
      }
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

  public function getAll() {
    try {
      return array('data' => $this->dao->getAll());
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }

  public function create($data) {
    try {

      if (!isset($data['languages']) || !is_array($data['languages']) ||
        !isset($data['defaultLanguage']) || !is_array($data['defaultLanguage']) ||
        !isset($data['name']) || !isset($data['enabled']) || !isset($data['data']) ||
        !is_array($data['data']) || count($data['data']) <= 0 || count($data['languages']) <= 0 ||
        count($data['defaultLanguage']) <= 0) {
        return array('error' => 'Incomplete Parameters');
      }

      $insertId = $this->dao->create($data);
      if ($insertId) {
        return array('message' => 'Data inserted', 'id' => $insertId);
      } else {
        return array('error' => 'Failed to insert data');
      }
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }

  public function update($id, $data) {
    try {
      if (!isset($data['languages']) || !is_array($data['languages']) ||
        !isset($data['defaultLanguage']) || !is_array($data['defaultLanguage']) ||
        !isset($data['name']) || !isset($data['enabled']) || !isset($data['data']) ||
        !is_array($data['data']) || count($data['data']) <= 0 || count($data['languages']) <= 0 ||
        count($data['defaultLanguage']) <= 0) {
        return array('error' => 'Incomplete Parameters');
      }

      if ($this->dao->update($id, $data)) {
        return array('message' => 'Data updated', 'id' => $id);
      } else {
        return array('error' => 'Failed to update data');
      }
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }

}
