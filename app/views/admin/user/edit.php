<? if ($flash['delete']) : ?>
<?= $this->render_partial("admin/user/_delete", array('data' => $flash['delete'])) ?>
<? endif ?>

<h2>
    <?= _('Benutzerverwaltung f�r ') ?><?= htmlReady($user['Vorname']) ?> <?= htmlReady($user['Nachname']) ?>
    <?= ($user['locked']) ? '<br><span style="color: red">(' . _('gesperrt von') . ' ' . get_fullname($user['locked_by']) : '' ?>
    <?= ($user['lock_comment']) ? ', Kommentar: '. $user['lock_comment'] : '' ?>
    <?= ($user['locked']) ? ')</span>' : '' ?>
</h2>

<form method="post" action="<?= $controller->url_for('admin/user/edit/' . $user['user_id']) ?>">
<?= CSRFProtection::tokenTag() ?>
<table class="default collapsable">
<tbody>
    <tr class="steel header-row">
        <td colspan="3" class="toggle-indicator">
            <a class="toggler"><b><?= _('Allgemeine Daten') ?></b></a>
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Benutzername:") ?>
        </td>
        <td colspan="2">
        <? if (StudipAuthAbstract::CheckField("auth_user_md5.username", $user['auth_plugin'])) : ?>
            <?= htmlReady($user['username']) ?>
        <? else : ?>
            <input class="user_form" type="text" name="username" value="<?= $user['username'] ?>">
        <? endif ?>
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("globaler Status:") ?>
        </td>
        <td colspan="2">
            <?= (StudipAuthAbstract::CheckField("auth_user_md5.perms", $user['auth_plugin'])) ? $user['perms'] : $perm->perm_sel("perms", $user['perms']) ?>
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Sichtbarkeit:") ?>
        </td>
        <td colspan="2">
            <?= vis_chooser($user['visible']) ?> <small>(<?= $user['visible'] ?>)</small>
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Vorname:") ?>
        </td>
        <td colspan="2">
        <? if (StudipAuthAbstract::CheckField("auth_user_md5.Vorname", $user['auth_plugin'])) : ?>
            <?=  htmlReady($user['Vorname']) ?>
        <? else : ?>
            <input class="user_form" type="text" name="Vorname" value="<?= htmlReady($user['Vorname']) ?>">
        <? endif ?>
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Nachname:") ?>
        </td>
        <td colspan="2">
        <? if (StudipAuthAbstract::CheckField("auth_user_md5.Nachname", $user['auth_plugin'])) : ?>
            <?= htmlReady($user['Nachname']) ?>
        <? else : ?>
            <input class="user_form" type="text" name="Nachname" value="<?= htmlReady($user['Nachname']) ?>">
        <? endif ?>
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Geschlecht:") ?>
        </td>
        <td colspan="2">
            <input type="radio"<?= (!$user['geschlecht']) ? ' checked' : '' ?> name="geschlecht" value="0"><?= _("unbekannt") ?>
            <input type="radio"<?= ($user['geschlecht'] == 1) ? ' checked' : '' ?> name="geschlecht" value="1"><?= _("m�nnlich") ?>
            <input type="radio"<?= ($user['geschlecht'] == 2) ? ' checked' : '' ?> name="geschlecht" value="2"><?= _("weiblich") ?>
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Titel:") ?>
        </td>
        <td colspan="2">
            <select name="title_front_chooser" onchange="jQuery('input[name=title_front]').val( jQuery(this).val() );">
            <? foreach(get_config('TITLE_FRONT_TEMPLATE') as $title) : ?>
                <option value="<?= $title ?>" <?= ($title == $user['title_front']) ? 'selected' : '' ?>><?= $title ?></option>
            <? endforeach ?>
            </select>
            <input class="user_form" type="text" name="title_front" value="<?= htmlReady($user['title_front']) ?>">
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?=_("Titel nachgestellt:") ?>
        </td>
        <td colspan="2">
            <select name="title_rear_chooser" onchange="jQuery('input[name=title_rear]').val( jQuery(this).val() );">
            <? foreach(get_config('TITLE_REAR_TEMPLATE') as $rtitle) : ?>
                <option value="<?= $rtitle ?>" <?= ($rtitle == $user['title_rear']) ? 'selected' : '' ?>><?= $rtitle ?></option>
            <? endforeach ?>
            </select>
            <input class="user_form" type="text" name="title_rear" value="<?= htmlReady($user['title_rear']) ?>">
        </td>
    </tr>
</tbody>
<tbody>
    <tr class="steel header-row">
        <td colspan="3" class="toggle-indicator">
            <a class="toggler"><b><?= _('Registrierungsdaten') ?></b></a>
        </td>
    </tr>

    <? if (!$user['locked']) : ?>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Benutzer sperren:") ?>
        </td>
        <td colspan="2">
            <input type="checkbox" name="locked" value="1">
            <?= _('Kommentar:') ?> <input class="user_form" name="locked_comment" type="text">
        </td>
    </tr>
    <? endif ?>

    <? if ($perm->have_perm('root') && get_config('ALLOW_ADMIN_USERACCESS') && !StudipAuthAbstract::CheckField("auth_user_md5.password", $user['auth_plugin'])) : ?>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Neues Passwort:") ?>
        </td>
        <td colspan="2">
            <input class="user_form" name="pass_1" type="password" id="pass_1">
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Passwortwiederholung:") ?>
        </td>
        <td colspan="2">
            <input class="user_form" name="pass_2" type="password" id="pass_2" onkeyup="jQuery('#pw_success').toggle( jQuery('#pass_1').val()==$('#pass_2').val() )">
            <span id="pw_success" style="display: none;"><?= Assets::img('icons/16/green/accept.png')?></span>
        </td>
    </tr>
    <? endif ?>

    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("E-Mail:") ?>
        </td>
        <td colspan="2">
        <? if (StudipAuthAbstract::CheckField("auth_user_md5.Email", $auth_plugin)) : ?>
            <?= htmlReady($db->f("Email")) ?>
        <? else : ?>
            <input class="user_form" type="text" name="Email" value="<?= htmlReady($user['Email']) ?>">
            <? if ($GLOBALS['MAIL_VALIDATE_BOX']) : ?>
                <input type="checkbox" id="disable_mail_host_check" name="disable_mail_host_check" value="1">
                <label for="disable_mail_host_check"><?= _("Mailbox�berpr�fung deaktivieren") ?></label>
            <? endif ?>
        <? endif ?>
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("inaktiv seit:") ?>
        </td>
        <td colspan="2">
        <? if ($user["changed_timestamp"] != "") :
            $inactive = time() - $user['changed_timestamp'];
            if ($inactive < 3600 * 24) {
                $inactive = date('H:i:s', $inactive);
            } else {
                $inactive = floor($inactive / (3600 * 24)).' '._('Tage');
            }
        else :
            $inactive = _("nie benutzt");
        endif ?>
        <?= $inactive ?>
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("registriert seit:") ?>
        </td>
        <td colspan="2">
            <?= ($user["mkdate"]) ? date("d.m.Y", $user["mkdate"]) : _('unbekannt') ?>
        </td>
    </tr>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Authentifizierung:") ?>
        </td>
        <td colspan="2">
            <select name="auth_plugin">
            <? foreach ($GLOBALS['STUDIP_AUTH_PLUGIN'] as $val): ?>
                <option value="<?= strtolower($val) ?>" <?= strcasecmp($val, $user['auth_plugin']) == 0 ? 'selected' : '' ?>><?= $val ?></option>
            <? endforeach ?>
            </select>
        </td>
    </tr>

    <? if ($user['validation_key']) : ?>
    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?=_("Validation-Key:")?>
        </td>
        <td colspan="2">
            <?= htmlReady($user['validation_key']) ?>
            <input type="checkbox" name="delete_val_key" value="1"> <?= _('l�schen') ?>
        </td>
    </tr>
    <? endif ?>

    <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
        <td>
            <?= _("Ablaufdatum:") ?>
        </td>
        <td colspan="2">
            <input id="datepicker" class="user_form" type="text" name="expiration_date" value="<?= date('d.m.Y', UserConfig::get($user['user_id'])->EXPIRATION_DATE) ?>">
            <input type="checkbox" onchange="jQuery('input[name=expiration_date]').val('');" name="expiration_date_delete" value="1"> <?= _('l�schen') ?>
        </td>
    </tr>
</tbody>

<? if (in_array($user['perms'], array('autor', 'tutor', 'dozent'))) : ?>
<tbody>
    <tr class="steel header-row">
        <td colspan="3" class="toggle-indicator">
            <a class="toggler"><b><?= _('Studiendaten') ?></b></a>
        </td>
    </tr>
    <? if (!StudipAuthAbstract::CheckField("studiengang_id", $auth_plugin)) : ?>
    <tr class="steel1">
        <td>
            <?= _('Neuer Studiengang')?>
        </td>
        <td colspan="2">
            <? $about->select_studiengang() ?>
            <? $about->select_abschluss() ?>
            <select name="fachsem">
            <? for ($s=1; $s < 51; $s++) : ?>
                <option><?= $s ?></option>
            <? endfor ?>
            </select>
        </td>
    </tr>
    <? endif ?>
    <? if (count($studycourses) > 0) : ?>
    <? foreach ($studycourses as $i => $studiengang) : ?>
    <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
        <td>
            <?= $i+1 ?>. <?= _('Studiengang')?>
        </td>
        <td>
            <?= htmlReady($studiengang['fach']) ?>,
            <?= htmlReady($studiengang['abschluss']) ?>,
            <?= $studiengang['semester'] ?>. <?= _('Fachsemester') ?>
        </td>
        <td align="right">
            <a href="<?= $controller->url_for('admin/user/delete_studycourse/' . $user['user_id'] . '/' . $studiengang['fach_id'] . '/' . $studiengang['abschluss_id']) ?>">
                <?= Assets::img('icons/16/blue/trash.png', array('class' => 'text-top', 'title' => _('Diesen Studiengang l�schen'))) ?>
            </a>
        </td>
    </tr>
    <? endforeach ?>
    <? endif ?>
    <tr class="steel1">
        <td>
            <?= _('Neue Einrichtung')?>
        </td>
        <td colspan="2">
            <select name="new_student_inst">
                <option selected="selected" value="none"><?= _('-- Bitte Einrichtung ausw�hlen --') ?></option>
                <? foreach ($available_institutes as $i) : ?>
                <option value="<?= $i['Institut_id'] ?>"><?= htmlReady(my_substr($i['Name'], 0, 50)) ?></option>
                <? endforeach ?>
            </select>
        </td>
    </tr>
    <? if (count($student_institutes) > 0) : ?>
    <? foreach ($student_institutes as $i => $institute) : ?>
    <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
        <td>
            <?= $i+1 ?>. <?= _('Einrichtung')?>
        </td>
        <td>
            <?= htmlReady($institute['Name']) ?>
        </td>
        <td align="right">
            <a href="<?= $controller->url_for('admin/user/delete_institute/' . $user['user_id'] . '/' . $institute['Institut_id']) ?>">
                <?= Assets::img('icons/16/blue/trash.png', array('class' => 'text-top', 'title' => _('Diese Einrichtung l�schen'))) ?>
            </a>
        </td>
    </tr>
    <? endforeach ?>
    <? endif ?>
</tbody>
<? endif ?>

<? if ($user['perms'] != 'root') : ?>
<tbody>
    <tr class="steel header-row">
        <td colspan="3" class="toggle-indicator">
            <a class="toggler"><b><?= _('Einrichtungsdaten') ?></b></a>
        </td>
    </tr>
    <tr class="steel1">
        <td>
            <?= _('Neue Einrichtung')?>
        </td>
        <td colspan="2">
            <select name="new_inst">
                <option selected="selected" value="none"><?= _('-- Bitte Einrichtung ausw�hlen --') ?></option>
                <? foreach ($available_institutes as $i) : ?>
                <option value="<?= $i['Institut_id'] ?>"><?= htmlReady(my_substr($i['Name'], 0, 50)) ?></option>
                <? endforeach ?>
            </select>
        </td>
    </tr>
    <? if (count($institutes) > 0) : ?>
    <? foreach ($institutes as $i => $institute) : ?>
    <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
        <td>
            <?= $i+1 ?>. <?= _('Einrichtung')?>
        </td>
        <td>
            <?= htmlReady($institute['Name']) ?>
        </td>
        <td align="right">
            <a href="<?= $controller->url_for('admin/user/delete_institute/' . $user['user_id'] . '/' . $institute['Institut_id']) ?>">
                <?= Assets::img('icons/16/blue/trash.png', array('class' => 'text-top', 'title' => _('Diese Einrichtung l�schen'))) ?>
            </a>
        </td>
    </tr>
    <? endforeach ?>
    <? endif ?>
</tbody>
<? endif ?>

<? if (count($userdomains) > 0 || $user['perms'] != 'root') : ?>
<tbody>
    <tr class="steel header-row">
        <td colspan="3" class="toggle-indicator">
            <a class="toggler"><b><?= _('Nutzerdom�nen') ?></b></a>
        </td>
    </tr>
    <tr class="steel1">
        <td>
            <?= _('Neue Nutzerdom�ne')?>
        </td>
        <td colspan="2">
            <? $about->select_userdomain() ?>
        </td>
    </tr>
    <? if (count($userdomains) > 0) : ?>
    <? foreach ($userdomains as $i => $domain) : ?>
    <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>">
        <td>
            <?= $i+1 ?>. <?= _('Nutzerdom�ne')?>
        </td>
        <td>
            <?= htmlReady($domain->getName()) ?>
        </td>
        <td align="right">
            <a href="<?= $controller->url_for('admin/user/delete_userdomain/' . $user['user_id'] . '/' . $domain->getID()) ?>">
                <?= Assets::img('icons/16/blue/trash.png', array('class' => 'text-top', 'title' => _('Aus dieser Nutzerdom�ne austragen'))) ?>
            </a>
        </td>
    </tr>
    <? endforeach ?>
    <? endif ?>
</tbody>
<? endif ?>

<? if (count($userfields) > 0) : ?>
<tbody>
    <tr class="steel header-row">
        <td colspan="3" class="toggle-indicator">
            <a class="toggler"><b><?= _('Datenfelder') ?></b></a>
        </td>
    </tr>
<? foreach ($userfields as $entry) : ?>
    <? if ($entry->isVisible()) : ?>
        <tr class="<?= TextHelper::cycle('steel1', 'steelgraulight') ?>">
            <td>
                <?= htmlReady($entry->getName()) ?>:
            </td>
            <td colspan="2">
            <? if ($entry->isEditable()) : ?>
                <?= $entry->getHTML("datafields") ?>
            <? else : ?>
                <?= $entry->getDisplayValue() ?>
            <? endif ?>
        </td>
    </tr>
    <? endif ?>
<? endforeach ?>
</tbody>
<? endif ?>

    <tr>
        <td colspan="3" align="center">
            <?= makeButton("speichern", "input", _("�bernehmen"), 'edit') ?>
            <a href="<?= $controller->url_for('admin/user/?reset') ?>"><?= makeButton("abbrechen", "img", _("abbrechen"), 'abort') ?></a>
        </td>
    </tr>
</table>
</form>

<script>
    jQuery('#datepicker').datepicker();
</script>

<? //infobox

include '_infobox.php';

$paktionen[] = array(
    "text" => '<a href="' .URLHelper::getLink('about.php?username=' . $user['username']) .'">' . _('Zum Benutzerprofil') .'</a>',
    "icon" => "icons/16/black/person.png");
$paktionen[] = array(
    "text" => '<a href="' .URLHelper::getLink('sms_send.php?rec_uname=' . $user['username']) .'">' . _('Nachricht an Benutzer verschicken') .'</a>',
    "icon" => "icons/16/black/mail.png");
$paktionen[] = array(
    "text" => '<a href="' .URLHelper::getLink('user_activities.php?username=' . $user['username']) . '">' . _('Datei- und Aktivit�ts�bersicht') .'</a>',
    "icon" => "icons/16/black/vcard.png");
if ($GLOBALS['LOG_ENABLE']) {
    $paktionen[] = array(
        "text" => '<a href="' . URLHelper::getLink('dispatch.php/event_log/show?search=' . $user['username'] .'&type=user&object_id=' .$user['user_id']) . '">' . _('Benutzereintr�ge im Log') . '</a>',
        "icon" => "icons/16/black/log.png");
}
if ($user['locked']) {
    $paktionen[] = array(
        "text" => '<a href="' . $controller->url_for('admin/user/unlock/' . $user['user_id'] . '') . '">' . _('Benutzer entsperren') . '</a>',
        "icon" => "icons/16/black/lock-unlocked.png");
}
$paktionen[] = array(
    "text" => '<a href="' . $controller->url_for('admin/user/change_password/' . $user['user_id'] . '') . '">' . _('Neues Passwort setzen') . '</a>',
    "icon" => "icons/16/black/lock-locked.png");
$paktionen[] = array(
    "text" => '<a href="' . $controller->url_for('admin/user/delete/' . $user['user_id'] . '/edit') . '">' . _('Benutzer l�schen') . '</a>',
    "icon" => "icons/16/black/trash.png");

$infobox = array(
    'picture' => 'infobox/board1.jpg',
    'content' => array(
        array(
            'kategorie' => _("Aktionen"),
            'eintrag' => $aktionen
        ),
        array(
            'kategorie' => _("Benutzerspezifische Aktionen"),
            'eintrag' => $paktionen
        )
    )
);
