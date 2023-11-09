<?php

/**
 * Plugin Name: Job Importer
 * Description: Importiert Jobanzeigen als 'job_listing' Beiträge in WordPress.
 * Version: 1.0.5
 * Author: Niko
 * Text Domain: job-importer
 */

// Sicherstellen, dass das Script nicht direkt aufgerufen werden kann
if (!defined('WPINC')) {
  die;
}

// Plugin-Version definieren
define('JOB_IMPORTER_VERSION', '1.0');

// Die Klassen inkludieren
require plugin_dir_path(__FILE__) . 'includes/class-job-importer.php';
require plugin_dir_path(__FILE__) . 'includes/class-job-importer-api.php';
require plugin_dir_path(__FILE__) . 'includes/class-job-importer-cpt.php';
require plugin_dir_path(__FILE__) . 'admin/class-job-importer-admin.php';
require plugin_dir_path(__FILE__) . 'public/class-job-importer-public.php';

// Die Instanz der Hauptklasse initialisieren
function run_job_importer()
{
  $plugin = new Job_Importer();
  $plugin_admin = new Job_Importer_Admin(JOB_IMPORTER_VERSION);

  // Hinzufügen der Aktions-Hooks für die Admin-Seite
  add_action('admin_menu', array($plugin_admin, 'add_menu'));
  add_action('admin_init', array($plugin_admin, 'register_plugin_settings'));

  $plugin->run();
}

run_job_importer();

register_activation_hook(__FILE__, 'job_importer_activate_cron');
register_deactivation_hook(__FILE__, 'job_importer_deactivate_cron');

function job_importer_activate_cron()
{
  if (!wp_next_scheduled('job_importer_cron_hook')) {
    wp_schedule_event(time(), 'twicedaily', 'job_importer_cron_hook');
  }
}

function job_importer_deactivate_cron()
{
  wp_clear_scheduled_hook('job_importer_cron_hook');
}

add_action('job_importer_cron_hook', 'job_importer_run_cron');

function job_importer_run_cron()
{
  // Instanz der Job_Importer Klasse
  $job_importer = new Job_Importer();
  $job_importer->import_jobs();
}
