<?php

/**
 * Plugin Name: Bundesjobs Importer
 * Plugin URI: https://github.com/NKDesign30/Bundesjobs-Importer
 * Description: Importiert Jobangebote von der Bundesagentur für Arbeit in WordPress als 'job_listing' Posttyp.
 * Version: 1.0.0
 * Author: Niko
 * Author URI: https://github.com/NKDesign30
 * License: GPL2
 * Text Domain: bundesjobs-importer
 */

// Sicherheitsmaßnahme: Direkten Zugriff auf Plugin-Dateien verhindern
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

// Plugin-Aktivierung
function bundesjobs_importer_activate()
{
  // Aktivierungslogik hier (z.B. Erstellen von Optionen, Datenbanktabellen, etc.)
}

// Plugin-Deaktivierung
function bundesjobs_importer_deactivate()
{
  // Deaktivierungslogik hier (z.B. Löschen von Optionen, Datenbanktabellen, etc.)
}

// Registrierung der Aktivierungs- und Deaktivierungshooks
register_activation_hook(__FILE__, 'bundesjobs_importer_activate');
register_deactivation_hook(__FILE__, 'bundesjobs_importer_deactivate');

// Hauptklasse des Plugins
class Bundesjobs_Importer
{
  // Konstruktor
  public function __construct()
  {
    add_action('admin_menu', [$this, 'add_admin_menu']);
    add_action('admin_init', [$this, 'handle_import']);
  }

  // Admin-Menü hinzufügen
  public function add_admin_menu()
  {
    add_menu_page(
      'Bundesjobs Importer', // Seite Titel
      'Bundesjobs Importer', // Menü Titel
      'manage_options', // Capability
      'bundesjobs-importer', // Menü Slug
      [$this, 'admin_page'], // Funktion
      'dashicons-admin-site', // Icon URL
      6 // Position
    );
  }

  // Admin-Seite anzeigen
  public function admin_page()
  {
?>
    <div class="wrap">
      <h1>Bundesjobs Importer</h1>
      <form method="post" action="options.php">
        <?php settings_fields('bundesjobs_importer_options_group'); ?>
        <table class="form-table">
          <tr valign="top">
            <th scope="row">API Key (falls erforderlich)</th>
            <td><input type="text" name="api_key" value="<?php echo get_option('api_key'); ?>" /></td>
          </tr>
        </table>
        <?php submit_button(); ?>
      </form>
      <form method="post">
        <input type="hidden" name="action" value="import_jobs" />
        <input type="submit" class="button button-primary" value="Import Jobs" />
      </form>
    </div>
  <?php
  }

  // Import-Handler
  public function handle_import()
  {
    if (isset($_POST['action']) && $_POST['action'] == 'import_jobs' && current_user_can('manage_options')) {
      $job_importer = new Bundesjobs_Job_Importer();
      $job_importer->import_jobs();
      // Weiterleitung oder Nachricht nach dem Import
      add_action('admin_notices', [$this, 'import_success_notice']);
    }
  }

  // Erfolgsmeldung
  public function import_success_notice()
  {
  ?>
    <div class="notice notice-success is-dismissible">
      <p>Jobs wurden erfolgreich importiert.</p>
    </div>
<?php
  }
}

// Instanz der Hauptklasse erstellen
$bundesjobs_importer = new Bundesjobs_Importer();

class Bundesjobs_Job_Importer
{
  // Die Basis-URL der API
  private $api_base_url = 'https://jobsuche.api.bund.dev/';

  // Importiert Jobs von der Bundesagentur
  public function import_jobs()
  {
    // API-Endpunkt für die Jobs
    $endpoint = 'jobs';

    // API-URL zusammenstellen
    $api_url = $this->api_base_url . $endpoint;

    // API-Anfrage senden und Antwort erhalten
    $response = wp_remote_get($api_url);

    // Überprüfen, ob die API-Anfrage erfolgreich war
    if (is_wp_error($response)) {
      // Fehlerbehandlung
      $error_message = $response->get_error_message();
      $this->log_error("API-Anfrage fehlgeschlagen: $error_message");
      return;
    }

    // Antwortkörper als JSON dekodieren
    $jobs = json_decode(wp_remote_retrieve_body($response), true);

    // Überprüfen, ob Jobs vorhanden sind
    if (empty($jobs)) {
      $this->log_error('Keine Jobs gefunden.');
      return;
    }

    // Jobs durchlaufen und in WordPress importieren
    foreach ($jobs as $job) {
      $this->import_job($job);
    }

    // Erfolgsmeldung
    $this->log_success('Jobs wurden erfolgreich importiert.');
  }

  // Importiert einen einzelnen Job in WordPress
  private function import_job($job)
  {
    // Hier die Logik zum Erstellen eines neuen 'job_listing'-Posts mit den Daten aus $job
    $post_id = wp_insert_post(array(
      'post_title'    => wp_strip_all_tags($job['title']),
      'post_content'  => $job['description'],
      'post_status'   => 'publish',
      'post_type'     => 'job_listing',
      // Weitere Felder können hier hinzugefügt werden, z.B. 'meta_input' für benutzerdefinierte Felder
    ));

    // Überprüfen, ob der Post erfolgreich erstellt wurde
    if ($post_id) {
      // Hier können Sie zusätzliche Aktionen durchführen, z.B. das Setzen von Post-Meta-Daten
    } else {
      // Fehlerbehandlung, wenn das Erstellen des Posts fehlschlägt
    }
  }

  // Loggt Erfolgsmeldungen
  private function log_success($message)
  {
    // Hier die Logik zum Loggen von Erfolgsmeldungen
  }

  // Loggt Fehlermeldungen
  private function log_error($message)
  {
    // Hier die Logik zum Loggen von Fehlermeldungen
  }
}


// Instanz der Hauptklasse erstellen
$bundesjobs_importer = new Bundesjobs_Importer();


// Weitere erforderliche Dateien einbinden
require_once plugin_dir_path(__FILE__) . 'includes/class-authentication.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-api-requests.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-job-importer.php';
