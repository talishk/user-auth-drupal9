<?php

namespace Drupal\nasdaq_user\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * The NasdaqAuthEventSubscriber class.
 */
class NasdaqAuthEventSubscriber implements EventSubscriberInterface {

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   The current user account.
   */
  public function __construct(LoggerChannelFactory $logger, EntityTypeManagerInterface $entity_type_manager) {
    $this->logger = $logger;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onLoad'];
    return $events;
  }

  /**
   * Check if auth token is valid then authenticate and login the user.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event to process.
   */
  public function onLoad(RequestEvent $event) {
    if (!empty($event->getRequest()->get('authtoken'))) {
      // Get the auth token from the query string.
      $auth_token = $event->getRequest()->get('authtoken');

      // Get the user id which has the given auth token.
      $user_query = $this->entityTypeManager->getStorage('user');

      // Check if any user has the given auth token.
      $uids = $user_query->getQuery()
        ->condition('field_auth_token', $auth_token)
        ->execute();

      // If user is found then login the user.
      if (!empty($uids)) {
        $uid = reset($uids);
        $user = $user_query->load($uid);
        user_login_finalize($user);

        // Redirect the user to front page.
        $url = Url::fromRoute('<front>')->toString();
        $event->setResponse(new RedirectResponse($url));
      }
      else {
        // Log the error if auth token is invalid.
        $this->logger->get('nasdaq_user')->error('Invalid Auth Token.');
      }
    }
    return;
  }

}
