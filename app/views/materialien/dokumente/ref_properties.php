<? use Studip\Button, Studip\LinkButton; ?>
<? $perm = MvvPerm::get($relation); ?>
<? $i18n_textarea = $controller->get_template_factory()->open('shared/i18n/textarea_grouped.php'); ?>
<form data-dialog="close" class="default mvv-form">
    <fieldset>
        <legend><?= _('Zusätzliche Anmerkungen/Kommentare zur Zuordnung') ?></legend>
        <label>
            <?= _('Kommentar') ?>
            <?= _('Beschreibung') ?>
            <?= I18N::textareaTmpl($i18n_textarea, 'kommentar', $relation->kommentar, ['perm' => $perm, 'input_attributes' => ['class' => 'add_toolbar ui-resizable wysiwyg', 'data-id' => $relation->dokument_id]]); ?>
        </label>
        <?= _('Die Änderungen werden erst gespeichert, wenn das Hauptformular gespeichert wurde!') ?>
    </fieldset>
    <footer data-dialog-button>
        <?= LinkButton::createAccept(_('Übernehmen'), '#dokumente_' . $relation->dokument_id, array('title' => _('Änderungen übernehmen'))) ?>
        <?= LinkButton::createCancel(_('Abbrechen'), '', array('title' => _('Bearbeitung abbrechen'))) ?>
    </footer>
</form>