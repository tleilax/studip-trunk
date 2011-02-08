<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * The default implementation of the StudipCache interface.
 *
 * @package    studip
 * @subpackage lib
 *
 * @author    Marco Diedrich (mdiedric@uos)
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @since     1.6
 */

class StudipNullCache implements StudipCache {


    /**
     * Expires just a single key.
     *
     * @param  string  the key
     *
     * @return void
     */
    function expire($key)
    {
    }


    /**
     * Reads just a single key from the cache.
     *
     * @param  string  the key
     *
     * @return mixed   the corresponding value
     */
    function read($key)
    {
        return FALSE;
    }


    /**
     * Store data at the server.
     *
     * @param string   the item's key.
     * @param string   the item's content.
     * @param int      the item's expiry time in seconds. Defaults to 12h.
     *
     * @returns mixed  returns TRUE on success or FALSE on failure.
     *
     */
    function write($name, $content, $expire = 43200)
    {
        return FALSE;
    }
}

