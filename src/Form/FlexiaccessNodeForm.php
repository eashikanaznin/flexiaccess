<?php

namespace Drupal\flexiaccess\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
// use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a basic Flexiaccess Node form.
 */
class FlexiaccessNodeForm extends FormBase {

  // /**
  //  * The messenger service.
  //  *
  //  * @var \Drupal\Core\Messenger\MessengerInterface
  //  */
  // protected MessengerInterface $messenger;

  // /**
  //  * Constructs a new FlexiaccessNodeForm.
  //  *
  //  * @param \Drupal\Core\Messenger\MessengerInterface $messenger
  //  *   The messenger service.
  //  */
  // public function __construct(MessengerInterface $messenger) {
  //   $this->messenger = $messenger;
  // }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): FlexiaccessNodeForm {
    return new static(
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'flexiaccess_node_form';
  }

  /**
   * Builds the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['title'] = [
      '#type' => 'markup',
      '#markup' => '<h2>' . $this->t('Flexiaccess Node Settings') . '</h2>',
    ];

    // Checkbox options for different permissions.
    $form['permissions'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Permissions'),
      '#description' => $this->t('Select permissions for the node.'),
    ];

    $form['permissions']['view_permission'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow view access'),
    ];

    $form['permissions']['update_permission'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow update access'),
    ];

    $form['permissions']['delete_permission'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow delete access'),
    ];

    // Submit button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save settings'),
    ];

    return $form;
  }

  /**
   * Form submission handler.
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $values = $form_state->getValues();

    // Handle submitted values.
    $permissions = [
      'view_permission' => $values['view_permission'],
      'update_permission' => $values['update_permission'],
      'delete_permission' => $values['delete_permission'],
    ];

    // Log the permissions or save to config as needed.
    $this->messenger->addMessage($this->t('Permissions have been saved.'));
  }

}