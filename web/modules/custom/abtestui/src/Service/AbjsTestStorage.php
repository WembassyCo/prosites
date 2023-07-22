<?php

namespace Drupal\abtestui\Service;

use Drupal\Core\Database\Connection;

/**
 * Class TestStorage.
 *
 * @package Drupal\abtestui\Service
 */
class AbjsTestStorage {

  const BASE_TABLE_NAME = 'abjs_test';

  const EXPERIENCE_RELATION_TABLE = 'abjs_test_experience';

  const CONDITION_RELATION_TABLE = 'abjs_test_condition';

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $db;

  /**
   * AbjsTestStorage constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->db = $database;
  }

  /**
   * Creates or updates a test.
   *
   * @param array $values
   *   Test data.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|mixed|string|null
   *   The saved test ID.
   *
   * @throws \Exception
   */
  public function save(array $values) {
    // If there's no tid, insert.
    if (NULL === $values['tid']) {
      $result = $this->db->insert(static::BASE_TABLE_NAME)->fields([
        'name' => $values['name'],
        'active' => $values['active'],
        'created' => $values['created'],
        'created_by' => $values['created_by'],
        'changed' => $values['changed'],
        'changed_by' => $values['changed_by'],
      ])->execute();

      return $result;
    }

    $this->db->update(static::BASE_TABLE_NAME)->fields([
      'name' => $values['name'],
      'active' => $values['active'],
      'changed' => $values['changed'],
      'changed_by' => $values['changed_by'],
    ])->condition('tid', $values['tid'])
      ->execute();

    return $values['tid'];
  }

  /**
   * Creates or updates an experience-condition relation.
   *
   * @param array $values
   *   Values to be saved.
   *
   * @return int|string
   *   The experience-condition relation ID.
   *
   * @throws \Exception
   *
   * @todo: Move to service.
   */
  public function addExperienceRelation(array $values) {
    // If there's no teid, insert.
    if (NULL === $values['teid']) {
      $result = $this->db->insert(static::EXPERIENCE_RELATION_TABLE)->fields([
        'eid' => $values['eid'],
        'tid' => $values['tid'],
        'fraction' => $values['fraction'],
      ])->execute();

      return $result;
    }

    $this->db->update(static::EXPERIENCE_RELATION_TABLE)->fields([
      'eid' => $values['eid'],
      'tid' => $values['tid'],
      'fraction' => $values['fraction'],
    ])->condition('teid', $values['teid'])
      ->execute();

    return $values['teid'];
  }

  /**
   * Creates or updates a test-condition relation.
   *
   * @param array $values
   *   Values to be saved.
   *
   * @return int|string
   *   The test-condition relation ID.
   *
   * @throws \Exception
   *
   * @todo: Move to service.
   */
  public function addConditionRelation(array $values) {
    // If there's no tid, insert.
    if (NULL === $values['tcid']) {
      $result = $this->db->insert(static::CONDITION_RELATION_TABLE)->fields([
        'cid' => $values['cid'],
        'tid' => $values['tid'],
      ])->execute();

      return $result;
    }

    $this->db->update(static::CONDITION_RELATION_TABLE)->fields([
      'cid' => $values['cid'],
      'tid' => $values['tid'],
    ])->condition('tcid', $values['tcid'])
      ->execute();

    return $values['tcid'];
  }

  /**
   * Deletes a test.
   *
   * @param string|int $tid
   *   Test ID.
   */
  public function delete($tid) {
    $this->deleteMultiple([$tid]);
  }

  /**
   * Deletes multiple tests.
   *
   * @param string[]|int[] $tids
   *   Test IDs.
   */
  public function deleteMultiple(array $tids) {
    if (empty($tids)) {
      return;
    }

    $query = $this->db->delete(static::BASE_TABLE_NAME);
    $query->condition('tid', $tids, 'in');
    $query->execute();
  }

  /**
   * Deletes a test-experience relation.
   *
   * @param string|int $teid
   *   Relation ID.
   */
  public function deleteExperienceRelation($teid) {
    $query = $this->db->delete(static::EXPERIENCE_RELATION_TABLE);
    $query->condition('teid', $teid);
    $query->execute();
  }

}
