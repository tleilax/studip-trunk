<?php
/**
 * faecher.php - Studiengaenge_FaecherController
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
require_once dirname(__FILE__) . '/studiengangteile.php';

class Studiengaenge_FaecherController
        extends Studiengaenge_StudiengangteileController
{
    public function index_action()
    {
        PageLayout::setTitle(_('Verwaltung der Studiengangteile - Studiengangteile gruppiert nach F�chern'));

        $this->initPageParams();
        $this->initSearchParams();
        
        $search_result = $this->getSearchResult('StudiengangTeil');
        
        // Nur Studiengangteile mit zugeordnetem Fach an dessen verantwortlicher
        // Einrichtung der User eine Rolle hat
        $filter['mfi.institut_id'] = MvvPerm::getOwnInstitutes();
        
        //get data
        if (count($search_result)) {
            $this->faecher = Fach::findByIdsStgteile(
                    $search_result,
                    $this->sortby, $this->order,
                    self::$items_per_page,
                    self::$items_per_page * ($this->page - 1), $filter);
            $this->count = count($search_result);
        } else {
            $this->faecher = Fach::getAllEnrichedByStgteile(
                    $this->sortby, $this->order,
                    self::$items_per_page,
                    self::$items_per_page * ($this->page - 1), $filter);
            if (sizeof($this->faecher) == 0) {
                PageLayout::postInfo(_('Es wurden noch keine Studiengangteile angelegt.'));
            }
            $this->count = StudiengangTeil::getCountAssignedFaecher($filter);
        }
        if (!isset($this->fach_id)) {
            $this->fach_id = null;
        }
        $this->show_sidebar_search = true;
        $this->setSidebar();
    }
    
    /**
     * Shows the studiengangteile of a Fach.
     * 
     * @param string $fach_id the id of the Fach
     */
    public function details_fach_action($fach_id)
    {
        $this->fach = Fach::get($fach_id);
        if ($this->fach->isNew()) {
            $this->details_id = null;
        } else {
            $this->details_id = $this->fach->id;
        }
        $this->stgteile = StudiengangTeil::findByFach($this->details_id,
                array('stgteil_id' => $this->getSearchResult('StudiengangTeil')),
                'zusatz,kp', 'ASC');
        if (Request::isXhr()) {
            $this->render_template('studiengaenge/studiengangteile/details_grouped');
        } else {
            $this->perform_relayed('index');
        }
    }
    
    public function stgteil_fach_action($fach_id)
    {
        $fach = Fach::find($fach_id);
        if ($fach) {
            $this->stgteil = StudiengangTeil::get();
            $this->stgteil->assignFach($fach->getId());
        } else {
            throw new Trails_Exception(404);
        }
        $this->perform_relayed('stgteil');
    }
}