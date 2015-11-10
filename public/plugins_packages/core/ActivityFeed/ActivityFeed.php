<?php
/*
 * ActivityFeed.php - A portal plugin for activities
 *
 * Copyright (C) 2014 - André Klaßen <klassen@elan-ev.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'vendor/autoload.php';


class ActivityFeed extends StudIPPlugin implements PortalPlugin
{
    public function getPluginName()
    {
        return _('Meine Aktivitäten');
    }

    public function getPortalTemplate()
    {
        //PageLayout::addScript($this->getPluginUrl() . '/js/ActivityFeed.js');
        PageLayout::addStylesheet($this->getPluginURL(). '/css/style.css');


        $observer_id = $GLOBALS['user']->id ;
        $context = new \Studip\Activity\SystemContext();
        $stream = new \Studip\Activity\Stream($observer_id, $context, new Studip\Activity\Filter());


        $template_factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $template_factory->open('stream');
        $template->stream = $stream;

        /*
        $navigation = new Navigation('', '#');
        $navigation->setImage('icons/16/blue/edit.png', array(
                                  'title' => _('Konfigurieren'),
                                  'onclick' => "ActivityFeed.openDialog('". PluginEngine::getLink($this, array(), 'configuration') ."'); return false;"
                              ));

        $template->icons = array($navigation);
        */

        return $template;
    }
}
