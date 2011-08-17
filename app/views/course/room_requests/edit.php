<?php

?>
<h3><?=sprintf(_("Raumanfrage \"%s\" bearbeiten"), htmlready($request->getTypeExplained()))?></h3>
<form method="POST" name="room_request" action="<?=$this->controller->link_for('edit/' . $course_id, array('request_id' => $request->getId()))?>">
<?= CSRFProtection::tokenTag() ?>
<?
echo $this->render_partial('course/room_requests/_form.php', array('submit' => makeButton('uebernehmen','input',_("�nderungen speichern"),'save')));
echo '</form>';
$infobox_content = array(
    array(
        'kategorie' => _('Raumanfragen und gew�nschte Raumeigenschaften'),
        'eintrag'   => array(
    array(
        'icon' => 'icons/16/black/info.png',
        'text' => _("Hier k�nnen Sie Angaben zu gew�nschten Raumeigenschaften machen.")
    ),
    array(
            'icon' => 'icons/16/black/minus.png',
            'text' => '<a href="'.$controller->link_for('index/'.$course_id).'">'._('Bearbeiten abbrechen').'</a>'
        ))
    ),
);
$infobox = array('picture' => 'infobox/board2.jpg', 'content' => $infobox_content);