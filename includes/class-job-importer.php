<?php
class Job_Importer
{

  private $version;

  public function __construct()
  {
    $this->version = JOB_IMPORTER_VERSION;
  }

  public function run()
  {
    // Initialisierung des Custom Post Types
    $cpt = new Job_Importer_CPT();
    add_action('init', array($cpt, 'register_cpt'));

    // Initialisierung der Admin-Funktionalitäten
    $admin = new Job_Importer_Admin($this->version);
    add_action('admin_menu', array($admin, 'add_menu'));

    // Initialisierung der öffentlichen Funktionalitäten
    $public = new Job_Importer_Public($this->version);
    add_action('wp_enqueue_scripts', array($public, 'enqueue_styles'));
    add_action('wp_enqueue_scripts', array($public, 'enqueue_scripts'));

    // Planen der regelmäßigen Job-Importe
    if (!wp_next_scheduled('job_importer_cron_hook')) {
      wp_schedule_event(time(), 'twicedaily', 'job_importer_cron_hook');
    }
    add_action('job_importer_cron_hook', array($this, 'import_jobs'));
  }

  public function import_jobs()
  {
    $api = new Job_Importer_API();
    $query_args = array(
      // Hier fügen Sie die erforderlichen Suchparameter ein
    );
    $jobs = $api->search_jobs($query_args);

    if ($jobs && !is_wp_error($jobs)) {
      foreach ($jobs->jobs as $job_data) {
        // Hier konvertieren Sie $job_data in einen WordPress-Beitrag
        // und verwenden wp_insert_post, um ihn zu speichern
        $post_id = wp_insert_post(array(
          'post_title'    => wp_strip_all_tags($job_data->title),
          'post_content'  => $job_data->description,
          'post_status'   => 'publish',
          'post_type'     => 'job_listing',
          // Weitere Felder wie 'post_author', 'post_date' etc. können hier eingefügt werden
        ));

        // Überprüfen Sie, ob der Beitrag erfolgreich erstellt wurde
        if ($post_id && !is_wp_error($post_id)) {
          // Hier können Sie zusätzliche Metadaten speichern, z.B.:
          // update_post_meta( $post_id, '_job_location', $job_data->location );
        }
      }
    }
  }
}
