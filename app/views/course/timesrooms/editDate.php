<div style="width: 47%; float: left">
<label for="date">
    <b><?= _('Datum') ?></b>
    <input style="display: block" type="text" name="date" id="date">
</label>
    <b><?=_('Uhrzeit')?></b>
<label id="start_time">
    <?= _('von') ?>
    <input style="display: block" type="text" name="start_time" id="start_time">
</label>
<label id="end_time">
    <?= _('bis') ?>
    <input style="display: block" type="text" name="end_time" id="end_time">
</label>    
<label id="course_type">
    <b><?= _('Art') ?></b>
    <select style="display: block" name="course_type" id="course_type">
        <option value="">
            <?= _('Art der Veranstaltung') ?>
        </option>
    </select>
</label>
<b><?= _('Durchführende Dozenten') ?></b><br>
<? if (!empty($dozenten)) : ?>
    <ul style="list-style-type: none">
        <? foreach ($dozenten as $dozent) : ?>
            <li>
                <?= htmlReady($dozent['Vorname']) ?> <?= htmlReady($dozent['Nachname']) ?>
                <?= Assets::img('icons/16/blue/trash.png', 
                        array('title' => sprintf(_('%s %s aus Veranstaltung austragen'),
                                htmlReady($dozent['Vorname']),htmlReady($dozent['Nachname']))))?>
            </li>
        <? endforeach; ?>
    </ul>
<? else : ?>
    <?= _('Keine Dozenten eingetragen') ?>
<? endif; ?>
<select name="addDozent">
<option><?=_('Dozent/in auswählen')?></option>
 </select>   
</div>
<div style="width: 47%; float: right">
    <b><?=_('Raumangabe')?></b>
    <label>
        <input type="radio" name="room">
        <?=_('Raum')?>
        <select>
            <option>Räume</option>
        </select>
    </label>
    <label>
        <input type="radio" name="room">
        <?=_('freie Ortsangabe (keine Raumbuchung)')?>
    </label>
    <label>
        <input type="radio" name="room">
        <?=_('kein Raum')?>
    </label>
    <label>
        <b><?=_('Beteiligte Gruppen')?></b>
        <select style="display: block">
            <option><?=_('Gruppe auswählen')?></option>
        </select>
    </label>
</div>
