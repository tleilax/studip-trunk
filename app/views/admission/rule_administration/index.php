<?php
//Infobox:
$actions = array();
$actions[] = array(
              "icon" => 'icons/16/black/add/plugin.png',
              "text" => _('Weitere Anmelderegeln installieren').
                        $this->render_partial('admission/rule_administration/upload-drag-and-drop'));
$info = array();
$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Sie können hier neue Anmelderegeln hochladen und installieren.");
$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Legen Sie fest, welche der installierten ".
                        "Anmelderegeln im System benutzt werden dürfen.");

$infobox = array(
    array("kategorie" => _('Informationen:'),
          "eintrag" => $info
    ),
    array("kategorie" => _("Aktionen:"),
          "eintrag"   => $actions
    )
);
$infobox = array('content' => $infobox,
                 'picture' => 'infobox/administration.png'
);

?>
<?= $this->render_partial('dialog/confirm_dialog') ?>
<h2><?= _('Installierte Anmelderegeln:') ?></h2>
<?php
if ($ruleTypes) {
?>
<table class="default" id="admissionrules" width="75%">
    <thead>
        <th><?= _('aktiv?') ?></th>
        <th><?= _('Art der Anmelderegel') ?></th>
        <th><?= _('Aktionen') ?></th>
    </thead>
    <tbody>
    <?php
        foreach ($ruleTypes as $type => $details) {
            if ($details['active']) {
                $src = 'checkbox-checked';
                $text = _('Klick zum deaktivieren');
                $val = '0';
            } else {
                $src = 'checkbox-unchecked';
                $text = _('Klick zum aktivieren');
                $val = '1';
            }
    ?>
    <tr id="ruletype_<?= $type ?>" class="table_row_<?= TextHelper::cycle('even', 'odd') ?>">
        <td>
            <a href="<?= $controller->url_for('admission/ruleadministration/activate', $type, $val) ?>">
                <?= Assets::img('icons/16/blue/'.$src.'.png', 
                    array('alt' => $text, 'title' => $text)); ?>
            </a>
        </td>
        <td>
            <b><?= $details['name'] ?></b> (<?= $type ?>)
            <br/>
            <?= $details['description'] ?>
        </td>
        <td>
            <a href="<?= $controller->url_for('admission/ruleadministration/download', $type) ?>">
                <?= Assets::input('icons/16/blue/download.png', 
                    array('type' => 'image', 'name' => 'activate_'.$type,
                        'alt' => 'Regeldefinition als ZIP herunterladen',
                        'title' => 'Regeldefinition als ZIP herunterladen')); ?>
            </a>
            <a href="<?= $controller->url_for('admission/ruleadministration/uninstall', $type) ?>"
                onclick="return STUDIP.Dialogs.showConfirmDialog('<?= 
                    sprintf(_('Soll die Anmelderegel vom Typ %s wirklich '.
                    'gelöscht werden? Dabei werden auch alle damit verbundenen '.
                    'Daten entfernt, z.B. Zuordnungen zu Anmeldesets oder '.
                    'Daten von Studierenden!'), $details['name']) ?>', '<?= 
                    URLHelper::getURL('dispatch.php/admission/ruleadministration/uninstall/'.
                    $type, array('really' => 1)) ?>')">
                <?= Assets::img('icons/16/blue/trash.png', 
                    array('alt' => _('Anmelderegel löschen'), 
                          'title' => _('Anmelderegel löschen'))); ?>
            </a>
        </td>
    </tr>
    <?php } ?>
    </tbody>
</table>
<br/>
<?php
} else {
?>
<?= MessageBox::info(sprintf(_('Sie haben noch keine Anmelderegeln installiert!'))); ?>
<?php
}
?>