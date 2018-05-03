<?php
/*
 * StudipAuthLTI.class.php - Stud.IP authentication against LTI 1.1 consumer
 * Copyright (c) 2018  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class StudipAuthLTI extends StudipAuthSSO
{
    public $consumer_keys;
    public $username;
    public $domain;

    /**
     * Validate the username passed to the auth plugin.
     */
    public function verifyUsername($username)
    {
        $consumer_key = Request::get('oauth_consumer_key');
        $username = Request::get('lis_person_sourcedid', Request::get('user_id'));
        $override = $this->consumer_keys[$consumer_key]['allow_domain_override'];
        $domain = $this->consumer_keys[$consumer_key]['domain'];

        if (!$username) {
            throw new InvalidArgumentException('user_id must not be empty');
        }

        if ($domain === null) {
            $domain = $consumer_key;
        }

        if ($override && strpos($username, '@') !== false) {
            list($username, $domain) = explode('@', $username);
        }

        if ($domain !== '') {
            $username .= '@' . $domain;
            $this->domain = $domain;
        }

        return $this->username = parent::verifyUsername($username);
    }

    /**
     * Check whether this user can be authenticated.
     */
    public function isAuthenticated($username, $password)
    {
        require_once 'vendor/oauth-php/library/OAuthRequestVerifier.php';

        OAuthStore::instance('PDO', [
            'dsn' => 'mysql:host=' . $GLOBALS['DB_STUDIP_HOST'] . ';dbname=' . $GLOBALS['DB_STUDIP_DATABASE'],
            'username' => $GLOBALS['DB_STUDIP_USER'],
            'password' => $GLOBALS['DB_STUDIP_PASSWORD']
        ]);

        $consumer_key = Request::get('oauth_consumer_key');
        $consumer_secret = $this->consumer_keys[$consumer_key]['consumer_secret'];

        $oarv = new OAuthRequestVerifier();
        $oarv->verifySignature($consumer_secret, false, false);

        return parent::isAuthenticated($username, $password);
    }

    /**
     * Authenticate this user and handle auto enrollment.
     */
    public function authenticateUser($username, $password)
    {
        $user = parent::authenticateUser($username, $password);
        $course_id = Request::option('sem_id');

        if ($user && $course_id) {
            header('Location: ' . URLHelper::getURL('dispatch.php/lti/index/' . $course_id));
        }

        return $user;
    }

    /**
     * Return the current username.
     */
    public function getUser()
    {
        return $this->username;
    }

    /**
     * Get the user domains to assign to the current user.
     */
    public function getUserDomains()
    {
        return $this->domain ? [$this->domain] : null;
    }

    /**
     * Callback that can be used in user_data_mapping array.
     */
    public function getUserData($key)
    {
        return Request::get($key);
    }
}
