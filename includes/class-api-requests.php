<?php
if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class Bundesjobs_API_Requests
{
  private $api_url = 'https://jobsuche.api.bund.dev/';

  public function get_jobs($query_args = [])
  {
    $response = wp_remote_get($this->api_url . 'jobs', [
      'timeout' => 15,
      'headers' => [
        'Accept' => 'application/json',
      ],
      'body' => $query_args,
    ]);

    if (is_wp_error($response)) {
      return [];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return $data['jobs'] ?? [];
  }
}
