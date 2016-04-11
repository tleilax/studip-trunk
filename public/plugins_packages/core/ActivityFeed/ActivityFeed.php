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


class ActivityFeed extends StudIPPlugin implements PortalPlugin
{
    
    public function getPluginName()
    {
        return _('Aktivitäten');
    }

    public function getPortalTemplate()
    {
        $this->addStylesheet('css/style.less');
        PageLayout::addScript($this->getPluginUrl() . '/javascript/activityfeed.js');

        $filter = WidgetHelper::getWidgetUserConfig($GLOBALS['user']->id, 'ACTIVITY_FILTER');

        //use filter iff user explicitly chooses one for each session, default otherwise
        $filter='';

        if(is_array($filter)){
            $start_date = date('Y-m-d', $filter['start_date']);
            $end_date   = date('Y-m-d', $filter['end_date']);
        } else {
            $start_date =  date('Y-m-d', strtotime("-4 week"));
            $end_date   =  date('Y-m-d', strtotime('+1 day'));
        }


        $template_factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $template_factory->open('activity_feed');

        $template->user_id = $GLOBALS['user']->id;
        $template->start_date = $start_date;
        $template->end_date = $end_date;


        $navigation = new Navigation('', PluginEngine::getLink($this, array(), 'configuration'));
        $navigation->setImage('icons/16/blue/edit.png', tooltip2(_('Konfigurieren')) + array('data-dialog' => 'size=auto'));

        $template->icons = array($navigation);

        return $template;
    }


    public function save_action()
    {

        if (get_config('ACTIVITY_FILTER') === NULL) {
            Config::get()->create('ACTIVITY_FILTER', array('range' => 'user', 'type' => 'array', 'description' => 'Filtereinstellungen des Aktivitäten-Widgets'));
        }


        $start_date = strtotime(Request::get('start_date'));
        $end_date = strtotime(Request::get('end_date'));

        //TODO CHECK IF end_date is greater than start_date


        $filter['start_date'] = $start_date;
        $filter['end_date'] = $end_date;



        WidgetHelper::addWidgetUserConfig($GLOBALS['user']->id, 'ACTIVITY_FILTER', $filter);

        $template_factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $template_factory->open('activity_feed');

        $template->user_id = $GLOBALS['user']->id;
        $template->start_date = date('Y-m-d',$start_date);
        $template->end_date = date('Y-m-d',$end_date);


        $navigation = new Navigation('', PluginEngine::getLink($this, array(), 'configuration'));
        $navigation->setImage('icons/16/blue/edit.png', tooltip2(_('Konfigurieren')) + array('data-dialog' => 'size=auto'));
        $template->icons = array($navigation);

        header('X-Dialog-Close: 1');
        header('X-Dialog-Execute: STUDIP.ActivityFeed.update');

        echo studip_utf8encode($template->render());
    }


    public function configuration_action()
    {
        $template_factory = new Flexi_TemplateFactory(__DIR__ . '/templates');
        $template = $template_factory->open('edit');
        $template->links = Navigation::getItem('/start');

        $filter = WidgetHelper::getWidgetUserConfig($GLOBALS['user']->id, 'ACTIVITY_FILTER');


        if(!empty($filter)){
            $start_date = date('Y-m-d',$filter['start_date']);
            $end_date = date('Y-m-d',$filter['end_date']);
        } else {
            $start_date =  date('Y-m-d', strtotime("-4 week"));
            $end_date =  date('Y-m-d');
        }


        $template->start_date = $start_date;
        $template->end_date = $end_date;
        $template->plugin = $this;

        header('X-Title: ' . _('Aktivitäten filtern'));
        echo studip_utf8encode($template->render());
    }

}
