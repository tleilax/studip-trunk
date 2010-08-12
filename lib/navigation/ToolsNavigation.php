<?php
/*
 * ToolsNavigation.php - navigation for tools page
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.0
 */

/**
 * This navigation includes all tools for a user depending on the
 * activated modules.
 */
class ToolsNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        parent::__construct(_('Tools'));

        $this->setImage('header/tools.png', array('title' => _('Tools')));
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $auth;

        parent::initSubNavigation();

        $username = $auth->auth['uname'];

        // news
        $navigation = new Navigation(_('Ank�ndigungen'), 'admin_news.php', array('range_id' => 'self'));
        $this->addSubNavigation('news', $navigation);

        // votes and tests, evaluations
        if (get_config('VOTE_ENABLE')) {
            $navigation = new Navigation(_('Umfragen und Tests'), 'admin_vote.php', array('page' => 'overview', 'showrangeID' => $username));
            $this->addSubNavigation('vote', $navigation);

            $navigation = new Navigation(_('Evaluationen'), 'admin_evaluation.php', array('rangeID' => $username));
            $this->addSubNavigation('evaluation', $navigation);
        }

        // literature
        if (get_config('LITERATURE_ENABLE')) {
            $navigation = new Navigation(_('Literatur'), 'admin_lit_list.php', array('_range_id' => 'self'));
            $this->addSubNavigation('literature', $navigation);
        }

        // elearning
        if (get_config('ELEARNING_INTERFACE_ENABLE')) {
            $navigation = new Navigation(_('Lernmodule'), 'my_elearning.php');
            $this->addSubNavigation('elearning', $navigation);
        }
    }
}
