<?php
class Job_Importer_API
{
  private $client_id;
  private $client_secret;
  private $token_url;
  private $search_url;

  public function __construct()
  {
    $this->client_id = 'c003a37f-024f-462a-b36d-b001be4cd24a'; // Ersetzen Sie dies durch Ihre Client-ID
    $this->client_secret = '32a39620-32b3-4307-9aa1-511e3d7f48a8'; // Ersetzen Sie dies durch Ihr Client-Secret
    $this->token_url = 'https://rest.arbeitsagentur.de/oauth/gettoken_cc';
    $this->search_url = 'https://rest.arbeitsagentur.de/jobboerse/jobsuche-service/pc/v4/jobs';
  }

  public function get_token()
  {
    $response = wp_remote_post($this->token_url, array(
      'body' => array(
        'client_id' => $this->client_id,
        'client_secret' => $this->client_secret,
        'grant_type' => 'client_credentials'
      ),
      'headers' => array(
        'Content-Type' => 'application/x-www-form-urlencoded'
      )
    ));

    if (is_wp_error($response)) {
      return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    return isset($data->access_token) ? $data->access_token : false;
  }

  public function search_jobs($query_args)
  {
    $token = $this->get_token();
    if (!$token) {
      return false;
    }

    $query_url = add_query_arg($query_args, $this->search_url);
    $response = wp_remote_get($query_url, array(
      'headers' => array(
        'Authorization' => 'Bearer ' . $token
      )
    ));

    if (is_wp_error($response)) {
      return false;
    }

    $body = wp_remote_retrieve_body($response);
    return json_decode($body);
  }

  public function import_jobs()
  {
    $query_args = []; // Definieren Sie hier die Abfrageparameter
    $jobs = $this->search_jobs($query_args);

    if ($jobs && is_array($jobs->jobs)) {
      foreach ($jobs->jobs as $job) {
        $post_id = wp_insert_post(array(
          'post_title'    => wp_strip_all_tags($job->title),
          'post_content'  => $job->description,
          'post_status'   => 'publish',
          'post_type'     => 'job_listing',
          // Weitere Felder wie 'post_author', 'post_date', etc.
        ));

        if ($post_id) {
          // Hier könnten Sie zusätzliche Metadaten speichern, z.B.:
          update_post_meta($post_id, '_job_location', $job->location);
          update_post_meta($post_id, '_job_company', $job->company);
          // Weitere Metadaten wie Gehalt, Art der Anstellung, etc.
        } else {
          // Fehlerbehandlung
          error_log('Fehler beim Erstellen des Job-Posts: ' . $job->title);
        }
      }
    } else {
      // Fehlerbehandlung, wenn keine Jobs gefunden oder ein Fehler aufgetreten ist
      error_log('Keine Jobs gefunden oder Fehler bei der API-Anfrage.');
    }
  }
}
