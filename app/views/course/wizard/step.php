<form class="studip_form" action="<?= $controller->url_for('course/wizard/process', $stepnumber, $temp_id) ?>" method="post">
    <?= $content ?>
    <div style="clear: both; padding-top: 25px;">
        <input type="hidden" name="step" value="<?= $stepnumber ?>"/>
        <?php if (!$first_step) { ?>
            <?= Studip\Button::create(_('Zur�ck'), 'back') ?>
        <?php } ?>
        <?= Studip\Button::create(_('Weiter'), 'next') ?>
    </div>
</form>