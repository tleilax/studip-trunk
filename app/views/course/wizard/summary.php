<form class="studip_form" action="<?= $controller->url_for('course/wizard/process', $stepnumber, $temp_id) ?>" method="post">
    <h1><?= _('Anlegen der Veranstaltung') ?></h1>
    <?= MessageBox::info(_('Sie haben alle benötigten Daten angegeben und '.
        'können nun die Veranstaltung anlegen. Der nächste Schritt führt Sie '.
        'gleich in den Verwaltungsbereich der neu angelegten Veranstaltung, wo '.
        'Sie weitere Daten hinzufügen können.')) ?>
    <div style="clear: both; padding-top: 25px;">
        <input type="hidden" name="step" value="<?= $stepnumber ?>"/>
        <?= Studip\Button::create(_('Zurück'), 'back') ?>
        <?= Studip\Button::create(_('Veranstaltung anlegen'), 'create') ?>
    </div>
</form>