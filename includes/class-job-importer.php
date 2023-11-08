<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class Bundesjobs_Job_Importer
{
  private $api_requests;

  public function __construct()
  {
    $this->api_requests = new Bundesjobs_API_Requests();
  }

  public function import_jobs()
  {
    $jobs = $this->api_requests->get_jobs();

    foreach ($jobs as $job) {
      $post_id = wp_insert_post([
        'post_title'   => sanitize_text_field($job['title']),
        'post_content' => sanitize_textarea_field($job['description']),
        'post_status'  => 'publish',
        'post_type'    => 'job_listing',
        // Weitere Felder nach Bedarf
      ]);

      if ($post_id && !is_wp_error($post_id)) {
        // Beispiel für das Hinzufügen von Metadaten
        update_post_meta($post_id, '_job_location', sanitize_text_field($job['location']));
        update_post_meta($post_id, '_job_company', sanitize_text_field($job['company']));
        // ... Weitere Metadaten hinzufügen

        // Beispiel für das Hinzufügen zu einer Taxonomie
        if (isset($job['category']) && taxonomy_exists('job_listing_category')) {
          wp_set_post_terms($post_id, [$job['category']], 'job_listing_category');
        }
      } else {
        // Fehlerbehandlung, wenn das Erstellen des Posts fehlschlägt
        // Loggen Sie den Fehler oder informieren Sie den Benutzer
      }
    }
  }
}
