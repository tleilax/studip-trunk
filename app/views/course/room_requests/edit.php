<h3><?=sprintf(_("Raumanfrage \"%s\" bearbeiten"), htmlready($request->getTypeExplained()))?></h3>
<form method="POST" name="room_request" action="<?=$this->controller->link_for('edit/' . $course_id, array('request_id' => $request->getId()))?>">
<?= CSRFProtection::tokenTag() ?>
<?
$buttons = '<span>' . makeButton('ok','input',_("Speichern und zur�ck zur �bersicht"),'save_close') . '</span>';
$buttons .= '<span style="padding-left:1em"><a href="'.$controller->link_for('index/'.$course_id).'">' . makeButton('abbrechen','img',_("Abbrechen")) . '</a></span>';
$buttons .= '<span style="padding-left:1em">' . makeButton('uebernehmen','input',_("�nderungen speichern"),'save') . '</span>';

echo $this->render_partial('course/room_requests/_form.php', array('submit' => $buttons));
echo '</form>';
if ($request->isNew()) {
    $info_txt = _("Dies ist eine neue Raumanfrage.");
} else {
    $info_txt = '<div>' . sprintf(_('Erstellt von: %s'), get_fullname($request->user_id)) . '</div>';
    $info_txt .= '<div>' . sprintf(_('Erstellt am: %s'), strftime('%x %H:%M', $request->mkdate)) . '</div>';
    $info_txt .= '<div>' . sprintf(_('Letzte �nderung: %s'), strftime('%x %H:%M', $request->chdate)) . '</div>';
}
$infobox_content = array(
    array(
        'kategorie' => _('Raumanfragen und gew�nschte Raumeigenschaften'),
        'eintrag'   => array(
    array(
        'icon' => 'icons/16/black/info.png',
        'text' => _("Hier k�nnen Sie Angaben zu gew�nschten Raumeigenschaften machen.")
    ),
    array(
        'icon' => 'icons/16/black/info.png',
        'text' => $info_txt
    ),
    array(
            'icon' => 'icons/16/black/minus.png',
            'text' => '<a href="'.$controller->link_for('index/'.$course_id).'">'._('Bearbeiten abbrechen').'</a>'
        ))
    ),
);
if (getGlobalPerms($GLOBALS['user']->id) == 'admin' || ($GLOBALS['perm']->have_perm('admin') && count(getMyRoomRequests(null, null, true, $request->getId())))) {
    $infobox_content[0]['eintrag'][] = array(
            'icon' => 'icons/16/black/admin.png',
            'text' => '<a href="'.UrlHelper::getLink('resources.php', array('view' => 'edit_request', 'single_request' => $request->getId())).'">'._('Raumanfrage aufl�sen').'</a>'
        );
}
$infobox = array('picture' => 'infobox/board2.jpg', 'content' => $infobox_content);