<?php

namespace Drupal\abtestui\Form;

use Drupal\abtestui\Service\TestStorage;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ABTestDeleteForm.
 *
 * @package Drupal\abtestui\Form
 */
class ABTestDeleteForm extends ConfirmFormBase {

  /**
   * A/B Test storage.
   *
   * @var \Drupal\abtestui\Service\TestStorage
   */
  private $testStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('abtestui.test_storage')
    );
  }

  /**
   * ABTestDeleteForm constructor.
   *
   * @param \Drupal\abtestui\Service\TestStorage $testStorage
   *   A/B Test storage.
   */
  public function __construct(
    TestStorage $testStorage
  ) {
    $this->testStorage = $testStorage;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'abtestui_test_delete';
  }

  /**
   * The ID of the item to delete.
   *
   * @var string
   */
  protected $id;

  /**
   * The loaded test.
   *
   * @var array
   */
  protected $test;

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Do you want to delete the "%name" test (ID %id)?', [
      '%id' => $this->id,
      '%name' => $this->test['name'],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('abtestui.test_edit_form', [
      'ab_test_id' => $this->id,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param string|int|null $ab_test_id
   *   The test ID.
   *
   * @return array
   *   The built form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $ab_test_id = NULL) {
    $this->id = $ab_test_id;
    $this->test = $this->testStorage->load($this->id);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->testStorage->delete($this->id);

    $this->messenger()
      ->addMessage($this->t('The "@name" test (ID @id) has been deleted.', [
        '@id' => $this->id,
        '@name' => $this->test['name'],
      ]));

    $form_state->setRedirect('abtestui.test_list');
  }

}
