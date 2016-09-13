<? use Studip\Button; ?>

<form id="edit_password" method="post" action="<?= $controller->url_for('settings/password/store') ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studipticket" value="<?= get_ticket() ?>">
    <fieldset>
        <legend>
            <?= _('Passwort �ndern') ?>
        </legend>
        <label>
            <span class="required"><?= _('Aktuelles Passwort') ?></span>
            <input required type="password" id="password" name="password">
        </label>
        <label>
            <span class="required"><?= _('Neues Passwort') ?></span>
            <input required type="password" pattern=".{4,}"
                   id="new_password" name="new_password"
                   data-message="<?= _('Das Passwort ist zu kurz - es sollte mindestens 4 Zeichen lang sein.') ?>">
        </label>
        <label>
            <span class="required"><?= _('Passwort best�tigen') ?></span>
            <input required type="password" pattern=".{4,}"
                   id="new_password_confirm" name="new_password_confirm"
                   data-must-equal="#new_password">
        </label>
        <footer><?= Button::create(_('�bernehmen'), 'store', ['title' => _('�nderungen �bernehmen')]) ?></footer>
    </fieldset>
</form>
