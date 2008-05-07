<table align="center" width="250" border="0" cellpadding="0" cellspacing="0">

  <!-- Bild -->
  
  <tr>
    <td class="infobox" width="100%" align="right">
      <?= Assets::img('groups.jpg') ?>
    </td>
  </tr>

  <tr>
    <td class="infoboxrahmen" width="100%">
    <table background="<?=Assets::url('images/white.gif')?>" align="center" width="99%" border="0" cellpadding="4" cellspacing="0">

      <!-- Statusmeldungen -->
      <? if ($messages) :
            // render status messages partial  
            echo $this->render_partial("infobox/infobox_statusmessages_partial.php", array('messages', $message)); 
         endif; 
      ?>
            
      <!-- Informationen -->
      <tr>
          <td class="infobox" align="center" width="1%" valign="top">
          	<?= Assets::img('ausruf_small') ?>
          </td>
          <td class="infobox" width="99%" align="left">
            <font size="-1">
							<?= _("Hier k�nnen sie ihre Kontaktdaten f�r die Einrichtungen angeben, an denen Sie t�tig sind."); ?>
						</font>
          </td>
      </tr>                             

      <tr>
        <td class="infobox" width="100%" colspan="2">
          <font size="-1"><b><?=_("Aktionen")?>:</b></font>
          <br>
        </td>
      </tr>
                	
	<? if ($GLOBALS['perm']->have_perm('admin')) : ?>
	<!-- only admins my add persons to roles -->
	<!-- Aktionen -->

	<tr>
		<td class="infobox" align="center" width="1%" valign="top">
			<?= Assets::img('link_intern') ?>
		</td>
		<td class="infobox" width="99%" align="left">
			<font size="-1">
			<?= sprintf(_("Diese Person einer weiteren Gruppe / Funktion %shinzuf�gen%s"),
				'<a href="'. $GLOBALS['PHP_SELF'] .'?view=Karriere&username='. $username .'&subview=addPersonToRole">', '</a>'); ?>
			</font>
		</td>
	</tr>
	<? endif; ?>

    </table>
    </td>
  </tr>
</table>

