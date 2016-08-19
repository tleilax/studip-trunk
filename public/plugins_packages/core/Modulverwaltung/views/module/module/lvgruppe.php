<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<?= $controller->renderMessages() ?>
<? $perm = MvvPerm::get($lvgruppe) ?>
<h1>
    <?= $headline ?>
</h1>
<form class="default" action="<?= $submit_url ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Bezeichnung') ?></legend>
        <label><?= _('Name der Lehrveranstaltungsgruppe') ?>
            <input <?= $perm->disable('name') ?> id="name" type="text" name="name" value="<?= htmlReady($lvgruppe->name) ?>" size="50">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Alternativtext') ?></legend>
        <label for="alttext">
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>" style="vertical-align: top;">
            <? if ($perm->haveFieldPerm('alttext')): ?>
            <textarea cols="60" rows="5" name="alttext" id="alttext" class="add_toolbar ui-resizable"><?= htmlReady($lvgruppe->alttext) ?></textarea>
            <? else: ?>
            <textarea readonly cols="60" rows="5" name="alttext" id="alttext" class="ui-resizable"><?= htmlReady($lvgruppe->alttext) ?></textarea>
            <? endif; ?>
        </label>
        <label for="alttext_en">
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>" style="vertical-align: top;">
             <? if ($perm->haveFieldPerm('alttext_en')): ?>
            <textarea cols="60" rows="5" name="alttext_en" id="alttext_en" class="add_toolbar ui-resizable"><?= htmlReady($lvgruppe->alttext_en) ?></textarea>
            <? else: ?>
            <textarea readonly cols="60" rows="5" name="alttetxt_en" id="alttext_en" class="ui-resizable"><?= htmlReady($lvgruppe->alttext_en) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <footer>
        <? if ($lvgruppe->isNew()) : ?>
            <? if ($perm->havePermCreate()) : ?>
            <?= Button::createAccept(_('anlegen'), 'store', array('title' => _('Lehrveranstaltungsgruppe anlegen'))) ?>
            <? endif; ?>
        <? elseif ($perm->havePermWrite()) : ?>
        <?= Button::createAccept(_('�bernehmen'), 'store', array('title' => _('�nderungen �bernehmen'))) ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('abbrechen'), $cancel_url, array('title' => _('zur�ck zur �bersicht'))) ?>
    </footer>
</form>