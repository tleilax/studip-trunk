<?php
/*
 * start.php - start page controller
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   Andr� Kla�en <klassen@elan-ev.de>
 * @author   Nadine Werner <nadine.werner@uni-osnabrueck.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @since    3.1
 */

require_once 'lib/functions.php';
require_once 'studip_controller.php';
require_once 'authenticated_controller.php';

class StartController extends AuthenticatedController
{
    /**
     * Callback function being called before an action is executed.
     */
    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (Request::isXhr()) {
            $this->set_layout(null);
            $this->set_content_type('text/html;Charset=windows-1252');
        }

        Navigation::activateItem('/start');
        PageLayout::setTabNavigation(NULL); // disable display of tabs
        PageLayout::setHelpKeyword("Basis.Startseite"); // set keyword for new help
        PageLayout::setTitle(_('Startseite'));
    }

    /**
     * Entry point of the controller that displays the start page of Stud.IP
     *
     * @param string $action
     * @param string $widgetId
     *
     * @return void
     */
    function index_action($action = false, $widgetId = null)
    {
        $this->left = array();
        $this->right = array();
        $this->widgets = WidgetHelper::getUserWidgets($GLOBALS['user']->id);

        if (empty($this->widgets)){
            $this->widgets = WidgetHelper::getInitialPositions($GLOBALS['perm']->get_perm());
            $idl = array();
            foreach ($this->widgets as $widget) {
                if ($widget['column'] == 0) {
                    $idl[$widget['row']] = $widget['pluginid'];
                }
            }

            WidgetHelper::addInitialPositions(0, $idl, $GLOBALS['user']->id);
            $this->widgets = WidgetHelper::getUserWidgets($GLOBALS['user']->id);
        }

        foreach ($this->widgets as $pos => $widget) {
            $this->left[$pos] = $widget;
        }

        ksort($this->left);
        WidgetHelper::setActiveWidget(Request::get('activeWidget'));

        $sidebar = Sidebar::get();
        
        $nav = new NavigationWidget();
        $nav->setTitle(_('Sprungmarken'));
        foreach ($this->widgets as $widget) {
            $nav->addLink($widget->getPluginName(),
                          $this->url_for('start#widget-' . $widget->widget_id));
        }
        $sidebar->addWidget($nav);
        
        $actions = new ActionsWidget();
        $actions->addLink(_('Neues Widget hinzuf�gen'),
                          $this->url_for('start/add'),
                          'icons/16/blue/add.png')->asDialog();
        $sidebar->addWidget($actions);
    }
    
    /**
     *  This actions adds a new widget to the start page
     *
     * @param string $choice representing the chosen widgetId
     * @param string $side where the widget should be paced (used in later versions)
     * @param string $studipticket
     *
     * @return void
     */
    public function add_action($choice, $side = 0, $studipticket = false)
    {
        if (Request::isPost()) {
            $ticket   = Request::get('studip_ticket');
            $widget   = Request::int('widget_id');
            $position = Request::int('position');

            $post_url = '';
            if (check_ticket($ticket)) {
                $id = WidgetHelper::addWidget($widget, $GLOBALS['user']->id);
                $post_url = '#widget-' . $id;
            }
            $this->redirect('start' . $post_url);
        }

        $this->widgets = PluginEngine::getPlugins('PortalPlugin');
    }

    /**
     *  This actions removes a new widget from the start page
     *
     * @param string $widgetId
     * @param string $approveDelete
     * @param string $studipticket
     *
     * @return void
     */
    function delete_action($id)
    {
        if (Request::isPost()) {
            if (Request::submitted('yes')) {
                $name = WidgetHelper::getWidgetName($id);
                if (WidgetHelper::removeWidget($id, $name, $GLOBALS['user']->id)) {
                    $message = sprintf(_('Widget "%s" wurde entfernt.'), $name);
                    PageLayout::postMessage(MessageBox::success($message));
                } else {
                    $message = sprintf(_('Widget "%s" konnte nicht entfernt werden.'), $name);
                    PageLayout::postMessage(MessageBox::error($message));
                }
            }
        } else {
            $message = sprintf(_('Sind Sie sicher, dass Sie das Widget "%s" von der Startseite entfernen m�chten?'),
                               WidgetHelper::getWidgetName($id));
            $this->flash['question'] = createQuestion2($message, array(), array(), $this->url_for('start/delete/' . $id));
        }
        $this->redirect('start');
    }

    /**
     *  Action to store the widget placements
     *
     * @return void
     */
    function storeNewOrder_action()
    {
        if ($ids = Request::get('ids')) {
             $idArray = explode(',', $ids);
             WidgetHelper::storeNewPositions($idArray);
        }
        $this->render_nothing();
    }
}
