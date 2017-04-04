<?php

/**
 * @file
 * Contains \Drupal\migrate_wordpress\Plugin\migrate\source\WordPressPost.
 */

namespace Drupal\migrate_wordpress\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * @MigrateSource(
 *   id = "wp_post"
 * )
 */
class WordPressPost extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Get all the posts, post_type=post filters out revisions and pages.
    $query = $this->select($this->configuration['table_prefix'] . 'posts', 'p')
      ->fields('p', array_keys($this->fields()))
      ->condition('post_type', 'post')
      ->condition('post_status', ['publish', 'draft'], 'IN');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $row->setSourceProperty('post_date', strtotime($row->getSourceProperty('post_date')));
    $row->setSourceProperty('post_modified', strtotime($row->getSourceProperty('post_modified')));
    $row->setSourceProperty('post_name', $row->getSourceProperty('post_name'));
    $row->setSourceProperty('post_status', $row->getSourceProperty('post_status'));

    // This gathers up WordPress tags and categories and applies it to the hard
    // code field name field_tags in migrate.migration.wp_posts.yml. Use
    // hook_migration_load() to update the field name or simply copy the
    // migration.
    $this->applyCustomTagsFieldMapping($row);

    return parent::prepareRow($row);
  }

  /**
   * Apply custom mapping logic for taxonomy reference fields.
   *
   * @param \Drupal\migrate\Row $row
   *   The row object.
   *
   * @throws \Exception
   */
  protected function applyCustomTagsFieldMapping(Row $row) {
    $query = $this->select($this->configuration['table_prefix'] . 'term_relationships', 'tr')
      ->fields('tt', ['term_taxonomy_id']);
    $query
      ->condition('object_id', $row->getSourceProperty('ID'));
    $query->join($this->configuration['table_prefix'] . 'term_taxonomy', 'tt', 'tt.term_taxonomy_id = tr.term_taxonomy_id');

    $results = $query->execute()->fetchCol();

    $tags = [];
    $previous_ids = [];
    foreach ($results as $result) {

      // We must check for existing ids in case we had a category and tag which
      // were the same thing.
      if (!in_array($result, $previous_ids)) {
        $tags[]['target_id'] = $result;
        $previous_ids[] = $result;
      }
    }

    $row->setSourceProperty('tags', $tags);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = array(
      'ID' => $this->t('The Post ID'),
      'post_author' => $this->t('The post author.'),
      'post_date' => $this->t('The date the post was created.'),
      'post_content' => $this->t('The post content'),
      'post_title' => $this->t('The title.'),
      'post_name' => $this->t('The machine name of the post.'),
      'post_modified' => $this->t('The last modified time.'),
      'post_status' => $this->t('The post status.')
    );
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['ID']['type'] = 'integer';
    $ids['ID']['alias'] = 'p';
    return $ids;
  }

}