<form class="studip_form" action="<?= $controller->url_for('course/wizard/process', $stepnumber, $temp_id) ?>" method="post">
    <h1><?= _('Anlegen der Veranstaltung') ?></h1>
    <?= MessageBox::info(_('Sie haben alle ben�tigten Daten angegeben und '.
        'k�nnen nun die Veranstaltung anlegen. Der n�chste Schritt f�hrt Sie '.
        'gleich in den Verwaltungsbereich der neu angelegten Veranstaltung, wo '.
        'Sie weitere Daten hinzuf�gen k�nnen.')) ?>
    <div style="clear: both; padding-top: 25px;">
        <input type="hidden" name="step" value="<?= $stepnumber ?>"/>
        <?= Studip\Button::create(_('Zur�ck'), 'back') ?>
        <?= Studip\Button::create(_('Veranstaltung anlegen'), 'create') ?>
    </div>
</form>