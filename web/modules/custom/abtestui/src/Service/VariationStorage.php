<?php

namespace Drupal\abtestui\Service;

use Drupal\Core\Database\Connection;

/**
 * Class VariationStorage.
 *
 * @package Drupal\abtestui\Service
 */
class VariationStorage {

  const BASE_TABLE_NAME = 'abtestui_variation';

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $db;

  /**
   * VariationStorage constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection.
   */
  public function __construct(Connection $database) {
    $this->db = $database;
  }

  /**
   * Creates or updates a variation.
   *
   * @param array $values
   *   Variation data.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|mixed|string|null
   *   The variation ID.
   *
   * @throws \Exception
   */
  public function save(array $values) {
    // If there's no vid, insert.
    if (NULL === $values['vid']) {
      $result = $this->db->insert(static::BASE_TABLE_NAME)->fields([
        'tid' => $values['tid'],
        'url' => $values['url'],
        'abjs_teid' => $values['abjs_teid'],
      ])->execute();

      return $result;
    }

    $this->db->update(static::BASE_TABLE_NAME)->fields([
      'tid' => $values['tid'],
      'url' => $values['url'],
      'abjs_teid' => $values['abjs_teid'],
    ])->condition('vid', $values['vid'])
      ->execute();

    return $values['vid'];
  }

  /**
   * Deletes a variation.
   *
   * @param string|int $vid
   *   The variation ID.
   */
  public function delete($vid) {
    $query = $this->db->delete(static::BASE_TABLE_NAME);
    $query->condition('vid', $vid);
    $query->execute();
  }

}
