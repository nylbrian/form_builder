<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . 'autoloader.php');

final class Bootstrap {
  const DEFAULT_METHOD = 'index';

  private $autoloader;

  public function __construct() {
    $this->autoloader = new AutoLoader(__DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR);
  }

  private function initializeEnvironment() {
    // temporary config placed here, will move it to a config file
    $config = array(
      'master' => array(
        'server' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'database' => 'form_builder',
      ),
      'slave' => array(
        'server' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'database' => 'form_builder',
      ),
    );

    DB::initialize($config);
    $session = new Session();
  }

  private function processRequest() {
    $request = FormInput::get();

    if (!is_array($request) || !isset($request['request']) || count($request['request']) <= 0) {
      return false;
    }

    $url = $request['request'];
    $fragments = explode('/', $url);

    eval('$class = new '. ucfirst($fragments[0]) . '();');
    $method = isset($fragments[1]) && !empty($fragments[1]) ? $fragments[1] : self::DEFAULT_METHOD;

    if (!method_exists($class, $method)) {
      return;
    }

    if (isset($fragments[2]) && !empty($fragments[2])) {
      $parameters = array_slice($fragments, 2, count($fragments));
    } else {
      $parameters = array();
    }

    return call_user_func_array(array($class, $method), $parameters);
  }

  private function response($output) {
    header("Content-type: text/json");
    echo json_encode($output);
  }

  public function run() {
    $this->initializeEnvironment();
    $result = $this->processRequest();

    if (!$result) {
      // should redirect to 404?
      $result = ResponseHandler::formatJSONResponse(array(
        'error' => 'Requested page doesn\'t exists',
        'ERROR_CODE' => 404
      ));
    }

    $this->response($result);
  }
}
