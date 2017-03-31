<?php

class QuestionsHandler {
  private $dao;

  public function __construct() {
    $this->dao = new QuestionsDAO();
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

  }

  public function create($data) {
    try {
      if ($this->dao->create($data)) {
        return array('message' => 'Data inserted');
      } else {
        return array('error' => 'Failed to insert data. Please complete required fields.');
      }
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }

  public function update($id, $data) {
    try {
      if ($this->dao->update($id, $data)) {
        return array('message' => 'Data updated');
      } else {
        return array('error' => 'Failed to update data. Please complete required fields.');
      }
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }

  public function export($id, $language) {
    try {
      return array('data' => $this->dao->export($id, $language));
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }

}
