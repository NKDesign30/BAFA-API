<?php

/**
 * Plugin Name: bundesAPI
 * Description: Importiert Jobs von der API und speichert sie als Beiträge.
 * Version: 1.1.3
 * Author: Niko
 */

// Sicherstellen, dass das Skript nicht direkt aufgerufen wird
defined('ABSPATH') or die('No script kiddies please!');

// Fügt einen Menüpunkt im Admin-Bereich hinzu
function job_importer_admin_menu()
{
  add_menu_page('Job Importer', 'Job Importer', 'manage_options', 'job-importer', 'job_importer_admin_page');
}
add_action('admin_menu', 'job_importer_admin_menu');

// Die Admin-Seite
function job_importer_admin_page()
{
?>
  <div class="wrap">
    <h1>Job Importer</h1>
    <form method="post" action="">
      <input type="hidden" name="action" value="import_jobs">
      <?php submit_button('Import starten'); ?>
    </form>
  </div>
<?php
  // Prüft, ob der Import-Button gedrückt wurde
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'import_jobs') {
    job_importer_import_jobs();
  }
}

// Holt das Token und importiert die Jobs
function job_importer_import_jobs()
{
  error_log('Import Jobs gestartet');
  $token_data = job_importer_get_token();
  if (!$token_data) {
    error_log('Token konnte nicht abgerufen werden');
    return;
  }

  error_log('Token erhalten: ' . print_r($token_data, true));

  $jobs_data = job_importer_get_jobs($token_data['access_token']);
  if (!$jobs_data || !isset($jobs_data['stellenangebote'])) {
    error_log('Jobs konnten nicht abgerufen werden');
    return;
  }

  $stellenangebote = $jobs_data['stellenangebote'];
  error_log('Jobs erhalten: ' . print_r($stellenangebote, true));

  foreach ($stellenangebote as $job) {
    job_importer_create_post($job);
  }
  error_log('Import Jobs beendet');
}



// Holt das Authentifizierungstoken
function job_importer_get_token()
{
  $url = 'https://rest.arbeitsagentur.de/oauth/gettoken_cc';
  $data = array(
    'client_id' => 'c003a37f-024f-462a-b36d-b001be4cd24a',
    'client_secret' => '32a39620-32b3-4307-9aa1-511e3d7f48a8',
    'grant_type' => 'client_credentials'
  );
  $options = array(
    'http' => array(
      'method'  => 'POST',
      'content' => http_build_query($data),
      'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
    )
  );
  $context  = stream_context_create($options);
  $token_data = file_get_contents($url, false, $context);
  return json_decode($token_data, true);
}

// Holt die Jobs von der API
function job_importer_get_jobs($access_token)
{
  $url = 'https://rest.arbeitsagentur.de/jobboerse/jobsuche-service/pc/v4/jobs';
  $options = array(
    'http' => array(
      'header'  => "OAuthAccessToken: $access_token\r\n",
      'method'  => 'GET'
    )
  );
  $context  = stream_context_create($options);
  $search_data = file_get_contents($url, false, $context);
  $jobs = json_decode($search_data, true);

  // Erstellen des Verzeichnisses und Speichern der API-Antwort
  $upload_dir = wp_upload_dir();
  $job_api_dir = $upload_dir['basedir'] . '/Job-API';
  if (!file_exists($job_api_dir)) {
    wp_mkdir_p($job_api_dir);
  }

  $file_path = $job_api_dir . '/api_response_' . date('Y-m-d_H-i-s') . '.json';
  file_put_contents($file_path, json_encode($jobs, JSON_PRETTY_PRINT));

  // Erweitern der Jobs mit Details und Logo
  foreach ($jobs['stellenangebote'] as &$job) {
    $encodedHashID = base64_encode($job['hashId']);
    $job_details = job_importer_get_job_details($access_token, $encodedHashID);
    $job['details'] = $job_details;

    $logo = job_importer_get_company_logo($access_token, $job['arbeitgeberHashId']);
    $job['logo'] = $logo;
  }

  return $jobs;
}

function job_importer_get_job_details($access_token, $encodedHashID)
{
  $url = "https://rest.arbeitsagentur.de/jobboerse/jobsuche-service/pc/v2/jobdetails/$encodedHashID";
  $options = array(
    'http' => array(
      'header'  => "OAuthAccessToken: $access_token\r\n",
      'method'  => 'GET'
    )
  );
  $context  = stream_context_create($options);
  $details_data = file_get_contents($url, false, $context);
  return json_decode($details_data, true);
}

function job_importer_get_company_logo($access_token, $hashID)
{
  $url = "https://rest.arbeitsagentur.de/jobboerse/jobsuche-service/pc/v2/jobdetails/$hashID";
  $options = array(
    'http' => array(
      'header'  => "OAuthAccessToken: $access_token\r\n",
      'method'  => 'GET'
    )
  );
  $context  = stream_context_create($options);
  $logo_data = file_get_contents($url, false, $context);

  if ($logo_data === FALSE) {
    error_log('Fehler beim Abrufen des Unternehmenslogos');
    return null;
  }

  // Speichern des Logos im Upload-Verzeichnis
  $upload_dir = wp_upload_dir();
  $logo_dir = $upload_dir['basedir'] . '/Company-Logos';
  if (!file_exists($logo_dir)) {
    wp_mkdir_p($logo_dir);
  }

  $file_path = $logo_dir . '/' . $hashID; // Keine Dateiendung festgelegt
  file_put_contents($file_path, $logo_data);

  return $file_path;
}







// Post Erstellen 
function job_importer_create_post($job)
{
  // Debugging: Überprüfen Sie den Wert von 'titel'
  error_log('Job Titel: ' . print_r(isset($job['titel']) ? $job['titel'] : 'Nicht vorhanden', true));

  $post_title = isset($job['titel']) ? wp_strip_all_tags($job['titel']) : 'Titel nicht verfügbar';

  // Erstellen Sie eine Beschreibung aus verfügbaren Daten
  $post_content = '';
  $post_content .= isset($job['arbeitgeber']) ? "Arbeitgeber: " . $job['arbeitgeber'] . "\n" : '';
  $post_content .= isset($job['arbeitsort']['ort']) ? "Ort: " . $job['arbeitsort']['ort'] . "\n" : '';
  $post_content .= isset($job['details']['stellenbeschreibung']) ? "Stellenbeschreibung: " . $job['details']['stellenbeschreibung'] . "\n" : '';
  $post_content .= isset($job['branchengruppe']) ? "Branchengruppe: " . $job['branchengruppe'] . "\n" : '';
  $post_content .= isset($job['branche']) ? "Branche: " . $job['branche'] . "\n" : '';
  $post_content .= isset($job['arbeitszeitmodelle']) ? "Arbeitszeitmodelle: " . implode(', ', $job['arbeitszeitmodelle']) . "\n" : '';
  $post_content .= isset($job['befristung']) ? "Befristung: " . $job['befristung'] . "\n" : '';
  $post_content .= isset($job['berufserfahrung']) ? "Berufserfahrung: " . $job['berufserfahrung'] . "\n" : '';
  $post_content .= isset($job['fertigkeiten']) ? "Fertigkeiten: " . implode(', ', $job['fertigkeiten']) . "\n" : '';
  $post_content .= isset($job['qualifikationen']) ? "Qualifikationen: " . implode(', ', $job['qualifikationen']) . "\n" : '';
  // Fügen Sie hier weitere Felder hinzu, die Sie in der Beschreibung anzeigen möchten

  // Fügen Sie das Unternehmenslogo hinzu, falls vorhanden
  if (isset($job['logo']) && !empty($job['logo'])) {
    $post_content .= "\nUnternehmenslogo: " . $job['logo'] . "\n";
  }

  $post_data = array(
    'post_title'    => $post_title,
    'post_content'  => $post_content,
    'post_status'   => 'publish',
    'post_author'   => get_current_user_id(),
    'post_type'     => 'job_listing',
  );

  $post_id = wp_insert_post($post_data);

  // Überprüfen, ob ein Fehler aufgetreten ist
  if (is_wp_error($post_id)) {
    error_log('Fehler beim Erstellen des Job Listings: ' . $post_id->get_error_message());
    return;
  }

  // Wenn kein Fehler auftritt, fahren Sie mit der Verarbeitung fort
  if ($post_id) {
    // Metadaten hinzufügen
    update_post_meta($post_id, '_job_location', isset($job['arbeitsort']['ort']) ? $job['arbeitsort']['ort'] : '');
    update_post_meta($post_id, '_company_name', isset($job['arbeitgeber']) ? $job['arbeitgeber'] : '');
    update_post_meta($post_id, '_application', isset($job['bewerbungslink']) ? $job['bewerbungslink'] : '');
    update_post_meta($post_id, '_job_ref_number', isset($job['refnr']) ? $job['refnr'] : '');

    error_log('Job Listing erstellt mit ID: ' . $post_id);
  } else {
    error_log('Fehler beim Erstellen des Job Listings');
  }
}




// Aktivierungshook
function job_importer_activate()
{
  // Hier könnten Sie Setup-Aufgaben durchführen, z.B. Datenbanktabellen erstellen
}
register_activation_hook(__FILE__, 'job_importer_activate');

// Deaktivierungshook
function job_importer_deactivate()
{
  // Hier könnten Sie Aufräumarbeiten durchführen, z.B. Datenbanktabellen löschen
}
register_deactivation_hook(__FILE__, 'job_importer_deactivate');
