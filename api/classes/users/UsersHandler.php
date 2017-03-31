<?php

class UsersHandler {
  private $dao;

  public function __construct() {
    $this->dao = new UsersDAO();
  }

  public function login($data) {
    try {
      $result = Auth::getInstance()->login($data);

      if ($result === true) {
        return array('data' => Auth::getInstance()->getLoggedUser());
      } else {
        return array('error' => 'Invalid username or password');
      }
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }

  public function logout() {
    try {
      $result = Auth::getInstance()->logout();

      if ($result === true) {
        return array('data' => array(), 'message' => 'Successfully logged out');
      } else {
        return array('error' => 'An error has occured. Please try again');
      }
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }

  public function getLoggedUser() {
    try {
      $result = Auth::getInstance()->getLoggedUser();

      if ($result === false) {
        return array('error' => 'User not logged in');
      } else {
        return array('data' => $result);
      }
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }

  public function getTableView($page = 1, $itemsPerPage = 10, $sortBy = 'id', $sortOrder = 'asc', $search = '') {
    try {
      $data = $this->dao->getTableView(
        $page,
        $itemsPerPage,
        $sortBy,
        $sortOrder,
        $search
      );
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
      if ($this->dao->update($id, $data)) {
        return array('message' => 'Data updated', 'id' => $id);
      } else {
        return array('error' => 'Failed to update data');
      }
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }

  public function updateSettings($id, $data) {
    try {
      $result = $this->dao->updateSettings($id, $data);
      if ($result === true) {
        return array('message' => 'Data updated');
      } else {
        return array('error' => $result['error']);
      }
    } catch (DBException $e) {
      return array('error' => $e->getMessage());
    }
  }

}
