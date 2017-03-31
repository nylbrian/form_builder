<?php

class FormInput {

  // data sanitizer to follow

  public static function get() {
    return $_GET;
  }

  public static function post() {
    return $_POST;
  }

  public static function request() {
    return $_REQUEST;
  }

}
