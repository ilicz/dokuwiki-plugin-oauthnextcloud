<?php

use dokuwiki\plugin\oauth\Adapter;
use dokuwiki\plugin\oauthnextcloud\DotAccess;
use dokuwiki\plugin\oauthnextcloud\Nextcloud;

use OAuth\Common\Storage\Session as SessionStorage;
use OAuth\OAuth1\Token\TokenInterface;
use OAuth\OAuth1\Service\AbstractService as Abstract1Service;
use OAuth\OAuth2\Service\AbstractService as Abstract2Service;


/**
 * Service Implementation for oAuth Nextcloud authentication
 */
class action_plugin_oauthnextcloud extends Adapter
{

    /** @inheritdoc */
    public function registerServiceClass()
    {
        return Nextcloud::class;
    }

    /** * @inheritDoc */
    public function getUser()
    {
        $oauth = $this->getOAuthService();
        $data = array();

        $baseurl = $this->getConf('siteurl');
        $url = rtrim($baseurl, "/") . "/" . ltrim($this->getConf('userurl'), "/");
        $raw = $oauth->request($url);

        if (!$raw) throw new OAuthException('Failed to fetch data from userurl');
        $result = json_decode($raw, true);
        if (!$result) throw new OAuthException('Failed to parse data from userurl');

        $user = DotAccess::get($result, $this->getConf('json-user'), '');
        $name = DotAccess::get($result, $this->getConf('json-name'), '');
        $mail = DotAccess::get($result, $this->getConf('json-mail'), '');
        $grps = DotAccess::get($result, $this->getConf('json-grps'), []);

        // type fixes
        if (is_array($user)) $user = array_shift($user);
        if (is_array($name)) $user = array_shift($name);
        if (is_array($mail)) $user = array_shift($mail);
        if (!is_array($grps)) {
            $grps = explode(',', $grps);
            $grps = array_map('trim', $grps);
        }

        // fallbacks for user name
        if (empty($user)) {
            if (!empty($name)) {
                $user = $name;
            } elseif (!empty($mail)) {
                list($user) = explode('@', $mail);
            }
        }

        // fallback for full name
        if (empty($name)) {
            $name = $user;
        }

        return compact('user', 'name', 'mail', 'grps');
    }

    // Nextcloud does not support scopes at the time
    ///** @inheritdoc */
    //public function getScopes()
    //{
    //    return $this->getConf('scopes');
    //}

    /** @inheritDoc */
    public function getLabel()
    {
        return $this->getConf('label');
    }

    /** @inheritDoc */
    public function getColor()
    {
        return $this->getConf('color');
    }

    /** @inheritDoc */
    /** Default maxsize value for inlineSVG is 2048 which is too low for ~9k NXC logo */
    public function getSvgLogo()
    {
        $logo = DOKU_PLUGIN . $this->getPluginName() . '/logo.svg';
        if (file_exists($logo)) return inlineSVG($logo, 10240);
        return '';
    }

    /** @inheritDoc */
    public function checkToken()
    {
        global $INPUT;
        $oauth = $this->getOAuthService();

        if (is_a($oauth, Abstract2Service::class)) {
            // this is workaround for Nextcloud sending empty state parameter in the callback URI
            if ($INPUT->has('state') && empty($INPUT->str('state', ''))) {
                $INPUT->remove('state');
            }

            /** @var Abstract2Service $oauth */
            if (!$INPUT->get->has('code')) return false;
            $state = $INPUT->get->str('state', null);
            $accessToken = $oauth->requestAccessToken($INPUT->get->str('code'), $state);
        } else {
            /** @var Abstract1Service $oauth */
            if (!$INPUT->get->has('oauth_token')) return false;
            /** @var TokenInterface $token */
            $token = $oauth->getStorage()->retrieveAccessToken($this->getServiceID());
            $accessToken = $oauth->requestAccessToken(
                $INPUT->get->str('oauth_token'),
                $INPUT->get->str('oauth_verifier'),
                $token->getRequestTokenSecret()
            );
        }

        if (
            $accessToken->getEndOfLife() !== $accessToken::EOL_NEVER_EXPIRES &&
            !$accessToken->getRefreshToken()) {
            msg('Service did not provide a Refresh Token. You will be logged out when the session expires.');
        }

        return true; 
    }
}
