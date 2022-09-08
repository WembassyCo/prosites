<?php

namespace Drupal\tcg_migrate\Plugin\migrate\source;

/*use Drupal\migrate\Plugin\SourceEntityInterface;*/
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;
/* for file */
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;

/**
 * Extract users from Drupal 7 database.
 *
 * @MigrateSource(
 *   id = "tcg_migrate_gallery_item"
 * )
 */
class Galleryitem extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Select node in its last revision.
    $query = $this->select('node', 'n')
      ->condition('n.type', 'gallery_item', '=')
      ->fields('n', [
        'nid',
        'vid',
        'type',
        'language',
        'title',
        'uid',
        'status',
        'created',
        'changed',
        'promote',
        'sticky',
        'uuid',
      ]);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->baseFields();

    $fields['field_caption_value'] = $this->t('Value of field_caption');
    $fields['field_gallery_tid'] = $this->t('Value of field_gallery');
    $fields['field_image_fid'] = $this->t('Value of field_image');

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');

    // field_locid.
    $result = $this->getDatabase()->query('
	  SELECT
		flo.field_image_fid,
		flo.entity_id,
		flo.field_image_alt,
		flo.field_image_title,
		file_managed.filename,
		file_managed.uri
	  FROM
		{field_data_field_image} flo
	  LEFT JOIN {file_managed} file_managed ON file_managed.fid=flo.field_image_fid
	  WHERE
        flo.entity_id = :nid
    ', [':nid' => $nid]);
    foreach ($result as $record) {

      $row->setSourceProperty('field_image_alt', $record->field_image_alt);
      $row->setSourceProperty('field_image_title', $record->field_image_title);

      $filename = $record->filename;
      $filepath = $record->uri;

      // $filepath=str_replace("public://","https://www.rocket.com/sites/default/files/",$filepath);
      if (str_contains($filepath, 'private://')) {
        // $filepath=str_replace("private://","https://www.rocket.com/",$filepath);
      }

      $file_temp = file_get_contents($filepath);

      $file_file_save_data = file_save_data($file_temp, 'public://' . $filename, FileSystemInterface::EXISTS_RENAME);

      $_file_save_data = File::load($file_file_save_data->id());

      if ($_file_save_data) {
        $row->setSourceProperty('field_image_fid', $_file_save_data->id());
      }

    }

    $result = $this->getDatabase()->query('
	  SELECT
		flo.field_caption_value
	  FROM
		{field_data_field_caption} flo
	  WHERE
        flo.entity_id = :nid
    ', [':nid' => $nid]);
    foreach ($result as $record) {
      $row->setSourceProperty('field_caption_value', $record->field_caption_value);
    }

    $result = $this->getDatabase()->query('
	  SELECT
		taxonomy_term_data.name,flo.field_gallery_tid
	  FROM
		{field_data_field_gallery} flo
	  INNER JOIN `taxonomy_term_data` ON taxonomy_term_data.tid=flo.field_gallery_tid	
	  WHERE
        flo.entity_id = :nid
    ', [':nid' => $nid]);
    foreach ($result as $record) {

      $field_gallery_tid = $record->field_gallery_tid;

      $term = \Drupal::entityQuery('taxonomy_term')
        ->condition('name', $record->name)
        ->execute();

      foreach ($term as $_term) {
        $row->setSourceProperty('field_gallery_tid', $_term);
      }

      /*$field_gallery_tid = \Drupal::entityQuery('node')
      ->condition('title', $record->name)
      ->sort('nid', 'DESC')
      ->execute();*/

      // $row->setSourceProperty('field_gallery_tid', $term->id());
    }

    /*print_r($row);die;/**/

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['nid']['type'] = 'integer';
    $ids['nid']['alias'] = 'n';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return 'node';
  }

  /**
   * Returns the user base fields to be migrated.
   *
   * @return array
   *   Associative array having field name as key and description as value.
   */
  protected function baseFields() {
    $fields = [
      'nid' => $this->t('Node ID'),
      'vid' => $this->t('Version ID'),
      'type' => $this->t('Type'),
      'title' => $this->t('Title'),
      'format' => $this->t('Format'),
      'teaser' => $this->t('Teaser'),
      'uid' => $this->t('Authored by (uid)'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Modified timestamp'),
      'status' => $this->t('Published'),
      'promote' => $this->t('Promoted to front page'),
      'sticky' => $this->t('Sticky at top of lists'),
      'uuid' => $this->t('Universally Unique ID'),
      'language' => $this->t('Language (fr, en, ...)'),
    ];
    return $fields;
  }

}
