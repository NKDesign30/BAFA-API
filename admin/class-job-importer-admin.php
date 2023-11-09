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
      'job_importer_client_id',
      'Client ID',
      array($this, 'client_id_field_callback'),
      'job-importer',
      'job_importer_settings_section'
    );

    add_settings_field(
      'job_importer_client_secret',
      'Client Secret',
      array($this, 'client_secret_field_callback'),
      'job-importer',
      'job_importer_settings_section'
    );
  }

  public function client_id_field_callback()
  {
    $options = get_option('job_importer_settings');
    $client_id = isset($options['client_id']) ? esc_attr($options['client_id']) : '';
    echo '<input type="text" id="job_importer_client_id" name="job_importer_settings[client_id]" value="' . $client_id . '" />';
  }

  public function client_secret_field_callback()
  {
    $options = get_option('job_importer_settings');
    $client_secret = isset($options['client_secret']) ? esc_attr($options['client_secret']) : '';
    echo '<input type="text" id="job_importer_client_secret" name="job_importer_settings[client_secret]" value="' . $client_secret . '" />';
  }


  // Callbacks für zusätzliche Einstellungsfelder können hier hinzugefügt werden
  // Zum Beispiel für einen Client Secret oder ähnliches

}
