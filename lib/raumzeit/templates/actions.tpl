<?
# Lifter010: TODO

use Studip\Button, Studip\LinkButton;
?>
<? if (!$_LOCKED) { ?>
<TABLE cellpadding="1" cellspacing="0" border="0" width="<?=$tpl['width']?>">
    <TR>
        <TD class="table_row_even" colspan="2">
            &nbsp;&nbsp;&nbsp;
            <SELECT name="checkboxActionCmd">
                <OPTION value="noSelection">-- <?=_("Aktion ausw&auml;hlen")?> --</OPTION>
                <OPTION value="chooseAll"><?=_("alle ausw&auml;hlen")?></OPTION>
                <OPTION value="chooseNone"><?=_("Auswahl aufheben")?></OPTION>
                <OPTION value="invert"><?=_("Auswahl umkehren")?></OPTION>
                <OPTION value="deleteChoosen"><?=_("ausgew&auml;hlte l&ouml;schen")?></OPTION>
                <OPTION value="unDeleteChoosen"><?=_("ausgew&auml;hlte wiederherstellen")?></OPTION>
                <OPTION value="deleteAll"><?=_("alle l&ouml;schen")?></OPTION>
                <OPTION value="chooseEvery2nd"><?=_("jeden 2. ausw&auml;hlen")?></OPTION>
            </SELECT>
            <?= Button::createAccept(_('Ok'), 'checkboxAction') ?>
        </TD>
    </TR>
    <TR>
        <TD colspan="2" class="table_row_even">
            &nbsp;
        </TD>
    </TR>
    <TR>
        <TD align="left" class="table_row_odd">&nbsp;</TD>
        <TD align="left" class="table_row_odd">
            <FONT size="-1">
                <B><?=_("Ausgew�hlten Terminen Dozenten hinzuf�gen oder entfernen.")?>&nbsp;</B>
            </FONT><BR/>
    </TR>
    <TR>
        <TD align="left" class="table_row_odd">&nbsp;</TD>
        <TD colspan="8" class="table_row_odd" align="left">
            <select name="related_persons_action" aria-label="<?= _("W�hlen Sie aus, ob Dozenten den ausgew�hlten regelm��igen Terminen hinzugef�gt, von diesen entfernt oder f�r diese Termine definiert werden sollen.") ?>">
                <option value=""><?= _("-- Aktion ausw�hlen --") ?></option>
                <option value="add" title="<?= _("Die ausgew�hlten Dozenten werden den ausgew�hlten Terminen hinzugef�gt. Die zuvor schon durchf�hrenden Dozenten bleiben aber weiterhin zus�tzlich eingetragen.") ?>"><?= _("durchf�hrende Dozenten hinzuf�gen") ?></option>
                <option value="delete" title="<?= _("Die ausgew�hlten Dozenten leiten nicht die ausgew�hlten Termine. Andere Dozenten bleiben bestehen.") ?>"><?= _("durchf�hrende Dozenten entfernen") ?></option>
            </select>
            <select name="related_persons[]" multiple style="vertical-align: top;" aria-label="<?= _("W�hlen Sie die Dozenten aus, die regelm��igen Terminen hinzugef�gt oder von diesen entfernt werden sollen.") ?>">
                <? foreach ($sem->getMembers('dozent') as $dozent) : ?>
                <option value="<?= htmlReady($dozent['user_id']) ?>"><?= htmlReady($dozent['Vorname']." ".$dozent['Nachname']) ?></option>
                <? endforeach ?>
            </select>
            <?= Button::create(_('�bernehmen'), 'related_persons_action_do') ?>
            <br>
        </TD>
    </TR>
    <TR>
        <TD colspan="2" class="table_row_even">
            &nbsp;
        </TD>
    </TR>
    <TR>
        <TD align="left" class="table_row_odd">&nbsp;</TD>
        <TD align="left" class="table_row_odd">
            <FONT size="-1">
                <B><?=_("ausgew&auml;hlte Termine")?>&nbsp;</B>
            </FONT><BR/>
    </TR>
    <TR>
        <TD align="left" class="table_row_odd">&nbsp;</TD>
        <TD colspan="8" class="table_row_odd" align="left">
            <FONT size="-1">
            <?
                if ($GLOBALS['RESOURCES_ENABLE'] && $resList->numberOfRooms()) :
                    $resList->reset();
                    echo _("Raum:");
            ?>
            <?= Assets::img('icons/16/blue/room_clear.png', array('class' => 'bookable_rooms_action', 'title' => _("Nur buchbare R�ume anzeigen"))) ?>
            <SELECT name="room">
                <OPTION value="nochange" selected><?=_("keine &Auml;nderung")?></option>
                <OPTION value="retreat"><?=_("Raumbuchung aufheben")?></option>
                <OPTION value="nothing"><?=_("keine Buchung, nur Textangabe")?></option>
                <?
                    while ($res = $resList->next()) {
                        echo '<OPTION value="'.$res['resource_id'].'">'.my_substr(htmlReady($res["name"]), 0, 30).'</OPTION>';
                    }
                ?>
            </SELECT>
            <?= Button::create(_('Buchen'), 'bookRoom') ?>
            <? endif; ?>
            <?=_("freie Ortsangabe")?>:
            <INPUT type="text" name="freeRoomText" size="50" maxlength="255">
            <?=$GLOBALS['RESOURCES_ENABLE']? _("(f&uuml;hrt <em>nicht</em> zu einer Raumbuchung)") : ''?>
            <?= Button::create(_('�bernehmen'), 'freeText') ?>
            </FONT>
        </TD>
    </TR>
</TABLE>
<INPUT type="hidden" name="cycle_id" value="<?=$tpl['cycle_id']?>">
<? } ?>
