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
    print_r($_POST);

    $options = variable_get( 'govready_options' );
    // @todo: check that request is coming from plugin.govready.com, or is properly nonced (for manual refreshes)
    if ($_POST['siteId'] == $options['siteId']) {

      $key = $_POST['key'];
      if ( !empty($key) ) { 
        $data = call_user_func( array($this, $key) );
        //print_r($data);
        if (!empty($data)) {
          //print_r($data);return;
          $endpoint = '/sites/' . $options['siteId'] . '/';
          $endpoint .= !empty($_POST['endpoint']) ? $_POST['endpoint'] : '';
          print_R($endpoint);
          $return = govready_api( $endpoint, 'POST', $data );
          print_r($data);
          print_r($return); // @todo: comment this out, also don't return data in API
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
    //print_r($modules);
    
    foreach ($modules as $key => $module) {
      if ( !(!empty($module->info['hidden']) && $module->info['hidden'] == 1) ) {
        array_push( $out, array(
          'label' => $module->info['name'],
          'namespace' => $key,
          'status' => $module->status,
          'version' => $module->info['version'],
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
          'userId' => $user->uid,
          'username' => $user->name,
          'email' => $user->mail,
          'name' => $user->name,
          'created' => $user->created,
          'roles' => $user->roles,
          'lastLogin' => $user->login,
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
      'database' => null,
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
