<form action="<?= $controller->url_for('file/unzipquestion') ?>" method="post" data-dialog>

    <? foreach ($file_refs as $file_ref) : ?>
        <input type="hidden" name="file_refs[]" value="<?= htmlReady($file_ref->getId()) ?>">
    <? endforeach ?>

    <?= Icon::create("unit-test", "inactive")->asImg(60, array('style' => "display: block; margin-left: auto; margin-right: auto;")) ?>

    <?= _("Soll diese ZIP-Datei entpackt werden?") ?>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Entpacken"), "unzip") ?>
        <?= \Studip\Button::create(_("Nicht Entpacken"), "dontunzip") ?>
    </div>
</form>