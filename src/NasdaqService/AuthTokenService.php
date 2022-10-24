<?php

namespace Drupal\nasdaq_user\NasdaqService;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * AuthTokenService class.
 */
class AuthTokenService {

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
   */
  public function __construct(
    LoggerChannelFactory $logger,
    EntityTypeManagerInterface $entity_type_manager) {
    $this->logger = $logger;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory'),
      $container->get('entity_type.manager')

    );
  }

  /**
   * Generate 32 digits random token.
   */
  public function generateAuthToken($uid) {
    try {
      // Generate 32 digits random token using md5 hash.
      $auth_token = md5($uid);
      // Get user object by uid.
      $user_info = $this->entityTypeManager->getStorage('user')
        ->load($uid);
      if ($user_info->hasField('field_auth_token')) {
        $user_info->field_auth_token->value = $auth_token;
        $user_info->save();
      }
      else {
        // Log the error if auth token field is not present.
        $this->logger->get('nasdaq_user')->error('Field Auth Token not found in user entity.');
      }
      return TRUE;

    }
    catch (\Exception $e) {
      // Log exception in watchdog.
      $message = $e->getMessage();
      $this->logger->get('nasdaq_user')->error($message);
    }
  }

}
