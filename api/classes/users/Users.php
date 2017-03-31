<?php

class Users {

  public function __construct() {
    $this->handler = new UsersHandler();
  }

  public function login() {
    $data = FormInput::post();

    $result = $this->handler->login($data);

    return ResponseHandler::formatJSONResponse($result);
  }

  public function logout() {
    $result = $this->handler->logout();
    return ResponseHandler::formatJSONResponse($result);
  }

  public function getLoggedUser() {
    $result = $this->handler->getLoggedUser();
    return ResponseHandler::formatJSONResponse($result);
  }

  public function index($id = null) {
    return $this->get($id);
  }

  public function get($id = null) {
    if ($id) {
      $result = $this->handler->getById($id);
    } else {
      $result = array('error' => 'Missing ID');
    }
    return ResponseHandler::formatJSONResponse($result);
  }

  public function getTableView($page = 1, $itemsPerPage = 10, $sortBy = 'id', $sortOrder = 'asc', $search = '') {
    return ResponseHandler::formatJSONResponse(
      $this->handler->getTableView(
        $page,
        $itemsPerPage,
        $sortBy,
        $sortOrder,
        $search
      )
    );
  }

  public function save($id = null) {
    $data = FormInput::post();

    if ($id) {
      $result = $this->handler->update($id, $data);
    } else {
      $result = $this->handler->create($data);
    }

    return ResponseHandler::formatJSONResponse($result);
  }

  public function updateSettings() {
    $userInfo = Auth::getInstance()->getLoggedUser();
    $data = FormInput::post();

    $result = $this->handler->updateSettings(
      $userInfo['userId'],
      $data
    );

    return ResponseHandler::formatJSONResponse($result);
  }
}
