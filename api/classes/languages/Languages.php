<?php

class Languages {

  public function __construct() {
    $this->handler = new LanguagesHandler();
  }

  public function index($id = null) {
    return $this->get($id);
  }

  public function get($id = null) {
    if ($id) {
      $result = $this->handler->getById($id);
    } else {
      $result = $this->handler->getAll();
    }

    return ResponseHandler::formatJSONResponse($result);
  }

}
