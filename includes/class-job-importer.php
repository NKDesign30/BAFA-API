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

      // Hier können Sie zusätzliche Metadaten und Taxonomien hinzufügen
    }
  }
}
