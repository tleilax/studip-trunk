<? use Studip\Button, Studip\LinkButton; ?>
<? if (Request::isXhr()) : ?>
    <? foreach (PageLayout::getMessages() as $messagebox) : ?>
        <?= $messagebox ?>
    <? endforeach ?>
<? endif; ?>
<script>
    MVV.PARENT_ID = '<?= $dokument->getId() ?>';
</script>
<h1>
    <? if ($dokument->isNew()) : ?>
    <?= _('Neues Dokument') ?>
    <? else : ?>
    <?= sprintf(_('Dokument: %s'), htmlReady($dokument->getDisplayName())) ?>
    <? endif; ?>
</h1>
<? $perm = MvvPerm::get($dokument) ?>
<? $i18n_input = $controller->get_template_factory()->open('shared/i18n/input_grouped.php'); ?>
<? $i18n_textarea = $controller->get_template_factory()->open('shared/i18n/textarea_grouped.php'); ?>
<form class="default" action="<?= $controller->url_for('/dokument', $dokument->id) ?>" method="post"<?= Request::isXhr() ? ' data-dialog' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label>
            <?= _('Name:') ?>
            <?= I18N::inputTmpl($i18n_input, 'name', $dokument->name, ['perm' => $perm, 'input_attributes' => ['maxlength' => '255', 'required' => '']]); ?>
        </label>
        <label>
            <?= _('Linktext') ?>
            <?= I18N::inputTmpl($i18n_input, 'linktext', $dokument->linktext, ['perm' => $perm, 'input_attributes' => ['maxlength' => '255', 'required' => '']]); ?>
        <label>
        <label>
            <?= _('URL des Dokuments') ?>
            <input <?= $perm->disable('url') ?> type="text" id="dokument_url" name="url" maxlength="4000" value="<?= htmlReady($dokument->url) ?>" required>
        <label>
            <?= _('Beschreibung') ?>
            <?= I18N::textareaTmpl($i18n_textarea, 'beschreibung', $dokument->beschreibung, ['perm' => $perm, 'input_attributes' => ['class' => 'add_toolbar ui-resizable wysiwyg']]); ?>
        </label>
    </fieldset>
    <footer data-dialog-button>
        <? if ($dokument->isNew()) : ?>
            <? if ($perm->havePermCreate()) : ?>
            <?= Button::createAccept(_('Anlegen'), 'store', array('title' => _('Dokument anlegen'))) ?>
            <? endif; ?>
        <? else : ?>
            <? if ($perm->havePermWrite()) : ?>
            <?= Button::createAccept(_('Übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
            <? endif; ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>