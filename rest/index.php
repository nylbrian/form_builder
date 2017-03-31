<?php
  require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
    'api' . DIRECTORY_SEPARATOR . 'bootstrap.php');

  // database config placed temporarily on bootstrap class
  $app = new Bootstrap();
  $app->run();
