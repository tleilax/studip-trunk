<?php
//Infobox:
$actions = array();
$actions[] = array(
              "icon" => "icons/16/black/plus.png",
              "text" => '<a href="' .
                        $controller->url_for('admission/courseset/configure').
                        '">' . _("Anmeldeset anlegen") . '</a>');
$info = array();
$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Anmeldesets legen fest, wer sich zu den zugeordneten ".
                        "Veranstaltungen anmelden darf.");
$info[] = array(
              "icon" => "icons/16/black/info.png",
              "text" => "Hier sehen Sie alle Anmeldesets, auf die Sie Zugriff ".
                        "haben.");

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
<h2><?= _('Anmeldesets') ?></h2>
<?php
if ($coursesets) {
?>
<div id="coursesets">
    <?php
    foreach ($coursesets as $courseset) {
        echo $courseset->toString();
    }
    ?>
</div>
<?php
} else {
?>
<span id="nosets">
    <i><?= _('Sie haben noch keine Anmeldesets angelegt.') ?></i>
</span>
<?php
}
?>