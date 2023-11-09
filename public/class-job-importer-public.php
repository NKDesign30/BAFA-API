<?php
class Job_Importer_Public
{

  private $version;

  public function __construct($version)
  {
    $this->version = $version;
  }

  public function enqueue_styles()
  {
    wp_enqueue_style(
      'job-importer-public', // Handle
      plugin_dir_url(__FILE__) . 'css/job-importer-public.css', // Source
      array(), // Dependencies
      $this->version, // Version
      'all' // Media
    );
  }

  public function enqueue_scripts()
  {
    wp_enqueue_script(
      'job-importer-public', // Handle
      plugin_dir_url(__FILE__) . 'js/job-importer-public.js', // Source
      array('jquery'), // Dependencies
      $this->version, // Version
      true // In footer
    );
  }

  // Hier können Sie zusätzliche Methoden hinzufügen, z.B. Shortcodes für die Jobanzeige
}
