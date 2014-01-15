<!DOCTYPE html>
      
<html>

 <head>	 
 	 
  <!-- Internet Explorer HTML5 enabling script: -->
	 
  <!--[if IE]>
	 
      <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	  <style type="text/css">
	 
	   .clear 
	       {
	        zoom: 1;
	        display: block;
	       }
	 
	   </style>
	 
  <![endif]-->
  
  
  <!--  Definition von Ausgabe-Funktionen -->
 
  <?php
       
   function tab($tab_weite)
    {
     for ($i = 0; $i < $tab_weite; $i++)
      print Assets::img('blank.gif');
    }
      
    
   function cr($cr_weite)
    {
     for ($i = 0; $i < $cr_weite; $i++)
      print '<br>';
    }
     
     
   function line($weite)
    {
     for ($i = 0; $i <= $weite; $i++)
      print Assets::img('line2.gif'). '<br>';
    }
  
   ?>
  
  <script type="text/javascript">
     
   <?php

    $ref = json_encode($flash['inhalt']); 
    print "var ref = '$ref';"; 
    
    ?>

  </script>
   
 </head>

 <body>
 
  <div style="display:table; width:100%;">
  
   <div style="display:table-cell; width:100%;">
         
    <table class="header" style="width:99%; background-color:white;">
     
     <tr>
     
      <td>
      
       <?php
     
        print Assets::img("icons/16/blue/folder-full.png");
        tab(1);
        print '<span style="font-size:1.4em; color:#444">'. "Persönlicher Dateibereich". '</span>';
        print '<br>';
        tab(5);
        print '<span style="font-size:11px; color:#444;">'. "Möglicher Beschreibungstext des Dateibereichs". '</span>';
        
       ?>     
        
      </td>
       
      <td style="text-align:right; color:#1E3E70;">
       
        up
       
      </td>
      
      <td style="text-align:right;">
      
       Liste anzeigen: <span style="color:#1E3E70;"> 10 </span> | <span style="color:#1E3E70;"> 20 </span> 
        | <span style="color:#1E3E70;"> 30 </span> | <span style="color:#1E3E70;"> Alle </span>
       
      </td>
            
     </tr>
     
    </table>
    
    <table class="default" style="width:99%;">
    
     <colgroup>
     
      <col style="width:5%;">
     
      <col style="width:20%;">
      
      <col style="width:10%;">
        
      <col style="witdh:30%;">
      
      <col style="width:15%;">
      
      <col style="width:20%;">
      
     </colgroup> 
              
     <thead>
     
      <tr>
     
       <th style="border-top:0px;"> Typ </th>
       
       <th style="border-top:0px;"> Name </th>
       
       <th style="border-top:0px;"> </th>
       
       <th style="border-top:0px;"> Autor/in </th>
       
       <th style="border-top:0px;"> Datum </th>
       
       <th style="border-top:0px;"> </th>      
        
      </tr>
     
     </thead>
     
     <tbody class="toggleable">
     
     <?php 
           
      $max = $flash['count'];
    
      if ($max == 0):
      
     ?>
     
      <tr>
      
       <td colspan="6"> 
       
        <?php

         print Assets::img('blank.gif');
          
         ?>
        
       </td>
      
      </tr>
     
     <?php
     
      else:
      
       for ($i = 1; $i <= $max; $i++):

        $id = $flash['inhalt'][$i][0];
      
        if ($flash['inhalt'][$i][2] == 'Ordner')
          $color = "color:black;";
         else
          $color = "color:#1E3E70;";
      ?>
      
      <tr id="<?= $id ?>" style="<?= $color ?>" >
       
       <td>
     
        <?php
                          
         if ($flash['inhalt'][$i][2] == 'Ordner'):
             
         ?>
       
        <a href="<?//= $controller->url_for("document/dateien/open") ?>" title="Öffnen">
         
         <?php
             
           print Assets::img('icons/16/blue/arr_1right.png');
          
          elseif($flash['inhalt'][$i][2] == 'Datei'):
          
           print Assets::img('icons/16/blue/file-pdf.png');
           
          endif;
                 
         ?>
       
        </a>
        
       </td>
           
       <td> <?= $flash['inhalt'][$i][3] ?> </td>
       
       <td>
        
        <?php
        
         if ($flash['inhalt'][$i][4] == 'unlocked')
          //print '<span style="text-align:right;">'. Assets::img("icons/16/blue/lock-unlocked.png"). '</span>';
         
         ?>
           
       </td>
      
       <td> <?= $flash['inhalt'][$i][5] ?> </td>
      
       <td> <?= $flash['inhalt'][$i][6] ?> </td>
       
       <td> 
         
        <?php
   
         if (isset($flash['inhalt'][$i][2])):
                    
         ?>
            
        <a id="<?= $id ?>" href="#" onClick="STUDIP.Document.freigeben(this.id,ref);" title="Freigeben">       
                              
         <?php
             
          print Assets::img("icons/16/blue/persons.png"); 
          tab(2);
                 
         ?>
       
        </a>
                        
        <a id="<?= $id ?>" href="#" onClick="STUDIP.Document.bearbeiten(this.id,ref);" title="Bearbeiten">
    
         <?php

          print Assets::img("icons/16/blue/visibility-checked.png"); 
          tab(2);
             
         ?>
       
        </a>
        
        <a id="<?= $id ?>" href="#" onClick="STUDIP.Document.verwalten(this.id,ref);" title="Verwalten">
         
         <?php
             
          print Assets::img("icons/16/blue/archive3.png"); 
          tab(2);
                 
         ?>
       
        </a>
        
        <a id="<?= $id ?>" href="#" onClick="STUDIP.Document.loeschen(this.id,ref);" title="Löschen">
         
         <?php
             
          print Assets::img("icons/16/blue/trash.png");
              
         ?>
        
        </a>
        
        <?php
        
         endif;
         
         ?>
         
       </td>  
       
      </tr>
      
     <?php

       endfor;
       
      endif;
        
      ?> 
            
     </tbody>
     
    </table>
   
   </div>
   
   <aside style="display:table-cell; vertical-align:top;">
  
    <?php 
     
     //print "<br>";
     //print Assets::img("files.png"); 

     ?>
    
    <?php
   
     //$quickSearch = new SQLSearch("SELECT username, Nachname " .
     // "FROM auth_user_md5 " .
     // "WHERE Nachname LIKE :input " .
     // "LIMIT 5", _("Nachname"), "username");
     
     //print QuickSearch::get("username", $quickSearch) -> setInputStyle("width: 160px") -> render();
     
     $infobox['picture'] = '/images/infobox/folders.jpg';
     $infobox['content'] = array(
	  //array('kategorie' => _('Ansichten:'),
	  //      'eintrag' => array(
	  //        array('icon' => 'icons/16/blue/checkbox-checked.png', 
	  //              'text' => 'Meine Dateien'),
	  //        array('icon' => 'icons/16/blue/checkbox-unchecked.png', 
	  //              'text' => 'Meine Veranstaltungen'),
	  //        array('icon' => 'icons/16/blue/checkbox-unchecked.png', 
	  //              'text' => 'Mein E-Portfolio'),
	  //        array('icon' => 'icons/16/blue/checkbox-unchecked.png', 
	  //              'text' => 'Mein Repositorium'),
	  //        array('icon' => 'icons/16/blue/checkbox-unchecked.png', 
	  //              'text' => 'Geteilte Dateien')
	  //        )),
	  array("kategorie" => _("Aktionen:"),
	        "eintrag" => array(
	          array('icon' => '/images/icons/16/black/upload.png', 
	                'text' => "<a href=\"". $controller->url_for("document/dateien/verwalten/$type"). "\">".
	                          _("Datei / Ordner hochladen")."</a>"),
	          array('icon' => '/images/icons/16/black/add/folder-empty.png', 
	                'text' => "<a href=\"". $controller->url_for("document/dateien/verwalten/$type"). "\">".
	                          _("Neuen Ordner erstellen")."</a>"),
              array('icon' => '/images/icons/16/black/comment.png', 
                    'text' => "<a href=\"". $controller->url_for("document/dateien/verwalten/$type"). "\">".
	                          _("Dateibereich beschreiben")."</a>"), //_("Dateibereich konfigurieren")."</a>"),
              //array('icon' => '/images/icons/16/blue/persons.png', 
              //      'text' => 'Dateibereich teilen'),
              array('icon' => '/images/icons/16/black/trash.png', 
                    'text' => "<a href=\"". $controller->url_for("document/dateien/loeschen/bereich"). "\">".
	                          _("Dateibereich löschen")."</a>"))),
	  //array('kategorie' => _('Suche:'),
	  //      'eintrag' => array(
	  //        array('icon' => 'icons/16/black/search.png', 
	  //              'text' => 'Suche'))),
	  array('kategorie' => _('Export:'),
	        'eintrag' => array(
	          array('icon' => 'icons/16/black/download.png', 
	                'text' => "Dateibereich herunterladen"))),
	  array('kategorie' => _('Information:'),
	        'eintrag' => array(
	          array('icon' => 'icons/16/black/info.png', 
	                'text' => "Quota: ". $flash['quota']. "- belegt: 0%")))
      );
   
    ?>
        
   </aside>
  
  </div>
  
  <!--  Benachrichtigung -->
  
  <?php
  
   if ($flash['closed'] == 1):
            
   ?> 
                    
   <div id="modal">
  
     <?= $this -> render_partial('document/dateien/_notification'); ?>
	    
    </div>
       
  <?php
      
    endif;
      
   ?>
    
  <!-- Modale Dialoge -->  
  
  <div id="modalDialog" style="visibility:collapse;" class="ui-doc-dialog">
	
   <table>
		  
    <tr>
		
	 <td style="vertical-align:top;">
		  
 	  <?php
              
       print Assets::img("icons/48/blue/folder-full.png");
       cr(2);
       print "<b> Informationen </b>";       
       cr(1);
       print "Root-Verzeichnis";
       cr(1);
       print "<b> Größe: </b>";
       cr(1);
       print "1 MB";
       cr(1);
       print "<b> Erstellt: </b>";
       cr(1);
       print "16.10.2013 - 14:10";
       cr(1);
       print "<b> Geändert: </b>";
       cr(1);
       print "16.10.2013 - 14:10";
       cr(1);
       print "<b> Autor/in: </b>";
       cr(1);
       print "Martin Mustermann";
          
      ?>
       
     </td>
       
     <td style="vertical-align:top; padding-left:15px;"> 
              
      <?php
        
       line(14);
           
       ?> 
       
     </td>
        
     <td style="vertical-align:top; padding-left:15px; width:52%;">
        
      <table class="default"">
          
       <colgroup>
          
        <col style="width:100%;">
           
       </colgroup>
          
       <thead> </thead>
          
       <tbody class="toggleable">
          
        <tr>
           
         <td style="border-bottom:0px;">
           
          <?php
           
           print Assets::img("icons/16/blue/edit.png"); 
           tab(2);
           print "<b> Beschreiben </b>";
            
          ?>
           
         </td>
          
        </tr>
           
        <tr>
           
         <td style="border-bottom:0px;">
           
          <?php
            
           print Assets::img("icons/16/blue/arr_2up.png"); 
           tab(2);
           print "<b> Verschieben </b>";
            
          ?>
           
         </td>
          
        </tr>
           
        <tr>
           
         <td  style="border-bottom:0px;">
           
          <a href="" title="Kopieren">
                     
           <?php
    
            print Assets::img("icons/16/blue/export.png"); 
            tab(2);
            print "<b> Kopieren </b>";
            
           ?>
           
          </a>
           
         </td>
          
        </tr>
           
       </tbody>
         
      </table>
        
     </td>
	   
    </tr>
	 
   </table>
   
  </div>
  
  <!-- Meldungen: -->
      
  <div id="message" style="visibility:collapse;" class="ui-doc-dialog">
    
  </div>
  
 </body>
 
</html>

