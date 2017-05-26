<label>
    <input name="perm_read" type="checkbox" value="1" <?= $folder->isReadable() ? "checked" : "" ?>>
    <b>r</b> - <?= _("Lesen (Dateien können heruntergeladen werden)") ?>
</label>
<label>
    <input name="perm_write" type="checkbox" value="1" <?= $folder->isWritable() ? "checked" : "" ?>>
    <b>w</b> - <?= _("Schreiben (Dateien können hochgeladen werden)") ?>
</label>
<label>
    <input name="perm_visible" type="checkbox" value="1" <?= $folder->isVisible() ? "checked" : "" ?>>
    <b>x</b> - <?= _("Sichtbarkeit (Ordner wird angezeigt)") ?>
</label>