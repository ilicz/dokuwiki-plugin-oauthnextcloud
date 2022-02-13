<?php

namespace dokuwiki\plugin\oauthnextcloud;

use dokuwiki\plugin\oauth\Service\AbstractOAuth2Base;
use OAuth\Common\Http\Uri\Uri;

use OAuth\OAuth2\Service\ServiceInterface as ServiceInterface;

/**
 * Custom sErvice for Nextcloud oAuth2
 */
class Nextcloud extends AbstractOAuth2Base
{
    /** @inheritdoc */
    public function getAuthorizationEndpoint()
    {
        $plugin = plugin_load('helper', 'oauthnextcloud');

        $baseurl = rtrim($plugin->getConf('siteurl'), "/");
        $authurl = ltrim($plugin->getConf('authurl'), "/");

        return new Uri($baseurl . "/" . $authurl);
    }

    /** @inheritdoc */
    public function getAccessTokenEndpoint()
    {
        $plugin = plugin_load('helper', 'oauthnextcloud');

        $baseurl = rtrim($plugin->getConf('siteurl'), "/");
        $tokenurl = ltrim($plugin->getConf('tokenurl'), "/");

        return new Uri($baseurl . "/" . $tokenurl);
    }

    /** @inheritdoc */
    protected function getAuthorizationMethod()
    {
        $plugin = plugin_load('helper', 'oauthnextcloud');

        return (int) ServiceInterface::AUTHORIZATION_METHOD_HEADER_BEARER;
    }
}
