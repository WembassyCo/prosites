<?php

namespace Drupal\sooperthemes_portfolio\Plugin\views\pager;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\pager\PagerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Views pager plugin to handle infinite scrolling.
 *
 * @ViewsPager(
 *  id = "sooperthemes_portfolio_load_more",
 *  title = @Translation("SooperThemes Portfolio: Ajax Loader"),
 *  short_title = @Translation("Portfolio: load more"),
 *  help = @Translation("A views pager plugin which appends portfolios to the view."),
 *  theme = "sooperthemes_portfolio_views_pager"
 * )
 */
class Portfolio extends PagerPluginBase {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a LoadMore object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render($input) {
    if ($this->view->style_plugin->getPluginId() != 'sooperthemes_portfolio') {
      trigger_error($this->t('"Load more" pager can only be used in portfolios.'), E_USER_WARNING);
      return;
    }
    $this->options['portfolio_id'] = $this->view->style_plugin->getPortfolioId();
    $this->options['url'] = Url::fromRoute(
      'sooperthemes_portfolio.views_load_more',
        [
          'view_name' => $this->view->id(),
          'display_id' => $this->view->current_display,
        ]
    );
    return [
      '#theme' => $this->themeFunctions(),
      '#options' => $this->options,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    $options['items_per_page'] = ['default' => 10];
    $options['offset'] = ['default' => 0];
    $options['button_text'] = ['default' => $this->t('Load more')];
    $options['auto'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    $summary = $this->formatPlural($this->options['items_per_page'], '@count item per page', '@count items per page');
    if ($this->options['auto']) {
      $summary .= ', ' . $this->t('infinite scroll');
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $limit = $this->options['items_per_page'];
    $offset = $this->options['offset'];

    if ($this->routeMatch->getRouteName() == 'sooperthemes_portfolio.views_load_more') {
      $limit = 0;
      $offset += $this->options['items_per_page'];
    }

    $this->view->query->setLimit($limit);
    $this->view->query->setOffset($offset);
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $pager_text = $this->displayHandler->getPagerText();

    $form['items_per_page'] = [
      '#title' => $pager_text['items per page title'],
      '#type' => 'number',
      '#description' => $pager_text['items per page description'],
      '#default_value' => $this->options['items_per_page'],
    ];

    $form['offset'] = [
      '#type' => 'number',
      '#title' => $this->t('Offset (number of items to skip)'),
      '#description' => $this->t('For example, set this to 3 and the first 3 items will not be displayed.'),
      '#default_value' => $this->options['offset'],
    ];

    $form['button_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button Text'),
      '#default_value' => $this->options['button_text'],
    ];

    $form['auto'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically load subsequent pages as the user scrolls'),
      '#default_value' => $this->options['auto'],
    ];
  }

}
