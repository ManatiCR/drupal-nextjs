<?php

namespace Drupal\manati_layout_builder\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * TwoColClass Section Layout Form class.
 */
class TwoColClass extends LayoutDefault implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $variants = array_keys($this->getVariants());
    $backgrounds = array_keys($this->getBackgrounds());
    $column_proportions = array_keys($this->getColumnProportions());
    $spacing_top = array_keys($this->getSpacing());
    $spacing_bottom = array_keys($this->getSpacing());
    $spacing_columns = array_keys($this->getSpacing());
    return parent::defaultConfiguration() + [
      'variant' => array_shift($variants),
      'background' => array_shift($backgrounds),
      'column_proportions' => array_shift($column_proportions),
      'spacing_top' => array_shift($spacing_top),
      'spacing_bottom' => array_shift($spacing_bottom),
      'spacing_columns' => array_shift($spacing_columns),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['variant'] = [
      '#type' => 'select',
      '#title' => $this->t('Variant'),
      '#default_value' => $this->configuration['variant'],
      '#options' => $this->getVariants(),
      '#description' => $this->t('Choose the variant to be used in this section.'),
    ];
    $form['background'] = [
      '#type' => 'select',
      '#title' => $this->t('Background color'),
      '#default_value' => $this->configuration['background'],
      '#options' => $this->getBackgrounds(),
      '#description' => $this->t('Choose the background color of this section.'),
    ];
    $form['spacing_top'] = [
      '#type' => 'select',
      '#title' => $this->t('Top space'),
      '#default_value' => $this->configuration['spacing_top'],
      '#options' => $this->getSpacing(),
      '#description' => $this->t('Choose the size of the space above this section.'),
    ];
    $form['spacing_bottom'] = [
      '#type' => 'select',
      '#title' => $this->t('Bottom space'),
      '#default_value' => $this->configuration['spacing_bottom'],
      '#options' => $this->getSpacing(),
      '#description' => $this->t('Choose the size of the space below this section.'),
    ];
    $form['column_proportions'] = [
      '#type' => 'select',
      '#title' => $this->t('Column dimensions'),
      '#default_value' => $this->configuration['column_proportions'],
      '#options' => $this->getColumnProportions(),
      '#description' => $this->t('Choose the dimension for the columns in this section.'),
    ];
    $form['spacing_columns'] = [
      '#type' => 'select',
      '#title' => $this->t('Column spacing'),
      '#default_value' => $this->configuration['spacing_columns'],
      '#options' => $this->getSpacing(),
      '#description' => $this->t('Choose the size of the space between the columns in this section.'),
    ];
    $form['side_spacing'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add margin on the sides of the section'),
      '#default_value' => isset($this->configuration['side_spacing']) ? $this->configuration['side_spacing'] : TRUE,
    ];
    $form['equal_height'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Make height of the columns content equal'),
      '#default_value' => isset($this->configuration['equal_height']) ? $this->configuration['equal_height'] : TRUE,
      '#description' => $this->t('Does not apply if there is more than one element in the column.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['variant'] = $form_state->getValue('variant');
    $this->configuration['background'] = $form_state->getValue('background');
    $this->configuration['column_proportions'] = $form_state->getValue('column_proportions');
    $this->configuration['spacing_top'] = $form_state->getValue('spacing_top');
    $this->configuration['spacing_bottom'] = $form_state->getValue('spacing_bottom');
    $this->configuration['spacing_columns'] = $form_state->getValue('spacing_columns');
    $this->configuration['equal_height'] = $form_state->getValue('equal_height');
    $this->configuration['side_spacing'] = $form_state->getValue('side_spacing');
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);
    $build['#attributes']['class'] = [
      'layout',
      'layout--twocol',
      $this->configuration['variant'] === 'fixed' ? 'layout--fixed-width' : '',
      isset($this->configuration['background']) ? 'layout--background-' . $this->configuration['background'] : 'layout--background-transparent',
      'layout--twocol-' . $this->configuration['column_proportions'],
      'layout--spacing-top-' . $this->configuration['spacing_top'],
      'layout--spacing-bottom-' . $this->configuration['spacing_bottom'],
      'layout--spacing-cols-' . $this->configuration['spacing_columns'],
    ];

    if (isset($this->configuration['side_spacing']) && $this->configuration['side_spacing'] == TRUE) {
      $build['#attributes']['class'][] = 'layout--side-spacing';
    }

    if (isset($this->configuration['equal_height']) && $this->configuration['equal_height'] == TRUE) {
      $build['#attributes']['class'][] = 'layout--content-equal-height';
    }

    return $build;
  }

  /**
   * Return an array with the available column proportions.
   */
  protected function getColumnProportions() {
    return [
      '50-50' => '50% - 50%',
      '30-70' => '30% - 70%',
      '40-60' => '40% - 60%',
      '70-30' => '70% - 30%',
      '60-40' => '60% - 40%',
    ];
  }

  /**
   * Return an array with the available variants.
   */
  protected function getVariants() {
    return [
      'full' => $this->t('Full width'),
      'fixed' => $this->t('Limited width'),
    ];
  }

  /**
   * Return an array with the available background colors.
   */
  protected function getBackgrounds() {
    return [
      'transparent' => $this->t('Transparent'),
      'gray' => $this->t('Gray'),
      'white' => $this->t('White'),
    ];
  }

  /**
   * Return an array with the available spacing options.
   */
  protected function getSpacing() {
    return [
      'none' => $this->t('None'),
      'small' => $this->t('Small'),
      'medium' => $this->t('Medium'),
      'large' => $this->t('Large'),
    ];
  }

}
