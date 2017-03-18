<form action="<?= $controller->link_for("file/edit_license") ?>" method="post" class="default" data-dialog>
    <? foreach ($file_refs as $file_ref) : ?>
        <input type="hidden" name="file_refs[]" value="<?= htmlReady($file_ref->getId()) ?>">
    <? endforeach ?>

    <?= _("Lizenz wählen") ?>

    <?= $this->render_partial(
        'file/_terms_of_use_select.php',
        ['content_terms_of_use_entries' => $licenses]
        ) ?>
</form>