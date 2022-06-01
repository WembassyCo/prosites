<?php

namespace Drupal\sooperthemes_portfolio\Plugin\views\style;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sooperthemes portfolio style plugin.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "sooperthemes_portfolio",
 *   title = @Translation("SooperThemes Portfolio"),
 *   help = @Translation("Portfolio grid layout."),
 *   theme = "sooperthemes_portfolio_views_style",
 *   display_types = {"normal"}
 * )
 */
class Portfolio extends StylePluginBase {

  /**
   * Unique selector for this portfolio.
   *
   * @var string
   */
  protected $portfolioId;

  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $usesRowClass = TRUE;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs Portfolio object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityDisplayRepositoryInterface $entity_display_repository, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityDisplayRepository = $entity_display_repository;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_display.repository'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['layout_mode'] = ['default' => 'grid'];
    $options['sort_to_prevent_gaps'] = ['default' => FALSE];
    $options['drag'] = ['default' => TRUE];
    $options['animation_type'] = ['default' => 'fadeOut'];
    $options['auto'] = ['default' => FALSE];
    $options['auto_timeout'] = ['default' => 5000];
    $options['auto_pause_on_hover'] = ['default' => TRUE];
    $options['show_navigation'] = ['default' => TRUE];
    $options['show_pagination'] = ['default' => TRUE];
    $options['rewind_nav'] = ['default' => TRUE];
    $options['scroll_by_page'] = ['default' => TRUE];
    $options['media_queries'] = [
      'default' => [
        ['width' => 1500, 'cols' => 5],
        ['width' => 1100, 'cols' => 4],
        ['width' => 800, 'cols' => 3],
        ['width' => 480, 'cols' => 2],
        ['width' => 320, 'cols' => 1],
      ],
    ];

    $filter_defaults = [
      'field' => '',
      'style' => 'links',
      'position' => 'left',
      'label' => $this->t('Filter by:'),
    ];
    $options['filters'] = [
      'default' => [
        1 => $filter_defaults,
        2 => $filter_defaults,
        3 => $filter_defaults,
      ],
    ];

    $options['layout'] = [
      'default' => [
        'gap_horizontal' => 0,
        'gap_vertical' => 0,
        'grid_adjustment' => 'responsive',
      ]
    ];
    $options['display_type'] = ['default' => 'default'];
    $options['display_type_speed'] = ['default' => 50];
    $options['search'] = ['default' => FALSE];
    $options['link'] = ['default' => FALSE];
    $options['content_page'] = ['default' => ''];
    $options['content_page_display'] = ['default' => ''];
    $options['single_page_animation'] = ['default' => 'left'];
    $options['single_page_inline_position'] = ['default' => 'top'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['search'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Quick Search'),
      '#default_value' => $this->options['search'],
    ];

    $form['link'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Link item to entity'),
      '#description' => $this->t('Makes the complete grid item clickable.'),
      '#default_value' => $this->options['link'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[content_page]"]' => ['value' => ''],
        ],
      ],
    ];

    $form['display_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Show animation'),
      '#options' => [
        'default' => $this->t('Default'),
        'bottomToTop' => $this->t('Bottom to top'),
        'fadeIn' => $this->t('Fade in'),
        'fadeInToTop' => $this->t('Fade in to top'),
        'sequentially' => $this->t('Sequential'),
      ],
      '#default_value' => $this->options['display_type'],
    ];

    $default_value = '';
    foreach ($this->options['media_queries'] as $query) {
      $default_value .= $query['width'] . ' | ' . $query['cols'] . "\n";
    }

    $form['display_type_speed'] = [
      '#type' => 'number',
      '#title' => $this->t('Show animation speed'),
      '#min' => 0,
      '#max' => 99999,
      '#default_value' => $this->options['display_type_speed'],
      '#field_suffix' => $this->t('ms'),
      '#states' => [
        'invisible' => [
          ':input[name="style_options[display_type]"]' => ['value' => 'default'],
        ],
      ],
    ];

    $form['content_page'] = [
      '#type' => 'select',
      '#title' => $this->t('Load content in modal'),
      '#options' => [
        '' => t('- No -'),
        'full' => t('Full Screen Modal'),
        'inline' => t('Inline Modal'),
      ],
      '#default_value' => $this->options['content_page'],
    ];

    $form['single_page_animation'] = [
      '#type' => 'select',
      '#title' => $this->t('Page animation'),
      '#options' => [
        'left' => t('Left'),
        'right' => t('Right'),
        'fade' => t('Fade'),
      ],
      '#default_value' => $this->options['single_page_animation'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[content_page]"]' => ['value' => 'full'],
        ],
      ],
    ];

    $form['single_page_inline_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Modal position'),
      '#options' => [
        'above' => t('Above'),
        'below' => t('Below'),
        'top' => t('Top'),
        'bottom' => t('Bottom'),
      ],
      '#default_value' => $this->options['single_page_inline_position'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[content_page]"]' => ['value' => 'inline'],
        ],
      ],
    ];

    $form['content_page_display'] = [
      '#type' => 'select',
      '#title' => $this->t('Modal display format'),
      '#options' => $this->entityDisplayRepository->getViewModeOptions('node'),
      '#default_value' => $this->options['content_page_display'],
      '#states' => [
        'invisible' => [
          ':input[name="style_options[content_page]"]' => ['value' => ''],
        ],
      ],
    ];

    $form['layout_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Layout mode'),
      '#options' => [
        'grid' => $this->t('Grid'),
        'mosaic' => $this->t('Mosaic'),
        'slider' => $this->t('Slider'),
      ],
      '#default_value' => $this->options['layout_mode'],
    ];

    $form['sort_to_prevent_gaps'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Sort the items (bigger to smallest) if there are gaps in grid'),
      '#default_value' => $this->options['sort_to_prevent_gaps'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[layout_mode]"]' => ['value' => 'mosaic'],
        ],
      ],
    ];

    $form['drag'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable mouse and touch drag support'),
      '#default_value' => $this->options['drag'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[layout_mode]"]' => ['value' => 'slider'],
        ],
      ],
    ];

    $form['auto'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Play the slider automatically'),
      '#default_value' => $this->options['auto'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[layout_mode]"]' => ['value' => 'slider'],
        ],
      ],
    ];

    $form['auto_timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Play timeout'),
      '#min' => 0,
      '#max' => 99999,
      '#default_value' => $this->options['auto_timeout'],
      '#field_suffix' => $this->t('ms'),
      '#states' => [
        'visible' => [
          ':input[name="style_options[layout_mode]"]' => ['value' => 'slider'],
        ],
      ],
    ];

    $form['auto_pause_on_hover'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Stop slider when user hover the slider'),
      '#default_value' => $this->options['auto_pause_on_hover'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[layout_mode]"]' => ['value' => 'slider'],
        ],
      ],
    ];

    $form['show_navigation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show "next" and "prev" buttons for slider'),
      '#default_value' => $this->options['show_navigation'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[layout_mode]"]' => ['value' => 'slider'],
        ],
      ],
    ];

    $form['show_pagination'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show pagination for slider'),
      '#default_value' => $this->options['show_pagination'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[layout_mode]"]' => ['value' => 'slider'],
        ],
      ],
    ];

    $form['rewind_nav'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable slide to first item (last item)'),
      '#default_value' => $this->options['rewind_nav'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[layout_mode]"]' => ['value' => 'slider'],
        ],
      ],
    ];

    $form['scroll_by_page'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Scroll by page and not by item'),
      '#default_value' => $this->options['scroll_by_page'],
      '#states' => [
        'visible' => [
          ':input[name="style_options[layout_mode]"]' => ['value' => 'slider'],
        ],
      ],
    ];
     $form['layout'] = [
       '#type' => 'details',
       '#title' => t('Grid layout settings'),
     ];

    $form['layout']['gap_horizontal'] = [
      '#type' => 'number',
      '#title' => $this->t('Row gap'),
      '#min' => 0,
      '#max' => 99999,
      '#default_value' => $this->options['layout']['gap_horizontal'],
      '#field_suffix' => $this->t('px'),
    ];

    $form['layout']['gap_vertical'] = [
      '#type' => 'number',
      '#title' => $this->t('Vertical gap'),
      '#min' => 0,
      '#max' => 99999,
      '#default_value' => $this->options['layout']['gap_vertical'],
      '#field_suffix' => $this->t('px'),
    ];

    $form['layout']['grid_adjustment'] = [
      '#type' => 'select',
      '#title' => $this->t('Grid Alignment'),
      '#options' => [
        'default' => $this->t('Default'),
        'alignCenter' => $this->t('Align center'),
        'responsive' => $this->t('Responsive'),
      ],
      '#default_value' => $this->options['layout']['grid_adjustment'],
    ];

    // Optional filter for nodes.
    $base_tables = $this->view->getBaseTables();
    if (isset($base_tables['node_field_data'])) {

      $form['filters'] = [
        '#type' => 'details',
        '#title' => t('Filters'),
        '#states' => [
          'invisible' => [
            ':input[name="style_options[layout_mode]"]' => ['value' => 'slider'],
          ],
        ],
      ];

      $options = ['' => $this->t('- Select -')];
      foreach ($this->entityFieldManager->getFieldStorageDefinitions('node') as $field_name => $definition) {
        $options[$field_name] = $definition->isBaseField() ? $definition->getLabel() : $field_name;
      }


      foreach ($this->options['filters'] as $delta => $filter) {

        $form['filters'][$delta] = [
          '#type' => 'details',
          '#open' => TRUE,
          '#title' => $this->t('Filter Field @number', ['@number' => $delta]),
        ];

        $form['filters'][$delta]['field'] = [
          '#type' => 'select',
          '#title' => $this->t('Field'),
          '#options' => $options,
          '#default_value' => $this->options['filters'][$delta]['field'],
        ];

        $filter_states= [
          'invisible' => [
            ':input[name="style_options[filters][' . $delta . '][field]"]' => ['value' => ''],
          ],
        ];

        $form['filters'][$delta]['style'] = [
          '#type' => 'radios',
          '#title' => $this->t('Style'),
          '#options' => [
            'links' => $this->t('Links'),
            'buttons' => $this->t('Buttons'),
            'dropdown' => $this->t('Dropdown'),
          ],
          '#default_value' => $this->options['filters'][$delta]['style'],
          '#states' => $filter_states,
        ];

        $form['filters'][$delta]['position'] = [
          '#type' => 'radios',
          '#title' => $this->t('Position'),
          '#options' => [
            'left' => $this->t('Left'),
            'center' => $this->t('Center'),
            'right' => $this->t('Right'),
          ],
          '#default_value' => $this->options['filters'][$delta]['position'],
          '#states' => $filter_states,
        ];

        $form['filters'][$delta]['label'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Label'),
          '#default_value' => $this->options['filters'][$delta]['label'],
          '#states' => $filter_states,
        ];
      }

    }

    $form['animation_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Filter Animation'),
      '#options' => [
        'fadeOut' => $this->t('Fade out'),
        'quicksand' => $this->t('Quick sand'),
        'sequentially' => $this->t('Sequential'),
        'scaleSides' => $this->t('Scale sides'),
        'frontRow' => $this->t('Front row'),
        'rotateRoom' => $this->t('Rotate room'),
      ],
      '#default_value' => $this->options['animation_type'],
      '#states' => [
        'invisible' => [
          ':input[name="style_options[layout_mode]"]' => ['value' => 'slider'],
        ],
      ],
    ];

    $form['media_queries'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Media queries'),
      '#default_value' => $default_value,
      '#description' => $this->t('Define "media queries" for columns layout. Enter one query per line, in the format <em>width | columns</em>.'),
      '#states' => [
        'visible' => [
          ':input[name="style_options[grid_adjustment]"]' => ['value' => 'responsive'],
        ],
      ],
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    $style_options = $form_state->getValue('style_options');
    $input = $style_options['media_queries'];
    $style_options['media_queries'] = [];
    if ($input) {
      $rows = explode("\n", $input);
      foreach ($rows as $row) {
        if (preg_match('/([0-9]+)\s?\|\s?([0-9]+)/', $row, $matches)) {
          $style_options['media_queries'][] = [
            'width' => $matches[1],
            'cols' => $matches[2],
          ];
        }
      }
    }
    $form_state->setValue('style_options', $style_options);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    if (($this->options['layout_mode'] == 'slider') && ($this->options['content_page'])) {
      $build['#attached']['library'][] = 'sooperthemes_portfolio/sooperthemes_cube_portfolio_slider_popup';
    }
    elseif ($this->options['layout_mode'] == 'slider') {
      $build['#attached']['library'][] = 'sooperthemes_portfolio/sooperthemes_cube_portfolio_slider';
    }
    elseif ($this->options['content_page']) {
      $build['#attached']['library'][] = 'sooperthemes_portfolio/sooperthemes_cube_portfolio_popup';
    }
    else {
      $build['#attached']['library'][] = 'sooperthemes_portfolio/sooperthemes_cube_portfolio';
    }
    $build['#attached']['library'][] = 'sooperthemes_portfolio/sooperthemes_portfolio';

    $portfolio_selector = $this->getPortfolioId();
    $settings = [
      'layoutMode' => $this->options['layout_mode'],
      'sortByDimension' => $this->options['sort_to_prevent_gaps'],
      'drag' => $this->options['drag'],
      'auto' => $this->options['auto'],
      'autoTimeout' => $this->options['auto_timeout'],
      'animationType' => $this->options['animation_type'],
      'autoPauseOnHover' => $this->options['auto_pause_on_hover'],
      'showNavigation' => $this->options['show_navigation'],
      'showPagination' => $this->options['show_pagination'],
      'rewindNav' => $this->options['rewind_nav'],
      'scrollByPage' => $this->options['scroll_by_page'],
      'gridAdjustment' => $this->options['layout']['grid_adjustment'],
      'gapHorizontal' => (int) $this->options['layout']['gap_horizontal'],
      'gapVertical' => (int) $this->options['layout']['gap_vertical'],
      'displayType' => $this->options['display_type'],
      'displayTypeSpeed' => (int) $this->options['display_type_speed'],
      'singlePageAnimation' => $this->options['single_page_animation'],
      'singlePageInlinePosition' => $this->options['single_page_inline_position'],
    ];

    $filters = [];
    foreach ($this->options['filters'] as $filter) {
      $filters[] = '[data-drupal-selector="sp-filter-' . $filter['field'] . '"]';
    }
    if ($filters) {
      $settings['filters'] = implode(',', $filters);
    }

    if ($this->options['search']) {
      $settings['search'] = '.js-cbp-search';
    }

    if ($this->options['media_queries']) {
      $settings['mediaQueries'] = $this->options['media_queries'];
    }

    if ($this->view->rowPlugin->getPluginId() == 'sooperthemes_portfolio_fields') {
      $settings['caption'] = $this->view->rowPlugin->options['caption'];
    }

    if ($this->view->pager->getPluginId() == 'sooperthemes_portfolio_load_more') {
      $settings['plugins']['loadMore'] = [
        'element' => '#pager-' . $this->getPortfolioId(),
        'action' => $this->view->pager->options['auto'] ? 'auto' : 'click',
        'loadItems' => $this->view->pager->options['items_per_page'],
      ];
    }

    $build['#attached']['drupalSettings']['stPortfolios'][$portfolio_selector] = $settings;
    foreach ($this->view->result as $result) {
      if (($this->options['link'])
        && ($this->options['content_page'] == FALSE)
        && property_exists($result, '_entity')
        && method_exists($result->_entity, 'url')) {
        $entity_url = $result->_entity->url();
        $result->onclick = 'onclick="javascript:location.href=\'' . $entity_url . '\'"';
      }
      else {
        $result->onclick = '';
      }
    }
    return $build;
  }

  /**
   * Creates portfolio selector.
   */
  public function getPortfolioId() {
    if (!$this->portfolioId) {
      $this->portfolioId = Html::getUniqueId('sp-' . $this->view->id() . '-' . $this->view->current_display);
    }
    return $this->portfolioId;
  }

}
