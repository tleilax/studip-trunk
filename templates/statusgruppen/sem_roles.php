<?
# Lifter010: TODO
?>
<?

$roles_pos = 1;
if (!isset($all_roles)) $all_roles = $roles;
if (is_array($roles)) foreach ($roles as $id => $role) :
    $topic_class = 'table_header_bold';
    if ($edit_role == $id) $topic_class = 'red_gradient';

// get dialog
URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
$mp = MultiPersonSearch::get("contacts_statusgroup_" . $id)
        ->setLinkText("")
        ->setDefaultSelectedUser(array_keys(getPersonsForRole($id)))
        ->setTitle(_('Personen eintragen'))
        ->setExecuteURL(URLHelper::getLink("admin_statusgruppe.php"))
        ->setSearchObject($search_obj)
        ->addQuickfilter(_("Veranstaltungsteilnehmende"), $quickfilter_sem)
        ->addQuickfilter(_("MitarbeiterInnen"), $quickfilter_inst) 
        ->addQuickfilter(_("Personen ohne Gruppe"), $quickfilter_sem_no_group)
        ->render();
?>
<a name="<?= $id ?>" ></a>
<table class="default" cellspacing="0" cellpadding="0" border="0">
<tr>
    <th></th>
    <th>
        <?= htmlReady($role['role']->getName()) ?>
    </th>
    <th width="5%" align="right" colspan="3" nowrap>
        <? if ($role['role']->hasFolder()) :
            echo Assets::img('icons/16/grey/files.png', array('title' => _("Dateiordner vorhanden")));
        endif; ?>
        <? if ($role['role']->getSelfAssign()) :
            echo Assets::img('icons/16/grey/person.png', array('title' => _("Personen k�nnen sich dieser Gruppe selbst zuordnen")));
        endif; ?>
        <?= $mp; ?>
        <a href="<?= URLHelper::getLink('?cmd=editRole&role_id='.  $id) ?>">
            <?= Assets::img('icons/16/blue/edit.png', array('title' => _("Gruppe bearbeiten"))) ?>
        </a>
        <a href="<?= URLHelper::getLink('?cmd=sortByName&role_id='.  $id) ?>">
            <?= Assets::img('icons/16/blue/arr_eol-down.png', array('title' => _("Personen dieser Gruppe alphabetisch sortieren"))) ?>
        </a>
    </th>
    <th width="1%" align="right">
        <a href="<?= URLHelper::getLink('?cmd=deleteRole&role_id='. $id) ?>">
            <?= Assets::img('icons/16/red/trash.png', array('title' => _("Gruppe mit Personenzuordnung entfernen"))) ?>
        </a>
    </th>
</tr>
<?
    $pos = 0;
    $persons = getPersonsForRole($id);
?>
<!-- Persons assigned to this role -->
<? if (is_array($persons)) foreach ($persons as $person) :
            $pos ++;
?>
<tr>
    <td width="1%" nowrap>
        <?= $pos ?>
    </td>

    <td>
        <a href="<?= URLHelper::getLink('dispatch.php/profile?username='. $person['username'] ) ?>">
            <?= htmlReady($person['fullname']) ?>
        </a>
    </td>

    <td >&nbsp;</td>
    <td width="1%" nowrap>
        <? if ($pos < sizeof($persons)) : ?>
        <a href="<?= URLHelper::getLink('?cmd=move_down&role_id='. $id .'&username='. $person['username']) ?>">
            <?= Assets::img('icons/16/yellow/arr_2down.png', array('title' => _("Person eine Position nach unten platzieren"))) ?>
        </a>
        <? endif; ?>
    </td>

    <td width="1%" nowrap style="padding-left: 4px">
        <? if ($pos > 1) : ?>
        <a href="<?= URLHelper::getLink('?cmd=move_up&role_id='. $id .'&username='. $person['username']) ?>">
            <?= Assets::img('icons/16/yellow/arr_2up.png', array('title' => _("Person einen Position nach oben platzieren"))) ?>
        </a>
        <? endif; ?>
    </td>

    <td width="1%" align="right">
        <a href="<?= URLHelper::getLink('?role_id='. $id .'&cmd=removePerson&username='. $person['username'])  ?>">
        <?= Assets::img('icons/16/blue/trash.png', array('title' => _("Gruppenzuordnung f�r diese Person aufheben"))) ?>
        </a>
    </td>
</tr>
<? endforeach; ?>

<!-- fill up to group size with empty roles -->
<? for ($i = $pos + 1; $i <= $role['role']->getSize(); $i++) : ?>
<tr>
    <td colspan="6">
        <span style="color:red"><?= $i ?></span>
    </td>
</tr>
<? endfor; ?>

<? if (sizeof($roles) > $roles_pos) : ?>
<tr>
    <td colspan="6" align="center">
        <br>
        <a href="<?= URLHelper::getLink('?cmd=swapRoles&role_id='. $id) ?>">
            <?= Assets::img('icons/16/yellow/arr_2up.png') ?>
            <?= Assets::img('icons/16/yellow/arr_2down.png') ?>
        </a><br>
        <br>
    </td>
</tr>
<? endif; ?>

</table>

<?
$roles_pos++;
endforeach;
