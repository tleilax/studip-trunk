<?
$sem = Seminar::getInstance($show_entry['id']);
?>
<div id="edit_sem_entry" class="schedule_edit_entry">
    <div id="edit_sem_entry_drag" class="window_heading">Veranstaltungsdetails bearbeiten</div>
    <form action="<?= $controller->url_for('calendar/schedule/editseminar/'. $show_entry['id'] .'/'. $show_entry['cycle_id'] ) ?>" method="post" name="edit_entry" style="padding-left: 10px; padding-top: 10px; margin-right: 10px;">
        <b><?= _("Farbe des Termins") ?>:</b>
        <? foreach ($GLOBALS['PERS_TERMIN_KAT'] as $data) : ?>
        <span style="background-color: <?= $data['color'] ?>; vertical-align: middle; padding-top: 3px;">
            <input type="radio" name="entry_color" value="<?= $data['color'] ?>" <?= ($data['color'] == $show_entry['color']) ? 'checked="checked"' : '' ?>>
        </span>
        <? endforeach ?>

        <br><br>

        <? if ($show_entry['type'] == 'virtual') : ?>
            <span style="color: red; font-weight: bold"><?= _("Dies ist lediglich eine vorgemerkte Veranstaltung") ?></span><br><br>
        <? endif ?>

        <b><?= _("Veranstaltungsnummer") ?>:</b>
        <?= htmlReady($sem->getNumber()) ?><br><br>
        
        <b><?= _("Name") ?>:</b>
        <?= htmlReady($sem->getName()) ?><br><br>


        <b><?= _("Dozenten") ?>:</b>
        <? $pos = 0;foreach ($sem->getMembers('dozent') as $dozent) :
            if ($pos > 0) echo ', ';
            ?><a href="<?= URLHelper::getLink('about.php?username=' . $dozent['username']) ?>"><?= htmlReady($dozent['fullname']) ?></a><?
            $pos++;
        endforeach ?>
        <br><br>
        
        <b><?= _("Veranstaltungszeiten") ?>:</b><br>
        <?= $sem->getDatesHTML() ?><br>

        <?= Assets::img('icons/16/blue/link-intern.png') ?>
        <a href="<?= URLHelper::getLink('details.php?sem_id='. $show_entry['id']) ?>">Zur Veranstaltung</a><br>
        <br>

        <div style="text-align: center">
            <input type="image" <?= makebutton('speichern', 'src') ?> style="margin-right: 20px;">

            <? if (!$show_entry['visible']) : ?>
                <a href="<?= $controller->url_for('calendar/schedule/bind/'. $show_entry['id'] .'/'. $show_entry['cycle_id'] .'/'. '?show_hidden=true') ?>" style="margin-right: 20px;">
                    <?= makebutton('einblenden') ?>
                </a>
            <? else : ?>
                <a href="<?= $controller->url_for('calendar/schedule/unbind/'. $show_entry['id'] .'/'. $show_entry['cycle_id']) ?>" style="margin-right: 20px;">
                <? if ($show_entry['type'] == 'virtual') : ?>
                    <?= makebutton('loeschen') ?>
                <? else : ?>
                    <?= makebutton('ausblenden') ?>
                <? endif ?>
                </a>
            <? endif ?>

            <a href="<?= $controller->url_for('calendar/schedule') ?>" onClick="$('#edit_sem_entry').fadeOut('fast'); return false">
                <?= makebutton('abbrechen') ?>
            </a>
        </div>
    </form>
</div>
<script>
    $('#edit_sem_entry').draggable({ handle: 'edit_sem_entry_drag' });
</script>
