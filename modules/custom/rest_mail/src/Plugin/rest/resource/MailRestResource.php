<?php
namespace Drupal\rest_mail\Plugin\rest\resource;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "mail_rest_resource",
 *   label = @Translation("Mail rest resource"),
 *   serialization_class = "Drupal\node\Entity\Node",
 *   uri_paths = {
 *     "canonical" = "/mail-rest",
 *     "https://www.drupal.org/link-relations/create" = "/mail-rest"
 *   }
 * )
 */
class MailRestResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new ExampleGetRestResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('example_node_rest'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to POST requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {
//    if (!$this->currentUser->hasPermission('access content')) {
//      throw new AccessDeniedHttpException();
//    }

    if($data->from != ''){
      $from = $data->from;
    }elseif($data->to != '') {
      $to = $data->to;
    }elseif($data->subject != '') {
      $subject = $data->subject;
    }elseif($data->message != '') {
      $message = $data->message;
    }

    //    $from = "supot@mail.ru";
    //    $to = "admin@google.com";
    //    $subject = "test msg";
    //    $message = "How are you";

    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'rest_mail';
    $key = 'rest_mail'; // Replace with Your key
    $params['message'] = $message;
    $params['title'] = $subject;
    $params['from'] = $from;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] != true) {
      $message = t('There was a problem sending your email notification to @email.', array('@email' => $to));
      \Drupal::logger('mail-log')->error($message);
      $response = new ResourceResponse($message);
      $response->addCacheableDependency($message);
      return $response;
    }

    $message = t('An email notification has been sent to @email ', array('@email' => $to));
    //\Drupal\Core\Messenger\MessengerInterface::addMessage($message);
    \Drupal::logger('mail-log')->notice($message);

    $response = new ResourceResponse($message);
    $response->addCacheableDependency($message);
    return $response;
  }

  /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($data) {
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $from = \Drupal::request()->query->get('from');
    $to = \Drupal::request()->query->get('to');
    $subject = \Drupal::request()->query->get('subject');
    $message = \Drupal::request()->query->get('message');

    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'rest_mail';
    $key = 'rest_mail'; // Replace with Your key
    $params['message'] = $message;
    $params['title'] = $subject;
    $params['from'] = $from;
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = true;

    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] != true) {
      $message = t('There was a problem sending your email notification to @email.', array('@email' => $to));
      \Drupal::logger('mail-log')->error($message);
      $response = new ResourceResponse($message);
      $response->addCacheableDependency($message);
      return $response;
    }

    $message = t('An email notification has been sent to @email ', array('@email' => $to));
    //\Drupal\Core\Messenger\MessengerInterface::addMessage($message);
    \Drupal::logger('mail-log')->notice($message);

    $response = new ResourceResponse($message);
    $response->addCacheableDependency($message);
    return $response;
  }

}

