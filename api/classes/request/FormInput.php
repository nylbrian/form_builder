<?php

class FormInput {

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
