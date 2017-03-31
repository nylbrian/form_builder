<?php

class Options {
  private $handler;

  public function __construct() {
    $this->handler = new OptionsHandler();
  }

  public function index($id = null) {
    return $this->get($id);
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

  public function get($id = null) {
    if ($id) {
      $result = $this->handler->getById($id);
    } else {
      $result = $this->handler->getAll();
    }

    return ResponseHandler::formatJSONResponse($result);
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
}
