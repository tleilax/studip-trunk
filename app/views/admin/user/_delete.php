<?
# Lifter010: TODO
use Studip\Button;

?>
<form action="<?= $controller->url_for('admin/user/delete') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <? if ($users) : ?>
        <? $details = '' ?>
        <? foreach ($users as $user) : ?>
            <? $details .= sprintf('%s (%s)', htmlReady($user->getFullName()), htmlReady($user->username)) ?>
            <input type="hidden" name="user_ids[]" value="<?= $user['user_id'] ?>">
        <? endforeach ?>
    <? endif ?>
    <?= MessageBox::warning(_('Wollen Sie die folgenden Nutzer wirklich l�schen?'), [$details]) ?>
    <label>
        <input id="documents" name="documents" value="1" checked type="checkbox">
        <?= _('Dokumente l�schen?') ?>
    </label>
    <label>
        <input id="mail" name="mail" value="1" checked type="checkbox">
        <?= _('Emailbenachrichtigung verschicken?') ?>
    </label>
    <footer data-dialog-button>
        <?= Button::createAccept(_('JA!'), 'delete', ['title' => _('Benutzer l�schen')]) ?>
        <?= Button::createCancel(_('NEIN!'), 'back', ['title' => _('Abbrechen   ')]) ?>
    </footer>
</form>
