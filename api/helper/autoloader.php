<?php

class Autoloader {
  const FILE_EXT = '.php';

  private $rootDirectory;

  public function __construct($directory) {
    $this->setRootDirectory($directory);
    spl_autoload_register(array($this, 'load'));
  }

  public function setRootDirectory($directory) {
    if (file_exists($directory)) {
      if ($lastPosSlash = strripos($directory, "\\")) {
        $this->rootDirectory = substr($directory, 0, $lastPosSlash);
      } else {
        $this->rootDirectory = $directory;
      }
    }
  }

  private function load($className) {
    $file = $this->scanDirectory($this->rootDirectory, $className . self::FILE_EXT);

    if ($file) {
      require_once($file);
    }
  }

  private function scanDirectory($directory, $file) {
    $iterator = new DirectoryIterator($directory);

    foreach ($iterator as $info) {
      if ($info->isFile() && $info->__toString() == $file) {
        return $directory . DIRECTORY_SEPARATOR . $info->__toString();
      } else if (!$info->isFile() && !$info->isDot()) {
        $result = $this->scanDirectory($directory . $info->__toString(), $file);
        if ($result) {
          return $result;
        }
      }
    }

    return;
  }
}
