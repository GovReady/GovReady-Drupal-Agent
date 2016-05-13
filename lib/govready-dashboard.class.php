<?php
/**
 * @author GovReady
 */

//namespace Govready\GovreadyDashboard;

//use Govready;

class GovreadyDashboard {


  function __construct() {

    $this->path = drupal_get_path('module', 'govready');
    $this->config = govready_config();
    //parent::__construct();

    // Display the admin notification
    //add_action( 'admin_notices', array( $this, 'plugin_activation' ) ) ;

    // Add the dashboard page
    //add_action( 'admin_menu', array($this, 'create_menu') );
  }

  /**
   * Display the GovReady dashboard.
   */
  public function dashboard_page() {
    $options = variable_get( 'govready_options', array() );

    $path = $this->path . '/includes/js/';
    $settings = array(
      'api_endpoint' => url('govready/api'),
      'token_endpoint' => url('govready/refresh-token'),
      'trigger_endpoint' => url('govready/trigger'),
    );

    // First time using app, need to set everything up
    if( empty($options['refresh_token']) ) {

      // Call GovReady /initialize to set the allowed CORS endpoint
      // @todo: error handling: redirect user to GovReady API dedicated login page
      global $base_url;
      if (empty($options['siteId'])) {
        $data = array(
          'url' => $base_url,
        );
        $response = govready_api( '/initialize', 'POST', $data, true );
        $options['siteId'] = $response['_id'];
        variable_set( 'govready_options', $options );
      }

      // Save some JS variables (available at govready.siteId, etc)
      drupal_add_js( $path . 'govready-connect.js' );
      $settings = array_merge(array(
        //'nonce' => wp_create_nonce( $this->key ),
        'auth0' => $this->config['auth0'],
        'siteId' => $options['siteId']
      ), $settings);
      drupal_add_js(array('govready_connect' => $settings), 'setting');

      return theme('govready_connect');
    
    }

    // Show me the dashboard!
    else {
    
      // Save some JS variables (available at govready.siteId, etc)
      drupal_add_js( $path . 'govready.js' );
      $settings = array_merge(array(
        'siteId' => !is_null($options['siteId']) ? $options['siteId'] : null, 
        //@todo: 'nonce' => wp_create_nonce( $this->key )
      ), $settings);
      drupal_add_js(array('govready' => $settings), 'setting');

      // Enqueue react
      drupal_add_js( $path . 'client/dist/vendor.dist.js', array(
        'scope' => 'footer',
        'group' => 'GovReady',
        'weight' => 1,
      ));
      drupal_add_js( $path . 'client/dist/app.dist.js', array(
        'scope' => 'footer',
        'group' => 'GovReady',
        'weight' => 2,
      ));
      drupal_add_css ( $path . 'client/dist/app.dist.css' );

      return theme('govready_dashboard');

    } // if()

  }

}
