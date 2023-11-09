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
?>
    <div class="wrap">
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
      <form action="options.php" method="post">
        <?php
        settings_fields('job_importer_options');
        do_settings_sections('job-importer');
        submit_button('Import starten', 'primary', 'start_import');
        ?>
      </form>
    </div>
<?php

    if (isset($_POST['start_import'])) {
      $this->handle_import();
    }
  }

  public function register_plugin_settings()
  {
    register_setting('job_importer_options', 'job_importer_settings');
    add_settings_section(
      'job_importer_settings_section', // ID
      'Job Importer Einstellungen', // Title
      array($this, 'settings_section_callback'), // Callback
      'job-importer' // Page
    );

    // Hier können Sie zusätzliche Einstellungsfelder hinzufügen
    // ...
  }

  public function settings_section_callback()
  {
    echo '<p>Hier können Sie die Einstellungen für den Job Importer konfigurieren.</p>';
  }

  // Hier können Sie zusätzliche Methoden hinzufügen, z.B. für das Registrieren von Einstellungsfeldern
  // ...
}
