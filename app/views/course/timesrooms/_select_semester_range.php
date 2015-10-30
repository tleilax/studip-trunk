<form action="<?= $controller->url_for('course/timesrooms/index', array('cmd' => 'applyFilter'))?>" method="post" class="default" data-dialog="size=big">
    <section class="hgroup">
        <label for="newFilter">
            <?= _('Semester auswählen') ?>
            <select name="newFilter" id="newFilter">
                <? foreach ($selection as $item) : ?>
                    <option value="<?= $item['value']?>" <?= $item['is_selected'] ? 'selected' : ''?>><?= htmlReady($item['linktext'])?></option>
                <? endforeach ?>
            </select>
        </label>
    </section>
    <section>
        <?= Studip\Button::createAccept(_('Auswählen'), 'select_sem')?>
    </section>
</form>