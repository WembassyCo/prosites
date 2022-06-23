<?php
namespace Drupal\custom_update_file\Controller;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('max_execution_time', '0');
error_reporting(E_ALL);

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use \Drupal\file\Entity\File;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\taxonomy\Entity\Term;      
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class spalambdaController.
 ** @package Drupal\spa_lambda\Controller
 */class spalambdaController extends ControllerBase {

	/**
	 * spa_lambda_node_update.
	 * *
	 * Update spa_lambda_node_update Invoice Number.
	 */
	public function spa_lambda_node_update() {
		
		//echo "<pre>";
		//print_r(Node::load(146));
		//exit();
		
		$url = "http://localhost/tcgd7/get_content.php?type=article";

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$resp = curl_exec($curl);
		curl_close($curl);
		$nodes = json_decode($resp)->data;
		
		$nids = \Drupal::entityQuery('node')
				->condition('type', 'article')
				->execute();
		$exnodes = \Drupal\node\Entity\Node::loadMultiple($nids);
		$extitles = array();
		
		/*$node = Node::load(146);		
		//$node->set('field_date', strtotime("2012-03-27 00:00:00"));
		$timestamp = \Drupal::time()->getRequestTime();
		$date = DrupalDateTime::createFromTimestamp($timestamp, 'UTC');
		//$node->field_date = $date->format("Y-m-d\TH:i:s");
		$node->set('field_date', $date->format("Y-m-d"));
		$node->save();
		echo "<pre>";
		print_r($node);
		exit();*/
		foreach($exnodes as $exnode){
			$extitles[] = $exnode->getTitle();
		}
		
		$rrr = 0;
		foreach($nodes as $d7node){

			$search = array_search($d7node->title,$extitles);
			//var_dump($search);
			if(!is_numeric($search)){
			//echo urldecode($d7node->body);
			if(count(explode(',',$d7node->field_tag)) > 1){
				$field_tags = $d7node->field_tag;
				
				$field_tags = explode(",",$field_tags);
				$field_tags_tids = array();
				foreach($field_tags as $field_tag){
					
					$vocabulary_name = 'tags'; //name of your vocabulary
					$query = \Drupal::entityQuery('taxonomy_term');
					$query->condition('vid', $vocabulary_name);
					$query->condition('name', $field_tag);
					$query->sort('weight');
					$tids = $query->execute();
					$terms = Term::loadMultiple($tids);
					$field_tags_tids[] = key($terms);
					
				}

				
			}else{
				$field_tags = $d7node->field_tag;
					
				$vocabulary_name = 'tags'; //name of your vocabulary
				$query = \Drupal::entityQuery('taxonomy_term');
				$query->condition('vid', $vocabulary_name);
				$query->condition('name', $field_tags);
				$query->sort('weight');
				$tids = $query->execute();
				$terms = Term::loadMultiple($tids);
				$field_tags_tids = key($terms);
			
			}
			
			//print_r($field_tags_tids);
			//exit();
			
			//$node = Node::load(147);		
			//$node->set('field_tags', ); // This is a Field added in to the content type
			//$node->set('field_press', urldecode($d7node->field_press)); // This is a Field added in to the content type
			//$node->field_press->format = 'full_html';
			
			//$node->save();

			// Create file object from remote URL.
			// $field_image = urldecode($d7node->field_image);
			// if($d7node->field_image){				
				// $data = file_get_contents($field_image);
				// if($data){
					
					// print_r($data);
					// print_r($field_image);
					// exit();
				// }
				////$file = file_save_data($data, 'public://druplicon.png', FILE_EXISTS_RENAME);
			// }
				if(urldecode($d7node->body) != null){

				// Create node object with attached file.
				$node = Node::create([
					'type'        => 'article',
					'title'       => $d7node->title,
					'body' => array(
						'value' => urldecode($d7node->body),
						'format' => 'full_html',
					),
					
					//'field_date' => array(
					//	'date' => $d7node->field_date,
					//),
					'field_tags' => array(
						'target_id' => $field_tags_tids,
					),
					  //'field_image' => [
						////'target_id' => $file->id(),
						//'alt' => 'Hello world',
						//'title' => 'Goodbye world'
					  //],
				]);
				$node->save();
				
				$path_alias = PathAlias::create([
				  'path' => '/node/' . $node->id(),
				  'alias' => '/'.$d7node->uri,
				]);
				$path_alias->save();
				
				$node = Node::load($node->id());
				//$timestamp = \Drupal::time()->getRequestTime();
				$timestamp = strtotime($d7node->field_date);
				$date = DrupalDateTime::createFromTimestamp($timestamp, 'UTC');
				//$node->field_date = $date->format("Y-m-d\TH:i:s");
				$node->set('field_date', $date->format("Y-m-d"));
				$node->save();
				echo "<pre>";
				print_r($node);
				exit();

				
				}
			}
			$rrr++;
			
			// $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
			// $uuid_service = \Drupal::service('uuid');
			// $uuid = $uuid_service->generate();
			// db_insert('node__field_listing_map')->fields(array(
			// 	'bundle' => $record['bundle'],
			// 	'deleted' => $record['deleted'],
			// 	'entity_id' => $record['entity_id'],
			// 	'revision_id' => $record['revision_id'],
			// 	'langcode' => $record['language'],
			// 	'delta' => $record['delta'],
			// 	'field_listing_map_value' => NULL,
			// 	'field_listing_map_geo_type' => $record['field_listing_map_geo_type'],
			// 	'field_listing_map_lat' => $record['field_listing_map_lat'],
			// 	'field_listing_map_lon' => $record['field_listing_map_lon'],
			// 	'field_listing_map_left' => $record['field_listing_map_left'],
			// 	'field_listing_map_top' => $record['field_listing_map_top'],
			// 	'field_listing_map_right' => $record['field_listing_map_right'],
			// 	'field_listing_map_bottom' => $record['field_listing_map_bottom'],
			// 	'field_listing_map_geohash' => $record['field_listing_map_geohash'],
				
			// ))->execute();
			
			
			
		}

exit();
		// $csv_uri = __DIR__ . "/../../files/drupal7_field_listing_map.csv";
		// // echo "==>".$csv_uri;die();
		// $handle = fopen($csv_uri, 'r');
		// $row = fgetcsv($handle);
		// $columns = array();
		// foreach ($row as $i => $header) {
			// $columns[$i] = trim($header);
		// }

		//while ($row = fgetcsv($handle)) {
			//$record = array();
			//foreach ($row as $i => $field) {
				// This is pretty brittle... if someone screws up the field
				// names the data won't be written.
			//	$record[$columns[$i]] = $field;
			//}
			//var_dump(unserialize($record["field_listing_map_geom"]));die();

			// $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
			// $uuid_service = \Drupal::service('uuid');
			// $uuid = $uuid_service->generate();
			// db_insert('node__field_listing_map')->fields(array(
			// 	'bundle' => $record['bundle'],
			// 	'deleted' => $record['deleted'],
			// 	'entity_id' => $record['entity_id'],
			// 	'revision_id' => $record['revision_id'],
			// 	'langcode' => $record['language'],
			// 	'delta' => $record['delta'],
			// 	'field_listing_map_value' => NULL,
			// 	'field_listing_map_geo_type' => $record['field_listing_map_geo_type'],
			// 	'field_listing_map_lat' => $record['field_listing_map_lat'],
			// 	'field_listing_map_lon' => $record['field_listing_map_lon'],
			// 	'field_listing_map_left' => $record['field_listing_map_left'],
			// 	'field_listing_map_top' => $record['field_listing_map_top'],
			// 	'field_listing_map_right' => $record['field_listing_map_right'],
			// 	'field_listing_map_bottom' => $record['field_listing_map_bottom'],
			// 	'field_listing_map_geohash' => $record['field_listing_map_geohash'],
				
			// ))->execute();


			// $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
			// $uuid_service = \Drupal::service('uuid');
			// $uuid = $uuid_service->generate();
			// db_insert('node__field_address')->fields(array(
			// 	'bundle' => $record['bundle'],
			// 	'deleted' => $record['deleted'],
			// 	'entity_id' => $record['entity_id'],
			// 	'revision_id' => $record['revision_id'],
			// 	'langcode' => $record['language'],
			// 	'delta' => $record['delta'],
			// 	'field_address_langcode' => NULL,
			// 	'field_address_country_code' => $record['field_address_country'],
			// 	'field_address_administrative_area' => $record['field_address_administrative_area'],
			// 	'field_address_locality' => $record['field_address_locality'],
			// 	'field_address_dependent_locality' => $record['field_address_dependent_locality'],
			// 	'field_address_postal_code' => $record['field_address_postal_code'],
			// 	'field_address_sorting_code' => NULL,
			// 	'field_address_address_line1' => $record['field_address_thoroughfare'],
			// 	'field_address_address_line2' => NULL,
			// 	'field_address_organization' => $record['field_address_organization_name'],
			// 	'field_address_given_name' => $record['field_address_first_name'],
			// 	'field_address_additional_name' => $record['field_address_data'],
			// 	'field_address_family_name' => $record['field_address_last_name'],
			// ))->execute();

			// $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
			// $uuid_service = \Drupal::service('uuid');
			// $uuid = $uuid_service->generate();
			// db_insert('node_revision__field_address')->fields(array(
			// 	'bundle' => $record['bundle'],
			// 	'deleted' => $record['deleted'],
			// 	'entity_id' => $record['entity_id'],
			// 	'revision_id' => $record['revision_id'],
			// 	'langcode' => $record['language'],
			// 	'delta' => $record['delta'],
			// 	'field_address_langcode' => NULL,
			// 	'field_address_country_code' => $record['field_address_country'],
			// 	'field_address_administrative_area' => $record['field_address_administrative_area'],
			// 	'field_address_locality' => $record['field_address_locality'],
			// 	'field_address_dependent_locality' => $record['field_address_dependent_locality'],
			// 	'field_address_postal_code' => $record['field_address_postal_code'],
			// 	'field_address_sorting_code' => NULL,
			// 	'field_address_address_line1' => $record['field_address_thoroughfare'],
			// 	'field_address_address_line2' => NULL,
			// 	'field_address_organization' => $record['field_address_organization_name'],
			// 	'field_address_given_name' => $record['field_address_first_name'],
			// 	'field_address_additional_name' => $record['field_address_data'],
			// 	'field_address_family_name' => $record['field_address_last_name'],
			// ))->execute();


			//var_dump($record['bundle']); die();
			//$language =  \Drupal::languageManager()->getCurrentLanguage()->getId();
			//$uuid_service = \Drupal::service('uuid');
			//$uuid = $uuid_service->generate();
			/*db_insert('node__field_contributor')->fields(array(
				'bundle' => $record['bundle'],
				'deleted' => $record['deleted'],
				'entity_id' => $record['entity_id'],
				'revision_id' => $record['revision_id'],
				'langcode' => $record['language'],
				'delta' => $record['delta'],
				'field_contributor_target_id' => $record['field_contributor_nid'],
			))->execute();*/
			/*db_insert('node_revision__field_contributor')->fields(array(
				'bundle' => $record['bundle'],
				'deleted' => $record['deleted'],
				'entity_id' => $record['entity_id'],
				'revision_id' => $record['revision_id'],
				'langcode' => $record['language'],
				'delta' => $record['delta'],
				'field_contributor_target_id' => $record['field_contributor_nid']))->execute();*/

			/*db_insert('node__field_brand')->fields(array(
				'bundle' => $record['bundle'],
				'deleted' => $record['deleted'],
				'entity_id' => $record['entity_id'],
				'revision_id' => $record['revision_id'],
				'langcode' => $record['language'],
				'delta' => $record['delta'],
				'field_brand_target_id' => $record['field_brand_nid']))->execute();*/

			// db_insert('node_revision__field_brand')->fields(array(
			// 	'bundle' => $record['bundle'],
			// 	'deleted' => $record['deleted'],
			// 	'entity_id' => $record['entity_id'],
			// 	'revision_id' => $record['revision_id'],
			// 	'langcode' => $record['language'],
			// 	'delta' => $record['delta'],
			// 	'field_brand_target_id' => $record['field_brand_nid']))->execute();

			/*db_insert('node__field_related')->fields(array(
				'bundle' => $record['bundle'],
				'deleted' => $record['deleted'],
				'entity_id' => $record['entity_id'],
				'revision_id' => $record['revision_id'],
				'langcode' => $record['language'],
				'delta' => $record['delta'],
				'field_related_target_id' => $record['field_related_nid']))->execute();*/

			/*db_insert('node_revision__field_related')->fields(array(
				'bundle' => $record['bundle'],
				'deleted' => $record['deleted'],
				'entity_id' => $record['entity_id'],
				'revision_id' => $record['revision_id'],
				'langcode' => $record['language'],
				'delta' => $record['delta'],
				'field_related_target_id' => $record['field_related_nid']))->execute();*/

			/*db_insert('node__field_featuredcontributor')->fields(array(
				'bundle' => $record['bundle'],
				'deleted' => $record['deleted'],
				'entity_id' => $record['entity_id'],
				'revision_id' => $record['revision_id'],
				'langcode' => $record['language'],
				'delta' => $record['delta'],
				'field_featuredcontributor_target_id' => $record['field_featuredcontributor_nid']))->execute();*/

			/*db_insert('node_revision__field_featuredcontributor')->fields(array(
				'bundle' => $record['bundle'],
				'deleted' => $record['deleted'],
				'entity_id' => $record['entity_id'],
				'revision_id' => $record['revision_id'],
				'langcode' => $record['language'],
				'delta' => $record['delta'],
				'field_featuredcontributor_target_id' => $record['field_featuredcontributor_nid']))->execute();*/

			/*db_insert('node__field_twitter')->fields(array(
				'bundle' => $record['bundle'],
				'deleted' => $record['deleted'],
				'entity_id' => $record['entity_id'],
				'revision_id' => $record['revision_id'],
				'langcode' => $record['language'],
				'delta' => $record['delta'],
				'field_twitter_value' => $record['field_twitter_twitter_username']))->execute();*/

			/*db_insert('node_revision__field_twitter')->fields(array(
				'bundle' => $record['bundle'],
				'deleted' => $record['deleted'],
				'entity_id' => $record['entity_id'],
				'revision_id' => $record['revision_id'],
				'langcode' => $record['language'],
				'delta' => $record['delta'],
				'field_twitter_value' => $record['field_twitter_twitter_username']))->execute();*/

			//die();




			// $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
			// $uuid_service = \Drupal::service('uuid');
			// $uuid = $uuid_service->generate();
			// db_insert('file_managed')->fields(array(
			// 	'fid' => $record['fid'],
			// 	'uuid' => $uuid,
			// 	'langcode' => $language,
			// 	'uid' => $record['uid'],
			// 	'filename' => $record['filename'],
			// 	'uri' => $record['uri'],
			// 	'filemime' => $record['filemime'],
			// 	'filesize' => $record['filesize'],
			// 	'status' => $record['status'],
			// 	'changed' => $record['timestamp'],
			// 	'created' => $record['timestamp']
			// ))->execute();
		//}
		die("successfully Added...");
	}
}
