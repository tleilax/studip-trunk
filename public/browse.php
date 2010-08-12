<?php
# Lifter001: TEST
# Lifter007: TODO
/**
 * browse.php - Personensuche in Stud.IP
 *
 * @author      Stefan Suchi <suchi@gmx.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl.html GPL Licence 2
 * @package     studip_core
 */


require '../lib/bootstrap.php';

page_open(array(
    'sess' => 'Seminar_Session',
    'auth' => 'Seminar_Default_Auth',
    'perm' => 'Seminar_Perm',
    'user' => 'Seminar_User'
));
$perm->check('user');

require_once 'lib/seminar_open.php';
require_once 'lib/visual.inc.php';
require_once 'lib/functions.php';
require_once 'lib/statusgruppe.inc.php';
require_once 'lib/user_visible.inc.php';
require_once 'lib/classes/Avatar.class.php';
require_once $RELATIVE_PATH_CHAT.'/chat_func_inc.php';

// disable register_globals if set
unregister_globals();

//Basics
PageLayout::setHelpKeyword('Basis.SuchenPersonen');
PageLayout::setTitle(_('Personensuche'));
Navigation::activateItem('/search/users');

$template = $GLOBALS['template_factory']->open('browse');
$template->set_layout('layouts/base');

/* --- Actions -------------------------------------------------------------- */

if (!Request::submitted('reset')) {
    $name = Request::get('name_parameter');
    $inst_id = Request::option('inst_id');
    $sem_id = Request::option('sem_id');
}

//Eine Suche wurde abgeschickt
if (isset($name))
{
    $template->set_attribute('name', $name);
    $template->set_attribute('inst_id', $inst_id);
    $template->set_attribute('sem_id', $sem_id);
}

//Ergebnisse sollen sortiert werden
$sortby_fields = array('perms', 'status');
$sortby = Request::option('sortby');
$sortby = in_array($sortby, $sortby_fields) ? "$sortby, Nachname, Vorname" : 'Nachname, Vorname';

/* --- Search --------------------------------------------------------------- */
$db = DBManager::get();

// print success message when returning from sms_send.php
if ($sms_msg)
{
    $template->set_attribute('sms_msg', $sms_msg);
    $sms_msg = '';
    $sess->unregister('sms_msg');
}

// exclude AUTO_INSERT_SEM courses
if (count($GLOBALS['AUTO_INSERT_SEM'])) {
    $exclude_sem = "AND Seminar_id NOT IN ('".join("','", $GLOBALS['AUTO_INSERT_SEM'])."')";
} else {
    $exclude_sem = '';
}

//List of Institutes
if ($perm->have_perm('admin'))
{
    $query = 'SELECT * FROM Institute WHERE (Institute.modules & 16) ORDER BY name';
}
else
{
    $query = "SELECT * FROM user_inst LEFT JOIN Institute USING (institut_id) WHERE user_id = '$user->id' AND (Institute.modules & 16) ORDER BY name";
}

$result = $db->query($query);

foreach ($result as $row)
{
    $institutes[] = array('id' => $row['Institut_id'], 'name' => my_substr($row['Name'], 0, 40));
}

//List of Seminars
if (!$perm->have_perm('admin'))
{
    $result = $db->query("SELECT * FROM seminar_user LEFT JOIN seminare USING (Seminar_id) WHERE user_id = '$user->id' AND (seminare.modules & 8) $exclude_sem ORDER BY Name");

    foreach ($result as $row)
    {
        $courses[] = array('id' => $row['Seminar_id'], 'name' => my_substr($row['Name'], 0, 40));
    }
}

$template->set_attribute('institutes', $institutes);
$template->set_attribute('courses', $courses);

/* --- Results -------------------------------------------------------------- */

$fields = array('username', $_fullname_sql['full_rev'].' AS fullname', 'perms', 'auth_user_md5.user_id', get_vis_query('auth_user_md5', 'search').' AS visible');
$tables = array('auth_user_md5', 'LEFT JOIN user_info USING (user_id)', 'LEFT JOIN user_visibility USING (user_id)');
$arguments = array();

//Admin-Abfrage
$fields[] = 'user_inst.inst_perms';
$tables[] = 'JOIN user_inst USING (user_id)';
$filter[] = "IF(:inst_id != '0', user_inst.Institut_id = :inst_id, TRUE)";
$filter[] = "user_inst.inst_perms != 'user'";
$arguments[":inst_id"] = $inst_id;


//Admin-Abfrage
$fields[] = 'seminar_user.status';
$tables[] = 'JOIN seminar_user USING (user_id)';
$filter[] = "IF(:sem_id != '0', seminar_user.Seminar_id = :sem_id, TRUE)";
$arguments[":sem_id"] = $sem_id;

// freie Suche
$name = str_replace('%', '\%', $name);
$name = str_replace('_', '\_', $name);
$filter[] = "IF(:input != '', CONCAT(Vorname, ' ', Nachname, ', ', Vorname, ',', Nachname, ',', Vorname) LIKE :input, TRUE)";
$arguments[":input"] = "%".addslashes($name)."%";

$query = 'SELECT '.join(',', $fields).' ' .
        'FROM '.join(' ', $tables).' ' .
        'WHERE '.join(' AND ', $filter).' ' .
        'GROUP BY user_id ' .
        'ORDER BY '.$sortby;
$search_object = new SQLSearch($query, "", "username");
$template->set_attribute('search_object', $search_object);

if ($name || $inst_id || $sem_id) 
{
    $statement = $db->prepare($query);
    $statement->execute($arguments);
    $result = $statement->fetchAll();

    foreach ($result as $row)
    {
        if ($row['visible']) {
            $userinfo = array(
                'user_id' => $row['user_id'],
                'username' => $row['username'],
                'fullname' => $row['fullname'],
                'status' => isset($row['status']) ? $row['status'] : $row['perms']
            );

            if (isset($row['inst_perms'])) {
                $gruppen = GetRoleNames(GetAllStatusgruppen($inst_id, $row['user_id']));
                $userinfo['status'] = is_array($gruppen) ? join(', ', array_values($gruppen)) : _('keiner Funktion zugeordnet');
            }

            if (get_config('CHAT_ENABLE')) {
                $userinfo['chat'] = chat_get_online_icon($row['user_id'], $row['username']);
            }

            $users[] = $userinfo;
        }
    }

    $template->set_attribute('users', $users);
}

/* --- View ----------------------------------------------------------------- */

echo $template->render();
page_close();

