<?
# Lifter010: TODO
?>
<TABLE cellspacing="0" cellpadding="0" border="0">
    <TR>
        <TD colspan="5" class="blank" height="10"></TD>
    </TR>
    <TR>
        <TD class="table_header" valign="middle">&nbsp;</TD>
        <TD class="table_header" valign="middle" nowrap>
            <FONT size="-1"><?= _('Ansicht') ?>:</FONT>
        </TD>
<? foreach ($tpl['view'] as $key => $val) {
    if ($tpl['selected'] == $key) { ?>
        <TD class="table_header_bold" nowrap="nowrap" valign="middle">
            <?= Icon::create('arr_1right', 'attention')->asImg(['class' => 'text-top']) ?>
            <FONT size="-1"><?=$val?></FONT>
        </TD>
<? } else { ?>
        <TD class="table_header" nowrap="nowrap" valign="middle">
            <A href="<?= URLHelper::getLink('themen.php?cmd=changeViewMode&newFilter=' . $key) ?>">
                <?= Icon::create('arr_1right', 'blue')->asImg(['align' => 'text-top']) ?>
                <font color="#555555" size="-1"><?=$val?></font>
            </A>
        </TD>
<?
    }
}
?>
    </TR>
</TABLE>
<? unset($tpl); ?>
