<?php

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all environments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to ensure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * Skipping permissions hardening will make scaffolding
 * work better, but will also raise a warning when you
 * install Drupal.
 *
 * https://www.drupal.org/project/drupal/issues/3091285
 */
// $settings['skip_permissions_hardening'] = TRUE;

/**
 * If there is a local settings file, then include it
 */
$settings['config_sync_directory'] = '../config/sync';
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
} 
$databases['migrate']['default'] = array (
	'database' => 'pantheon',
	'username' => 'pantheon',
	'password' => 'cc2d7578c24640209b07ff5abc6de974',
	'prefix' => '',
	'host' => 'dbserver.tcgd7.505c0b60-2f0b-49e8-970b-ba563cf3692b.drush.in',
	'port' => '16326',
	'namespace' => 'Drupal\Core\Database\Driver\mysql',
	'driver' => 'mysql',
);
$settings['hash_salt'] = 'eYuIsf6oCnkacvsfkodJ-0CvIwqtGHEY4J_a0eIJGCx_Je6wrIhhrIaW479Md6ad8WCVRWiBmA';
