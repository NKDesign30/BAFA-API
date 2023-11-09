<?php
class Job_Importer_Admin
{

  private $version;

  public function __construct($version)
  {
    $this->version = $version;
    add_action('admin_menu', array($this, 'add_menu'));
    add_action('admin_init', array($this, 'register_plugin_settings'));
  }

  public function add_menu()
  {
    add_menu_page(
      'Job Importer Einstellungen', // Page title
      'Job Importer', // Menu title
      'manage_options', // Capability
      'job-importer', // Menu slug
      array($this, 'display_settings_page'), // Function
      'dashicons-admin-tools', // Icon URL
      6 // Position
    );
  }

  public function display_settings_page()
  {
    echo '<div class="wrap">';
    echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
    echo '<form action="options.php" method="post">';
    settings_fields('job_importer_options');
    do_settings_sections('job-importer');
    submit_button('Änderungen speichern');
    echo '</form>';
    echo '</div>';
  }

  public function register_plugin_settings()
  {
    register_setting('job_importer_options', 'job_importer_settings');

    add_settings_section(
      'job_importer_settings_section',
      'Job Importer Einstellungen',
      array($this, 'settings_section_callback'),
      'job-importer'
    );

    add_settings_field(
      'job_importer_api_key',
      'API Schlüssel',
      array($this, 'api_key_field_callback'),
      'job-importer',
      'job_importer_settings_section'
    );

    // Weitere Einstellungsfelder können hier hinzugefügt werden
  }

  public function settings_section_callback()
  {
    echo '<p>Hier können Sie die Einstellungen für den Job Importer konfigurieren.</p>';
  }

  public function api_key_field_callback()
  {
    $options = get_option('job_importer_settings');
    $api_key = isset($options['api_key']) ? esc_attr($options['api_key']) : '';
    echo '<input type="text" id="job_importer_api_key" name="job_importer_settings[api_key]" value="' . $api_key . '" />';
  }

  // Callbacks für zusätzliche Einstellungsfelder können hier hinzugefügt werden
  // Zum Beispiel für einen Client Secret oder ähnliches

}
