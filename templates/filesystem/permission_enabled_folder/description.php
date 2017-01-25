<div><?=$type?></div>
<label>
    <input disabled type="checkbox" value="1" <?= $folder->isReadable() ? "checked" : "" ?>>
    <b>r</b> - <?= _("Lesen (Dateien können heruntergeladen werden)") ?>
</label>
<br>
<label>
    <input disabled type="checkbox" value="1" <?= $folder->isWritable() ? "checked" : "" ?>>
    <b>w</b> - <?= _("Schreiben (Dateien können heraufgeladen werden)") ?>
</label>
<br>
<label>
    <input disabled type="checkbox" value="1" <?= $folder->isVisible() ? "checked" : "" ?>>
    <b>x</b> - <?= _("Sichtbarkeit (Ordner wird angezeigt)") ?>
</label>


