<form method="post" action="<?= $controller->url_for('course/timesrooms/saveStack/'. $cycle_id, $editParams) ?>" class="studip-form"
      data-dialog="size=big;reload-on-close">
    <input type="hidden" name="method" value="preparecancel"/>
    <?= $this->render_partial('course/timesrooms/_cancel_form.php')?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('�bernehmen'), 'cancel')?>
        <?= Studip\LinkButton::create(_('Zur�ck zur �bersicht'), $controller->url_for('course/timesrooms/index'), array('data-dialog' => 'size=big;reload-on-close')) ?>
    </footer>
</form>
