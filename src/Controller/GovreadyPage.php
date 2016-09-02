<?php

/**
 * @file
 * Contains \Drupal\govreday\Controller\GovReadyController.
 */

namespace Drupal\govready\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides route responses for the Example module.
 */
class GovreadyPage extends ControllerBase {

  /**
   * Page callback for the GovReady Dashboard.
   */
  public function govready_dashboard() {

    //module_load_include('class.php', 'govready', 'lib/govready-dashboard');
    $dashboard = new \Drupal\govready\Controller\GovreadyDashboard();
    return $dashboard->dashboardPage();

  }

  /**
   * Call the GovReady Agent trigger.
   */
  public function govready_trigger_callback() {

    //module_load_include('class.php', 'govready', 'lib/govready-agent');
    $agent = new \Drupal\govready\Controller\GovreadyAgent();
    return $agent->ping();

  }

  /**
   * Make a request to the GovReady API.
   */
  public function govready_api($endpoint, $method = 'GET', $data = array(), $anonymous = FALSE) {

    $config = govready_config();
    $url = $config['govready_url'] . $endpoint;

    // Make sure our token is a-ok.
    $token = \Drupal::config('govready.settings')->get('govready_token');

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
    $options = \Drupal::config('govready.settings')->get('govready_options');
    if (!empty($_REQUEST['refresh_token']) && $_REQUEST['refresh_token']) {
      $token = $_REQUEST['refresh_token'];
      $options['refresh_token'] = $token;
      \Drupal::configFactory()->getEditable('govready.settings')
        ->set('govready_options', $options)
        ->save();
    }
    else {
      $token = !empty($options['refresh_token']) ? $options['refresh_token'] : '';
    }

    $response = $this->govready_api('/refresh-token', 'POST', array('refresh_token' => $token), TRUE);
    $response['endoflife'] = time() + (int) $response['expires_in'];
    \Drupal::configFactory()->getEditable('govready.settings')
        ->set('govready_token', $response)
        ->save();

    if ($return) {
      return $response;
    }
    else {
      return new JsonResponse($response);
    }

  }

  /**
   * Call the GovReady API.
   */
  public function govready_api_proxy() {

    $method = !empty($_REQUEST['method']) ? $_REQUEST['method'] : $_SERVER['REQUEST_METHOD'];
    $response = govready_api($_REQUEST['endpoint'], $method, $_REQUEST);
    return new JsonResponse($response);

  }

}