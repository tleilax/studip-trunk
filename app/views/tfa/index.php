<p>
    <?= _('Zwei-Faktor-Authentisierung ist aktiviert') ?>:
    <?= $secret->type == 'app' ? _('Authenticator-App') : _('E-Mail') ?>
</p>
<form action="<?= $controller->revoke(['foo' => 'bar']) ?>" method="post">
    <input type="hidden" name="foo" value="bar">
    <input type="hidden" name="foos[]" value="foo">
    <input type="hidden" name="foos[]" value="bar">
    <input type="hidden" name="foos[]" value="baz">
    <?= Studip\Button::createAccept(_('Aufheben')) ?>
</form>
