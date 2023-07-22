<?php

namespace Drupal\abtestui\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class TestStorage.
 *
 * @package Drupal\abtestui\Service
 */
class TestStorage {

  const BASE_TABLE_NAME = 'abtestui_test';

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $db;

  /**
   * Logger channel fo abtestui.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $logger;

  /**
   * Variation storage.
   *
   * @var \Drupal\abtestui\Service\VariationStorage
   */
  private $variationStorage;

  /**
   * A/B JS test storage.
   *
   * @var \Drupal\abtestui\Service\AbjsTestStorage
   */
  private $abjsTestStorage;

  /**
   * A/B JS condition storage.
   *
   * @var \Drupal\abtestui\Service\AbjsConditionStorage
   */
  private $abjsConditionStorage;

  /**
   * A/B JS experience storage.
   *
   * @var \Drupal\abtestui\Service\AbjsExperienceStorage
   */
  private $abjsExperienceStorage;

  /**
   * TestStorage constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   DB Connection.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger channel factory.
   * @param \Drupal\abtestui\Service\VariationStorage $variationStorage
   *   Variation storage.
   * @param \Drupal\abtestui\Service\AbjsTestStorage $abjsTestStorage
   *   A/B JS test storage.
   * @param \Drupal\abtestui\Service\AbjsConditionStorage $abjsConditionStorage
   *   A/B JS condition storage.
   * @param \Drupal\abtestui\Service\AbjsExperienceStorage $abjsExperienceStorage
   *   A/B JS experience storage.
   */
  public function __construct(
    Connection $database,
    LoggerChannelFactoryInterface $loggerChannelFactory,
    VariationStorage $variationStorage,
    AbjsTestStorage $abjsTestStorage,
    AbjsConditionStorage $abjsConditionStorage,
    AbjsExperienceStorage $abjsExperienceStorage
  ) {
    $this->db = $database;
    $this->logger = $loggerChannelFactory->get('abtestui');
    $this->variationStorage = $variationStorage;
    $this->abjsTestStorage = $abjsTestStorage;
    $this->abjsConditionStorage = $abjsConditionStorage;
    $this->abjsExperienceStorage = $abjsExperienceStorage;
  }

  /**
   * Loads a test.
   *
   * @param string|int $tid
   *   Test ID.
   *
   * @return array|bool
   *   The loaded test or FALSE.
   */
  public function load($tid) {
    if ($tid === NULL) {
      return FALSE;
    }

    $test = $this->fetchTest($tid);

    if ($test === FALSE) {
      return FALSE;
    }

    $conditions = $this->fetchConditions($tid);
    $experiences = $this->fetchExperiences($tid);

    $baseVariation = $experiences[$test['abjs_teid']];
    $baseCondition = $conditions[$test['abjs_tcid']];

    $test['base_odd'] = $baseVariation->fraction * 100;
    $test['eid'] = $baseVariation->eid;
    $test['cid'] = $baseCondition->cid;
    unset($experiences[$test['abjs_teid']]);

    $test['variations'] = [];

    /** @var array $experience */
    foreach ($experiences as $experience) {
      $test['variations'][] = [
        'name' => $experience->name,
        'eid' => $experience->eid,
        'url' => $experience->url,
        'odd' => $experience->fraction * 100,
        'vid' => $experience->vid,
        'teid' => $experience->teid,
      ];
    }

    return $test;
  }

  /**
   * Loads a test (un-hydrated version).
   *
   * @param string|int $tid
   *   Test ID.
   *
   * @return array|bool
   *   The test data or FALSE.
   */
  private function fetchTest($tid) {
    $query = $this->db->select(static::BASE_TABLE_NAME, 'base_table');
    $query->addJoin('left', 'abjs_test', 'test', 'base_table.tid = test.tid');
    $query->condition('base_table.tid', $tid);
    $query->fields('base_table');
    $query->fields('test');
    $result = $query->execute();
    return $result->fetchAssoc();
  }

  /**
   * Loads conditions for a test.
   *
   * @param string|int $tid
   *   Test ID.
   *
   * @return array
   *   The conditions.
   */
  private function fetchConditions($tid) {
    $query = $this->db->select('abjs_condition', 'cond');
    $query->addJoin('left', 'abjs_test_condition', 'test_cond', 'cond.cid = test_cond.cid');
    $query->condition('test_cond.tid', $tid);
    $query->fields('cond');
    $query->fields('test_cond', ['tcid']);
    $cResult = $query->execute();
    return $cResult->fetchAllAssoc('tcid');
  }

  /**
   * Loads experiences for a test.
   *
   * @param string|int $tid
   *   Test ID.
   *
   * @return array
   *   The experiences.
   */
  private function fetchExperiences($tid) {
    $query = $this->db->select('abjs_experience', 'exp');
    $query->addJoin('left', 'abjs_test_experience', 'test_exp', 'exp.eid = test_exp.eid');
    $query->addJoin('left', 'abtestui_variation', 'var', 'test_exp.teid = var.abjs_teid');
    $query->condition('test_exp.tid', $tid);
    $query->fields('exp');
    $query->fields('test_exp', ['teid', 'fraction']);
    $query->fields('var', ['abjs_teid', 'url', 'vid']);
    $result = $query->execute();
    return $result->fetchAllAssoc('teid');
  }

  /**
   * Saves a test.
   *
   * @param array $values
   *   Test data.
   *
   * @return string|int
   *   The saved test ID.
   *
   * @throws \Exception
   */
  public function save(array $values) {
    $this->db->merge(static::BASE_TABLE_NAME)
      ->key('tid', $values['tid'])
      ->fields([
        'tid' => $values['tid'],
        'base_url' => $values['base_url'],
        'analytics_url' => $values['analytics_url'],
        'abjs_tcid' => $values['abjs_tcid'],
        'abjs_teid' => $values['abjs_teid'],
      ])
      ->execute();

    return $values['tid'];
  }

  /**
   * Loads multiple tests.
   *
   * @param string[]|int[] $tids
   *   Test IDs.
   *
   * @return array
   *   The tests.
   */
  public function loadMultiple(array $tids = []) {
    if (empty($tids)) {
      $query = $this->db->select(static::BASE_TABLE_NAME, 'base_table');
      $query->addField('base_table', 'tid');
      $result = $query->execute();
      $tids = $result->fetchCol();
    }

    $tests = [];
    foreach ($tids as $tid) {
      $test = $this->load($tid);
      if (FALSE === $test) {
        $this->logger->error('Test with ID @tid could not be loaded.', [
          '@tid' => $tid,
        ]);
        continue;
      }
      $tests[$tid] = $test;
    }

    return $tests;
  }

  /**
   * Delete a test and every dependent row.
   *
   * Removes data from the following tables:
   *   abjs_test
   *   abjs_condition
   *   abjs_experience
   *   abjs_test_condition
   *   abjs_test_experience
   *   abtestui_test
   *   abtestui_variation.
   *
   * @param string|int $tid
   *   The test id.
   */
  public function delete($tid) {
    $this->deleteMultiple([$tid]);
  }

  /**
   * Remove multiple tests and their dependent rows.
   *
   * Removes data from the following tables:
   *   abjs_test
   *   abjs_condition
   *   abjs_experience
   *   abjs_test_condition
   *   abjs_test_experience
   *   abtestui_test
   *   abtestui_variation.
   *
   * @param int[]|string[] $tids
   *   An array of test ids.
   */
  public function deleteMultiple(array $tids) {
    if (empty($tids)) {
      return;
    }

    // @todo: add storage deleteMultipleByTids
    // Remove abtestui_varitaion, abtestui_test, abjs_test rows.
    $query = $this->db->delete(static::BASE_TABLE_NAME);
    $query->condition('tid', $tids, 'in');
    $query->execute();

    $this->abjsTestStorage->deleteMultiple($tids);

    // @todo: Replace with $this->variationStorage->deleteMultipleByTids($tids);
    $query = $this->db->delete(VariationStorage::BASE_TABLE_NAME);
    $query->condition('tid', $tids, 'in');
    $query->execute();

    // Delete the experiences and conditions.
    $this->abjsExperienceStorage->deleteMultipleByTids($tids);
    $this->abjsConditionStorage->deleteMultipleByTids($tids);

    // @todo: Replace with $this->experienceRelationStorage->deleteMultipleByTids($tids);
    // Remove relations.
    $query = $this->db->delete(AbjsTestStorage::EXPERIENCE_RELATION_TABLE);
    $query->condition('tid', $tids, 'in');
    $query->execute();

    // @todo: Replace with $this->conditionRelationStorage->deleteMultipleByTids($tids);
    $query = $this->db->delete(AbjsTestStorage::CONDITION_RELATION_TABLE);
    $query->condition('tid', $tids, 'in');
    $query->execute();
  }

}
