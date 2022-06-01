<?php

namespace Drupal\sooperthemes_portfolio\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;

/**
 * Plugin implementation of the Portfolio image formatter.
 *
 * @FieldFormatter(
 *   id = "sooperthemes_portfolio",
 *   label = @Translation("SooperThemes Portfolio Lightbox"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class Portfolio extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_style' => '',
      'lightbox_image_style' => '',
      'lightbox_title' => 'image_alt',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $image_styles = image_style_options(FALSE);

    $element['image_style'] = [
      '#title' => $this->t('Image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#empty_option' => $this->t('None (original image)'),
      '#options' => $image_styles,
    ];

    $element['lightbox_image_style'] = $element['image_style'];
    $element['lightbox_image_style']['#title'] = $this->t('Lightbox image style (default)');
    $element['lightbox_image_style']['#default_value'] = $this->getSetting('lightbox_image_style');

    $element['lightbox_title'] = [
      '#title' => $this->t('Lightbox title'),
      '#type' => 'select',
      '#options' => $this->titleSourceOptions(),
      '#default_value' => $this->getSetting('lightbox_title'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $image_styles = image_style_options(FALSE);

    // Unset possible 'No defined styles' option.
    unset($image_styles['']);

    $image_style_setting = $this->getSetting('image_style');
    $style = isset($image_styles[$image_style_setting]) ?
      $image_styles[$image_style_setting] : $this->t('Original image');
    $summary[] = $this->t('Image style: @style', ['@style' => $style]);

    $image_style_setting = $this->getSetting('lightbox_image_style');
    $style = isset($image_styles[$image_style_setting]) ?
      $image_styles[$image_style_setting] : $this->t('Original image');
    $summary[] = $this->t('Lightbox image style (default): @style', ['@style' => $style]);

    $lightbox_title = $this->getSetting('lightbox_title');
    if ($lightbox_title) {
      $options = $this->titleSourceOptions();
      $summary[] = $this->t('Lightbox title: @title', ['@title' => $options[$lightbox_title]]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];
    $files = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($files)) {
      return $elements;
    }

    $settings = $this->getSettings();

    // Collect cache tags to be added for each item in the field.
    $cache_tags = [];
    if ($settings['image_style']) {
      $image_style = $this->imageStyleStorage->load($settings['image_style']);
      $cache_tags = $image_style->getCacheTags();
    }

    // Prepare image styles.
    if ($settings['lightbox_image_style']) {
      /** @var \Drupal\image\ImageStyleInterface $lightbox_image_style */
      $lightbox_image_style = $this->imageStyleStorage->load($settings['lightbox_image_style']);
    }

    /** @var \Drupal\file\FileInterface[] $files */
    foreach ($files as $delta => $file) {
      $cache_contexts = [];
      $cache_contexts[] = 'url.site';
      $cache_tags = Cache::mergeTags($cache_tags, $file->getCacheTags());

      // Extract field item attributes for the theme function, and unset them
      // from the $item so that the field template does not re-render them.
      $item = $file->_referringItem;
      $item_attributes = $item->_attributes;
      unset($item->_attributes);

      $image_uri = $file->getFileUri();
      $default_url = file_create_url($image_uri);
      $link_attributes = [];

      $elements[$delta] = [
        '#theme' => 'sooperthemes_portfolio_image_formatter',
        '#item' => $item,
        '#item_attributes' => $item_attributes,
        '#link_attributes' => $link_attributes,
        '#image_style' => $settings['image_style'],
        '#url' => empty($lightbox_image_style) ? $default_url : $lightbox_image_style->buildUrl($image_uri),
        '#lightbox_title' => $settings['lightbox_title'],
        '#cache' => [
          'tags' => $cache_tags,
          'contexts' => $cache_contexts,
        ],
      ];
    }

    $elements['#attached']['drupalSettings']['sooperthemesPortfolio'] = $settings;
    $elements['#attached']['library'][] = 'sooperthemes_portfolio/image_formatter';
    $elements['#attributes']['class'][] = 'sp-formatterr';
    return $elements;
  }

  /**
   * Returns title source options.
   */
  protected function titleSourceOptions() {
    return [
      'none' => $this->t('- None -'),
      'image_title' => $this->t('Image title'),
      'image_alt' => $this->t('Image alt'),
      'entity_label' => $this->t('Entity label'),
    ];
  }

  /**
   * Returns labels for boolean settings.
   */
  protected function getBooleanSettingLabel($setting) {
    return $this->getSetting($setting) ? $this->t('Yes') : $this->t('No');
  }

}
