<form class="studip_form" action="<?= $controller->url_for('course/wizard/process', $stepnumber, $temp_id) ?>" method="post">
    <?= $content ?>
    <?php if (!$first_step) { ?>
        <?= Studip\Button::create(_('Zur�ck'), 'back') ?>
    <?php } ?>
    <?= Studip\Button::create(_('Weiter'), 'proceed') ?>
</form>