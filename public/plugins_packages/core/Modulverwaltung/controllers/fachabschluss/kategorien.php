<?php
/**
 * kategorien.php - controller class for Abschluss-Kategorien
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

class Fachabschluss_KategorienController extends MVVController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        // set navigation
        Navigation::activateItem($this->me . '/fachabschluss/kategorien');
        $this->action = $action;

        if (Request::isXhr()) {
            $this->response->add_header('Content-Type',
                    'text/html; charset=WINDOWS-1252');
            $this->set_layout(null);
        }
    }

    /**
     * Lists all Abschluss-Kategorien
     */
    function index_action()
    {
        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Sie k�nnen die Reihenfolge der Abschluss-Kategorien durch Ziehen der Zeilen �ndern.').'</br>'));
        $widget->addElement(new WidgetElement(_('Eine Abschluss-Katgorie kann aufgeklappt werden, um die Abschl�sse anzuzeigen, die der Abschluss-Kategorie bereits zugeordnet wurden.')));
        $widget->addElement(new WidgetElement(_('Die Reihenfolge der zugeordneten Abschl�sse kann ebenfalls ge�ndert werden.')));
        $helpbar->addWidget($widget);

        // Nur Abschluss-Kategorien mit Abschl�ssen, die Studieng�ngen zugeordnet
        // sind an deren Verantwortliche Einrichtung dem User eine Rolle
        // zugewiesen wurde
        $filter = array('ms.institut_id' => MvvPerm::getOwnInstitutes());

        $this->abschluss_kategorien =
                AbschlussKategorie::getAllEnriched('position', 'ASC', null, null, $filter);
        if (sizeof($this->abschluss_kategorien) == 0) {
            PageLayout::postInfo(_('Es wurden noch keine Abschluss-Kategorien angelegt.'));
        }
        PageLayout::setTitle(_('Abschluss-Kategorien'));
        $this->setSidebar();
    }

    /**
     * Creates a new Abschluss-Kategorie
     */
    function kategorie_action($kategorie_id = null)
    {
        $this->abschluss_kategorie = new AbschlussKategorie($kategorie_id);
        $this->dokumente = $this->abschluss_kategorie->document_assignments;
        if ($this->abschluss_kategorie->isNew()) {
            PageLayout::setTitle(_('Neue Abschluss-Kategorie anlegen'));
            $success_message = ('Die Abschluss-Kategorie "%s" wurde angelegt.');
        } else {
            PageLayout::setTitle(_('Abschluss-Kategorie bearbeiten'));
            $success_message = _('Die Abschluss-Kategorie "%s" wurde ge�ndert.');
        }
        $this->sessSet('dokument_target', array($this->abschluss_kategorie->getId(),
                'AbschlussKategorie'));
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            $this->abschluss_kategorie->name = trim(Request::get('name'));
            $this->abschluss_kategorie->name_en = trim(Request::get('name_en'));
            $this->abschluss_kategorie->name_kurz = trim(Request::get('name_kurz'));
            $this->abschluss_kategorie->name_kurz_en = trim(Request::get('name_kurz_en'));
            $this->abschluss_kategorie->beschreibung = Request::get('beschreibung');
            $this->abschluss_kategorie->beschreibung_en = Request::get('beschreibung_en');
            try {
                $stored = $this->abschluss_kategorie->store();
            } catch (InvalidValuesException $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                Dokument::updateDocuments($this->abschluss_kategorie,
                        Request::optionArray('dokumente_items'),
                        Request::getArray('dokumente_properties'));

                PageLayout::postSuccess(sprintf($success_message,
                    htmlReady($this->abschluss_kategorie->name)));

                //the if-else block was deactivated because
                //it can't tell for sure if changes have been made

                /*

                if ($stored) {
                    PageLayout::postSuccess(sprintf($success_message,
                            htmlReady($this->abschluss_kategorie->name)));
                } else {
                    PageLayout::postInfo(_('Es wurden keine �nderungen vorgenommen.'));
                }
                */
                $this->redirect($this->url_for('/index'));
                return;
            }
        }

        $this->search_dokumente =
                Dokument::getQuickSearch($this->dokumente->pluck('dokument_id'));

        $this->cancel_url = $this->url_for('/index');

        $this->setSidebar();
        if (!$this->abschluss_kategorie->isNew()) {
            $sidebar = Sidebar::get();
            $action_widget = $sidebar->getWidget('actions');
            $action_widget->addLink(_('Log-Eintr�ge dieser Kategorie'),
                    $this->url_for('shared/log_event/show/AbschlussKategorie', $this->abschluss_kategorie->getId()),
                    Icon::create('log', 'clickable'))->asDialog();
        }
    }

    /**
     * Deletes a Abschluss-Kategorie
     */
    function delete_action($kategorie_id)
    {
        $abschluss_kategorie = new AbschlussKategorie($kategorie_id);
        if ($abschluss_kategorie->isNew()) {
             PageLayout::postError( _('Unbekannte Abschluss-Kategorie'));
        } else {
            if (Request::submitted('yes')) {
                CSRFProtection::verifyUnsafeRequest();
                if (!MvvPerm::get('AbschlussKategorie')->haveFieldPerm('position')) {
                    throw new Trails_Exception(403);
                }
                if (!count($abschluss_kategorie->abschluesse)) {
                    PageLayout::postSuccess(sprintf(_('Abschluss-Kategorie "%s" gel�scht!'),
                            htmlReady($abschluss_kategorie->name)));
                    $abschluss_kategorie->delete();
                } else {
                    PageLayout::postError( sprintf(_('L�schen nicht m�glich! Die Abschluss-Kategorie "%s" ist noch Abschl�ssen zugeordnet!'),
                            htmlReady($abschluss_kategorie->name)));
                }
            }
            if (!Request::isPost()) {
                $this->flash_set('dialog', sprintf(_('Wollen Sie wirklich die Abschluss-Kategorie "%s" l�schen?'),
                                $abschluss_kategorie->name),
                        '/delete/' . $abschluss_kategorie->getId(),
                        '/index');
            }
        }
        $this->redirect($this->url_for('/index'));
    }

    /**
     * sorts abschluss kategorien
     */
    public function sort_action()
    {
        $list = Request::option('list_id');
        $orderedIds = Request::getArray('newOrder');
        if ($list == 'abschluss_kategorien') {
            if (!MvvPerm::get('AbschlussKategorie')->haveFieldPerm('position')) {
                throw new Trails_Exception(403);
            }
            $kategorien = SimpleORMapCollection::createFromArray(
                    AbschlussKategorie::findBySql('1 ORDER BY position'));
            if (is_array($orderedIds)) {
                $i = 1;
                foreach ($orderedIds as $id) {
                    $kategorie = $kategorien->find($id);
                    if ($kategorie) {
                        if ($kategorie->position != $i) {
                            $kategorie->position = $i;
                            $kategorie->store();
                        }
                        $i++;
                    }
                }
            }
        } else {
            if (!MvvPerm::get('AbschlussZuord')->haveFieldPerm('position')) {
                throw new Trails_Exception(403);
            }
            list(, $kategorie_id) = explode('_', $list);
            $abschluss_kategorie = AbschlussKategorie::find($kategorie_id);
            if ($abschluss_kategorie) {
                $i = 1;
                foreach ($orderedIds as $id) {
                    list(, $abschluss_id) = explode('_', $id);
                    if ($abschluss_kategorie->abschluesse->find($abschluss_id)) {
                        $abschluss_zuord = new AbschlussZuord($abschluss_id);
                        if ($abschluss_zuord->position != $i) {
                            $abschluss_zuord->position = $i;
                            $abschluss_zuord->store();
                        }
                        $i++;
                    }
                }
            }
        }
        $this->set_status(200);
        $this->render_nothing();
    }

    public function details_action($kategorie_id)
    {
        $this->kategorie = AbschlussKategorie::get($kategorie_id);
        $this->kategorie_id = $this->kategorie->getId();
        $this->perm_institutes = MvvPerm::getOwnInstitutes();
        if (!Request::isXhr()){
            $this->perform_relayed('index');
            return;
        }
    }

    public function dokumente_properties_action($dokument_id)
    {
        $target = $this->sessGet('dokument_target');
        if ($target) {
            $this->redirect('materialien/dokumente/ref_properties/' . $dokument_id
                . '/' . join('/', $target));
        }
    }

    /**
     * Creates the sidebar widgets
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/learnmodule-sidebar.png');

        if (MvvPerm::havePermCreate('AbschlussKategorie')) {
            $widget  = new ActionsWidget();
            $widget->addLink( _('Neue Abschluss-Kategorie anlegen'),
                    $this->url_for('/kategorie'),
                    Icon::create('file+add', 'clickable'));
            $sidebar->addWidget($widget);
        }
    }

}
