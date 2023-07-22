<?php

namespace Drupal\abtestui;

use Drupal;

/**
 * Helper trait for AJAX forms.
 *
 * @note: Regular dependency injection breaks with AJAX forms.
 *
 * @package Drupal\abtestui
 */
trait AbAjaxFormHelperTrait {

  /**
   * Time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Returns the time service.
   *
   * @return \Drupal\Component\Datetime\TimeInterface
   *   The service.
   */
  public function time() {
    if (empty($this->time)) {
      $this->time = Drupal::time();
    }

    return $this->time;
  }

  /**
   * Returns the current user.
   *
   * @return \Drupal\Core\Session\AccountProxyInterface
   *   The user.
   */
  public function currentUser() {
    if (empty($this->currentUser)) {
      $this->currentUser = Drupal::currentUser();
    }

    return $this->currentUser;
  }

}
