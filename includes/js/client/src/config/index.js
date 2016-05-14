let config = {};

// Wordpress
if(window.govready) {
  config = window.govready;
  config.cms = 'wordpress';
  config.plugText = 'Plugin';
  config.cmsNice = 'Wordpress';
  let url = '/wp-admin/admin-ajax.php?';
  if(process.env.NODE_ENV === 'development') {
    url = 'http://localhost:8080/wp-admin/admin-ajax.php?';
  }
  config.apiUrl = url + 'action=govready_proxy&endpoint=/sites/' + config.siteId + '/';
  config.apiUrlNoSite = url + 'action=govready_proxy&endpoint=';
  config.apiTrigger = url + '?action=govready_v1_trigger';
}
else if(window.Drupal && window.Drupal.settings.govready) {
  config = window.Drupal.settings.govready;
  config.cms = 'drupal';
  config.plugText = 'Module';
  config.cmsNice = 'Drupal';
  let url = '/govready/api?';
  config.apiTrigger = 'govready/trigger';
  if(process.env.NODE_ENV === 'development') {
    url = 'http://localhost:80/govready/api?';
    config.apiTrigger = 'http://localhost:80/govready/trigger';
  }
  config.apiUrl = url + 'action=govready_proxy&endpoint=/sites/' + config.siteId + '/';
  config.apiUrlNoSite = url + 'action=govready_proxy&endpoint=';

}
else {
  config = {};
}

export default config;