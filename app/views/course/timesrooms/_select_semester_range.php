<form action="<?= $controller->url_for('course/timesrooms/index', array('cmd' => 'applyFilter'))?>" method="post" class="studip-form" data-dialog="size=big">
    <section>
        <label for="newFilter">
            <?= _('Semester auswählen') ?>
        </label>
        <select class="size-s" name="newFilter" id="newFilter">
            <? foreach ($selection as $item) : ?>
                <option value="<?= $item['value']?>" <?= $item['is_selected'] ? 'selected' : ''?>><?= htmlReady($item['linktext'])?></option>
            <? endforeach ?>
        </select>
        <?= Studip\Button::createAccept(_('Auswählen'), 'select_sem')?>
    </section>
</form>