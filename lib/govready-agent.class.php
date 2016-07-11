<?php
/**
 * @author GovReady
 */

//namespace Govready\GovreadyAgent;

//use Govready;

class GovreadyAgent { //extends Govready\Govready {


  function __construct() {
    //parent::__construct();
  }

  /**
   * Generic callback for ?action=govready_v1_trigger&key&endpoint&siteId
   * Examples:
   * ?action=govready_v1_trigger&key=plugins&endpoint=plugins&siteId=xxx
   * ?action=govready_v1_trigger&key=accounts&endpoint=accounts&siteId=xxx
   * ?action=govready_v1_trigger&key=stack&endpoint=stack/phpinfo&siteId=xxx
   */
  public function ping() {
    //print_r($_POST);

    $options = variable_get( 'govready_options' );
    // @todo: check that request is coming from plugin.govready.com, or is properly nonced (for manual refreshes)
    if ($_POST['siteId'] == $options['siteId']) {

      if ( !empty($_POST['key']) ) { 
        $key = $_POST['key'];
        $data = call_user_func( array($this, $key) );
        //print_r($data);
        if (!empty($data)) {
          if( !empty( $_POST['endpoint'] ) ) {
            //print_r($data);return;
            $endpoint = '/sites/' . $options['siteId'] . '/' . $_POST['endpoint'];
            // print_R($endpoint);
            $return = govready_api( $endpoint, 'POST', $data );
            //print_r($return); // @todo: comment this out, also don't return data in API
          }
          // @TODO return meaningful information
          drupal_json_output(array('response' => 'ok'));
        }
      }

    }
    else {
      print_r('Invalid siteId');
    }
  }


  // Callback for ?action=govready_v1_trigger&key=plugins
  private function plugins() {
    $out = array();

    // Hint to use system_rebuild_module_data() came from 
    // http://stackoverflow.com/questions/4232113/drupal-how-to-get-the-modules-list
    $modules = system_rebuild_module_data();
    
    foreach ($modules as $key => $module) {
      // Make sure not hidden, testing, core, or submodule
      if ( !(!empty($module->info['hidden']) && $module->info['hidden'] == 1) 
        && !(!empty($module->info['package']) && $module->info['package'] === 'Testing') 
        && $module->info['project'] !== 'drupal'
        && $module->info['project'] === $key
      ) {
        //print_r($module);
        array_push( $out, array(
          'label' => $module->info['name'],
          'namespace' => $key,
          'status' => (boolean) $module->status,
          'version' => $module->info['version'],
          'project_link' => !empty( $module->info['project'] ) ? 'https://www.drupal.org/project/' . $module->info['project'] : ''
        ) );
      } //if
    } //foreach
    
    return array( 'plugins' => $out, 'forceDelete' => true );

  }


  // Callback for ?action=govready_v1_trigger&key=accounts
  private function accounts() {
    $out = array();
    
    $users = entity_load('user');

    foreach ($users as $key => $user) {
      if ($key > 0) {
        array_push( $out, array(
          'userId' => $account->uid,
          'username' => $account->name,
          'email' => $account->mail,
          'name' => $account->name,
          'created' => $account->created,
          'roles' => array_values($account->roles),
          'superAdmin' => user_access('administer site configuration', $account),
          'lastLogin' => $account->login,
        ) );
      }
      
    }
    
    return array( 'accounts' => $out, 'forceDelete' => true );

  }


  // Callback for ?action=govready_v1_trigger&key=stack
  private function stack() {

    $stack = array(
      'os' => php_uname( 's' ) .' '. php_uname( 'r' ),
      'language' => 'PHP ' . phpversion(),
      'server' => $_SERVER["SERVER_SOFTWARE"],
      'application' => array(
        'platform' => 'Drupal',
        'version' => VERSION, // @todo
      ),
      'database' => function_exists('mysql_get_client_info') ? 'MySQL ' . mysql_get_client_info() : null,
    );

    return array( 'stack' => $stack );

  }


  // Callback for ?action=govready_v1_trigger&key=changeMode
  private function changeMode() {
    
    $options = variable_get( 'govready_options', array() );
    $options['mode'] = $_POST['mode'];
    variable_set('govready_options', $options);

    return array( 'mode' => $options['mode'] );

  }


} // class
