<? use Studip\Button, Studip\LinkButton; ?> 
<h3><?=sprintf(_("Raumanfrage \"%s\" bearbeiten"), htmlready($request->getTypeExplained()))?></h3>
<form method="POST" name="room_request" action="<?=$this->controller->link_for('edit/' . $course_id, array('request_id' => $request->getId()))?>">
<?= CSRFProtection::tokenTag() ?>
<?
$buttons = '<span>' . Button::createAccept(_('OK'), 'save_close', array('title' => _('Speichern und zurück zur Übersicht'))) . '</span>';
$buttons .= '<span style="padding-left:1em">' . LinkButton::createCancel(_('Abbrechen'), $controller->link_for('index/'.$course_id), array('title' => _('Abbrechen'))) . '</span>';
$buttons .= '<span style="padding-left:1em">' . Button::create(_('Übernehmen'), 'save', array('title' => _('Änderungen speichern'))) . '</span>';

echo $this->render_partial('course/room_requests/_form.php', array('submit' => $buttons));
echo '</form>';
if ($request->isNew()) {
    $info_txt = _("Dies ist eine neue Raumanfrage.");
} else {
    $info_txt = '<div>' . _('Erstellt von') . ': ' . get_fullname($request->user_id) . '</div>';
    $info_txt .= '<div>' . _('Erstellt am') . ': ' . strftime('%x %H:%M', $request->mkdate) . '</div>';
    $info_txt .= '<div>' . _('Letzte Änderung') . ': ' . strftime('%x %H:%M', $request->chdate) . '</div>';
}
$infobox_content = array(
    array(
        'kategorie' => _('Raumanfragen und gewünschte Raumeigenschaften'),
        'eintrag'   => array(
    array(
        'icon' => Icon::create('info', 'clickable'),
        'text' => _("Hier können Sie Angaben zu gewünschten Raumeigenschaften machen.")
    ),
    array(
        'icon' => Icon::create('info', 'clickable'),
        'text' => $info_txt
    ),
    array(
            'icon' => Icon::create('remove', 'clickable'),
            'text' => '<a href="'.$controller->link_for('index/'.$course_id).'">'._('Bearbeiten abbrechen').'</a>'
        ))
    ),
);
if (getGlobalPerms($GLOBALS['user']->id) == 'admin' || ($GLOBALS['perm']->have_perm('admin') && count(getMyRoomRequests(null, null, true, $request->getId())))) {
    $infobox_content[0]['eintrag'][] = array(
            'icon' => Icon::create('admin', 'clickable'),
            'text' => '<a href="'.URLHelper::getLink('resources.php', array('view' => 'edit_request', 'single_request' => $request->getId())).'">'._('Raumanfrage auflösen').'</a>'
        );
}
$infobox = array('picture' => 'sidebar/resources-sidebar.png', 'content' => $infobox_content);