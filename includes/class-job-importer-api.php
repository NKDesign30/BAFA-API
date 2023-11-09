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
}
