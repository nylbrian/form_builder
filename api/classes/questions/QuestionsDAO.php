<?php

class QuestionsDAO {
  protected $questions;
  protected $routes;
  protected $dependencies;
  protected $computations;
  protected $optionsDAO;

  public function __construct() {
    $this->clearVars();
    $this->optionsDAO = new OptionsDAO();
  }

  public function create($data) {
    $this->clearVars();
    DB::startTransaction();

    $sql = 'INSERT INTO `questionnaire`(`name`, `description`, `enabled`) VALUES("%s", "%s", %d)';
    $result = DB::queryMaster($sql, array(
      $data['name'],
      $data['description'],
      $data['enabled'] === 'true' ? 1 : 0,
    ));

    $questionnaireId = $result->insertId();

    if (!$questionnaireId) {
      DB::rollback();
      return false;
    }

    if (!$this->insertLanguages($data, $questionnaireId)) {
      DB::rollback();
      return false;
    }

    if (!$this->insertQuestions($data, $questionnaireId)) {
      DB::rollback();
      return false;
    }

    $this->insertAnswerDependencies();
    $this->insertRoutes();

    $this->updateComputations();

    DB::commit();
    return true;
  }

  public function update($id, $data) {
    $this->clearVars();
    DB::startTransaction();

    $sql = 'UPDATE `questionnaire` SET `name` = "%s", `description` = "%s", `enabled` = %d WHERE `id` = %d';
    $result = DB::queryMaster($sql, array(
      $data['name'],
      $data['description'],
      $data['enabled'] === 'true' ? 1 : 0,
      $id
    ));

    DB::queryMaster('DELETE FROM `questionnaire_item` WHERE `questionnaire_id` = %d', array($id));
    DB::queryMaster('DELETE FROM `questionnaire_languages` WHERE `questionnaire_id` = %d', array($id));

    if (!$this->insertLanguages($data, $id)) {
      DB::rollback();
      return false;
    }

    if (!$this->insertQuestions($data, $id)) {
      DB::rollback();
      return false;
    }

    $this->insertAnswerDependencies();
    $this->insertRoutes();

    $this->updateComputations();

    DB::commit();
    return true;
  }

  public function export($id, $language) {
    $this->clearVars();
    $return = array();

    $sql = 'SELECT * FROM `questionnaire_item` WHERE `questionnaire_id` = %d ORDER by `order` ASC';
    $result = DB::querySlave($sql, array($id));

    if ($result->numRows() <= 0) {
      return;
    }

    $rows = $result->fetch();

    foreach ($rows as $key => $row) {
      $questionContentId = $row['question_id'] ? $row['question_id'] : $row['div_name'];
      $returnIndex = $key === 0 ? 'start' : $questionContentId;
      $questionId = $row['id'];

      $return[$returnIndex] = array(
        'divName' => String::formatQuestionDiv($row['div_name'], $questionId),
      );

      if ($row['question_id']) {
        $return[$returnIndex]['questionId'] = String::formatQuestionId($questionContentId, $questionId);
      }

      $questionContent = $this->getQuestionItemQuestionLanguage($row['id'], $language);
      $labelContent = $this->getQuestionItemLabelLanguage($row['id'], $language);

      if ($labelContent) {
        $return[$returnIndex]['label'] = $labelContent;
      }

      if ($questionContent) {
        $return[$returnIndex]['question'] = $questionContent;
      }

      // optional
      if ((boolean) $row['optional'] === true) {
        $return[$returnIndex]['optional'] = (boolean) $row['optional'];
      }

      $questionAnswers = $this->getAnswers($row['id'], $language);
      $answers = array();
      $validation = array();

      foreach ($questionAnswers as $key => $answer) {
        $answerIdentifier = String::formatAnswerId($answer['name'], $key);
        $answers[] = $answerIdentifier;
        $optionValue = array();

        if (isset($answer['validation'])) {
          $validation = $answer['validation'];
        }

        if (isset($answer['optionValue'])) {
          $optionValue = $answer['optionValue'];
          unset($answer['optionValue']);
        }

        $return[$answerIdentifier] = $answer;

        if (count($optionValue) > 0) {
          $return[$answer['value']] = $optionValue;
        }
      }

      if (count($answers) > 0 && count($answers) <= 1) {
        $return[$returnIndex]['answer'] = $answers[0];
      } else if (count($answers) > 1) {
        $return[$returnIndex]['answer'] = $answers;
      }

      // routes
      $route = $this->getQuestionRoute($row['id']);

      if (count($route) > 0) {
        $return[$returnIndex]['route'] = $route;
      }

      if (count($validation) > 0) {
        $return[$returnIndex] = array_merge($return[$returnIndex], $validation);
      }
    }

    return $return;

    /*echo '<pre>' . json_encode($return, JSON_PRETTY_PRINT) . '</pre>';
    die();*/
  }

  protected function getAnswers($id, $language) {
    $sql = 'SELECT qia.*, it.`name` as input_name, qia.`value`,
        qia2.`name` as compute_name, dt.`name` as data_type
      FROM `questionnaire_item_answer` qia
        INNER JOIN `input_type` it
          ON it.`id` = qia.`input_type_id`
        LEFT JOIN `questionnaire_item_answer` qia2
          ON qia.`compute` = qia2.`id`
        LEFT JOIN `data_type` dt
          ON dt.`id` = qia.`data_type_id`
      WHERE qia.`questionnaire_item_id` = %d
        ORDER by qia.`order`';
    $result = DB::querySlave($sql, array($id));
    $rows = $result->fetch();

    $answers = array();

    foreach ($rows as $answer) {
      $answerId = $answer['id'];

      $answers[$answerId] = array(
        'type' => strtolower($answer['input_name']),
        'name' => $answer['name'],
        'dType' => $answer['data_type'],
      );

      $label = $this->getAnswerItemLabelLanguage($answer['id'], $language);
      $labelId = String::formatLabelId($answer['label_id'], $answer['id']);

      if ($label) {
        $answers[$answerId]['label'] = $label;
        $answers[$answerId]['labelId'] = $labelId;
      }

      if ($answer['position']) {
        $answers[$answerId]['position'] = $answer['position'];
      }

      if ($answer['compute_name']) {
        $answers[$answerId]['compute'] = $answer['compute_name'];
      }

      if ($answer['formula']) {
        $answers[$answerId]['formula'] = $answer['formula'];
      }

      $dependencies = $this->getAnswerDependency($answer['id'], $language);

      if (count($dependencies) > 0) {
        $answers[$answerId]['value'] = String::formatOption($answer['name'], $answer['id']);
        $answers[$answerId]['valueDep'] = $dependencies['name'];
        $answers[$answerId]['optionValue'] = $dependencies['list'];
      } else if ($answer['value'] !== '' && in_array($answer['input_type_id'], array(2, 3, 6))) {
        $answers[$answerId]['value'] = String::formatOption($answer['name'], $answer['id']);
        $answers[$answerId]['optionValue'] = $this->getOptions($answer['value'], $language);
      }

      $attributes = $this->getAnswerAttributes($answer['id']);

      if (count($attributes) > 0) {
        foreach ($attributes as $attr) {
          $answers[$answerId]['attr'][$attr['key']] = $attr['value'];
        }

      }

      $validation = $this->getAnswerValidation($answer['id']);

      if (count($validation) > 0) {
        $answers[$answerId]['validation'] = array(
          'warning' => $validation['warningMessage'],
          'warnId' => String::formatAnswerWarning($validation['warningId'], $answer['id']),
          'warnCond' => array(array(
            'fieldId' => $answer['name'],
            'operator' => (int) $validation['operator'],
            'conValue' => (int) $validation['value']['id'],
          )),
        );
      }
    }

    return $answers;
  }

  protected function getAnswerDependency($id, $language) {
    $sql = 'SELECT qia.`input_type_id`, qia2.`input_type_id` as input_type_dep_id,
      qiadi.`value`, qiadi.`answer`, qia2.`name`
      FROM `questionnaire_item_answer` qia
        INNER JOIN `questionnaire_item_answer_dependency` qiad
          ON qia.`id` = qiad.`questionnaire_item_answer_id`
        INNER JOIN `questionnaire_item_answer_dependency_item` qiadi
          ON qiad.`id` = qiadi.`questionnaire_item_answer_dependency_id`
        INNER JOIN `questionnaire_item_answer` qia2
          ON qia2.`id` = qiad.`questionnaire_item_answer_id_dependency`
      WHERE qiad.`questionnaire_item_answer_id` = %d';

    $result = DB::querySlave($sql, array($id));

    if (!$result || $result->numRows() <= 0) {
      return array();
    }

    $rows = $result->fetch();
    $dependencies = array();

    foreach ($rows as $row) {
      if (in_array($row['input_type_id'], array(2, 3, 6))) {
        $dependencies['list'][$row['value']] = $this->getOptions($row['answer'], $language);
      } else {
        $dependencies['list'][$row['value']] = $row['answer'];
      }

      $dependencies['name'] = $row['name'];
    }

    return $dependencies;
  }

  protected function getOptions($id, $language) {
    $return = new ArrayObject();
    $options = $this->optionsDAO->getOptions($id, $language);

    foreach ($options as $option) {
      $return[$option['id']] = $option['name'];
    }

    return $return;
  }

  protected function getQuestionRoute($id) {
    $sql = 'SELECT qir.`id`, qid.`id` as dep_id, qid.`question_id`, qid.`div_name`
      FROM `questionnaire_item_route` qir
        INNER JOIN `questionnaire_item` qi
          ON qi.`id` = qir.`questionnaire_item_id`
        LEFT JOIN `questionnaire_item` qid
          ON qid.`id` = qir.`questionnaire_item_id_route`
      WHERE qir.`questionnaire_item_id` = %d';

    $result = DB::querySlave($sql, array($id));
    $rows = $result->fetch();
    $routes = array();

    foreach ($rows as $key => $row) {
      $routes[$key] = array();

      $condition = $this->getQuestionRouteFields($row['id']);
      $clear = $this->getQuestionRouteClears($row['id']);

      if (count($condition) > 0) {
        $routes[$key]['condition'] = $condition;
      }

      if (count($clear) > 0) {
        $routes[$key]['clear'] = $clear;
      }

      $routes[$key]['nextQuest'] = $row['question_id'] ? $row['question_id'] : $row['div_name'];
    }

    return $routes;
  }

  protected function getQuestionRouteClears($id) {
    $sql = 'SELECT qia.`id`, qia.`name`
      FROM `questionnaire_item_route` qir
        INNER JOIN `questionnaire_item_route_clear` qirc
          ON qirc.`questionnaire_item_route_id` = qir.`id` AND qir.`id` = %d
        INNER JOIN `questionnaire_item_answer` qia
          ON qia.`id` = qirc.`questionnaire_item_answer_id`';

    $result = DB::querySlave($sql, array($id));
    $rows = $result->fetch();
    $clears = array();

    foreach ($rows as $row) {
      $clears[] = $row['name'];
    }

    return $clears;
  }

  protected function getQuestionRouteFields($id) {
    $sql = 'SELECT qia.`id`, qia.`name`, qirc.`condition`,
      qirc.`operator`, qirc.`value`, qia.`input_type_id`, qia.`value` as dropdown,
      qiad.`id` as dependency_id
      FROM `questionnaire_item_route_condition` qirc
        INNER JOIN `questionnaire_item_answer` qia
          ON qia.`id` = qirc.`questionnaire_item_answer_id`
        LEFT JOIN `questionnaire_item_answer_dependency` qiad
          ON qiad.`questionnaire_item_answer_id` = qia.`id`
      WHERE qirc.`questionnaire_item_route_id` = %d';

    $result = DB::querySlave($sql, array($id));
    $rows = $result->fetch();
    $conditions = array();

    foreach ($rows as $key => $condition) {
      $cond = json_decode($condition['value'], true);
      $conditions[] = array(
        'fieldId' => $condition['name'],
        'operator' => (int) $condition['condition'],
        'conValue' => isset($cond['id']) ? (int) $cond['id'] : '',
      );

      if (in_array($condition['input_type_id'], array(2, 3, 6)) && in_array($condition['condition'], array(9, 10))) {
        $current = count($conditions) - 1;
        $conditions[$current]['conValue'] = explode("|||", $cond['id']);

        /*if (!$condition['dependency_id']) {
          $dropdown = $this->optionsDAO->getById($condition['dropdown']);
          foreach ($dropdown['data'] as $value) {
            $conditions[$current]['conValue'][] = $value['id'];
          }
        } else {
          $result = DB::queryMaster('
            SELECT oi.FROM
            id `questionnaire_item_answer_dependency` qiad
              INNER JOIN `questionnaire_item_answer_dependency_item` qiadi
                ON qiad.`id` = qiadi.`questionnaire_item_answer_dependency_id`
              INNER JOIN options o ON o.id = qiadi.answer
              INNER JOIN options_item oi ON o.id = oi.options_id
            WHERE qiad.`questionnaire_item_answer_id` = %d
            ORDER BY oi.`id` ASC',
            array($condition['id']));

          if ($result->numRows() > 0) {
            $result = $result->fetch();

            foreach ($result as $value) {
              $conditions[$current]['conValue'][] = (int) $value['id'];
            }
          }
        }*/
      }

      if ($key < (count($rows) - 1)) {
        $conditions[] = (int) $condition['operator'];
      }
    }

    return $conditions;
  }

  protected function getQuestionItemQuestionLanguage($id, $language_id) {
    $sql = 'SELECT l.`id`, qi.`value`
      FROM `questionnaire_item_question` qi
        INNER JOIN `languages` l on l.`id` = qi.`languages_id`
      WHERE qi.`questionnaire_item_id` = %d
        AND qi.`languages_id` = %d LIMIT 1';
    $result = DB::querySlave($sql, array($id, $language_id));

    if ($result->numRows() <= 0) {
      return '';
    }

    $rows = $result->fetch();
    $question = explode("\n", $rows[0]['value']);

    if (count($question) <= 1) {
      return $question[0];
    }

    return $question;
  }

  protected function getQuestionItemLabelLanguage($id, $language_id) {
    $sql = 'SELECT l.`id`, qi.`value`
      FROM `questionnaire_item_label` qi
        INNER JOIN `languages` l on l.`id` = qi.`languages_id`
      WHERE qi.`questionnaire_item_id` = %d
        AND qi.`languages_id` = %d LIMIT 1';
    $result = DB::querySlave($sql, array($id, $language_id));

    if ($result->numRows() <= 0) {
      return '';
    }

    $rows = $result->fetch();
    $label = explode("\n", $rows[0]['value']);

    return $label;
  }

  protected function getAnswerItemLabelLanguage($id, $language_id) {
    $sql = 'SELECT l.`id`, qi.`value`
      FROM `questionnaire_item_answer_label` qi
        INNER JOIN `languages` l on l.`id` = qi.`languages_id`
      WHERE qi.`questionnaire_item_answer_id` = %d
        AND qi.`languages_id` = %d LIMIT 1';
    $result = DB::querySlave($sql, array($id, $language_id));

    if ($result->numRows() <= 0) {
      return '';
    }

    $rows = $result->fetch();
    $label = explode("\n", $rows[0]['value']);

    if (count($label) <= 1) {
      return $label[0];
    }

    return $label;
  }

  public function getTableView($page = 1, $itemsPerPage = 10, $sortBy = 'id', $sortOrder = 'asc', $search = '') {
    if ($search !== '') {
      $where = ' WHERE `name` LIKE "%%' . $search . '%%" OR `description` LIKE "%%' . $search . '%%"';
    } else {
      $where = '';
    }

    $sql = 'SELECT count(`id`) as cnt from `questionnaire`' . $where;
    $result = DB::querySlave($sql);

    if ($result->numRows() <= 0) {
      return null;
    }

    $count = $result->fetch();
    $count = $count[0];

    if ($page > ceil($count['cnt'] / $itemsPerPage)) {
      $page = 1;
    }

    $offset = ($page - 1) * $itemsPerPage;
    $sql = 'SELECT * FROM `questionnaire` ' . $where . ' order by `%s` %s LIMIT %d, %d';
    $result = DB::querySlave($sql, array(
      $sortBy,
      $sortOrder,
      $offset,
      $itemsPerPage,
    ));

    if ($result->numRows() <= 0) {
      return null;
    }

    return array(
      'rows' => $result->fetch(),
      'count' => (int) $count['cnt']
    );
  }

  public function getById($id) {
    $this->clearVars();

    $sql = 'SELECT * FROM `questionnaire` WHERE `id` = %d LIMIT 1';
    $result = DB::querySlave($sql, array($id));

    if (!$result->numRows()) {
      return false;
    }

    $questionForm = $result->fetch();

    $return = array(
      'name' => $questionForm[0]['name'],
      'id' => $questionForm[0]['id'],
      'description' => $questionForm[0]['description'],
      'enabled' => (boolean) $questionForm[0]['enabled'],
      'languages' => $this->getQuestionnaireLanguages($id),
      'questions' => $this->getQuestionnaireItems($id)
    );

    $this->formatAnswerDependencies($return);
    $this->formatQuestionRoute($return);
    $this->formatComputeFields($return);

    return $return;
  }

  public function getQuestionnaireLanguages($id) {
    $languages = array();

    $sql = 'SELECT l.*
      FROM `questionnaire_languages` ql
        INNER JOIN `languages` l ON l.`id` = ql.`languages_id`
      WHERE `questionnaire_id` = %d';

    $result = DB::querySlave($sql, array($id));
    $rows = $result->fetch();

    foreach ($rows as $row) {
      $languages[] = $row;
    }

    return $languages;
  }

  public function getQuestionnaireItems($id) {
    $questions = array(
      'list' => array()
    );

    $sql = 'SELECT * FROM `questionnaire_item` WHERE `questionnaire_id` = %d ORDER BY `order` ASC';
    $result = DB::querySlave($sql, array($id));
    $rows = $result->fetch();

    foreach ($rows as $key => $row) {
      $this->questions[$row['id']] = array(
        'countId' => $key,
        'answers' => new ArrayObject()
      );

      $labels = $this->getQuestionItemLabels($row['id']);
      $question = $this->getQuestionItemQuestions($row['id']);

      $questions['list'][] = array(
        'properties' => array(
          'questionId' => $row['question_id'],
          'name' => $row['div_name'],
          'questions' => $question,
          'countId' => $key,
          'display' => array(
            'hasQuestions' => count($question) > 0,
            'hasLabels' => count($labels) > 0,
            'enabled' => (boolean) $row['enabled'],
            'optional' => (boolean) $row['optional']
          ),
          'labels' => $labels,
          'answers' => $this->getQuestionAnswers($row['id']),
          'route' => array(),
          'id' => $row['id'],
        )
      );
    }

    return $questions;
  }

  public function getQuestionItemQuestions($id) {
    $questions = new ArrayObject();

    $sql = 'SELECT l.`id`, qi.`value`
      FROM `questionnaire_item_question` qi
        INNER JOIN `languages` l on l.`id` = qi.`languages_id`
      WHERE qi.`questionnaire_item_id` = %d';
    $result = DB::querySlave($sql, array($id));
    $rows = $result->fetch();

    foreach ($rows as $row) {
      $questions[$row['id']] = $row['value'];
    }

    return $questions;
  }

  public function getQuestionItemLabels($id) {
    $labels = new ArrayObject();

    $sql = 'SELECT l.`id`, qi.`value`
      FROM `questionnaire_item_label` qi
        INNER JOIN `languages` l on l.`id` = qi.`languages_id`
      WHERE qi.`questionnaire_item_id` = %d';
    $result = DB::querySlave($sql, array($id));
    $rows = $result->fetch();

    foreach ($rows as $row) {
      $labels[$row['id']] = $row['value'];
    }

    return $labels;
  }

  public function getQuestionAnswers($id) {
    $answers = array();

    $sql = 'SELECT qia.* FROM `questionnaire_item_answer` qia
      WHERE `questionnaire_item_id` = %d ORDER by `order`';
    $result = DB::querySlave($sql, array($id));
    $rows = $result->fetch();

    foreach ($rows as $key => $row) {
      $this->questions[$id]['answers'][$row['id']] = $key;

      $attributes = $this->getAnswerAttributes($row['id']);
      $labels = $this->getAnswerLabels($row['id']);
      $validation = $this->getAnswerValidation($row['id']);

      $answers[] = array(
        'properties' => array(
          'name' => $row['name'],
          'type' => $row['input_type_id'],
          'size' => $row['size'],
          'position' => $row['position'],
          'formula' => $row['formula'],
          'compute' => $row['compute'],
          'dataType' => (int) $row['data_type_id'],
          'display' => array(
            'hasAttributes' => count($attributes) > 0,
            'hasValidation' => count($validation) > 0,
            'hasLabels' => count($labels) > 0,
            'hasDependency' => false,
            'enabled' => (boolean) $row['enabled']
          ),
          'attributes' => $attributes,
          'validation' => $validation,
          'labelId' => $row['label_id'],
          'labels' => $labels,
          'dependency' => array(),
          'countId' => $key,
          'value' => $row['value'],
          'id' => $row['id'],
        )
      );

      $currentKey = count($answers) - 1;
      if ($answers[$currentKey]['properties']['value'] &&
        in_array($answers[$currentKey]['properties']['type'], array(2, 3, 6))) {

        $answers[$currentKey]['properties']['option']['properties'] = $this->optionsDAO->getById(
          $answers[$currentKey]['properties']['value']
        );
      }
    }

    return $answers;
  }

  public function getAnswerAttributes($id) {
    $attributes = array();

    $sql = 'SELECT * FROM `questionnaire_item_answer_attribute` WHERE `questionnaire_item_answer_id` = %d';
    $result = DB::querySlave($sql, array($id));
    $rows = $result->fetch();

    foreach ($rows as $attribute) {
      $attributes[] = array(
        'key' => $attribute['key'],
        'value' => $attribute['value']
      );
    }

    return $attributes;
  }

  public function getAnswerLabels($id) {
    $labels = new ArrayObject();

    $sql = 'SELECT l.`id`, qi.`value`
      FROM `questionnaire_item_answer_label` qi
        INNER JOIN `languages` l on l.`id` = qi.`languages_id`
      WHERE qi.`questionnaire_item_answer_id` = %d';
    $result = DB::querySlave($sql, array($id));
    $rows = $result->fetch();

    foreach ($rows as $row) {
      $labels[$row['id']] = $row['value'];
    }

    return $labels;
  }

  public function getAnswerValidation($id) {
    $validation = new ArrayObject();

    $sql = 'SELECT * FROM `questionnaire_item_answer_validation` WHERE `questionnaire_item_answer_id` = %d';
    $result = DB::querySlave($sql, array($id));

    if ($result->numRows() > 0) {
      $row = $result->fetch();
      $validation = array(
        'operator' => (int) $row[0]['operator'],
        'value' => array('id' => $row[0]['value']),
        'warningId' => $row[0]['warning_id'],
        'warningMessage' => $row[0]['message'],
      );
    }

    return $validation;
  }

  public function getAnswerDependencies($id) {
    $dependencies = new ArrayObject();

    $sql = 'SELECT qi.`id` as question_id, qia.`id` as answer_id,
      qir.`id` as question_dependency_id, qiar.`id` as answer_dependency_id,
      qia.`input_type_id`, qiar.`input_type_id` as dep_input_type_id,
      qiadi.`value`, qiadi.`answer`
      FROM `questionnaire_item` qi
        INNER JOIN `questionnaire_item_answer` qia
          ON qi.`id` = qia.`questionnaire_item_id` AND qi.`questionnaire_id` = %d
        INNER JOIN `questionnaire_item_answer_dependency` qiad
          ON qiad.`questionnaire_item_answer_id` = qia.`id`
        INNER JOIN `questionnaire_item_answer` qiar
          ON qiad.`questionnaire_item_answer_id_dependency` = qiar.`id`
        INNER JOIN `questionnaire_item` qir
          ON qir.`id` = qiar.`questionnaire_item_id`
        INNER JOIN `questionnaire_item_answer_dependency_item` qiadi
          ON qiadi.`questionnaire_item_answer_dependency_id` = qiad.`id`';
    $result = DB::querySlave($sql, array($id));

    $rows = $result->fetch();

    foreach ($rows as $row) {
      $dependencies[$row['question_id']][$row['answer_id']][] = $row;
    }

    return $dependencies;
  }

  public function getQuestionRoutes($id) {
    $routes = new ArrayObject();

    $sql = 'SELECT qi.`id`, qir.`id` as `route_id`, qir.`questionnaire_item_id_route`
      FROM `questionnaire_item` qi
        INNER JOIN `questionnaire_item_route` qir
          ON qi.`id` = qir.`questionnaire_item_id`
      WHERE qi.`questionnaire_id` = %d';

    $result = DB::querySlave($sql, array($id));

    $rows = $result->fetch();

    foreach ($rows as $row) {
      $routes[$row['id']][] = array(
        'conditions' => $this->getQuestionRouteConditions($row['route_id']),
        'clear' => $this->getQuestionRouteClear($row['route_id']),
        'next' => $row['questionnaire_item_id_route']
      );
    }

    return $routes;
  }

  public function getQuestionRouteConditions($id) {
    $conditions = array();

    $sql = 'SELECT qi.`id`, qi2.`id` as id2, qirc.`condition`, qirc.`questionnaire_item_answer_id`,
      qirc.`condition`, qirc.`value`, qirc.`operator`
      FROM `questionnaire_item_route` qir
        INNER JOIN `questionnaire_item_route_condition` qirc
          ON qirc.`questionnaire_item_route_id` = qir.`id` AND qir.`id` = %d
        INNER JOIN `questionnaire_item` qi
          ON qi.`id` = qir.`questionnaire_item_id`
        INNER JOIN `questionnaire_item_answer` qia
          ON qia.`id` = qirc.`questionnaire_item_answer_id`
        INNER JOIN questionnaire_item qi2
          ON qi2.`id` = qia.`questionnaire_item_id` ';

    $result = DB::querySlave($sql, array($id));
    $rows = $result->fetch();

    foreach ($rows as $row) {
      $conditions[] = array(
        'questionId' => $row['id'],
        'answer' => $row['questionnaire_item_answer_id'],
        'condition' => $row['condition'],
        'value' => $row['value'],
        'operator' => $row['operator'],
        'question' => $row['id2'],
      );
    }

    return $conditions;
  }

  public function getQuestionRouteClear($id) {
    $clear = array();

    $sql = 'SELECT qi.`id`, qirc.`questionnaire_item_answer_id`
      FROM `questionnaire_item_route` qir
        INNER JOIN `questionnaire_item_route_clear` qirc
          ON qirc.`questionnaire_item_route_id` = qir.`id` AND qir.`id` = %d
        INNER JOIN `questionnaire_item_answer` qia
          ON qia.`id` = qirc.`questionnaire_item_answer_id`
        INNER JOIN `questionnaire_item` qi
          ON qi.`id` = qia.`questionnaire_item_id`';

    $result = DB::querySlave($sql, array($id));
    $rows = $result->fetch();

    foreach ($rows as $row) {
      $clear[] = array(
        'question' => $row['id'],
        'answer' => $row['questionnaire_item_answer_id']
      );
    }

    return $clear;
  }

  protected function insertLanguages($data, $id) {
    if (!isset($data['languages']) || !is_array($data['languages']) || count($data['languages']) <= 0) {
      return false;
    }

    foreach ($data['languages'] as $key => $language) {
      $sql = 'INSERT INTO `questionnaire_languages`(`questionnaire_id`, `languages_id`) VALUES(%d, %d)';

      DB::queryMaster($sql, array($id, $language['id']));
    }

    return true;
  }

  protected function insertQuestions($data, $id) {
    foreach ($data['questions']['list'] as $key => $question) {
      $sql = 'INSERT INTO `questionnaire_item`
        (`questionnaire_id`, `div_name`, `question_id`, `optional`, `order`, `enabled`)
        VALUES(%d, "%s", "%s", %d, %d, %d)';

      $result = DB::queryMaster($sql, array(
        $id,
        $question['properties']['name'],
        $question['properties']['questionId'],
        $question['properties']['display']['optional'] === 'true' ? 1 : 0,
        $key,
        $question['properties']['display']['enabled'] === 'true' ? 1 : 0,
      ));

      $questionItemId = $result->insertId();

      if (!$questionItemId) {
        return false;
      }

      $this->questions[$question['properties']['countId']] = array(
        'questionItemId' => $questionItemId,
        'answers' => array()
      );

      $this->insertQuestionItems($question, $questionItemId);
      $this->insertQuestionLabels($question, $questionItemId);

      $insertAnswer = $this->insertAnswers(
        $question['properties']['answers'],
        $questionItemId,
        $question['properties']['countId']
      );

      if (!$insertAnswer) {
        return false;
      }

      if (isset($question['properties']['route']) &&
        is_array($question['properties']['route']) &&
        count($question['properties']['route']) > 0) {

        $this->routes[] = array(
          'questionId' => $question['properties']['countId'],
          'routes' => $question['properties']['route'],
        );
      }
    }

    return true;
  }

  protected function insertQuestionItems($data, $id) {
    if (!isset($data['properties']['questions']) ||
      !is_array($data['properties']['questions']) ||
      count($data['properties']['questions']) <= 0) {
      return;
    }

    foreach ($data['properties']['questions'] as $key => $question) {
      $sql = 'INSERT INTO `questionnaire_item_question`
        (`questionnaire_item_id`, `languages_id`, `value`)
        VALUES(%d, %d, "%s")';

      DB::queryMaster($sql, array($id, $key, $question));
    }

    return true;
  }

  protected function insertQuestionLabels($data, $id) {
    if (!isset($data['properties']['labels']) ||
      !is_array($data['properties']['labels']) ||
      count($data['properties']['labels']) <= 0) {
      return;
    }

    foreach ($data['properties']['labels'] as $key => $label) {
      $sql = 'INSERT INTO `questionnaire_item_label`
        (`questionnaire_item_id`, `languages_id`, `value`)
        VALUES(%d, %d, "%s")';

      DB::queryMaster($sql, array($id, $key, $label));
    }

    return true;
  }

  protected function insertAnswers($data, $id, $countId) {
    foreach ($data as $key => $answer) {
      $sql = 'INSERT INTO `questionnaire_item_answer`
        (`questionnaire_item_id`, `name`, `input_type_id`, `size`,
        `position`, `formula`, `value`, `label_id`, `order`, `enabled`, `data_type_id`)
        VALUES(%d, "%s", %d, "%s", "%s", "%s", "%s", "%s", %d, %d, %d)';

      $result = DB::queryMaster($sql, array(
        $id,
        $answer['properties']['name'],
        $answer['properties']['type'],
        isset($answer['properties']['size']) ? $answer['properties']['size'] : null,
        $answer['properties']['position'],
        $answer['properties']['formula'],
        $answer['properties']['display']['hasDependency'] === 'true' ? '' : $answer['properties']['value'],
        $answer['properties']['labelId'],
        $key,
        $answer['properties']['display']['enabled'] === 'true' ? 1 : 0,
        $answer['properties']['dataType'],
      ));

      $answerId = $result->insertId();

      if (!$answerId) {
        return false;
      }

      $this->questions[$countId]['answers'][$answer['properties']['countId']] = $answerId;

      if ($answer['properties']['compute'] !== '') {
        $this->computations[] = array(
          'answerId' => $answerId,
          'answerCount' => $answer['properties']['compute'],
          'questionCount' => $countId,
        );
      }

      if ($answer['properties']['display']['hasAttributes'] === 'true' &&
        isset($answer['properties']['attributes']) &&
        is_array($answer['properties']['attributes']) &&
        count($answer['properties']['attributes']) > 0 &&
        !$this->insertAnswerAttributes($answer['properties']['attributes'], $answerId)) {
        return false;
      }

      if ($answer['properties']['display']['hasLabels'] === 'true' &&
        isset($answer['properties']['labelId']) &&
        isset($answer['properties']['labels']) &&
        is_array($answer['properties']['labels']) &&
        count($answer['properties']['labels']) > 0 &&
        !$this->insertAnswerLabels($answer['properties']['labels'], $answerId)) {
        return false;
      }

      if ($answer['properties']['display']['hasValidation'] === 'true' &&
        isset($answer['properties']['validation']) &&
        is_array($answer['properties']['validation']) &&
        count($answer['properties']['validation']) > 0 &&
        !$this->insertAnswerValidation($answer['properties']['validation'], $answerId)) {
        return false;
      }

      if ($answer['properties']['display']['hasDependency'] === 'true' &&
        isset($answer['properties']['dependency']) &&
        is_array($answer['properties']['dependency']) &&
        count($answer['properties']['dependency']) > 0) {
        $answer['properties']['dependency']['source'] = $answerId;
        $this->dependencies[] = $answer['properties']['dependency'];
      }
    }

    return true;
  }

  protected function insertAnswerAttributes($data, $answerId) {
    foreach ($data as $key => $attribute) {
      $sql = 'INSERT INTO `questionnaire_item_answer_attribute`
        (`questionnaire_item_answer_id`, `key`, `value`)
        VALUES(%d, "%s", "%s")';

      DB::queryMaster($sql, array(
        $answerId,
        $attribute['key'],
        $attribute['value'],
      ));
    }

    return true;
  }

  protected function insertAnswerLabels($data, $answerId) {
    foreach ($data as $key => $value) {
      $sql = 'INSERT INTO `questionnaire_item_answer_label`
        (`questionnaire_item_answer_id`, `languages_id`, `value`)
        VALUES(%d, %d, "%s")';

      DB::queryMaster($sql, array(
        $answerId,
        $key,
        $value,
      ));
    }

    return true;
  }

  protected function insertAnswerValidation($data, $answerId) {
    $sql = 'INSERT INTO `questionnaire_item_answer_validation`
      (`questionnaire_item_answer_id`, `operator`, `value`, `warning_id`, `message`)
      VALUES(%d, %d, "%s", "%s", "%s")';

    DB::queryMaster($sql, array(
      $answerId,
      $data['operator'],
      $data['value']['id'],
      $data['warningId'],
      $data['warningMessage'],
    ));

    return true;
  }

  protected function updateComputations() {
    foreach ($this->computations as $computation) {
      DB::queryMaster('
        UPDATE `questionnaire_item_answer`
        SET compute = %d
        WHERE `id` = %d',
        array(
          $this->getAnswerId(
            $computation['questionCount'],
            $computation['answerCount']
          ),
          $computation['answerId']
        )
      );
    }
  }

  protected function insertRoutes() {
    foreach ($this->routes as $routes) {
      foreach ($routes['routes'] as $route) {
        $questionItemId = $this->questions[$routes['questionId']]['questionItemId'];

        if (!isset($route['properties']['nextQuestion'])) {
          $nextQuestionItemId = null;
        } else {
          $nextQuestionItemId = $this->questions[$route['properties']['nextQuestion']]['questionItemId'];
        }

        if (!$questionItemId) {
          continue;
        }

        $sql = 'INSERT INTO `questionnaire_item_route`
          (`questionnaire_item_id`, `questionnaire_item_id_route`)
          VALUES(%d, %d)';

        $result = DB::queryMaster($sql, array(
          $questionItemId,
          $nextQuestionItemId,
        ));

        $routeId = $result->insertId();

        if (!$routeId) {
          continue;
        }

        foreach ($route['properties']['list'] as $item) {
          $sql = 'INSERT INTO `questionnaire_item_route_condition`
            (`questionnaire_item_route_id`, `questionnaire_item_answer_id`,
            `condition`, `value`, `operator`) VALUES(%d, %d, %d, "%s", %d)';

          DB::queryMaster($sql, array(
            $routeId,
            $this->getAnswerId(
              $item['properties']['question'],
              $item['properties']['value']
            ),
            $item['properties']['condition'],
            json_encode($item['properties']['conditionValue']),
            $item['properties']['operator'],
          ));
        }

        if (isset($route['properties']['clear'])) {
          foreach ($route['properties']['clear'] as $clear) {
            $sql = 'INSERT INTO `questionnaire_item_route_clear`
              (`questionnaire_item_route_id`, `questionnaire_item_answer_id`)
              VALUES(%d, %d)';

            DB::queryMaster($sql, array(
              $routeId,
              $this->getAnswerId(
                $clear['properties']['question'],
                $clear['properties']['answer']
              ),
            ));
          }
        }
      }
    }

    return true;
  }

  protected function insertAnswerDependencies() {
    foreach ($this->dependencies as $dependency) {
      $nextQuestionId = $this->getAnswerId(
        $dependency['question'],
        $dependency['answer']
      );

      if (!$nextQuestionId) {
        continue;
      }

      $sql = 'INSERT INTO `questionnaire_item_answer_dependency`
        (`questionnaire_item_answer_id`, `questionnaire_item_answer_id_dependency`)
        VALUES(%d, %d)';

      $result = DB::queryMaster($sql, array(
        $dependency['source'],
        $nextQuestionId,
      ));

      $questionItemAnswerDependencyId = $result->insertId();

      if (!$questionItemAnswerDependencyId) {
        continue;
      }

      foreach ($dependency['list'] as $item) {
        $sql = 'INSERT INTO `questionnaire_item_answer_dependency_item`
          (`questionnaire_item_answer_dependency_id`, `value`, `answer`)
          VALUES(%d, "%s", "%s")';

        DB::queryMaster($sql, array(
          $questionItemAnswerDependencyId,
          $item['source'],
          $item['answer'],
        ));
      }
    }

    return true;
  }

  protected function getAnswerId($questionCount, $answerCount) {
    if (isset($this->questions[$questionCount]) &&
      isset($this->questions[$questionCount]['answers'][$answerCount])) {
      return $this->questions[$questionCount]['answers'][$answerCount];
    }
    return false;
  }

  protected function getAnswerCountId($questionId, $answerId) {
    if (isset($this->questions[$questionId]) &&
      isset($this->questions[$questionId]['answers'][$answerId])) {
      return $this->questions[$questionId]['answers'][$answerId];
    }
    return false;
  }

  protected function formatComputeFields(&$return) {
    foreach ($return['questions']['list'] as &$question) {
      $questionId = $question['properties']['id'];
      foreach ($question['properties']['answers'] as &$answer) {
        if ((int) $answer['properties']['compute'] > 0) {
          $answerId = $answer['properties']['compute'];
          $answer['properties']['compute'] = (int) $this->questions[$questionId]['answers'][$answerId];
        } else {
          unset($answer['properties']['compute']);
        }
      }
    }
  }

  protected function formatAnswerDependencies(&$return) {
    $answerDependencies = $this->getAnswerDependencies($return['id']);

    foreach ($return['questions']['list'] as &$question) {
      $questionId = $question['properties']['id'];

      if (!isset($answerDependencies[$questionId])) {
        continue;
      }

      foreach ($question['properties']['answers'] as &$answer) {
        $answerId = $answer['properties']['id'];

        if (!isset($answerDependencies[$questionId][$answerId])) {
          continue;
        }

        $list = array();
        $answerDependency = $answerDependencies[$questionId][$answerId];

        foreach ($answerDependency as $value) {
          $list[] = array(
            'source' => $value['value'],
            'answer' => $value['answer']
          );

          if (in_array($value['input_type_id'], array(2, 3, 6))) {
            $list[count($list) - 1]['option'] = array(
              'properties' => $this->optionsDAO->getById($value['answer'])
            );
          }

          $questionDependencyId = $value['question_dependency_id'];
          $answerDependencyId = $value['answer_dependency_id'];
        }

        $answer['properties']['display']['hasDependency'] = true;
        $answer['properties']['dependency'] = array(
          'question' => $this->questions[$questionDependencyId]['countId'],
          'answer' => $this->questions[$questionDependencyId]['answers'][$answerDependencyId],
          'list' => $list
        );
      }
    }
  }

  protected function formatQuestionRoute(&$return) {
    $routes = $this->getQuestionRoutes($return['id']);

    foreach ($return['questions']['list'] as &$question) {
      $questionId = $question['properties']['id'];

      if (!isset($routes[$questionId])) {
        continue;
      }

      $questionRoutes = array();
      foreach ($routes[$questionId] as $route) {
        $conditions = array();

        foreach ($route['conditions'] as $condition) {
          $questionRouteId = $condition['questionId'];
          $answerId = $condition['answer'];

          $conditions[] = array(
            'properties' => array(
              'condition' => (int) $condition['condition'],
              'value' => $this->questions[$condition['question']]['answers'][$answerId],
              'conditionValue' => json_decode($condition['value']),
              'operator' => (int) $condition['operator'],
              'question' => (int) $this->questions[$condition['question']]['countId'],
            )
          );
        }

        if (isset($questionRouteId)) {
          $nextQuestion = $questionRouteId;
        }

        $clears = array();

        foreach ($route['clear'] as $clear) {
          $questionId = $clear['question'];
          $answerId = $clear['answer'];

          $clears[] = array(
            'properties' => array(
              'question' => $this->questions[$questionId]['countId'],
              'answer' => $this->questions[$questionId]['answers'][$answerId],
            )
          );
        }

        if (isset($this->questions[$route['next']])) {
          $nextQuestionCount = $this->questions[$route['next']]['countId'];
        } else {
          $nextQuestionCount = null;
        }

        $questionRoutes[] = array(
          'properties' => array(
            'list' => $conditions,
            'nextQuestion' => $nextQuestionCount,
            'clear' => $clears,
          )
        );
      }

      $question['properties']['route'] = $questionRoutes;
    }
  }

  protected function clearVars() {
    $this->questions = array();
    $this->dependencies = array();
    $this->routes = array();
    $this->computations = array();
  }
}
