<label>
    <input name="perm_visible" type="checkbox" value="1" <? if ($folder->isVisible()) echo 'checked'; ?>>
    <strong>x</strong> - <?= _('Sichtbarkeit (Ordner wird angezeigt)') ?>
</label>
