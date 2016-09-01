<?php

/**
 * @file
 * Contains \Drupal\govreday\Controller\GovReadyController.
 */

namespace Drupal\govready\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class GovReadyController extends ControllerBase {

  /**
   * GovReady configuration settings.
   */
  public function govready_config() {

    return array(
      'api_debug' => FALSE,
      'auth0' => array(
        'domain' => 'govready.auth0.com',
        'client_id' => 'HbYZO5QXKfgNshjKlhZGizskiaJH9kGH',
      ),
      'commercial' => FALSE,
      'govready_url' => 'https://plugin.govready.com/v1.0',
    );

  }

  /**
   * Page callback for the GovReady Dashboard.
   */
  public function govready_dashboard() {

    module_load_include('class.php', 'govready', 'lib/govready-dashboard');
    $dashboard = new GovreadyDashboard();
    return $dashboard->dashboardPage();

  }

  /**
   * Call the GovReady Agent trigger.
   */
  public function govready_trigger_callback() {

    module_load_include('class.php', 'govready', 'lib/govready-agent');
    $agent = new GovreadyAgent();
    return $agent->ping();

  }

  /**
   * Make a request to the GovReady API.
   */
  public function govready_api($endpoint, $method = 'GET', $data = array(), $anonymous = FALSE) {

    $config = govready_config();
    $url = $config['govready_url'] . $endpoint;

    // Make sure our token is a-ok.
    $token = variable_get('govready_token', array());

    if (!$anonymous && (empty($token['id_token']) || empty($token['endoflife']) || $token['endoflife'] < time())) {
      $token = govready_refresh_token(TRUE);
    }
    $token = !$anonymous && !empty($token['id_token']) ? $token['id_token'] : FALSE;

    // Make the API request with cURL.
    // @todo should we support HTTP_request (https://pear.php.net/manual/en/package.http.http-request.intro.php)?
    $headers = array('Content-Type: application/json');
    if ($token) {
      array_push($headers, 'Authorization: Bearer ' . $token);
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    if ($data) {
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    // Only for debugging.
    if (!empty($config['api_debug']) && $config['api_debug']) {
      print_r($url);
    }

    $response = curl_exec($curl);
    curl_close($curl);

    // Only for debugging.
    if (!empty($config['api_debug']) && $config['api_debug']) {
      print_r($data);
      print_r($response);
    }

    $response = json_decode($response, TRUE);

    return $response;

  }

  /**
   * Refresh the access token.
   */
  public function govready_refresh_token($return = FALSE) {

    // @todo: nonce this call
    $options = variable_get('govready_options');
    if (!empty($_REQUEST['refresh_token']) && $_REQUEST['refresh_token']) {
      $token = $_REQUEST['refresh_token'];
      $options['refresh_token'] = $token;
      variable_set('govready_options', $options);
    }
    else {
      $token = !empty($options['refresh_token']) ? $options['refresh_token'] : '';
    }

    $response = govready_api('/refresh-token', 'POST', array('refresh_token' => $token), TRUE);
    $response['endoflife'] = time() + (int) $response['expires_in'];
    variable_set('govready_token', $response);

    if ($return) {
      return $response;
    }
    else {
      drupal_json_output($response);
    }

  }

  /**
   * Call the GovReady API.
   */
  public function govready_api_proxy() {

    $method = !empty($_REQUEST['method']) ? $_REQUEST['method'] : $_SERVER['REQUEST_METHOD'];
    $response = govready_api($_REQUEST['endpoint'], $method, $_REQUEST);
    drupal_json_output($response);

  }

  /**
   * Implements hook_theme().
   */
  function govready_theme() {

    $path = drupal_get_path('module', 'govready');
    $variables = array(
      'logo' => url($path . '/images/logo.png'),
      'path' => '',
    );

    return array(
      'govready_connect' => array(
        'template' => 'templates/govready-connect',
        'variables' => $variables,
      ),
      'govready_dashboard' => array(
        'template' => 'templates/govready-dashboard',
        'variables' => $variables,
      ),
    );

  }


}