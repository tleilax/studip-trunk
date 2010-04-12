<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
/**
* ExternModuleTemplateNews.class.php
* 
* 
* 
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModuleTemplateNews
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleTemplateNews.class.php
// 
// Copyright (C) 2007 Peter Thienel <thienel@data-quest.de>,
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


require_once($GLOBALS['RELATIVE_PATH_EXTERN'].'/lib/ExternModule.class.php');
require_once($GLOBALS['RELATIVE_PATH_EXTERN'].'/views/extern_html_templates.inc.php');
require_once('lib/classes/StudipNews.class.php');
require_once('lib/visual.inc.php');
require_once('lib/user_visible.inc.php');
require_once('lib/statusgruppe.inc.php');
require_once($GLOBALS['RELATIVE_PATH_EXTERN'].'/lib/extern_functions.inc.php');


class ExternModuleTemplateNews extends ExternModule {

    var $markers = array();
    var $args = array('seminar_id');
    
    /**
    *
    */
    function ExternModuleTemplateNews ($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {

        $this->data_fields = array();
        $this->registered_elements = array(
                'TemplateGeneric'
        );
        $this->field_names = array();
        
        parent::ExternModule($range_id, $module_name, $config_id, $set_config, $global_id);
    }
    
    function setup () {
        // extend $data_fields if generic datafields are set
    //  $config_datafields = $this->config->getValue("Main", "genericdatafields");
    //  $this->data_fields = array_merge((array)$this->data_fields, (array)$config_datafields);
        
        // setup module properties
    //  $this->elements["LinkIntern"]->link_module_type = 2;
    //  $this->elements["LinkIntern"]->real_name = _("Link zum Modul MitarbeiterInnendetails");
    
        $this->elements['TemplateGeneric']->real_name = _("Template");
        $this->elements['TemplateGeneric']->link_module_type = 2;
    
    }
    
    function toStringEdit ($open_elements = '', $post_vars = '',
            $faulty_values = '', $anker = '') {
        
        $this->elements['TemplateGeneric']->markers = $this->getMarkerDescription('TemplateGeneric');
        
        return parent::toStringEdit($open_elements, $post_vars, $faulty_values, $anker);
    }
    
    function getMarkerDescription ($element_name) {
        $markers['TemplateGeneric'][] = array('__GLOBAL__', '');
        $markers['TemplateGeneric'][] = array('###STUDIP-LINK###','');
        $markers['TemplateGeneric'][] = array('###NEWS-COUNT###', _('Anzahl aller sichtbaren News'));
        $markers['TemplateGeneric'][] = array('###ARCHIV-NEWS-COUNT###', _('Anzahl aller archivierten News'));
        $markers['TemplateGeneric'][] = array('<!-- BEGIN NEWS -->', '');
        $markers['TemplateGeneric'][] = array('<!-- BEGIN NO-NEWS -->', '');
        $markers['TemplateGeneric'][] = array('###NO-NEWS_TEXT###', '');
        $markers['TemplateGeneric'][] = array('<!-- END NO-NEWS -->', '');
        $markers['TemplateGeneric'][] = array('<!-- BEGIN ALL-NEWS -->', _('Alle sichtbaren News'));
        $markers['TemplateGeneric'][] = array('<!-- BEGIN SINGLE-NEWS -->', '');
        $markers['TemplateGeneric'][] = array('###NEWS_DATE###', '');
        $markers['TemplateGeneric'][] = array('###NEWS_TOPIC###', '');
        $markers['TemplateGeneric'][] = array('###NEWS_BODY###', '');
        $markers['TemplateGeneric'][] = array('<!-- BEGIN NEWS_ADMIN-MESSAGE -->', '');
        $markers['TemplateGeneric'][] = array('###NEWS_ADMIN-MESSAGE###', '');
        $markers['TemplateGeneric'][] = array('<!-- END NEWS_ADMIN-MESSAGE -->', '');
        $markers['TemplateGeneric'][] = array('###NEWS_NO###', '');
        $markers['TemplateGeneric'][] = array('###FULLNAME###', _("Vollst�ndiger Name des Autors."));
        $markers['TemplateGeneric'][] = array('###LASTNAME###', _("Nachname des Autors."));
        $markers['TemplateGeneric'][] = array('###FIRSTNAME###', _("Vorname des Autors."));
        $markers['TemplateGeneric'][] = array('###TITLEFRONT###', _("Titel des Autors (vorangestellt)."));
        $markers['TemplateGeneric'][] = array('###TITLEREAR###', _("Titel des Autors (nachgestellt)."));
        $markers['TemplateGeneric'][] = array('###PERSONDETAIL-HREF###', '');
        $markers['TemplateGeneric'][] = array('###USERNAME###', '');
        $markers['TemplateGeneric'][] = array('<!-- BEGIN PERSONDETAIL-LINK -->');
        $markers['TemplateGeneric'][] = array('###LINK_PERSONDETAIL-HREF###', '');
        $markers['TemplateGeneric'][] = array('###LINK_FULLNAME###', '');
        $markers['TemplateGeneric'][] = array('###LINK_LASTNAME###', '');
        $markers['TemplateGeneric'][] = array('###LINK_FIRSTNAME###', '');
        $markers['TemplateGeneric'][] = array('###LINK_TITLEFRONT###', '');
        $markers['TemplateGeneric'][] = array('###LINK_TITLEREAR###', '');
        $markers['TemplateGeneric'][] = array('<!-- END SINGLE-NEWS -->', '');
        $markers['TemplateGeneric'][] = array('<!-- END ALL-NEWS -->', _('Ende aller sichtbaren News'));
        
        $markers['TemplateGeneric'][] = array('<!-- BEGIN ALL-ARCHIV-NEWS -->', _('Alle archivierten News'));
        $markers['TemplateGeneric'][] = array('<!-- BEGIN SINGLE-ARCHIVE-NEWS -->', '');
        $markers['TemplateGeneric'][] = array('###ARCHIV_NEWS_DATE###', '');
        $markers['TemplateGeneric'][] = array('###ARCHIV_NEWS_TOPIC###', '');
        $markers['TemplateGeneric'][] = array('###ARCHIV_NEWS_BODY###', '');
        $markers['TemplateGeneric'][] = array('<!-- BEGIN ARCHIV-NEWS-ADMIN-MESSAGE -->', '');
        $markers['TemplateGeneric'][] = array('###ARCHIV-NEWS_ADMIN-MESSAGE###', '');
        $markers['TemplateGeneric'][] = array('<!-- END ARCHIV-NEWS-ADMIN-MESSAGE -->', '');
        $markers['TemplateGeneric'][] = array('###ARCHIV_NEWS_NO###', '');
        $markers['TemplateGeneric'][] = array('###ARCHIV_FULLNAME###', _("Vollst�ndiger Name des Autors."));
        $markers['TemplateGeneric'][] = array('###ARCHIV_LASTNAME###', _("Nachname des Autors."));
        $markers['TemplateGeneric'][] = array('###ARCHIV_FIRSTNAME###', _("Vorname des Autors."));
        $markers['TemplateGeneric'][] = array('###ARCHIV_TITLEFRONT###', _("Titel des Autors (vorangestellt)."));
        $markers['TemplateGeneric'][] = array('###ARCHIV_TITLEREAR###', _("Titel des Autors (nachgestellt)."));
        $markers['TemplateGeneric'][] = array('###ARCHIV_PERSONDETAIL-HREF###', '');
        $markers['TemplateGeneric'][] = array('###ARCHIV_USERNAME###', '');
        $markers['TemplateGeneric'][] = array('<!-- BEGIN ARCHIV_PERSONDETAIL-LINK -->');
        $markers['TemplateGeneric'][] = array('###ARCHIV_LINK_PERSONDETAIL-HREF###', '');
        $markers['TemplateGeneric'][] = array('###ARCHIV_LINK_FULLNAME###', '');
        $markers['TemplateGeneric'][] = array('###ARCHIV_LINK_LASTNAME###', '');
        $markers['TemplateGeneric'][] = array('###ARCHIV_LINK_FIRSTNAME###', '');
        $markers['TemplateGeneric'][] = array('###ARCHIV_LINK_TITLEFRONT###', '');
        $markers['TemplateGeneric'][] = array('###ARCHIV_LINK_TITLEREAR###', '');
        $markers['TemplateGeneric'][] = array('<!-- END SINGLE-ARCHIVE-NEWS -->', '');
        $markers['TemplateGeneric'][] = array('<!-- END ALL-ARCHIV-NEWS -->', _('Ende aller archivierten News'));
        $markers['TemplateGeneric'][] = array('<!-- END NEWS -->', '');
    
        return $markers[$element_name];
    }
    
    function getContent ($args = NULL, $raw = FALSE) {
        $content = array();
        $db = new DB_Seminar();
        $error_message = "";

        // stimmt die �bergebene range_id?
        $query = "SELECT Name FROM Institute WHERE Institut_id='" . $this->config->range_id . "'";
        $db->query($query);
        if(!$db->next_record()) {
            $error_message = $GLOBALS['EXTERN_ERROR_MESSAGE'];
        }
        
        $local_fullname_sql = $GLOBALS['_fullname_sql'];
        if (!$nameformat = $this->config->getValue('Main', 'nameformat')) {
            $nameformat = 'no_title';
        }
        if ($nameformat == 'last') $local_fullname_sql['last'] = ' Nachname ';

        $news =& StudipNews::GetNewsByRange($this->config->range_id);
        if (!count($news)) {
            $content['NEWS']['NO-NEWS']['NO-NEWS_TEXT'] = $this->config->getValue('Main', "nodatatext");
        }

        $studip_link = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'institut_main.php?auswahl=' . $this->config->range_id;
        $studip_link .= "&redirect_to=admin_news.php&cmd=new_entry&view=inst&new_inst=TRUE&range_id=";
        $studip_link .= $this->config->range_id;
        $content['__GLOBAL__']['STUDIP-LINK'] = $studip_link;

        $dateform = $this->config->getValue("Main", "dateformat");
        $show_date_author = $this->config->getValue("Main", "showdateauthor");
        $i = 1;
        $j = 1;
        foreach ($news as $news_id => $news_detail) {
            //aktuelle News ausgeben
            if (($news_detail['date'] + $news_detail['expire']) >= time())
                {
                list($news_content, $admin_msg) = explode("<admin_msg>", $news_detail['body']);
                if ($news_detail['chdate_uid']){
                    $admin_msg = StudipNews::GetAdminMsg($news_detail['chdate_uid'],$news_detail['chdate']);
                }
                if ($admin_msg) {
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_ADMIN-MESSAGE'] = preg_replace('# \(?(.*)\)?#', '$1', $admin_msg);
                }
                
                if (!$news_content) {
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_BODY'] = _("Keine Beschreibung vorhanden.");
                } else {
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_BODY'] =  ExternModule::ExtFormatReady($news_content);
                }
                
                $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_DATE'] = strftime($dateform, $news_detail['date']);
                $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_TOPIC'] = ExternModule::ExtHtmlReady($news_detail['topic']);
                $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['NEWS_NO'] = $i;
                
                $query = "SELECT Nachname, Vorname, title_front, title_rear, {$local_fullname_sql[$nameformat]} AS fullname, username, aum.user_id FROM auth_user_md5 aum LEFT JOIN user_info ui USING (user_id) WHERE aum.user_id = '{$news_detail['user_id']}'";
                $db->query($query);
                if ($db->next_record()) {
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['FULLNAME'] = ExternModule::ExtHtmlReady($db->f('fullname'));
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['FIRSTNAME'] = ExternModule::ExtHtmlReady($db->f('Vorname'));
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['LASTNAME'] = ExternModule::ExtHtmlReady($db->f('Nachname'));
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['TITLEFRONT'] = ExternModule::ExtHtmlReady($db->f('title_front'));
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['TITLEREAR'] = ExternModule::ExtHtmlReady($db->f('title_rear'));
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['USERNAME'] = $db->f('username');
                    $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['PERSONDETAIL-HREF'] = $this->elements['TemplateGeneric']->createUrl('Persondetails', array('link_args' => 'username=' . $db->f('username')));
                                    
                    if (GetAllStatusgruppen($this->config->range_id, $db->f('user_id'), true)) {
                        $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['PERSONDETAIL-LINK']['LINK_PERSONDETAIL-HREF'] = $this->elements['TemplateGeneric']->createUrl('Persondetails', array('link_args' => 'username=' . $db->f('username')));
                        $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['PERSONDETAIL-LINK']['LINK_FULLNAME'] = ExternModule::ExtHtmlReady($db->f('fullname'));
                        $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['PERSONDETAIL-LINK']['LINK_FIRSTNAME'] = ExternModule::ExtHtmlReady($db->f('Vorname'));
                        $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['PERSONDETAIL-LINK']['LINK_LASTNAME'] = ExternModule::ExtHtmlReady($db->f('Nachname'));
                        $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['PERSONDETAIL-LINK']['LINK_TITLEFRONT'] = ExternModule::ExtHtmlReady($db->f('title_front'));
                        $content['NEWS']['ALL-NEWS']['SINGLE-NEWS'][$i]['PERSONDETAIL-LINK']['LINK_TITLEREAR'] = ExternModule::ExtHtmlReady($db->f('title_rear'));
                    }
                }
                $i++;
            }
            //archivierte News ausgeben
            elseif(($news_detail['date'] + $news_detail['expire']) < time())
            {
                list($news_content, $admin_msg) = explode("<admin_msg>", $news_detail['body']);
                if ($news_detail['chdate_uid']){
                    $admin_msg = StudipNews::GetAdminMsg($news_detail['chdate_uid'],$news_detail['chdate']);
                }
                if ($admin_msg) {
                    $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_NEWS_ADMIN-MESSAGE'] = preg_replace('# \(?(.*)\)?#', '$1', $admin_msg);
                }
                
                if (!$news_content) {
                    $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_NEWS_BODY'] = _("Keine Beschreibung vorhanden.");
                } else {
                    $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_NEWS_BODY'] =  ExternModule::ExtFormatReady($news_content);
                }
                
                $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_NEWS_DATE'] = strftime($dateform, $news_detail['date']);
                $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_NEWS_TOPIC'] = ExternModule::ExtHtmlReady($news_detail['topic']);
                $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_NEWS_NO'] = $j;
                
                $query = "SELECT Nachname, Vorname, title_front, title_rear, {$local_fullname_sql[$nameformat]} AS fullname, username, aum.user_id FROM auth_user_md5 aum LEFT JOIN user_info ui USING (user_id) WHERE aum.user_id = '{$news_detail['user_id']}'";
                $db->query($query);
                if ($db->next_record()) {
                    $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_FULLNAME'] = ExternModule::ExtHtmlReady($db->f('fullname'));
                    $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_FIRSTNAME'] = ExternModule::ExtHtmlReady($db->f('Vorname'));
                    $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_LASTNAME'] = ExternModule::ExtHtmlReady($db->f('Nachname'));
                    $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_TITLEFRONT'] = ExternModule::ExtHtmlReady($db->f('title_front'));
                    $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_TITLEREAR'] = ExternModule::ExtHtmlReady($db->f('title_rear'));
                    $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_USERNAME'] = $db->f('username');
                    $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_PERSONDETAIL-HREF'] = $this->elements['TemplateGeneric']->createUrl('Persondetails', array('link_args' => 'username=' . $db->f('username')));
                                    
                    if (GetAllStatusgruppen($this->config->range_id, $db->f('user_id'), true)) {
                        $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_PERSONDETAIL-LINK']['ARCHIV_LINK_PERSONDETAIL-HREF'] = $this->elements['TemplateGeneric']->createUrl('Persondetails', array('link_args' => 'username=' . $db->f('username')));
                        $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_PERSONDETAIL-LINK']['ARCHIV_LINK_FULLNAME'] = ExternModule::ExtHtmlReady($db->f('fullname'));
                        $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_PERSONDETAIL-LINK']['ARCHIV_LINK_FIRSTNAME'] = ExternModule::ExtHtmlReady($db->f('Vorname'));
                        $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_PERSONDETAIL-LINK']['ARCHIV_LINK_LASTNAME'] = ExternModule::ExtHtmlReady($db->f('Nachname'));
                        $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_PERSONDETAIL-LINK']['ARCHIV_LINK_TITLEFRONT'] = ExternModule::ExtHtmlReady($db->f('title_front'));
                        $content['NEWS']['ALL-ARCHIV-NEWS']['SINGLE-ARCHIVE-NEWS'][$j]['ARCHIV_PERSONDETAIL-LINK']['ARCHIV_LINK_TITLEREAR'] = ExternModule::ExtHtmlReady($db->f('title_rear'));
                    }
                }
                $j++;
            }
        }
        $content['__GLOBAL__']['NEWS-COUNT'] = $i  - 1; 
        $content['__GLOBAL__']['ARCHIV-NEWS-COUNT'] = $j -1;
        return $content;
    }

    function printout ($args) {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);
        
        echo $this->elements['TemplateGeneric']->toString(array('content' => $this->getContent(), 'subpart' => 'NEWS'));
        
    }
    
    function printoutPreview () {
        if (!$language = $this->config->getValue("Main", "language"))
            $language = "de_DE";
        init_i18n($language);
        
        echo $this->elements['TemplateGeneric']->toString(array('content' => $this->getContent(), 'subpart' => 'NEWS', 'hide_markers' => FALSE));
        
    }
    
}

?>
