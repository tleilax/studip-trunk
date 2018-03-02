<form name="approve"
      action="<?= $controller->url_for('/approve', $modul_id) ?>"
      method="post"
      style="margin-left: auto; margin-right: auto;">

    <? $response = $controller->relay('shared/modul/description/' . $modul_id);?>
    <?= $response->body ?>

    <div style="text-align: center;" data-dialog-button>
        <?= Studip\Button::createAccept(_('Genehmigen'), 'approval') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), ['data-dialog' => 'close']) ?>
    </div>
</form>