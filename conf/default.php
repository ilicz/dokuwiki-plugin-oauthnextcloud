<?php
/**
 * Default settings for the oauthgeneric plugin
 */

$conf['key'] = '';
$conf['secret'] = '';

$conf['siteurl'] = '';
$conf['authurl'] = 'apps/oauth2/authorize';
$conf['tokenurl'] = 'apps/oauth2/api/v1/token';
$conf['userurl'] = 'ocs/v2.php/cloud/user?format=json';

$conf['json-user'] = 'ocs.data.id';
$conf['json-name'] = 'ocs.data.display-name';
$conf['json-mail'] = 'ocs.data.email';
$conf['json-grps'] = 'ocs.data.groups';

$conf['label'] = 'Nextcloud';
$conf['color'] = '#0082c9';
