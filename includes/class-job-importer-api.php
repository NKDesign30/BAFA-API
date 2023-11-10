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
    if (!function_exists('wp_remote_post')) {
      return false;
    }

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

    if (!isset($data->access_token)) {
      return false;
    }

    return $data->access_token;
  }

  public function search_jobs($query_args)
  {
    if (!function_exists('wp_remote_get')) {
      return false;
    }

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
    $jobs = json_decode($body, true);

    if (!isset($jobs['stellenangebote']) || !is_array($jobs['stellenangebote'])) {
      return false;
    }

    return $jobs;
  }

  public function import_jobs()
  {
    $query_args = []; // Definieren Sie hier die Abfrageparameter
    $jobs = $this->search_jobs($query_args);

    if ($jobs && is_array($jobs['stellenangebote'])) {
      foreach ($jobs['stellenangebote'] as $job) {
        // Titel, Ort und Arbeitgeber extrahieren
        $titel = $job['titel'] ?? 'Kein Titel verfügbar';
        $ort = $job['arbeitsort']['ort'] ?? 'Kein Ort verfügbar';
        $arbeitgeber = $job['arbeitgeber'] ?? 'Kein Arbeitgeber verfügbar';

        // Beschreibung zusammensetzen
        $beschreibung = "Veröffentlichungsdatum: " . ($job['aktuelleVeroeffentlichungsdatum'] ?? 'N/A') .
          "\nModifikationszeitstempel: " . ($job['modifikationsTimestamp'] ?? 'N/A') .
          "\nEintrittsdatum: " . ($job['eintrittsdatum'] ?? 'N/A') .
          "\nLogo Hash ID: " . ($job['logoHashId'] ?? 'N/A') .
          "\nHash ID: " . ($job['hashId'] ?? 'N/A');

        // WordPress-Beitrag erstellen
        $post_id = wp_insert_post(array(
          'post_title'    => wp_strip_all_tags($titel),
          'post_content'  => $beschreibung,
          'post_status'   => 'publish',
          'post_type'     => 'job_listing',
          // Weitere Felder wie 'post_author', 'post_date', etc.
        ));

        if (is_wp_error($post_id)) {
          error_log('Fehler beim Erstellen des Job-Posts: ' . $titel);
          continue;
        }

        // Metadaten speichern
        update_post_meta($post_id, '_job_location', $ort);
        update_post_meta($post_id, '_job_company', $arbeitgeber);
        // Weitere Metadaten wie Gehalt, Art der Anstellung, etc.
      }
    } else {
      // Fehlerbehandlung, wenn keine Jobs gefunden oder ein Fehler aufgetreten ist
      error_log('Keine Jobs gefunden oder Fehler bei der API-Anfrage.');
    }
  }
}
