<?php
class SQLBaseModifier
{
  public static function default_handler($modifier, &$queryObject, $field) {
    $fieldName = $queryObject['select'][$field]['field'];
    $aliasName = $queryObject['select'][$field]['alias'];
    $params    = SuQLReservedWords::toSql($queryObject['select'][$field]['modifier'][$modifier]);
    $strParams = (count($params) > 0 ? ', ' . implode(', ', $params) : '');

    $queryObject['select'][$field]['field'] = "$modifier($fieldName" . "$strParams)";
    $queryObject['select'][$field]['alias'] = $aliasName;
    unset($queryObject['select'][$field]['modifier'][$modifier]);
  }

  public static function mod_case($case, &$queryObject, $field) {
    $fieldName = $queryObject['select'][$field]['field'];
    $caseList = [];

    foreach ($case as $when => $then) {
      if ($when === 'default') {
        $caseList[] = "else $then";
      } else {
        $caseList[] = "when " . str_replace('$', $fieldName, $when) . " then $then";
      }
    }

    $queryObject['select'][$field]['field'] = 'case ' . implode(' ', $caseList) . ' end';
  }

  public static function mod_asc(&$queryObject, $field) {
    $sortBy = $queryObject['select'][$field]['alias']
            ? $queryObject['select'][$field]['alias']
            : $queryObject['select'][$field]['field'];

    $queryObject['order'][] = [
      'field' => $sortBy,
      'direction' => 'asc',
    ];
  }

  public static function mod_desc(&$queryObject, $field) {
    $sortBy = $queryObject['select'][$field]['alias']
            ? $queryObject['select'][$field]['alias']
            : $queryObject['select'][$field]['field'];

    $queryObject['order'][] = [
      'field' => $sortBy,
      'direction' => 'desc',
    ];
  }

  public static function mod_group(&$queryObject, $field) {
    $queryObject['group'][] = $queryObject['select'][$field]['field'];
    if (!empty($queryObject['select'][$field]['modifier']['group']))
    {
      $group = $queryObject['select'][$field]['alias'];
      $name = $queryObject['select'][$field]['modifier']['group'][0];
      $queryObject['having'][] = "$group = $name";
    }
  }

  public static function mod_count(&$queryObject, $field) {
    self::default_handler('count', $queryObject, $field);
  }

  public static function mod_min(&$queryObject, $field) {
    self::default_handler('min', $queryObject, $field);
  }

  public static function mod_max(&$queryObject, $field) {
    self::default_handler('max', $queryObject, $field);
  }

  public static function mod_sum(&$queryObject, $field) {
    self::default_handler('sum', $queryObject, $field);
  }

  public static function mod_join(&$queryObject, $field) {
    $fieldOptions = $queryObject['select'][$field];
    $table = $fieldOptions['table'];
    $queryObject['join'][$table]['type'] = 'inner';
    $queryObject['join'][$table]['on'] = $fieldOptions['field'] . ' = ' . $fieldOptions['modifier']['join'][0];
  }

  public static function mod_left_join(&$queryObject, $field) {
    $fieldOptions = $queryObject['select'][$field];
    $table = $fieldOptions['table'];
    $queryObject['join'][$table]['type'] = 'left';
    $queryObject['join'][$table]['on'] = $fieldOptions['field'] . ' = ' . $fieldOptions['modifier']['left_join'][0];
  }

  public static function mod_right_join(&$queryObject, $field) {
    $fieldOptions = $queryObject['select'][$field];
    $table = $fieldOptions['table'];
    $queryObject['join'][$table]['type'] = 'right';
    $queryObject['join'][$table]['on'] = $fieldOptions['field'] . ' = ' . $fieldOptions['modifier']['right_join'][0];
  }
}
