<?
if (!$sd_open[$tpl['md_id']] || $_LOCKED) { ?>
<TR>
    <TD class="steel1" colspan="9">
        <A name="<?=$tpl['md_id']?>" />
        <TABLE cellpadding="2" cellspacing="0" border="0" width="100%">
            <TR>
                <TD width="2%" align="right" valign="center" class="<?=$tpl['class']?>">
                    <A href="<?= URLHelper::getLink('?cmd=open&open_close_id=' . $tpl['md_id'] .'#'. $tpl['md_id']) ?>">
                    	<?= Assets::img('icons/16/blue/arr_1right.png', array('class' => 'text-top')) ?>
                    </A>
                </TD>
                <TD width="23%" nowrap="nowrap" class="<?=$tpl['class']?>">
                    <? if (!$_LOCKED || !$sd_open[$tpl['md_id']]) { ?>
                    <A class="tree" href="<?= URLHelper::getLink('?cmd=open&open_close_id='. $tpl['md_id'] .'#'. $tpl['md_id']) ?>">
                    <? } else { ?>
                    <A class="tree" href="<?= URLHelper::getLink('?cmd=close&open_close_id='. $tpl['md_id'] .'#'. $tpl['md_id']) ?>">
                    <? } ?>
                        <FONT size="-1" <?=tooltip($tpl['date_tooltip'], false)?>>
                            <?=htmlready($tpl['date'])?>
                        </FONT>
                    </A>
                </TD>
                <? if ($GLOBALS['RESOURCES_ENABLE']) { ?>
                <TD width="35%" nowrap="nowrap" class="<?=$tpl['class']?>">
                    <FONT size="-1">
                        <B><?=_("Raum:")?></B>
                        <?=$tpl['room']?>
                    </FONT>
                    <? /* rotes Ausrufungszeichen */?>
                    <? if ($tpl['ausruf']) { ?>
                    <A href="javascript:alert('<?=$tpl['ausruf']?>')">
                        <IMG src="<?=$GLOBALS['ASSETS_URL']?>images/ausrufezeichen_rot.gif" alt="<?=$tpl['ausruf']?>" border="0" align="absmiddle" <?=tooltip(_("Wichtige Informationen �ber Raumbuchungen anzeigen"))?>>
                    </A>
                    <? } ?>
                </TD>
                <TD width="20%" nowrap="nowrap" class="<?=$tpl['class']?>">
                <? if( $GLOBALS['RESOURCES_ALLOW_ROOM_REQUESTS']) : ?>
                    <FONT size="-1">
                        <B><?=_("Einzel-Raumanfragen:")?></B>
                        <?=$tpl['anfragen']?>
                    </FONT>
                <? endif; ?>
                </TD>
                <? } else { ?>
                <TD width="55%" class="<?=$tpl['class']?>">&nbsp;</TD>
                <? } ?>
                <TD width="20%" nowrap="nowrap" class="<?=$tpl['class']?>" align="right">
                    <? if (!$_LOCKED) { ?>
                    <a href="<?=URLHelper::getLink('?cmd=moveCycle&direction=up&cycle_id='. $tpl['md_id']) ?>">
                    <?= Assets::img('move_up.gif', array('align' => 'absmiddle'))?>
                    </a>
                    <a href="<?=URLHelper::getLink('?cmd=moveCycle&direction=down&cycle_id='. $tpl['md_id']) ?>">
                    <?= Assets::img('move_down.gif', array('align' => 'absmiddle'))?>
                    </a>
                    <A href="<?= URLHelper::getLink('?cmd=deleteCycle&cycle_id='. $tpl['md_id']) ?>">
                        <?=Assets::img('icons/16/blue/trash.png', array('class' => 'text-top', 'title' => _('Regelm��ige Zeit inklusive aller zugeh�rigen Termine l�schen!'))) ?>
                    </A>
                    <? } ?>
                </TD>
            </TR>
        </TABLE>
    </TD>
<?
} else { ?>
<TR>
    <TD class="steel1" colspan="9">
        <A name="<?=$tpl['md_id']?>" />
        <TABLE cellpadding="2" cellspacing="0" border="0" width="100%">
            <TR>
                <TD width="2%" align="left" valign="top" class="<?=$tpl['class']?>" nowrap="nowrap">
                    <A href="<?= URLHelper::getLink('?cmd=close&open_close_id='. $tpl['md_id'] .'#'. $tpl['md_id']) ?>">
                        <IMG src="<?=$GLOBALS['ASSETS_URL']?>images/icons/16/blue/arr_1down.png" border="0" valign="absmiddle">
                    </A>
                </TD>
                <TD width="93%" nowrap="nowrap" class="<?=$tpl['class']?>">
                    <FORM action="<?= URLHelper::getLink() ?>" method="post" name="EditCycle" style="display: inline">
                        <FONT size="-1"><B>
                            <SELECT name="day">
                            <? foreach(range(1,6) + array(0) as $d) : ?>
                                <OPTION value="<?=$d?>"<?=($tpl['mdDayNumber']==$d) ? 'selected="selected"' : ''?>><?=getWeekday($d, false)?></OPTION>
                            <? endforeach; ?>
                            </SELECT>,
                            <INPUT type="text" name="start_stunde" maxlength="2" size="2" value="<?=leadingZero($tpl['mdStartHour'])?>"> :
                            <INPUT type="text" name="start_minute" maxlength="2" size="2" value="<?=leadingZero($tpl['mdStartMinute'])?>">
                            <?=_("bis")?>
                            <INPUT type="text" name="end_stunde" maxlength="2" size="2" value="<?=leadingZero($tpl['mdEndHour'])?>"> :
                            <INPUT type="text" name="end_minute" maxlength="2" size="2" value="<?=leadingZero($tpl['mdEndMinute'])?>"> Uhr
                            <?=Termin_Eingabe_javascript(2,0,0,$tpl['mdStartHour'],$tpl['mdStartMinute'],$tpl['mdEndHour'],$tpl['mdEndMinute']);?>
                            &nbsp;&nbsp;<?=_("Beschreibung:")?> <INPUT type="text" name="description" value="<?=$tpl['mdDescription']?>">
                            &nbsp;&nbsp;<INPUT type="image" name="editCycle" align="absmiddle" <?=makebutton('uebernehmen', 'src')?>>
                            <INPUT type="hidden" name="cycle_id" value="<?=$tpl['md_id']?>">
                        </B></FONT>
                         <br>
                        <?=_("Turnus")?>:
                        <select name="turnus">
                        <option value="0"<?=$tpl['cycle'] == 0 ? 'selected' : ''?>><?=_("w�chentlich");?></option>
                        <option value="1"<?=$tpl['cycle'] == 1 ? 'selected' : ''?>><?=_("zweiw�chentlich")?></option>
                        <option value="2"<?=$tpl['cycle'] == 2 ? 'selected' : ''?>><?=_("dreiw�chentlich")?></option>
                        </select>
                        &nbsp;&nbsp;
                        <?=_("beginnt in der")?>:
                        <select name="startWeek">
                        <?
                            foreach ($start_weeks as $value => $data) :
                                echo '<option value="'.$value.'"';
                                if ($tpl['week_offset'] == $value) echo ' selected="selected"';
                                echo '>'.$data['text'].'</option>', "\n";
                            endforeach;
                        ?>
                        </select>
                        &nbsp;&nbsp;
                        <?=_("SWS Dozent:")?>
                        &nbsp;
                        <INPUT type="text" name="sws" maxlength="3" size="1" value="<?=$tpl['sws']?>">
                    </FORM></TD>
                <TD width="5%" nowrap="nowrap" class="<?=$tpl['class']?>" align="right">
                    <a href="<?=URLHelper::getLink('?cmd=moveCycle&direction=up&cycle_id='. $tpl['md_id']) ?>">
                    <?= Assets::img('move_up.gif', array('align' => 'absmiddle'))?>
                    </a>
                    <a href="<?=URLHelper::getLink('?cmd=moveCycle&direction=down&cycle_id='. $tpl['md_id']) ?>">
                    <?= Assets::img('move_down.gif', array('align' => 'absmiddle'))?>
                    </a>
                    <A href="<?= URLHelper::getLink('?cmd=deleteCycle&cycle_id='. $tpl['md_id']) ?>">
                        <?=Assets::img('icons/16/blue/trash.png', array('class' => 'text-top', 'title' => _('Regelm��ige Zeit inklusive aller zugeh�rigen Termine l�schen!'))) ?>
                    </A>

                </TD>
            </TR>
        </TABLE>
    </TD>
<?
}
unset($tpl);
