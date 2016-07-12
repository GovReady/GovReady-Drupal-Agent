# GovReady
GovReady provides a dashboard and tools to enhance security for government
websites and achieve FISMA compliance.

**Note: This module is currently under active development and should not be used
on production websites.**

## Overview

The GovReady Agent monitors your Drupal site, domain, and ssl certificate to
ensure that you are following current security best-practices.

The GovReady Dashboard gives you a shared, easy-to-digest overview of the status
of security on your website, including:
* Drupal Core update status
* Contributed module update status
* Superadmin accounts
* Site uptime monitoring
* The status of your domain and SSL certificate renewals
* A manual measures checklist to be completed periodically to ensure compliance
* A contact info matrix ("who do I contact to change my password", etc)
* Accounts that have not recently logged-in (and may have left your
  organization)

## Requirements
* cURL must be installed and appear in `php.ini`. 
[Tutorial to enable cURL in PHP]
(http://www.tomjepson.co.uk/enabling-curl-in-php-php-ini-wamp-xamp-ubuntu/).


## Installation
Currently, we do not recommend installing this module on production websites.

1. Download the module code, or install with Drush: `drush dl govready`.
2. Copy the module code into `./sites/all/modules` (or similar).
3. Log into Drupal and enable the module on `/admin/build/modules`, or enable
   with Drush (`drush en govready`).
4. Go to `/admin/reports/govready`, create a GovReady account and proceed
   through the module auto-activation steps.

---
 
## Developing

To delete the token and force re-authentication, run this Drush command:
```
drush vdel govready_options
```

### Making calls to the GovReady API
```javascript
http://localhost:8080/govready/api?endpoint=/initialize&method=POST
```
