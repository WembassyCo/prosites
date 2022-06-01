<?php

namespace Drupal\sooperthemes_portfolio\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Returns responses for View portfolio route.
 */
class ViewPortfolioController extends ControllerBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs the controller object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * Builds the response.
   */
  public function displayView($view_name, $display_id) {

    /** @var \Drupal\views\ViewEntityInterface $view */
    $view = $this->entityTypeManager
      ->getStorage('view')
      ->load($view_name);

    if (!$view) {
      throw new NotFoundHttpException();
    }

    $view_executable = $view->getExecutable();
    if (!$view_executable->access($display_id)) {
      throw new AccessDeniedHttpException();
    }

    // Execute the view.
    $build = $view_executable->preview('default');

    // This controller is designed to serve "load more" pager and nothing else.
    if ($view_executable->pager->getPluginId() != 'sooperthemes_portfolio_load_more') {
      throw new NotFoundHttpException();
    }

    // It is important to return only rows to avoid Views wrappers.
    return new Response($this->renderer->render($build['#rows'][0]));
  }

}
