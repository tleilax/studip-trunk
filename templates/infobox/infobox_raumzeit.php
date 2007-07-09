<table align="center" width="250" border="0" cellpadding="0" cellspacing="0">

  <!-- Bild -->
  
  <tr>
    <td class="blank" width="100%" align="right">
      <img src="<?=$GLOBALS['ASSETS_URL']?>images/board2.jpg">
    </td>
  </tr>

  <tr>
    <td class="angemeldet" width="100%">
    <table background="<?=$GLOBALS['ASSETS_URL']?>images/white.gif" align="center" width="99%" border="0" cellpadding="4" cellspacing="0">

      <!-- Statusmeldungen -->
      <? if ($messages) :
            // render status messages partial  
            echo $this->render_partial("infobox/infobox_statusmessages_partial.php"); 
         endif; 
      ?>
            
      <!-- Informationen -->
    
      <tr>
        <td class="blank" width="100%" colspan="2">
          <font size="-1"><b><?=_("Informationen")?>:</b></font>
          <br>
        </td>
      </tr>

      <tr>
          <td class="blank" align="center" valign="top" width="1%">
            <img src="<?=$GLOBALS['ASSETS_URL']?>images/ausruf_small.gif">
          </td>
          <td class="blank" width="99%">
            <font size="-1"><?=_("Hier k�nnen Sie alle Termine der Veranstaltung verwalten.")?></font>
            <br>
          </td>
      </tr>                             
                
      <!-- Semesterauswahl -->
      
      <?       
        // render "semesterauswahl" selection list partial  
        echo $this->render_partial("infobox/infobox_selectionlist_partial.php"); 
      ?>


      <? if ($GLOBALS['RESOURCES_ENABLE']) : ?>
      
      <!-- Legende -->

      <tr>
        <td class="blank" width="100%" colspan="2">
          <font size="-1"><b>Legende:</b></font>
          <br>
        </td>
      </tr>
    
      
        <tr>
          <td class="blank" width="1%" align="center" valign="top">
            <img src="http://develop.studip.de:8080/studip/assets/images/steelrot.jpg" height="20" width="25" alt="">
          </td>
          <td class="blank" width="99%">
            <font size="-1"><?=_("Kein Termin hat eine Raumbuchung!")?></font>
            <br>
          </td>
    
        </tr>
    
      
        <tr>
          <td class="blank" width="1%" align="center" valign="top">
            <img src="http://develop.studip.de:8080/studip/assets/images/steelgelb.jpg" height="20" width="25" alt="">
          </td>
          <td class="blank" width="99%">
            <font size="-1"><?=_("Mindestens ein Termin hat keine Raumbuchung!")?></font>
            <br>
    
          </td>
        </tr>

  
        <tr>
          <td class="blank" width="1%" align="center" valign="top">
            <img src="http://develop.studip.de:8080/studip/assets/images/steelgruen.jpg" height="20" width="25" alt="">
          </td>
          <td class="blank" width="99%">
            <font size="-1"><?=_("Alle Termine haben eine Raumbuchung.")?></font>
    
            <br>
          </td>
        </tr>

        <? endif; ?>
                
    </table>
    </td>
  </tr>
</table>

