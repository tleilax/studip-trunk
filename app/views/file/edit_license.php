<form action="<?= $controller->link_for("files/edit_license") ?>" method="post" class="default" data-dialog>
    <? foreach ($file_refs as $file_ref) : ?>
        <input type="hidden" name="file_refs[]" value="<?= htmlReady($file_ref->getId()) ?>">
    <? endforeach ?>

    <?= _("Lizenz wählen") ?>

    <div class="file_select_possibilities">
        <? foreach ($licenses as $license) : ?>
            <button type="submit" name="license_id" value="<?= htmlReady($license['id']) ?>">
                <? if ($license['icon']) : ?>
                    <? if (filter_var($license['icon'], FILTER_VALIDATE_URL)) : ?>
                        <img src="<?= htmlReady($license['icon']) ?>" width="50px" height="50px">
                    <? else : ?>
                        <?= Icon::create($license['icon'], "clickable")->asImg(50) ?>
                    <? endif ?>
                <? endif ?>
                <?= htmlReady($license['name']) ?>
            </button>
        <? endforeach ?>
    </div>

</form>