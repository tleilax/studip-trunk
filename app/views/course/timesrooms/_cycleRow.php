<tr>
    <td>
        <label for="<?=  htmlReady($termin->termin_id)?>">
            <input type="checkbox" id="<?=htmlReady($termin->termin_id)?>" name="cycle_ids[]">
            <?=htmlReady($termin->toString())?>
        </label>
    </td>
    <td><?=$termin->getRoom()?></td>
    <td>
        <a class="load-in-new-row" href="
        <?=isset($termin->metadate_id) ? 
        $controller->link_for('course/timesrooms/editDate/'.$termin->termin_id.'/'.$termin->metadate_id)  
        : $controller->link_for('course/timesrooms/editDate/'.$termin->termin_id)?>
           ">
                <?= Assets::img('icons/16/blue/edit.png', array('title' => _('Termin bearbeiten'))) ?>
            </a>
        
        <?= Assets::img('icons/16/blue/place.png', array('title' => _('Raum bearbeiten'))) ?>
        <?= Assets::img('icons/16/blue/trash.png', array('title' => _('Termin löschen'))) ?>
    </td>
</tr>