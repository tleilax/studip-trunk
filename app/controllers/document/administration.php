<?
/**
 * Document_AdministrationController
 *
 * @author      Stefan Osterloh s.osterloh@uni-oldenburg.de
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// administration.php
//
// Copyright (C) 2013 s.osterloh@uni-oldenburg.de
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

require_once 'app/controllers/authenticated_controller.php';

class Document_AdministrationController extends AuthenticatedController {

    public function before_filter(&$action, &$args) 
    {
        parent::before_filter($action, $args);
        Navigation::activateItem('/admin/config/document_area');
        PageLayout::setTitle(_('Dateibereich') . ' - ' . _('Administration'));
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        $this->getInfobox();

    }
    
    public function index_action($group=NULL)
    {
        PageLayout::addScript('ui.multiselect.js');
        PageLayout::addStylesheet('jquery-ui-multiselect.css');
        $types = DocFiletype::findBySQL('id IS NOT NULL ORDER BY type');
        if(empty($types)){
            $viewData['types'] = array();
        }else{
            $viewData['types'] = $types;
        }
        $viewData['configEdit'] = array();
        $viewData['configAll'] =  DocUsergroupConfig::getGroupConfigAll();
        $data=$viewData['configAll'];
        for($i=0;$i<count($data);$i++){
            $value = $data[$i];
            $value['upload']=$this->sizeInUnit($value['upload'], $value['upload_unit']);
            $value['quota']=$this->sizeInUnit($value['quota'], $value['quota_unit']);
            $data[$i]= $value;
        }        
        $viewData['configAll']=$data;
        //Load old config-data for usergroup by ID
        if($group!= NULL){
            $data = DocUsergroupConfig::getGroupConfig($group);
            $data['upload'] = $this->sizeInUnit($data['upload'], $data['upload_unit']);
            $data['quota'] = $this->sizeInUnit($data['quota'], $data['quota_unit']);         
            $viewData['configEdit'] = $data;
        }
        $this->viewData=$viewData;
    }    
    
    function store_action()
    {
        if (Request::float('upload') && Request::float('quota') && Request::get('usergroup') != '') {
            $data['id'] = '';
            $data['usergroup'] = Request::get('usergroup');
            $data['upload_quota'] = $this->sizeInByte(Request::float('upload'), Request::get('unitUpload'));
            $data['quota'] = $this->sizeInByte(Request::float('quota'), Request::get('unitQuota'));
            $data['is_group_config'] = 1;
            if ($data['upload_quota'] <= $data['quota'] && $data['quota'] >= 0 && $data['upload_quota'] >= 0) {
                $data['upload_forbidden'] =  '0';
                $data['quota_unit'] = Request::get('unitQuota');
                $data['upload_unit'] = Request::get('unitUpload');
                if(Request::get('forbidden')=='on' || $data['upload_quota'] == 0) {
                    $data['upload_forbidden'] = '1';
                }
                $data['datetype_id'] = Request::intArray('datetype');
                if(DocUsergroupConfig::setConfig($data)){
                    $message = 'Das Speichern der Einstellungen war erfolgreich. ';
                         if($data['upload_quota']== 0){
                            $message .= 'Der Upload wurde gesperrt, da der max. Upload '.
                                     Request::get('upload').' ' .Request::get('unitUpload').' beträgt';
                         }
                    PageLayout::postMessage(MessageBox::success($message));     
                }else{
                    PageLayout::postMessage(MessageBox::error(_(
                            'Beim speichern der Einstellungen ist ein Fehler aufgetreten'.
                            ' oder es wurden keine Änderungen vorgenommen.')));
                }
            }else{
                PageLayout::postMessage(MessageBox::error(_(
                        'Upload-Quota ist größer als das gesamte Nutzer-Quota. Bitte korrigieren Sie Ihre Eingabe.')));
            }
        }else{
             PageLayout::postMessage(MessageBox::error(_(
                     'Es wurden fehlerhafte Werte für die Quota eingegeben oder es wurde keine Nutzergruppe ausgewählt.')));
        } 
        $this->redirect('document/administration/index/');
    }
    
    /*
     * $id represents the value for the primarykey in the Database
     * $type represents the kind of configuration. individual oder group-config
     */
    public function delete_action($id, $type)
    {
        $db = DBManager::get();
        DocUsergroupConfig::deleteBySQL('usergroup = ' .$db->quote($id));
        DocFileTypeForbidden::deleteBySQL('usergroup = ' .$db->quote($id));
        if($type=='groupConfig'){
            $this->redirect('document/administration/index/');   
        }else if($type =='userConfig'){
            $this->redirect('document/administration/individual/'.$id); 
        }     
    }
    
    /*
    @Deprecated
     * function replaced (delete_action($id,$type))
     */
    public function individualDelete_action($user_id)
    {
        $db = DBManager::get();
        DocUsergroupConfig::deleteBySQL('usergroup = ' .$db->quote($user_id));
        DocFileTypeForbidden::deleteBySQL('usergroup = ' .$db->quote($user_id));
        $this->redirect('document/administration/individual/'.$user_id); 
    }
    
    public function individual_action($user_id = null) 
    {
        $users = array();
        if ($user_id != null) {
            $users = DocUsergroupConfig::searchForUser(array('user_id' => $user_id));
        }
        if (Request::submitted('search')) {
            $data['username'] = Request::get('userName');
            $data['Vorname'] = Request::get('userVorname');
            $data['Nachname'] = Request::get('userNachname');
            $data['Email'] = Request::get('userMail');
            if (Request::get('userGroup') != 'alle') {
                $data['perms'] = Request::get('userGroup');
            }
            $users = DocUsergroupConfig::searchForUser($data);
        }
        $userSetting = array();

        foreach ($users as $u) {
            $config = DocUsergroupConfig::getGroupConfig($u['user_id']);
            $foo = array();
            foreach ($u as $key => $value) {
                $foo[$key] = $value;
            }
            if (empty($config)) {
                $foo['upload'] = 'keine individuelle Einstellung';
                $foo['upload_unit'] = '';
                $foo['quota'] = 'keine individuelle Einstellung';
                $foo['quota_unit'] = '';
                $foo['forbidden'] = 0;
                $foo['area_close'] = 0;
                $foo['types'] = array();
                $foo['deleteIcon'] = 0;
                $userSetting[] = $foo;
            } else {
                $foo['upload'] = $this->sizeInUnit($config['upload'], $config['upload_unit']);
                $foo['upload_unit'] = $config['upload_unit'];
                $foo['quota'] = $this->sizeInUnit($config['quota'], $config['quota_unit']);
                $foo['quota_unit'] = $config['quota_unit'];
                $foo['forbidden'] = $config['forbidden'];
                $foo['area_close'] = $config['area_close'];
                $foo['types'] = $config['types'];
                $foo['deleteIcon'] = 1;
                $userSetting[] = $foo;
            }
        }
        $viewData['users'] = $userSetting;
        $this->viewData = $viewData;
    }
        
    public function individualEdit_action($user_id)
    {
        PageLayout::addScript('ui.multiselect.js');
        PageLayout::addStylesheet('jquery-ui-multiselect.css');
        $viewData['types'] = DocFiletype::findBySQL('id IS NOT NULL ORDER BY type'); 
        $viewData['userInfo'] = DocUsergroupConfig::getUser($user_id);
        $userConfig = DocUsergroupConfig::getUserConfig($user_id);
        if($userConfig['name'] != $user_id){
            $viewData['groupConfig'] = $userConfig['name'];
        }else{ 
            $viewData['groupConfig'] = 'individuell';
        }
        if(empty($userConfig)){
            $viewData['userConfig']=array();
        }else{
            $userConfig['upload']=$this->sizeInUnit($userConfig['upload'], $userConfig['upload_unit']);
            $userConfig['quota']= $this->sizeInUnit($userConfig['quota'], $userConfig['quota_unit']);
            $viewData['userConfig'] = $userConfig;
        }
        $this->viewData=$viewData;
    }
    
    public function storeIndividual_action($user_id)
    {
        if (Request::float('upload') && Request::float('quota')) {
            $data['id'] = '';
            $data['usergroup'] = $user_id;
            $data['upload_quota'] = $this->sizeInByte(Request::float('upload'), Request::get('unitUpload'));
            $data['quota'] = $this->sizeInByte(Request::float('quota'), Request::get('unitQuota'));
            $data['is_group_config'] = 0;
            if ($data['upload_quota'] <= $data['quota'] && $data['quota'] >= 0 && $data['upload_quota'] >= 0) {
                $data['upload_forbidden'] = '0';
                $data['quota_unit'] = Request::get('unitQuota');
                $data['upload_unit'] = Request::get('unitUpload');
                if (Request::get('forbidden') == 'on' || $data['upload_quota'] == 0) {
                    $data['upload_forbidden'] = '1';
                }
                if (Request::get('close') == 'on') {
                    $data['area_close'] = '1';
                } else {
                    $data['area_close'] = '0';
                }
                $data['area_close_text'] = trim(Request::get('closeText'));
                $data['datetype_id'] = Request::intArray('datetype');
                if(DocUsergroupConfig::setConfig($data)){
                         $message = 'Das Speichern der Einstellungen war erfolgreich. ';
                         if($data['upload_quota']== 0){
                             $message .= 'Der Upload wurde gesperrt, da der max. Upload '.
                                     Request::get('upload').' ' .Request::get('unitUpload').' beträgt';
                         }
                         PageLayout::postMessage(MessageBox::success($message));
                }else{
                    PageLayout::postMessage(MessageBox::error(
                            'Beim speichern der Einstellungen ist ein Fehler aufgetreten'.
                            ' oder es wurden keine Änderungen vorgenommen.'));
                }
                $this->redirect('document/administration/individual/' . $user_id);
            } else {
                PageLayout::postMessage(MessageBox::error(
                        'Upload-Quota ist größer als das gesamte Nutzer-Quota. Bitte korrigieren Sie Ihre Eingabe.'));
                $this->redirect('document/administration/individualEdit/' . $user_id);
            }
        } else {
            PageLayout::postMessage(MessageBox::error(
                    'Es wurden fehlerhafte Werte für die Quota eingegeben.'));
            $this->redirect('document/administration/individualEdit/' . $user_id);
        }
    }
    
    
    public function sizeInByte($size, $unit)
    {
        $byte = 0;
        switch ($unit) {
            case 'kB' :
                $byte = $size * 1024;
                break;
            case 'MB':
                $byte = $size * 1048576;
                break;
            case'GB':
                $byte = $size * 1073741824;
                break;
            case 'TB':
                $byte = $size * 1099511627776;
                break;
        }
        return $byte;
    }
    
    /*
     * TODO
     * Wenn relsize() aus functions.php mit float umgehen kann
     * dann kommt diese Funktion raus
     */
    public function sizeInUnit($byte, $unit) 
    {
        $size = 0;
        switch ($unit) {
            case 'kB' :
                $size = $byte / 1024;
                break;
            case 'MB':
                $size = $byte / 1048576;
                break;
            case'GB':
                $size = $byte / 1073741824;
                break;
            case 'TB':
                $size = $byte / 1099511627776;
                break;
        }
        return $size;
    }
    
    //Infobox erstellen mit Navigation ->erweiterbarkeit
    function getInfobox()
    {
        $this->setInfoboxImage(Assets::image_path('infobox/config.jpg'));
        $default = sprintf('<a href="%s">%s</a>', $this->url_for('document/administration/index'), _('Nutzergruppen-Einstellungen'));
        $individual = sprintf('<a href="%s">%s</a>', $this->url_for('document/administration/individual'), _('individuelle Einstellungen'));
        $this->addToInfobox(_('Aktionen'), $default, 'icons/16/black/link-intern');
        $this->addToInfobox(_('Aktionen'), $individual, 'icons/16/black/link-intern');
        $this->addToInfobox(_('Hinweis'), _('Diese Ebene der Einstellungen ermöglicht es Ihnen den persönlichen Dateibereich zu konfigurieren'));
    }
}