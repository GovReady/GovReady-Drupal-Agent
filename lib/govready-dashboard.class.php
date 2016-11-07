<?php

/**
 * @file
 * Displays the GovReady Dashboard.
 */

/**
 * GovreadyDashboard class.
 *
 * Namespace Govready\GovreadyDashboard.
 */
class GovreadyDashboard {

  /**
   * Construct function.
   */
  function __construct() {
    $this->path = drupal_get_path('module', 'govready');
    $this->config = govready_config();
  }

  /**
   * Display the GovReady dashboard.
   */
  public function dashboardPage() {
    $options = variable_get('govready_options', array());

    $path = $this->path . '/includes/js';
    $client_path = variable_get('govready_client', 'remote') != 'local' ? $this->config['govready_client_url'] : $path . '/client/dist';
    $settings = array(
      'api_endpoint' => url('govready/api'),
      'token_endpoint' => url('govready/refresh-token'),
      'trigger_endpoint' => url('govready/trigger'),
    );

    // Add warning message if overlay is enabled.
    if (module_exists('overlay')) {
      $url = url('admin/reports/govready');
      drupal_set_message(
        t(
          'The GovReady Dashboard may not work properly with the Overlay module enabled. To use the Dashboard, please disable the Overlay module on the !modules, or view the dashboard from a !tab outside of the overlay.', array(
            '!modules' => l('modules page', 'admin/modules'), 
            '!tab' => l('new tab', $url, array('attributes' => array('onclick' => 'window.open("'.$url.'");return false;')))
          )
        ), 'warning');
    }

    // First time using app, need to set everything up.
    if (empty($options['refresh_token'])) {

      // Call GovReady /initialize to set the allowed CORS endpoint.
      // @todo: error handling: redirect user to GovReady API dedicated login page
      global $base_url;
      if (empty($options['siteId'])) {
        $data = array(
          'url' => $base_url,
          'application' => 'drupal',
        );
        $response = govready_api('/initialize', 'POST', $data, TRUE);
        $options['siteId'] = !empty($response['_id']) ? $response['_id'] : NULL;
        variable_set('govready_options', $options);
      }

      // Save some JS variables (available at govready.siteId, etc)
      drupal_add_js($path . '/govready-connect.js');
      $settings = array_merge(array(
        'govready_nonce' => drupal_get_token(GOVREADY_KEY),
        'auth0' => $this->config['auth0'],
        'siteId' => !empty($options['siteId']) ? $options['siteId'] : NULL,
      ), $settings);
      drupal_add_js(array('govready_connect' => $settings), 'setting');

      return theme('govready_connect');

    }

    // Show me the dashboard!
    else {

      // Save some JS variables (available at govready.siteId, etc)
      $settings = array_merge(array(
        'siteId' => !empty($options['siteId']) ? $options['siteId'] : NULL,
        'mode' => !empty($options['mode']) ? $options['mode'] : 'preview',
        'govready_nonce' => drupal_get_token(GOVREADY_KEY),
        'connectUrl' => $this->config['govready_api_url'],
        'application' => 'drupal',
      ), $settings);
      drupal_add_js(array('govready' => $settings), 'setting');

      // Enqueue react.
      drupal_add_js($client_path . '/vendor.dist.js', array(
        'scope' => 'footer',
        'group' => 'GovReady',
        'weight' => 1,
      ));
      drupal_add_js($client_path . '/app.dist.js', array(
        'scope' => 'footer',
        'group' => 'GovReady',
        'weight' => 2,
      ));
      if(variable_get('govready_client', 'remote') == 'local') {
        drupal_add_css($client_path . '/app.dist.css');
      }
      else {
        drupal_add_css($client_path . '/app.dist.css', 'external');
      }

      return theme('govready_dashboard');

    } // if()

  }

}
