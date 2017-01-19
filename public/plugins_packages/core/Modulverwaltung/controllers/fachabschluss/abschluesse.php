<?php
/**
 * abschluss.php - controller class for Abschluesse
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * 
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

require_once dirname(__FILE__) . '/../MVV.class.php';

/**
 *
 *
 */
class Fachabschluss_AbschluesseController extends MVVController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        // set navigation
        Navigation::activateItem($this->me . '/fachabschluss/abschluesse');
        $this->action = $action;
        if (Request::isXhr()) {
            $this->response->add_header('Content-Type',
                    'text/html; charset=WINDOWS-1252');
            $this->set_layout(null);
        }
    }

    /**
     * Shows list of Abschluesse
     */
    public function index_action()
    {
        // set title
        PageLayout::setTitle(_('Verwaltung der Abschl�sse'));

        $this->initPageParams();

        $filter = array('mfi.institut_id' => MvvPerm::getOwnInstitutes());

        //get data
        $this->abschluesse = Abschluss::getAllEnriched($this->sortby, $this->order,
                self::$items_per_page,
                self::$items_per_page * ($this->page - 1), $filter);
        if (sizeof($this->abschluesse) == 0) {
            PageLayout::postInfo(_('Es wurden noch keine Abschl�sse angelegt.'));
        }
        $this->count = Abschluss::getCount($filter);

        $this->setSidebar();

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf diesen Seiten k�nnen Sie F�cher und Abschl�sse verwalten.').'</br>'));
        $widget->addElement(new WidgetElement(_('Ein Abschluss kann aufgeklappt werden, um die F�cher anzuzeigen, die diesem Abschluss bereits zugeordnet wurden.')));
        $helpbar->addWidget($widget);
    }

    public function details_action($abschluss_id = null)
    {
        $this->abschluss = Abschluss::get($abschluss_id);
        $this->abschluss_id = $this->abschluss->id;
        $this->perm_institutes = MvvPerm::getOwnInstitutes();
        if (!Request::isXhr()){
            $this->perform_relayed('index');
            return;
        }
    }

    /**
     * Edits the selected Abschluss
     *
     * @param $abschluss_id
     */
    public function abschluss_action($abschluss_id = null)
    {
        $this->abschluss_kategorien = AbschlussKategorie::getAll();
        if (count($this->abschluss_kategorien) == 0) {
            PageLayout::postError( _('Es wurden noch keine Abschluss-Kategorien angelegt. Bevor Sie fortfahren, legen Sie bitte hier zun�chst eine Abschluss-Kategorie an!'));
            $this->redirect('fachabschluss/kategorien/kategorie');
        }
        $this->abschluss = new Abschluss($abschluss_id);
        if ($this->abschluss->isNew()) {
            PageLayout::setTitle(_('Neuen Abschluss anlegen'));
            $success_message = _('Der Abschluss "%s" wurde angelegt.');
        } else {
            PageLayout::setTitle(_('Abschluss bearbeiten'));
            $success_message = _('Der Abschluss "%s" wurde ge�ndert.');
        }
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            $this->abschluss->name = trim(Request::get('name'));
            $this->abschluss->name_en = trim(Request::get('name_en'));
            $this->abschluss->name_kurz = trim(Request::get('name_kurz'));
            $this->abschluss->name_kurz_en = trim(Request::get('name_kurz_en'));
            $this->abschluss->beschreibung = trim(Request::get('beschreibung'));
            $this->abschluss->beschreibung_en = trim(Request::get('beschreibung_en'));
            $this->abschluss->assignKategorie(Request::option('kategorie_id'));
            try {
                $stored = $this->abschluss->store(true);
            } catch (InvalidValuesException $e) {
                 PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                $this->sessSet('sortby', 'chdate');
                $this->sessSet('order', 'DESC');
                if ($stored) {
                    PageLayout::postSuccess(sprintf($success_message,
                            htmlReady($this->abschluss->name)));
                } else {
                    PageLayout::postInfo(_('Es wurden keine �nderungen vorgenommen.'));
                }
                $this->redirect($this->url_for('/index'));
                return;
            }
        }

        $this->setSidebar();
        if (!$this->abschluss->isNew()) {
            $sidebar = Sidebar::get();
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink( _('Log-Eintr�ge dieses Abschlusses'),
                    $this->url_for('shared/log_event/show/Abschluss', $this->abschluss->getId()),
                    Icon::create('log', 'clickable'))->asDialog();
        }
    }

    /**
     * Deletes the Abschluss
     */
    public function delete_action($abschluss_id)
    {
        $abschluss = Abschluss::get($abschluss_id);
        if (Request::submitted('yes')) {
            if ($abschluss->isNew()) {
                PageLayout::postError( _('Der Abschluss kann nicht gel�scht werden (unbekannter Abschluss).'));
            } else {
                CSRFProtection::verifyUnsafeRequest();
                $name = $abschluss->name;
                $abschluss->delete();
                PageLayout::postSuccess(sprintf(_('Der Abschluss "%s" wurde gel�scht.'), htmlReady($name)));
            }
        }
        if (!Request::isPost()) {
            $this->flash_set('dialog', sprintf(_('Wollen Sie wirklich den Abschluss "%s" l�schen?'),
                            $abschluss->name),
                    '/delete/' . $abschluss->id, '/index');
        }
        $this->redirect($this->url_for('/index'));
    }

    public function fach_action($fach_id = null)
    {
        $response = $this->relay('fachabschluss/faecher/fach/' . $fach_id);
        if (Request::isXhr()) {
            $this->render_text($response->body);
        } else {
            if ($response->headers['Location']) {
                $this->redirect($response->headers['Location']);
            } else {
              $this->relocate('fachabschluss/faecher/fach', $fach_id);
            }
        }
    }

    /**
     * Creates the sidebar widgets
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/learnmodule-sidebar.png');

        if (MvvPerm::havePermCreate('Abschluss')) {
            $widget  = new ActionsWidget();
            $widget->addLink( _('Neuen Abschluss anlegen'),
                    $this->url_for('/abschluss'),
                    Icon::create('file', 'clickable'));
            $sidebar->addWidget($widget);
        }
        $this->sidebar_rendered = true;
    }

}
