<?php
// Sicherstellen, dass die Datei nicht direkt aufgerufen wird
if (!defined('ABSPATH')) {
  exit;
}

class Bundesjobs_Authentication
{
  private $client_id = 'c003a37f-024f-462a-b36d-b001be4cd24a';
  private $client_secret = '32a39620-32b3-4307-9aa1-511e3d7f48a8';
  private $token_url = 'https://rest.arbeitsagentur.de/oauth/gettoken_cc';

  // Holt ein neues Token mit den Client Credentials
  public function get_token()
  {
    $response = wp_remote_post($this->token_url, [
      'body' => [
        'client_id' => $this->client_id,
        'client_secret' => $this->client_secret,
        'grant_type' => 'client_credentials',
      ],
    ]);

    if (is_wp_error($response)) {
      // Fehlerbehandlung
      $error_message = $response->get_error_message();
      return new WP_Error('token_retrieval_failed', $error_message);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['access_token'])) {
      return $data['access_token'];
    } else {
      // Fehlerbehandlung, falls kein Token in der Antwort ist
      return new WP_Error('token_retrieval_failed', 'Token konnte nicht abgerufen werden.');
    }
  }
}
