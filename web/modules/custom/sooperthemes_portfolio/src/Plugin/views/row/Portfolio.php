<?php

namespace Drupal\sooperthemes_portfolio\Plugin\views\row;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\row\Fields;

/**
 * Portfolio views row plugin.
 *
 * This add caption support to parent fields plugin.
 *
 * @ViewsRow(
 *   id = "sooperthemes_portfolio_fields",
 *   title = @Translation("SooperThemes Portfolio Captions"),
 *   help = @Translation("Displays the fields with an optional template."),
 *   theme = "sooperthemes_portfolio_views_row",
 *   display_types = {"normal"}
 * )
 */
class Portfolio extends Fields {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['default_fields'] = ['default' => []];
    $options['caption_fields'] = ['default' => []];
    $options['caption_field_animation'] = ['default' => FALSE];
    $options['caption'] = ['default' => 'pushTop'];
    $options['caption_color'] = ['default' => ''];
    $options['caption_text_color'] = ['default' => ''];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['default_fields'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Default fields'),
      '#options' => $form['inline']['#options'],
      '#description' => $this->t('The fields are shown by default and covered by the caption on hover.'),
      '#default_value' => $this->options['default_fields'],
    ];

    $form['caption_fields'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Caption fields'),
      '#options' => $form['inline']['#options'],
      '#default_value' => $this->options['caption_fields'],
    ];

    $form['caption'] = [
      '#type' => 'select',
      '#title' => $this->t('Caption style'),
      '#options' => [
        'pushTop' => $this->t('Push top'),
        'pushDown' => $this->t('Push down'),
        'revealBottom' => $this->t('Reveal bottom'),
        'revealTop' => $this->t('Reveal top'),
        'revealLeft' => $this->t('Reveal left'),
        'moveRight' => $this->t('Move right'),
        'overlayBottom' => $this->t('Overlay bottom'),
        'overlayBottomPush' => $this->t('Overlay bottom push'),
        'overlayBottomReveal' => $this->t('Overlay bottom reveal'),
        'overlayBottomAlong' => $this->t('Overlay bottom along'),
        'overlayRightAlong' => $this->t('Overlay right along'),
        'minimal' => $this->t('Minimal'),
        'fadeIn' => $this->t('Fade in'),
        'zoom' => $this->t('Zoom'),
      ],
      '#default_value' => $this->options['caption'],
    ];

    $form['caption_field_animation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reveal caption fields one by one'),
      '#default_value' => $this->options['caption_field_animation'],
    ];

    $form['caption_color'] = [
      '#title' => $this->t('Custom Caption Color'),
      '#type' => 'textfield',
      '#default_value' => $this->options['caption_color'],
    ];

    $form['caption_text_color'] = [
      '#title' => $this->t('Custom Text Color'),
      '#type' => 'textfield',
      '#default_value' => $this->options['caption_text_color'],
    ];

    $form['#attached']['library'][] = 'sooperthemes_portfolio/admin';

    /**
     * Get the theme color pallette for the current theme
     */
    $moduleHandler = \Drupal::service('module_handler');
    $configFactory = \Drupal::service('config.factory');
    $settings = [];
    if ($moduleHandler->moduleExists('color')) {
      if ($palette = color_get_palette($configFactory->get('system.theme')->get('default'))) {
        $settings['palette'] = array_slice($palette, 0, 10);
      }
    }

    $form['#attached']['drupalSettings']['sooperthemesPortfolio'] = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    parent::submitOptionsForm($form, $form_state);
    $default_fields = $form_state->getValue(['row_options', 'default_fields']);
    $form_state->setValue(['row_options', 'default_fields'], array_filter($default_fields));
    $caption_fields = $form_state->getValue(['row_options', 'caption_fields']);
    $form_state->setValue(['row_options', 'caption_fields'], array_filter($caption_fields));
  }

}
