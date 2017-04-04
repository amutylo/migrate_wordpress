<?php

/**
 * @file
 * Contains \Drupal\migrate_wordpress\Plugin\migrate\source\WordPressTermBase.
 */

namespace Drupal\migrate_wordpress\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * @MigrateSource(
 *   id = "wp_term"
 * )
 */
class WordPressTerm extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select($this->configuration['table_prefix'] . 'terms', 't')
      ->fields('t', array_keys($this->termsFields()));
    return $query;
  }

  /**
   * Returns the User fields to be migrated.
   *
   * @return array
   *   Associative array having field name as key and description as value.
   */
  protected function termsFields() {
    $fields = array(
      'term_id' => $this->t('The term ID.'),
      'name' => $this->t('The name of the term.'),
      'slug' => $this->t('The term slug.')
    );
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->termsFields();
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Find parents for this row.
    $query = $this->select($this->configuration['table_prefix'] .'term_taxonomy', 'wptt')
      ->fields('wptt', array('parent', 'term_id', 'taxonomy', 'description'))
      ->condition('term_id', $row->getSourceProperty('term_id'))
      ->execute()
      ->fetchAssoc();

    if (!empty($query)) {
      $row->setSourceProperty('parent', $query['parent']);
      $row->setSourceProperty('description', $query['description']);
      $row->setSourceProperty('vid', $query['taxonomy']);
    }

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['term_id']['type'] = 'integer';
    $ids['term_id']['alias'] = 't';
    return $ids;
  }


}
