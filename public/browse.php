<?php
# Lifter001: TEST
# Lifter002: TEST (mriehe)
/**
 * browse.php
 *
 * Personen-Suche in Stud.IP
 *
 * PHP Version 5
 *
 * @author		Stefan Suchi <suchi@gmx.de>
 * @author		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @copyright 	2000-2009 Stud.IP
 * @license 	http://www.gnu.org/licenses/gpl.html GPL Licence 3
 * @package 	studip_core
 * @access 		public
 */

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
require_once $RELATIVE_PATH_CHAT.'/chat_func_inc.php';

// disable register_globals if set
unregister_globals();

//Basics
$HELP_KEYWORD = 'Basis.SuchenPersonen';
$CURRENT_PAGE = _('Personensuche');

$template = $GLOBALS['template_factory']->open('browse');
$template->set_layout('layouts/base');

/* --- Actions -------------------------------------------------------------- */
//Eine Suche wurde abgeschickt
if (isset($_REQUEST['send_x']))
{
	$vorname = remove_magic_quotes($_REQUEST['vorname']);
	$nachname = remove_magic_quotes($_REQUEST['nachname']);
	$inst_id = preg_replace('/\W/', '', $_REQUEST['inst_id']);
	$sem_id = preg_replace('/\W/', '', $_REQUEST['sem_id']);

	$template->set_attribute('vorname', $vorname);
	$template->set_attribute('nachname', $nachname);
	$template->set_attribute('inst_id', $inst_id);
	$template->set_attribute('sem_id', $sem_id);
}

//Ergebnisse sollen sortiert werden
$sortby_fields = array('Nachname', 'perms', 'status');
$sortby = in_array($_REQUEST['sortby'], $sortby_fields) ? $_REQUEST['sortby'] : 'Nachname';

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

$fields = array($_fullname_sql['full_rev'].' AS fullname', 'username', 'perms', 'auth_user_md5.user_id', get_vis_query().' AS visible');
$tables = array('auth_user_md5', 'LEFT JOIN user_info USING (user_id)');

if ($inst_id) {
	$result = $db->query("SELECT Institut_id FROM user_inst WHERE Institut_id = '".$inst_id."' AND user_id = '$user->id'");

	// entweder wir gehoeren auch zum Institut oder sind global admin
	if ($result->rowCount() > 0 || $perm->have_perm('admin')) {
		$fields[] = 'user_inst.inst_perms';
		$tables[] = 'JOIN user_inst USING (user_id)';
		$filter[] = "user_inst.Institut_id = '".$inst_id."'";
	}
}

if ($sem_id) {
	$result = $db->query("SELECT Seminar_id FROM seminar_user WHERE Seminar_id = '".$sem_id."' AND user_id = '$user->id' $exclude_sem");

	// wir gehoeren auch zum Seminar
	if ($result->rowCount() > 0) {
		$fields[] = 'seminar_user.status';
		$tables[] = 'JOIN seminar_user USING (user_id)';
		$filter[] = "seminar_user.Seminar_id = '".$sem_id."'";
	}
}

// freie Suche
if (strlen($vorname) > 2) {
	$vorname = str_replace('%', '\%', $vorname);
	$vorname = str_replace('_', '\_', $vorname);
	$filter[] = "Vorname LIKE '%".addslashes($vorname)."%'";
}

if (strlen($nachname) > 2) {
	$nachname = str_replace('%', '\%', $nachname);
	$nachname = str_replace('_', '\_', $nachname);
	$filter[] = "Nachname LIKE '%".addslashes($nachname)."%'";
}

if (count($filter))
{
	$query = 'SELECT '.join(',', $fields).' FROM '.join(' ', $tables).' WHERE '.join(' AND ', $filter).' ORDER BY '.$sortby;
	$result = $db->query($query);

	foreach ($result as $row)
	{
		if ($row['visible']) {
			$userinfo = array(
				'username' => $row['username'],
				'fullname' => $row['fullname'],
				'status' => isset($row['status']) ? $row['status'] : $row['perms']
			);

			if (isset($row['inst_perms'])) {
				if ($row['inst_perms'] == 'user') {
					$userinfo['status'] = _('Studierender');
				} else {
					$gruppen = GetRoleNames(GetAllStatusgruppen($inst_id, $row['user_id']));
					$userinfo['status'] = is_array($gruppen) ? join(', ', array_values($gruppen)) : _('keiner Funktion zugeordnet');
				}
			}

			if ($GLOBALS['CHAT_ENABLE']) {
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
?>
