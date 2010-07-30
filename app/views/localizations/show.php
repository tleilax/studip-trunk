<?

$translations = array(
    "suchen" => _("suchen")
  , "Sonntag" => _("Sonntag")
  , "Montag" => _("Montag")
  , "Dienstag" => _("Dienstag")
  , "Mittwoch" => _("Mittwoch")
  , "Donnerstag" => _("Donnerstag")
  , "Freitag" => _("Freitag")
  , "Samstag" => _("Samstag")
  // add your translations here
);

// translations have to be UTF8 for #json_encode
foreach ($translations as &$value) {
    $value = utf8_encode($value);
}

?>
String.toLocaleString({
  "<?= strtr($language, "_", "-") ?>": <?= json_encode($translations) ?>

});
