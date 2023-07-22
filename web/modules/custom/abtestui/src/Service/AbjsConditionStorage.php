<?php

namespace Drupal\abtestui\Service;

use Drupal\Core\Database\Connection;
use PDO;
use function array_keys;

/**
 * Class ConditionStorage.
 *
 * @package Drupal\abtestui\Service
 */
class AbjsConditionStorage {

  const BASE_TABLE_NAME = 'abjs_condition';

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $db;

  /**
   * AbjsConditionStorage constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection.
   */
  public function __construct(Connection $database) {
    $this->db = $database;
  }

  /**
   * Creates or updates a condition.
   *
   * @param array $values
   *   Condition data.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|mixed|string|null
   *   The ID of the saved condition.
   *
   * @throws \Exception
   */
  public function save(array $values) {
    // If there's no cid, insert.
    if (NULL === $values['cid']) {
      $result = $this->db->insert(static::BASE_TABLE_NAME)->fields([
        'name' => $values['name'],
        'script' => $values['script'],
        'created' => $values['created'],
        'created_by' => $values['created_by'],
        'changed' => $values['changed'],
        'changed_by' => $values['changed_by'],
      ])->execute();

      return $result;
    }

    $this->db->update(static::BASE_TABLE_NAME)->fields([
      'name' => $values['name'],
      'script' => $values['script'],
      'changed' => $values['changed'],
      'changed_by' => $values['changed_by'],
    ])->condition('cid', $values['cid'])
      ->execute();

    return $values['cid'];
  }

  /**
   * Loads conditions by test IDs.
   *
   * @param string[]|int[] $tids
   *   The test IDs.
   *
   * @return string[]|int[]
   *   Condition IDs.
   */
  public function loadMultipleByTids($tids) {
    $query = $this->db->select(AbjsTestStorage::CONDITION_RELATION_TABLE, 'base_table');
    $query->condition('base_table.tid', $tids, 'in');
    $query->addField('base_table', 'cid');
    $result = $query->execute();

    return array_keys($result->fetchAllAssoc('cid', PDO::FETCH_ASSOC));
  }

  /**
   * Deletes multiple conditions.
   *
   * @param string[]|int[] $cids
   *   The condition IDs.
   */
  public function deleteMultiple(array $cids) {
    if (empty($cids)) {
      return;
    }

    $query = $this->db->delete(static::BASE_TABLE_NAME);
    $query->condition('cid', $cids, 'in');
    $query->execute();
  }

  /**
   * Deletes conditions by test IDs.
   *
   * @param string[]|int[] $tids
   *   The test IDs.
   */
  public function deleteMultipleByTids(array $tids) {
    if (empty($tids)) {
      return;
    }

    $this->deleteMultiple($this->loadMultipleByTids($tids));
  }

}
