<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
* class to handle content module data
*
* This class handles all data from a single ILIAS content module
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  ilias_interface_modules
* @module       IliasModule
* @package  ILIAS-Interface
*/

class IliasModule
{
    var $id;
    var $title;
    var $description;
    var $module_type;
    var $module_type_name;
    var $author;
    var $make_date;
    var $change_date;
    var $path;
    var $ilias_index;
    var $ilias_version;
    var $allowed_operations;
    var $is_connected;
    var $is_dummy;

    /**
    * constructor
    *
    * init class
    * @access public
    * @param string $module_id module-id
    * @param string $module_type module-type
    * @param string $ilias_index ilias installation index
    */
    function __construct($module_id, $module_data, $ilias_index, $ilias_version)
    {
        $this->id = $module_id;
        $this->title = $module_data['title'];
        $this->description = $module_data['description'];
        $this->ilias_index = $ilias_index;
        $this->module_type = $module_data['type'];
        $this->make_date = $module_data['create_date'];
        $this->change_date = $module_data['last_update'];
        $supported_modules = ConnectedIlias::getSupportedModuleTypes();
        $this->module_type_name = $supported_modules[$this->module_type];
        $this->owner = $module_data['owner'];
        $this->author_studip = false;
        if (is_array($module_data['references'][$module_id]['operations'])) {
            $this->allowed_operations = $module_data['references'][$module_id]['operations'];
        } else {
            $this->allowed_operations = [];
        }
//        var_dump($module_id);
//        var_dump($module_data['references']);
//        var_dump($this->allowed_operations);
/*        if ($module_data['references']) {
            foreach ($module_data['references'] as $ref_id => $reference) {
                if ($reference['ref_id'] == $module_id) {
                    $this->allowed_operations = $reference['operations'];
                }
            }
        } else/**/
/*        if ($module_data['operations']) {
            $this->allowed_operations = $module_data['operations'];
        }/**/
        $this->is_dummy = false;
    }

    /**
    * get id
    *
    * returns id
    * @access public
    * @return string id
    */
    function getId()
    {
        return $this->id;
    }

    /**
    * get ILIAS installation index
    *
    * returns ILIAS installation index
    * @access public
    * @return string cms-type
    */
    function getIndex()
    {
        return $this->ilias_index;
    }

    /**
    * get module-type
    *
    * returns module-type
    * @access public
    * @return string module-type
    */
    function getModuleType()
    {
        return $this->module_type;
    }

    /**
    * get module-type name
    *
    * returns module-type name
    * @access public
    * @return string module-type name
    */
    function getModuleTypeName()
    {
        return $this->module_type_name;
    }

    /**
    * set title
    *
    * sets title
    * @access public
    * @param string $module_title title
    */
    function setTitle($module_title)
    {
        $this->title = $module_title;
    }

    /**
    * get title
    *
    * returns title
    * @access public
    * @return string title
    */
    function getTitle()
    {
        return $this->title;
    }

    /**
    * set description
    *
    * sets description
    * @access public
    * @param string $module_description description
    */
    function setDescription($module_description)
    {
        $this->description = $module_description;
    }

    /**
    * get description
    *
    * returns description
    * @access public
    * @return string description
    */
    function getDescription()
    {
        return $this->description;
    }

    /**
     * get make date
     *
     * returns make date
     * @access public
     * @return string make date
     */
    function getMakeDate()
    {
        return $this->make_date;
    }

    /**
     * get change date
     *
     * returns change date
     * @access public
     * @return string change date
     */
    function getChangeDate()
    {
        return $this->change_date;
    }

    /**
     * get ILIAS path
     *
     * returns ILIAS path
     * @access public
     * @return string ILIAS path
     */
    function getPath()
    {
        return $this->path;
    }

    /**
     * get route
     *
     * returns route for given action
     * @access public
     * @param string action start, edit, view_tools, view_course, add, remove
     * @return string url
     */
    function getRoute($action = '')
    {
        switch ($action) {
            case 'start'       : return 'my_ilias_accounts/redirect/'.$this->ilias_index.'/start/'.$this->id.'/'.$this->module_type;
            case 'edit'        : return 'my_ilias_accounts/redirect/'.$this->ilias_index.'/edit/'.$this->id.'/'.$this->module_type;
            case 'view_tools'  : return 'my_ilias_accounts/view_object/'.$this->ilias_index.'/'.$this->id;
            case 'view_course' : return 'course/ilias_interface/view_object/'.$this->ilias_index.'/'.$this->id;
            case 'add'         : return 'course/ilias_interface/edit_object_assignment/'.$this->ilias_index.'?add_module=1&ilias_module_id='.$this->id;
            case 'remove'      : return 'course/ilias_interface/edit_object_assignment/'.$this->ilias_index.'?remove_module&ilias_module_id='.$this->id;
        }
    }


    /**
     * get permission status
     *
     * returns true if given action is allowed
     * @access public
     * @param string action start, edit
     * @return boolean permission status
     */
    function isAllowed($action = '')
    {
        switch ($action) {
            case 'start' : return in_array('read', $this->allowed_operations);
            case 'edit' : return in_array('write', $this->allowed_operations);
            case 'delete' : return in_array('delete', $this->allowed_operations);
            case 'copy' : return in_array('copy', $this->allowed_operations);
        }
        return true;
    }

    /**
    * set ILIAS author ID
    *
    * sets author
    * @access public
    * @param array $module_authors authors
    */
    function setAuthorIlias($module_author)
    {
        $this->owner = $module_author;
    }

    /**
     * get ILIAS author ID
     *
     * returns author
     * @access public
     * @return string author id
     */
    function getAuthorIlias()
    {
        return $this->owner;
    }

    /**
     * get author Stud.IP-User
     *
     * returns author User object
     * @access public
     * @return object User
     */
    function getAuthorStudip()
    {
        if (is_object($this->author_studip)) {
            return $this->author_studip;
        }

        $query = "SELECT studip_user_id
                  FROM auth_extern
                  WHERE external_user_id = ? AND external_user_system_type = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($this->owner, $this->ilias_index));
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->author_studip = User::find($data['studip_user_id']);
        }

        return $this->author_studip;
    }

    /**
    * set connection
    *
    * sets connection with seminar
    * @access public
    * @param string $seminar_id seminar-id
    * @return boolean successful
    */
    function setConnection($seminar_id)
    {
        $this->is_connected = true;
        return IliasObjectConnections::setConnection($seminar_id, $this->id, $this->module_type, $this->cms_type);
    }

    /**
    * unset connection
    *
    * unsets connection with seminar
    * @access public
    * @param string $seminar_id seminar-id
    * @return boolean successful
    */
    function unsetConnection($seminar_id)
    {
        $this->is_connected = false;
        return IliasObjectConnections::unsetConnection($seminar_id, $this->id, $this->module_type, $this->cms_type);
    }

    /**
    * set connection-status
    *
    * sets connection-status
    * @access public
    * @param boolean $is_connected connection-status
    */
    function setConnectionType($is_connected)
    {
        $this->is_connected = $is_connected;
    }

    /**
    * get connection-status
    *
    * returns true, if module is connected to seminar
    * @access public
    * @return boolean connection-status
    */
    function isConnected()
    {
        return $this->is_connected;
    }

    /**
    * get reference string
    *
    * returns reference string for content-module
    * @access public
    * @return string reference string
    */
    function getReferenceString()
    {
        return $this->ilias_index."_".$this->module_type."_".$this->id;
    }

    /**
    * get icon-image
    *
    * returns icon-image
    * @access public
    * @return string icon-image
    */
    function getIcon()
    {
        if (!$this->icon_file) {
            $this->icon_file = 'learnmodule';
        }
        return Icon::create($this->icon_file, 'inactive', [])->asImg();
    }

    /**
    * get module-status
    *
    * returns true, if module is a dummy
    * @access public
    * @return boolean module-status
    */
    function isDummy()
    {
        return $this->is_dummy;
    }

    /**
    * create module-dummy
    *
    * sets title and description of module to display error-message
    * @access public
    * @param string $error error-type
    */
    function createDummyForErrormessage($error = "unknown")
    {
        global $connected_cms;

        switch($error)
        {
            case "no permission":
                $this->setTitle(_("--- Keine Lese-Berechtigung! ---"));
                $this->setDescription(sprintf(_("Sie haben im System \"%s\" keine Lese-Berechtigung für das Lernmodul, das dieser Veranstaltung / Einrichtung an dieser Stelle zugeordnet ist."), $this->getCMSName()));
                break;
            case "not found":
                $this->setTitle(_("--- Dieses Content-Modul existiert nicht mehr im angebundenen System! ---"));
                $this->setDescription(sprintf(_("Das Lernmodul, das dieser Veranstaltung / Einrichtung an dieser Stelle zugeordnet war, existiert nicht mehr. Dieser Fehler tritt auf, wenn das angebundene LCMS \"%s\" nicht erreichbar ist oder wenn das Lernmodul innerhalb des angebundenen Systems gelöscht wurde."), $this->getCMSName()));
                break;
            case "deleted":
                $this->setTitle(_("--- Dieses Content-Modul wurde im angebundenen System gelöscht! ---"));
                $this->setDescription(sprintf(_("Das Lernmodul, das dieser Veranstaltung / Einrichtung an dieser Stelle zugeordnet war, wurde gelöscht."), $this->getCMSName()));
                break;
            default:
                $this->setTitle(_("--- Es ist ein unbekannter Fehler aufgetreten! ---"));
                $this->setDescription(sprintf(_("Unbekannter Fehler beim Lernmodul mit der Referenz-ID \"%s\" im LCMS \"%s\""), $this->getId(), $this->getCMSName()));
        }

        $this->is_dummy = true;
    }
}
?>
