<?php

declare(strict_types=1);

namespace Drupal\flexiaccess\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure flexiaccess settings for this site.
 */
final class FlexiaccessSettingsForm extends ConfigFormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs a new FlexiaccessSettingsForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'flexiaccess_flexiaccess_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['flexiaccess.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $contentTypes = [];
    $content_types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    foreach ($content_types as $type) {
      $contentTypes[$type->id()] = $type->label();
    }
    $selectedContentTypes = $this->config('flexiaccess.settings')->get('content_types') ?: [];
    $form['flexiaccess']['content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content Types'),
      '#description' => $this->t('Check the content types you want to assign ACLs using Flexi access.
       Then press “Save”.'),
      '#options' => $contentTypes,
      '#default_value' => $selectedContentTypes,
    ];
    $form['flexiaccess']['typesettings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('PER-TYPE SETTINGS'),
      '#description_display' => 'before',
      '#description' => $this->t('To configure flexiaccess settings for a specific content type,
       enable it first, above.'),
    ];

    foreach ($selectedContentTypes as $typeId => $value) {
      if ($value) {
        $form['flexiaccess']['typesettings'][$typeId] = [
          '#type' => 'details',
          '#title' => $this->t('@type Settings', ['@type' => $contentTypes[$typeId]]),
        ];

        $form['flexiaccess']['typesettings'][$typeId]["autoACL_$typeId"] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Automatic ACL creation'),
          '#description'   => t('Enable this for content types which should have restricted access by default.
           This will create an empty ACL for every new node created, thereby restricting access to the node by all users.
            This does not affect existing nodes.'),
          '#default_value' => $this->config('flexiaccess.settings')->get("flexiaccess_typesettings_$typeId") ?? 0,
        ];

      }
    }
    $form['flexiaccess']['priority'] = [
      '#type' => 'textfield',
      '#size' => 2,
      '#title' => t('Default priority for new ACL entries'),
      '#default_value' => $this->config('flexiaccess.settings')->get('flexiaccess_priority') ?? 0,
      '#description' => t('With every ACL entry (which translate to entries in the node_access table) there is an associated priority.
        You only need to change this if you understand what it does, and you want to integrate Flexi access with other access control modules.
        Note that changing this value does not change previously created ACL entries.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

    $config = $this->config('flexiaccess.settings');
    $config->set('content_types', array_filter($form_state->getValue('content_types')))
      ->set('flexiaccess_priority', $form_state->getValue('priority'));
    foreach ($form_state->getValue('content_types') as $typeId => $value) {
      if ($value) {
        $autoAclValue = $form_state->getValue('autoACL_' . $typeId);
        $config->set("flexiaccess_typesettings_$typeId", $autoAclValue);
      }
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

}