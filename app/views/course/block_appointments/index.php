<? foreach(PageLayout::getMessages() as $pm) : ?>
    <?= $pm ?>
<? endforeach; ?>
<?= $form->getFormStart($controller->url_for('course/block_appointments')) ?>
<h3>
<?=_("Die Veranstaltung findet in folgendem Zeitraum statt")?>:
</h3>
<div style="border: 1px solid; padding: 5px; margin: 5px;">
    <div>
    <?= $form->getFormFieldCaption('start_day', array('style' => 'float:left;width:100px;'))?>
    <?= $form->getFormField('start_day')?>
    </div>
    <div>
    <?= $form->getFormFieldCaption('end_day', array('style' => 'float:left;width:100px;'))?>
    <?= $form->getFormField('end_day')?>
    </div>
</div>
<h3>
<?=_("Die Veranstaltung findet zu folgenden Zeiten statt")?>:
</h3>
<div style="border: 1px solid; padding: 5px; margin: 5px;">
    <div>
        <?= $form->getFormFieldCaption('start_time', array('style' => 'float:left;width:100px;'))?>
        <?= $form->getFormField('start_time')?>
    </div>
    <div>
        <?= $form->getFormFieldCaption('end_time', array('style' => 'float:left;width:100px;'))?>
        <?= $form->getFormField('end_time')?>
    </div>
    <div>
        <?= $form->getFormFieldCaption('termin_typ', array('style' => 'float:left;width:100px;'))?>
        <?= $form->getFormField('termin_typ')?>
    </div>
<? if ($GLOBALS['TIME_PRESETS']) : ?>
    <div style="padding-top:5px;font-size:smaller;">
    <? foreach ($GLOBALS['TIME_PRESETS'] as $preset) : ?>
    <button type="button" class="button" onClick="var preset=$.parseJSON('<?=jsReady(json_encode($preset), 'inline-double')?>');$('#block_appointments_start_time input,#block_appointments_end_time input').attr('value', function(i){return preset[i];});"><?=vsprintf('%s:%s - %s:%s', $preset)?></button>
    <? endforeach ?>
    </div>
<? endif ?>
</div>
<h3>
<?=_("Die Veranstaltung findet an folgenden Tagen statt")?>:
</h3>
<?= $form->getFormField('days') ?>
<div style="text-align:center">
     <?= $form->getFormButton('save_close')?>
     <?= $form->getFormButton('preview')?>
     <?= $form->getFormButton('close')?>
</div>
<?= $form->getFormEnd() ?>