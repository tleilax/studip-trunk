<div id="activityfeedfilteredit">
    <form id="configure_quickselection" action="<?= PluginEngine::getURL($plugin, array(), 'save') ?>" method="post" class="studip-form" data-dialog>
        <section>
            <label>Start
                <input type="text" name="start_date" value="<?=$start_date ?>" class="size-m has-date-picker">
            </label>

            <label>Ende
                <input type="text" name="end_date" value="<?=$end_date ?>" class="size-m has-date-picker">
            </label>
        </section>

        <footer data-dialog-button>
            <?= Studip\Button::createAccept(_('Speichern')) ?>
            <?= Studip\Button::createCancel(_('Abbrechen'), URLHelper::getLink('dispatch.php/start')) ?>
        </footer>
    </form>
</div>
