<?php

Class String {

  public static function formatIds($string, $id, $append) {
    if (!$string) {
      return null;
    }

    return str_replace(' ', '', $string . $id . $append);
  }

  public static function formatAnswerWarning($string, $id) {
    return self::formatIds($string, $id, 'answerWarning');
  }

  public static function formatOption($string, $id) {
    return self::formatIds($string, $id, 'answerOption');
  }

  public static function formatQuestionDiv($string, $id) {
    return self::formatIds($string, $id, 'divQuestion');
  }

  public static function formatQuestionId($string, $id) {
    return self::formatIds($string, $id, 'question');
  }

  public static function formatAnswerId($string, $id) {
    return self::formatIds($string, $id, 'answer');
  }

  public static function formatLabelId($string, $id) {
    return self::formatIds($string, $id, 'label');
  }

  public static function randomString($min = 10, $max = 50) {
    $stringLength = rand($min, $max -1);
    $reference = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    return substr(str_shuffle(str_repeat($reference, 5)), 0, $stringLength);
  }
}
