<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td class="blank" style="padding-left: 8px;" valign="top">
			<?= $range_type == 'sem' ? $this->render_partial('statusgruppen/optionbox.php') : '' ?>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<?= $content_for_layout ?>
			</table>
		</td>
		<td class="blank">&nbsp;</td>
		<td class="blank" width="240" valign="top">
			<?= $this->render_partial('statusgruppen/infobox.php') ?>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan="3">&nbsp;</td>
	</tr>
<table>
