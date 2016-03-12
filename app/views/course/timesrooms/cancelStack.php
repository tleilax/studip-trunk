<form method="post" action="<?= $controller->url_for('course/timesrooms/saveStack/' . $cycle_id, $linkAttributes) ?>"
      class="default" data-dialog="size=big">
    <input type="hidden" name="method" value="preparecancel">

    <?= $this->render_partial('course/timesrooms/_cancel_form.php') ?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('�bernehmen'), 'cancel') ?>
    <? if (Request::int('fromDialog')): ?>
        <?= Studip\LinkButton::create(_('Zur�ck zur �bersicht'),
              $controller->url_for('course/timesrooms/index'),
              array('data-dialog' => 'size=big')) ?>
    <? endif; ?>
    </footer>
</form>
