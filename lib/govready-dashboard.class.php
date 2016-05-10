<?php
/**
 * @author GovReady
 */

//namespace Govready\GovreadyDashboard;

//use Govready;

class GovreadyDashboard {


  function __construct() {

    $this->path = drupal_get_path('module', 'govready');
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
    $logo = $this->path . '/images/logo.png';

    // Enqueue Bootstrap
    drupal_add_js('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css', 'external');
    wp_enqueue_script( 'govready-bootstrap-script', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', array(), true );

    // First time using app, need to set everything up
    if( empty($options['refresh_token']) ) {

      // Call GovReady /initialize to set the allowed CORS endpoint
      // @todo: error handling: redirect user to GovReady API dedicated login page
      global $base_url;
      if (empty($options['siteId'])) {
        $data = array(
          'url' => $base_url,
        );
        $response = $this->api( '/initialize', 'POST', $data, true );
        $options['siteId'] = $response['_id'];
        update_option( 'govready_options', $options );
      }

      // Save some JS variables (available at govready.siteId, etc)
      wp_enqueue_script( 'govready-connect', $path . 'govready-connect.js' );
      wp_localize_script( 'govready-connect', 'govready_connect', array( 
        'nonce' => wp_create_nonce( $this->key ),
        'auth0' => $this->auth0,
        'siteId' => $options['siteId']
      ) );

      require_once plugin_dir_path(__FILE__) . '../templates/govready-connect.php';
    
    }

    // Show me the dashboard!
    else {
    
      // Save some JS variables (available at govready.siteId, etc)
      wp_enqueue_script( 'govready-dashboard', $path . 'govready.js' );
      wp_localize_script( 'govready-dashboard', 'govready', array( 
        'siteId' => !is_null($options['siteId']) ? $options['siteId'] : null, 
        'nonce' => wp_create_nonce( $this->key )
      ) );

      // Enqueue react
      wp_enqueue_script( 'govready-dashboard-app-vendor', $path . 'client/dist/vendor.dist.js' );
      wp_enqueue_script( 'govready-dashboard-app', $path . 'client/dist/app.dist.js', array('govready-dashboard-app-vendor') );
      wp_enqueue_style ( 'govready-dashboard-app', $path . 'client/dist/app.dist.css' );

      require_once plugin_dir_path(__FILE__) . '../templates/govready-dashboard.php';

    } // if()

  }

}
