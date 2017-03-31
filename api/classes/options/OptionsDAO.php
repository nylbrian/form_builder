<?php

class OptionsDAO {

  public function create($data) {
    DB::startTransaction();

    $sql = 'INSERT INTO `options`(name, enabled, default_language)
      VALUES("%s", %d, %d)';

    $result = DB::queryMaster($sql, array(
      $data['name'],
      $data['enabled'] === 'true' || (int) $data['enabled'] === 1 ? 1 : 0,
      $data['defaultLanguage']['id']
    ));

    $optionId = $result->insertId();

    if (!$optionId) {
      DB::rollback();
      return false;
    }

    $languages = $this->insertOptionLanguages($optionId, $data['languages']);
    if (!$languages) {
      return false;
    }

    $items = $this->insertOptionItem($optionId, $data['data'], $languages);
    if (!$items) {
      return false;
    }

    DB::commit();
    return $optionId;
  }

  public function update($id, $data) {
    DB::startTransaction();

    $sql = 'UPDATE `options` SET `name` = "%s", `enabled` = %d, `default_language` = %d WHERE `id` = %d';
    $result = DB::queryMaster($sql, array(
      $data['name'],
      $data['enabled'] === 'true' || (int) $data['enabled'] === 1 ? 1 : 0,
      $data['defaultLanguage']['id'],
      $id
    ));

    $languages = $this->insertOptionLanguages($id, $data['languages']);
    if (!$languages) {
      return false;
    }

    $items = $this->insertOptionItem($id, $data['data'], $languages);
    if (!$items) {
      return false;
    }

    DB::commit();
    return true;
  }

  public function getTableView($page = 1, $itemsPerPage = 10, $sortBy = 'id', $sortOrder = 'asc', $search = '') {
    if ($search !== '') {
      $where = ' WHERE `name` LIKE "%%' . $search . '%%" ';
    } else {
      $where = '';
    }

    $sql = 'SELECT count(`id`) as cnt from `options`' . $where;
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
    $sql = 'SELECT * FROM `options` ' . $where . ' order by `%s` %s LIMIT %d, %d';
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
    $sql = 'SELECT id, name, enabled, default_language FROM `options` WHERE id = %d';
    $result = DB::querySlave($sql, array($id));

    if (!$result || $result->numRows() <= 0) {
      return null;
    }

    $languageDAO = new LanguagesDAO();
    $result = $result->fetch();
    $option = $result[0];
    $option['enabled'] = (bool) $option['enabled'];

    // get options' data and translations
    $option['defaultLanguage'] = $languageDAO->getById($option['default_language']);
    $option['languages'] = $this->getLanguagesForOption($option['id']);
    $option['data'] = $this->getOptionTranslations($option['id'], $option['languages']);

    return $option;
  }

  public function getOptions($id, $language) {
    $sql = 'SELECT id, name, enabled, default_language FROM `options` WHERE id = %d order by `name` ASC';
    $result = DB::querySlave($sql, array($id));

    if (!$result || $result->numRows() <= 0) {
      return array();
    }

    $result = $result->fetch();
    $option = $result[0];

    $sql = 'SELECT oi.`id`, IFNULL(l.`description`, d.`description`) as `name`
      FROM `options_item` oi
        LEFT JOIN `options_item_translations` d
          ON d.`options_item_id` = oi.`id` AND d.`languages_id` = %d
        LEFT JOIN `options_item_translations` l
          ON l.`options_item_id` = oi.`id` AND l.`languages_id` = %d
      WHERE oi.`options_id` = %d';

    $result = DB::querySlave($sql, array($language, $option['default_language'], $id));

    if (!$result || $result->numRows() <= 0) {
      return array();
    }

    return $result->fetch();
  }

  public function getOptionValues($id) {
    $sql = 'SELECT oi.`id`
      FROM `options` o
        INNER JOIN `options_item` oi
          ON oi.`options_id` = o.`id`
      WHERE o.`id` = %d ORDER BY oi.`id` ASC';

    $options = array();

    $rows = $result->fetch();

    foreach ($rows as $row) {
      $options[] = $row['id'];
    }

    return $options;
  }

  public function getAll() {
    $sql = 'SELECT id, name, enabled, default_language FROM `options` order by `name`';

    $result = DB::querySlave($sql);

    if (!$result || $result->numRows() <= 0) {
      return array();
    }

    $languageDAO = new LanguagesDAO();
    $options = $result->fetch();

    // get options' data and translations
    foreach ($options as &$option) {
      $option['defaultLanguage'] = $languageDAO->getById($option['default_language']);
      $option['languages'] = $this->getLanguagesForOption($option['id']);
      $option['data'] = $this->getOptionTranslations($option['id'], $option['languages']);
    }

    return $options;
  }

  public function getOptionTranslations($id, $languages = array()) {
    if (!$languages || !is_array($languages)) {
      $languages = $this->getLanguagesForOption($id);
    }

    $languageJoin = '';
    $languageSelect = '';
    foreach ($languages as $language) {
      $languageSelect .= DB::sanitize(
        ', %s.`description` as %s',
        array($language['iso6391'], $language['iso6391'])
      );

      $languageJoin .= DB::sanitize('
        LEFT JOIN `options_item_translations` %s
          ON %s.`options_item_id` = oi.`id`
          AND %s.languages_id = %d',
        array($language['iso6391'], $language['iso6391'], $language['iso6391'], $language['id'])
      );
    }

    $sql = 'SELECT oi.id ' . $languageSelect . '
      FROM `options_item` oi ' . $languageJoin . '
      WHERE oi.`options_id` = %d';

    $result = DB::querySlave($sql, array($id));

    if (!$result || $result->numRows() <= 0) {
      return array();
    }

    return $result->fetch();
  }

  public function getLanguagesForOption($id) {
    $sql = 'SELECT l.*
      FROM `options_languages` ol
        INNER JOIN `languages` l ON l.id = ol.languages_id
      WHERE ol.options_id = %d';

    $result = DB::querySlave($sql, array($id));

    if (!$result || $result->numRows() <= 0) {
      return array();
    }

    return $result->fetch();
  }

  private function insertOptionLanguages($id, $data) {
    DB::queryMaster('DELETE FROM `options_languages` WHERE `options_id` = %d', array($id));

    $languages = array();
    foreach ($data as $language) {
      $sql = 'INSERT INTO options_languages(options_id, languages_id) VALUES(%d, %d)';
      $result = DB::queryMaster($sql, array($id, $language['id']));

      if ($result->affectedRows() != 1) {
        DB::rollback();
        return false;
      }

      $languages[$language['iso6391']] = $language['id'];
    }

    return $languages;
  }

  private function insertOptionItem($id, $data, $languages) {
    $inserted = array();

    foreach ($data as $items) {
      if (!isset($items['id']) || !is_numeric($items['id'])) {
        $sql = 'INSERT INTO `options_item`(options_id) VALUES(%d)';
        $result = DB::queryMaster($sql, array($id));
        $itemId = $result->insertId();
      } else {
        $itemId = $items['id'];
      }

      if (!$itemId) {
        DB::rollback();
        return false;
      }

      $inserted[] = $itemId;

      DB::queryMaster('DELETE FROM `options_item_translations` WHERE `options_item_id` = %d', array($itemId));

      foreach ($items as $key => $item) {
        if (trim($item) == '' || !array_key_exists($key, $languages)) {
          continue;
        }

        $sql = 'INSERT INTO `options_item_translations`(options_item_id, languages_id, description)
          VALUES(%d, %d, "%s")';
        $result = DB::queryMaster($sql, array($itemId, $languages[$key], $item));

        if ($result->affectedRows() != 1) {
          DB::rollback();
          return false;
        }
      }
    }

    DB::queryMaster('DELETE FROM `options_item` WHERE `options_id` = %d AND `id` NOT IN (%s)',
      array($id, implode(',', $inserted))
    );

    return true;
  }


}
