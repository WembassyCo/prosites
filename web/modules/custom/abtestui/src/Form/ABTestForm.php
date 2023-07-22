<?php

namespace Drupal\abtestui\Form;

use DateTime;
use Drupal\abtestui\AbAjaxFormHelperTrait;
use Drupal\abtestui\AbStorageTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function array_keys;
use function array_unique;
use function chr;
use function count;
use function drupal_get_path;
use function end;
use function parse_url;
use function reset;
use function sprintf;
use function strlen;
use function substr;

/**
 * Class ABTestForm.
 *
 * @package Drupal\abtestui\Form
 */
class ABTestForm extends FormBase {

  use AbStorageTrait;
  use AbAjaxFormHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'abtestui_test';
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
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function buildForm(array $form, FormStateInterface $form_state, $ab_test_id = NULL) {
    $test = $this->testStorage()->load($ab_test_id);

    if ($ab_test_id !== NULL && $test === FALSE) {
      throw new NotFoundHttpException();
    }

    if (!$form_state->has('variations_to_remove')) {
      $form_state->set('variations_to_remove', []);
    }

    $form['info_bar'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'info-bar',
        ],
      ],
      'title' => [
        '#markup' => '<h2>' . $this->t('A/B Testing Tool') . '</h2>',
      ],
      'ab_test_tool_help' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'ab-test-tool-help',
          ],
        ],
        'link' => [
          '#title' => $this->t('How does this tool work?'),
          '#type' => 'link',
          '#url' => Url::fromUri('base:/' . drupal_get_path('module', 'abtestui') . '/help/ab-test-tool-help.html'),
          '#options' => [
            'attributes' => [
              'class' => [
                'help-modal',
              ],
            ],
          ],
        ],
      ],
    ];

    $form['back'] = [
      '#type' => 'container',
      'link' => [
        '#title' => $this->t('Back to the list'),
        '#type' => 'link',
        '#url' => Url::fromRoute('abtestui.test_list'),
      ],
    ];

    // We need this to access every value.
    $form['#tree'] = TRUE;

    if ($test !== FALSE) {
      $form['test_id'] = [
        '#type' => 'item',
        '#title' => $this->t('Test ID:'),
        '#markup' => $test['tid'],
      ];
    }

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => 'Test name',
      '#default_value' => empty($test['name']) ? NULL : $test['name'],
      '#size' => 65,
      '#maxlength' => 64,
      '#required' => TRUE,
    ];

    $form['variations_fieldgroup'] = [
      '#type' => 'fieldgroup',
      '#title' => $this->t('Variations'),
      // Set up the wrapper so that AJAX will be able to replace the fieldgroup.
      '#prefix' => '<div id="variations-fieldgroup-wrapper">',
      '#suffix' => '</div>',
    ];

    // We need to allow the removal of specific elements via AJAX.
    // If we use the test directly to load variations, removal won't
    // happen as expected, and adding a new element will be bugged as well.
    // So we move the variations data to a separate variable,
    // and try to load them from the form_state.
    $variationsData = isset($test['variations']) ? $test['variations'] : [];
    $userInput = $form_state->getUserInput();
    if (isset($userInput['variations_fieldgroup']['variations'])) {
      /** @var array $inputVariations */
      $inputVariations = $userInput['variations_fieldgroup']['variations'];
      // Variations user input contains the 'base' variation, so we unset it.
      unset($inputVariations['base']);

      $completeForm = $form_state->getCompleteForm();
      if (isset($completeForm['variations_fieldgroup']['variations'])) {
        $variations = $completeForm['variations_fieldgroup']['variations'];
        $variationsData = [];
        foreach ($inputVariations as $delta => $data) {
          $eid = isset($variations[$delta]['eid']['#value']) ? $variations[$delta]['eid']['#value'] : NULL;
          $vid = isset($variations[$delta]['vid']['#value']) ? $variations[$delta]['vid']['#value'] : NULL;
          $teid = isset($variations[$delta]['teid']['#value']) ? $variations[$delta]['teid']['#value'] : NULL;

          $variationsData[] = $data + [
            'eid' => $eid,
            'vid' => $vid,
            'teid' => $teid,
          ];
        }
      }
    }

    if (!$form_state->has('variation_count')) {
      $form_state->set('variation_count', (FALSE === $test) ? 1 : (count($variationsData) + 1));
    }

    $form['variations_fieldgroup']['variations']['base'] = [
      '#type' => 'fieldgroup',
      '#prefix' => '<div id="variation-delta-' . 0 . '-fieldgroup-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['variations_fieldgroup']['variations']['base']['delta'] = [
      '#type' => 'item',
      '#markup' => chr(65),
    ];
    $form['variations_fieldgroup']['variations']['base']['name'] = [
      '#type' => 'item',
      '#markup' => $this->t('Base URL') . ' <a href="/' . drupal_get_path('module', 'abtestui') . '/help/base-url.html" class="help-modal"><img width="20" height="20" src="/' . drupal_get_path('module', 'abtestui') . '/img/help.svg" alt="Help about the base URL"></a>',
    ];
    $form['variations_fieldgroup']['variations']['base']['url'] = [
      '#type' => 'url',
      '#placeholder' => $this->t('The base URL.'),
      '#default_value' => empty($test['base_url']) ? NULL : $test['base_url'],
    ];
    $form['variations_fieldgroup']['variations']['base']['odd'] = [
      '#type' => 'number',
      '#title' => $this->t('Odd'),
      '#title_display' => 'invisible',
      '#min' => 1,
      '#max' => 100,
      '#default_value' => $test['base_odd'],
      '#suffix' => '<div class="percent form-item">%</div>',
      '#placeholder' => $this->t('Odd'),
    ];

    for ($i = 1; $i < (int) $form_state->get('variation_count'); ++$i) {
      if (isset($variationsData[$i - 1])) {
        $this->createVariationFormElement($form, $i, $variationsData[$i - 1]);
      }
      else {
        $this->createVariationFormElement($form, $i, [
          'name' => '',
          'url' => '',
          'odd' => NULL,
          'eid' => NULL,
          'vid' => NULL,
          'teid' => NULL,
        ]);
      }
    }

    $form['variations_fieldgroup']['add_variation'] = [
      '#type' => 'submit',
      '#value' => $this->t('@plus_sign Add new variation', [
        '@plus_sign' => '+',
      ]),
      '#name' => 'add-variation',
      '#submit' => ['::ajaxAddVariation'],
      '#ajax' => [
        'callback' => '::ajaxVariationCallback',
        'wrapper' => 'variations-fieldgroup-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    if ($test !== FALSE) {
      $date = new DateTime();
      $date->setTimestamp((int) $test['created']);

      $form['created'] = [
        '#type' => 'item',
        '#title' => $this->t('Created:'),
        '#markup' => $date->format('d/m/Y'),
      ];
    }

    // Add selector for activating/deactivating test.
    $form['active'] = [
      '#type' => 'select',
      '#title' => $this->t('Status:'),
      '#options' => [
        0 => $this->t('Inactive'),
        1 => $this->t('Active'),
      ],
      '#default_value' => isset($test['active']) ? $test['active'] : 1,
    ];

    $form['actions'] = [
      '#type' => 'fieldgroup',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#weight' => 5,
    ];
    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#weight' => 10,
      '#submit' => ['::cancelCallback'],
      '#limit_validation_errors' => [],
    ];
    if ($test !== FALSE) {
      $form['actions']['delete'] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete'),
        '#weight' => 15,
        '#submit' => ['::deleteCallback'],
        '#limit_validation_errors' => [],
      ];
    }

    $form['tid'] = [
      '#type' => 'value',
      '#value' => empty($test['tid']) ? NULL : $test['tid'],
    ];
    $form['cid'] = [
      '#type' => 'value',
      '#value' => empty($test['cid']) ? NULL : $test['cid'],
    ];
    $form['eid'] = [
      '#type' => 'value',
      '#value' => empty($test['eid']) ? NULL : $test['eid'],
    ];
    $form['abjs_tcid'] = [
      '#type' => 'value',
      '#value' => empty($test['abjs_tcid']) ? NULL : $test['abjs_tcid'],
    ];
    $form['abjs_teid'] = [
      '#type' => 'value',
      '#value' => empty($test['abjs_teid']) ? NULL : $test['abjs_teid'],
    ];

    // Add library.
    $form['#attached']['library'][] = 'abtestui/admin_design';

    return $form;
  }

  /**
   * Helper function for creating a form element for a variation.
   *
   * @param array $form
   *   The parent form.
   * @param int $delta
   *   The index of the variation.
   * @param array $data
   *   The variation data.
   */
  private function createVariationFormElement(array &$form, $delta, array $data) {
    $form['variations_fieldgroup']['variations'][$delta] = [
      '#type' => 'fieldgroup',
      '#prefix' => '<div id="variation-delta-' . $delta . '-fieldgroup-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['variations_fieldgroup']['variations'][$delta]['delta'] = [
      '#type' => 'item',
      '#markup' => chr(65 + $delta),
    ];
    $form['variations_fieldgroup']['variations'][$delta]['name'] = [
      '#type' => 'textfield',
      '#default_value' => $data['name'],
      '#size' => 65,
      '#maxlength' => 64,
      '#required' => TRUE,
      '#placeholder' => $this->t('A name for the variation.'),
    ];
    $form['variations_fieldgroup']['variations'][$delta]['url'] = [
      '#type' => 'url',
      '#placeholder' => $this->t('The url of the variation.'),
      '#default_value' => $data['url'],
    ];
    $form['variations_fieldgroup']['variations'][$delta]['odd'] = [
      '#type' => 'number',
      '#min' => 1,
      '#max' => 100,
      '#default_value' => $data['odd'],
      '#suffix' => '<div class="percent form-item">%</div>',
      '#placeholder' => $this->t('Odd'),
      '#title' => $this->t('Odd'),
      '#title_display' => 'invisible',
    ];
    $form['variations_fieldgroup']['variations'][$delta]['eid'] = [
      '#type' => 'value',
      '#value' => $data['eid'],
    ];
    $form['variations_fieldgroup']['variations'][$delta]['vid'] = [
      '#type' => 'value',
      '#value' => $data['vid'],
    ];
    $form['variations_fieldgroup']['variations'][$delta]['teid'] = [
      '#type' => 'value',
      '#value' => $data['teid'],
    ];
    $form['variations_fieldgroup']['variations'][$delta]['actions']['remove'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove'),
      '#name' => 'remove-variation-' . $delta,
      '#element_delta' => $delta,
      '#submit' => ['::ajaxRemoveVariation'],
      '#ajax' => [
        'method' => 'replaceWith',
        'callback' => '::ajaxVariationCallback',
        'wrapper' => 'variations-fieldgroup-wrapper',
      ],
      '#limit_validation_errors' => [],
      '#attributes' => [
        'class' => [
          'remove-variation',
        ],
      ],
    ];
  }

  /**
   * Assigns AJAX changes to 'Variations'.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form element.
   */
  public function ajaxVariationCallback(array $form, FormStateInterface $form_state) {
    return $form['variations_fieldgroup'];
  }

  /**
   * AJAX callback for removing a variation.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function ajaxRemoveVariation(array $form, FormStateInterface $form_state) {
    $elementDelta = array_keys($form_state->getValue([
      'variations_fieldgroup',
      'variations',
    ]));
    $elementDelta = reset($elementDelta);

    $userInput = $form_state->getUserInput();
    unset($userInput['variations_fieldgroup']['variations'][(string) $elementDelta]);
    $form_state->setUserInput($userInput);

    $removedElement = $form_state->getCompleteForm()['variations_fieldgroup']['variations'][(string) $elementDelta];

    if ($removedElement['eid']['#value'] !== NULL) {
      $removals = $form_state->get('variations_to_remove');
      $removals[] = [
        'eid' => $removedElement['eid']['#value'],
        'vid' => $removedElement['vid']['#value'],
        'teid' => $removedElement['teid']['#value'],
      ];
      $form_state->set('variations_to_remove', $removals);
    }

    $variationCount = $form_state->get('variation_count');
    if ($variationCount > 1) {
      $form_state->set('variation_count', $variationCount - 1);
    }

    $form_state->setRebuild();
  }

  /**
   * AJAX callback for adding a variation.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function ajaxAddVariation(array $form, FormStateInterface $form_state) {
    $lastElement = end($form_state->getUserInput()['variations_fieldgroup']['variations']);

    // If the last element is empty, don't increase the count.
    $variationCount = $form_state->get('variation_count');
    if ($variationCount < 26 && !empty($lastElement['url'])) {
      $form_state->set('variation_count', $variationCount + 1);
    }

    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var array $variations */
    $variations = $form_state->getValue([
      'variations_fieldgroup',
      'variations',
    ]);
    if (count($variations) === 1) {
      $form_state->setError(
        $form,
        $this->t('Please add at least one new variation.')
      );
      return;
    }

    $names = [];
    $oddSum = 0;
    foreach ($variations as $delta => $variation) {
      $oddSum += (int) $variation['odd'];

      if ($delta === 'base') {
        continue;
      }

      $names[] = $variation['name'];
    }

    // @todo: Try not to iterate twice.
    // @todo: Only flag duplicates as errors, not every field.
    $namesNotUnique = $names !== array_unique($names);
    $oddSumNotValid = 100 !== $oddSum;
    foreach ($variations as $delta => $variation) {
      // @todo: Check if URL is unique (globally unique!);
      if ($namesNotUnique) {
        $form_state->setError(
          $form['variations_fieldgroup']['variations'][(string) $delta]['name'],
          $this->t('Variation names have to be unique.')
        );
      }

      if ($oddSumNotValid) {
        $form_state->setError(
          $form['variations_fieldgroup']['variations'][(string) $delta]['odd'],
          $this->t('The sum of odds has to be 100, but it is @curr_odd_sum.', [
            '@curr_odd_sum' => $oddSum,
          ])
        );
      }
    }
  }

  /**
   * Callback for pressing the 'Cancel' button.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function cancelCallback(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('abtestui.test_list');
  }

  /**
   * Callback for pressing the 'Delete' button.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function deleteCallback(array &$form, FormStateInterface $form_state) {
    // @todo: FIXME, form state SHOULD contain the tid.
    $form_state->setRedirect('abtestui.test_delete_form', [
      'ab_test_id' => $form['tid']['#value'],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $requestTime = $this->time()->getRequestTime();
    $uid = $this->currentUser()->id();

    $baseValues = [
      'created' => $requestTime,
      'created_by' => $uid,
      'changed' => $requestTime,
      'changed_by' => $uid,
    ];

    // 1, Create test.
    $testValues = [
      'tid' => $values['tid'],
      'name' => $values['name'],
      'active' => (int) $values['active'],
    ] + $baseValues;
    $tid = $this->abjsTestStorage()->save($testValues);

    // 2, Create condition and bind it to the test.
    $conditionPath = parse_url($values['variations_fieldgroup']['variations']['base']['url'])['path'];
    $conditionName = sprintf('The path is %s', $conditionPath);

    // Truncates the condition path whenever it is bigger than 52 characters.
    if (strlen($conditionName) > 52) {
      $conditionName = sprintf('The path is %s...%s', substr($conditionPath, 0, 24), substr($conditionPath, -25, 25));
    }

    $conditionValues = [
      'cid' => $values['cid'],
      // @todo: Name can be only 64 chars long.
      // 12 + $conditionPath.
      'name' => $conditionName,
      'script' => "return window.location.pathname == '$conditionPath';",
    ] + $baseValues;
    $cid = $this->abjsConditionStorage()->save($conditionValues);

    $testConditionValues = [
      'tcid' => $values['abjs_tcid'],
      'cid' => $cid,
      'tid' => $tid,
    ];
    $abjsTcid = $this->abjsTestStorage()->addConditionRelation($testConditionValues);

    // 3, Bind (/create?) control experience and test. (control_experience_eid).
    $controlEid = $this->abjsExperienceStorage()->createOrLoadControl();

    $testControlExperienceValues = [
      'teid' => $values['abjs_teid'],
      'eid' => $controlEid,
      'tid' => $tid,
      'fraction' => $values['variations_fieldgroup']['variations']['base']['odd'] / 100,
    ];
    $abjsTeid = $this->abjsTestStorage()->addExperienceRelation($testControlExperienceValues);

    // 4, Create abtestui_test.
    $abtestuiTestValues = [
      'tid' => $tid,
      'base_url' => $values['variations_fieldgroup']['variations']['base']['url'],
      'analytics_url' => isset($values['analytics_url']) ? $values['analytics_url'] : '',
      'abjs_tcid' => $abjsTcid,
      'abjs_teid' => $abjsTeid,
    ];
    $this->testStorage()->save($abtestuiTestValues);

    // 5, Create experiences and bind them to the test.
    // 6, Create variations.
    /** @var array $variations */
    $variations = $values['variations_fieldgroup']['variations'];
    unset($variations['base']);

    foreach ($variations as $variation) {
      $experiencePath = parse_url($variation['url'])['path'];
      $experienceValues = [
        'eid' => $variation['eid'],
        // @todo: Name can be only 64 chars long.
        // 21 + $tid + $experiencePath length <= 64.
        // "Test id $tid redirect to $experiencePath".
        'name' => $variation['name'],
        'script' => "window.location.replace('$experiencePath'); document.write('<style>html { display:none !important; }</style>');",
      ] + $baseValues;

      $eid = $this->abjsExperienceStorage()->save($experienceValues);

      $testExperienceValues = [
        'teid' => $variation['teid'],
        'eid' => $eid,
        'tid' => $tid,
        'fraction' => $variation['odd'] / 100,
      ];

      $teid = $this->abjsTestStorage()->addExperienceRelation($testExperienceValues);

      $variationValues = [
        'vid' => $variation['vid'],
        'tid' => $tid,
        'url' => $variation['url'],
        'abjs_teid' => $teid,
      ];

      $this->variationStorage()->save($variationValues);
    }

    // Delete the AJAX-removed items from the DB.
    $removals = $form_state->get('variations_to_remove');
    foreach ($removals as $removal) {
      $this->variationStorage()->delete($removal['vid']);
      $this->abjsExperienceStorage()->delete($removal['eid']);
      $this->abjsTestStorage()->deleteExperienceRelation($removal['teid']);
    }

    if (NULL === $values['tid']) {
      $this->messenger()->addMessage($this->t('The test has been created.'));
    }
    else {
      $this->messenger()->addMessage($this->t('The test has been updated.'));
    }

    $form_state->setRedirect('abtestui.test_list');
  }

}
