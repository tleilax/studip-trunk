<?

if (!$show_entry) return;

foreach ($show_entry['seminars'] as $sem_id) {
    $seminars[] = Seminar::getInstance($sem_id);
}
?>
<div id="edit_inst_entry" class="schedule_edit_entry">
    <div id="edit_inst_entry_drag" class="window_heading"><?=_("Liste der Veranstaltungen")?></div>
    <form action="<?= $controller->url_for('calendar/schedule/editseminar/'. $show_entry['id'] .'/'. $show_entry['cycle_id'] ) ?>" method="post" name="edit_entry" style="padding-left: 10px; padding-top: 10px; margin-right: 10px;">
        <table class="default">
            <thead>
                <tr>
                    <th><?=_("Nummer")?></th>
                    <th><?=_("Name")?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($seminars as $seminar) : ?>
                    <tr class="<?= TextHelper::cycle('steelgraulight', 'steel1')?>">
                        <td width="15%"><?= htmlready($seminar->getNumber()) ?></td>
                        <td width="85%">
                            <a href="<?= UrlHelper::getLink('details.php?sem_id='. $seminar->getId()) ?>">
                                <?= Assets::img('link_intern') ?>
                                <?= htmlready($seminar->getName()) ?>
                            </a>
                        </td>
                    </tr>
                <? endforeach ?>
            </tbody>
        </table>
        <br>

        <div style="text-align: center">
            <a href="<?= $controller->url_for('calendar/schedule') ?>" onClick="return STUDIP.Schedule.hideInstOverlay('#edit_inst_entry')">
                <?= makebutton('schliessen') ?>
            </a>
        </div>
    </form>
</div>
<script>
    $('#edit_inst_entry').draggable({ handle: 'edit_inst_entry_drag' });
</script>
