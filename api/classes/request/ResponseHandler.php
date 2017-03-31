<?php

class ResponseHandler {

  public static function formatJSONResponse($data = array()) {
    if (!is_array($data)) {
      return array('status' => 'failed');
    }

    if (isset($data['error'])) {
      $data['status'] = 'failed';
      return $data;
    }

    $data['status'] = 'success';
    return $data;
  }

}
