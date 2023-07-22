<?php

namespace Drupal\abtestui\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\State\StateInterface;
use PDO;
use function array_filter;
use function array_keys;
use function array_values;
use function in_array;

/**
 * Class ExperienceStorage.
 *
 * @package Drupal\abtestui\Service
 */
class AbjsExperienceStorage {

  const BASE_TABLE_NAME = 'abjs_experience';

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $db;

  /**
   * Logger channel for abtestui.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $logger;

  /**
   * Drupal state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private $state;

  /**
   * Time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  private $time;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  private $currentUser;

  /**
   * AbjsExperienceStorage constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   DB connection.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger channel factory.
   * @param \Drupal\Core\State\StateInterface $state
   *   State service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   Time service.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   */
  public function __construct(
    Connection $database,
    LoggerChannelFactoryInterface $loggerChannelFactory,
    StateInterface $state,
    TimeInterface $time,
    AccountProxyInterface $currentUser
  ) {
    $this->db = $database;
    $this->logger = $loggerChannelFactory->get('abtestui');
    $this->state = $state;
    $this->time = $time;
    $this->currentUser = $currentUser;
  }

  /**
   * Creates the control experience, or loads it.
   *
   * @return bool|\Drupal\Core\Database\StatementInterface|int|string|null
   *   The ID of the control experience.
   *
   * @throws \Exception
   */
  public function createOrLoadControl() {
    $controlExperienceID = $this->loadControl();

    // If no control exists, create it.
    if ($controlExperienceID === FALSE) {
      $controlExperienceID = $this->createControl();
    }

    // Store the eid in the site state so we don't have to search the DB.
    $this->state->set('abtestui', [
      'control_experience_eid' => $controlExperienceID,
    ]);

    return $controlExperienceID;
  }

  /**
   * Loads the control experience.
   *
   * @return bool|int|string
   *   The ID or FALSE.
   */
  public function loadControl() {
    $abtestuiState = $this->state->get('abtestui');

    if (isset($abtestuiState['control_experience_eid'])) {
      return $abtestuiState['control_experience_eid'];
    }

    // Search for an existing 'Control'.
    $controlExperienceID = $this->db->select(static::BASE_TABLE_NAME)
      ->fields(static::BASE_TABLE_NAME, ['eid'])
      ->range(NULL, 1)
      ->condition('script', '')
      ->execute()
      ->fetchAssoc();

    $controlExperienceID = isset($controlExperienceID['eid']) ? $controlExperienceID['eid'] : FALSE;

    if ($controlExperienceID !== FALSE) {
      $this->logger->notice("Control experience with ID $controlExperienceID found.");
    }

    return $controlExperienceID;
  }

  /**
   * Creates the control experience.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|string|null
   *   The ID of the control.
   *
   * @throws \Exception
   */
  public function createControl() {
    // Create a 'Control' experience.
    $requestTime = $this->time->getRequestTime();

    $controlExperienceID = $this->db->insert(static::BASE_TABLE_NAME)
      ->fields([
        'name' => 'Base URL',
        'script' => '',
        'created' => $requestTime,
        'created_by' => $this->currentUser->id(),
        'changed' => $requestTime,
        'changed_by' => $this->currentUser->id(),
      ])
      ->execute();

    $this->logger->notice("Control experience with ID $controlExperienceID created.");

    return $controlExperienceID;
  }

  /**
   * Insert or update an experience.
   *
   * @param array $values
   *   An associative array of values.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|mixed|string|null
   *   The ID of the saved experience.
   *
   * @throws \Exception
   */
  public function save(array $values) {
    // If there's no eid, insert.
    if (NULL === $values['eid']) {
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
      'created' => $values['created'],
      'created_by' => $values['created_by'],
      'changed' => $values['changed'],
      'changed_by' => $values['changed_by'],
    ])->condition('eid', $values['eid'])
      ->execute();

    return $values['eid'];
  }

  /**
   * Loads multiple experiences by test IDs.
   *
   * @param string[]|int[] $tids
   *   An array of test IDs.
   *
   * @return int[]
   *   An array of experience IDs.
   */
  public function loadMultipleByTids($tids) {
    $query = $this->db->select(AbjsTestStorage::EXPERIENCE_RELATION_TABLE, 'base_table');
    $query->condition('base_table.tid', $tids, 'in');
    $query->addField('base_table', 'eid');
    $result = $query->execute();

    return array_keys($result->fetchAllAssoc('eid', PDO::FETCH_ASSOC));
  }

  /**
   * Delete multiple experiences.
   *
   * @param string[]|int[] $eids
   *   The experience IDs.
   */
  public function deleteMultiple(array $eids) {
    if (empty($eids)) {
      return;
    }

    $query = $this->db->delete(static::BASE_TABLE_NAME);
    $query->condition('eid', $eids, 'in');
    $query->execute();
  }

  /**
   * Remove experiences for the given tests.
   *
   * Does NOT remove the control experience.
   *
   * @param string[]|int[] $tids
   *   Test IDs.
   */
  public function deleteMultipleByTids(array $tids) {
    if (empty($tids)) {
      return;
    }

    $eids = $this->loadMultipleByTids($tids);
    $controlEid = $this->loadControl();

    // We don't want to delete the control experience.
    if (FALSE !== $controlEid && in_array($controlEid, $eids, FALSE)) {
      $eids = array_values(array_filter($eids, static function ($value) use ($controlEid) {
        return (int) $value !== (int) $controlEid;
      }));
    }

    $this->deleteMultiple($eids);
  }

  /**
   * Deletes an experience.
   *
   * @param string|int $eid
   *   The ID of the experience.
   */
  public function delete($eid) {
    $query = $this->db->delete(static::BASE_TABLE_NAME);
    $query->condition('eid', $eid);
    $query->execute();
  }

}
