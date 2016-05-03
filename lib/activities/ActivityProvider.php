<?php

/**
 * @author      Andr� Kla�en <klassen@elan-ev.de>
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @license     GPL 2 or later
 */


namespace Studip\Activity;

interface ActivityProvider
{
    public function getActivityDetails(&$activity);
    public static function getLexicalField();
}
