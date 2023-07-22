<?php

namespace Drupal\abtestui;

use Drupal;

/**
 * Trait for injecting A/B Storage services into an AJAX form.
 *
 * @note: Regular dependency injection breaks with AJAX forms.
 *
 * @package Drupal\abtestui
 */
trait AbStorageTrait {

  /**
   * A/B JS Test storage.
   *
   * @var \Drupal\abtestui\Service\AbjsTestStorage
   */
  protected $abjsTestStorage;

  /**
   * A/B JS Condition storage.
   *
   * @var \Drupal\abtestui\Service\AbjsConditionStorage
   */
  protected $abjsConditionStorage;

  /**
   * A/B JS Experience storage.
   *
   * @var \Drupal\abtestui\Service\AbjsExperienceStorage
   */
  protected $abjsExperienceStorage;

  /**
   * Variation storage.
   *
   * @var \Drupal\abtestui\Service\VariationStorage
   */
  protected $variationStorage;

  /**
   * Test storage.
   *
   * @var \Drupal\abtestui\Service\TestStorage
   */
  protected $testStorage;

  /**
   * A/B JS Test storage.
   *
   * @return \Drupal\abtestui\Service\AbjsTestStorage
   *   The storage service.
   */
  public function abjsTestStorage() {
    if (empty($this->abjsTestStorage)) {
      $this->abjsTestStorage = Drupal::service('abtestui.abjs_test_storage');
    }

    return $this->abjsTestStorage;
  }

  /**
   * A/B JS Condition storage.
   *
   * @return \Drupal\abtestui\Service\AbjsConditionStorage
   *   The storage service.
   */
  public function abjsConditionStorage() {
    if (empty($this->abjsConditionStorage)) {
      $this->abjsConditionStorage = Drupal::service('abtestui.abjs_condition_storage');
    }

    return $this->abjsConditionStorage;
  }

  /**
   * A/B JS Experience storage.
   *
   * @return \Drupal\abtestui\Service\AbjsExperienceStorage
   *   The storage service.
   */
  public function abjsExperienceStorage() {
    if (empty($this->abjsExperienceStorage)) {
      $this->abjsExperienceStorage = Drupal::service('abtestui.abjs_experience_storage');
    }

    return $this->abjsExperienceStorage;
  }

  /**
   * Variation storage.
   *
   * @return \Drupal\abtestui\Service\VariationStorage
   *   The storage service.
   */
  public function variationStorage() {
    if (empty($this->variationStorage)) {
      $this->variationStorage = Drupal::service('abtestui.variation_storage');
    }

    return $this->variationStorage;
  }

  /**
   * Test storage.
   *
   * @return \Drupal\abtestui\Service\TestStorage
   *   The storage service.
   */
  public function testStorage() {
    if (empty($this->testStorage)) {
      $this->testStorage = Drupal::service('abtestui.test_storage');
    }

    return $this->testStorage;
  }

}
