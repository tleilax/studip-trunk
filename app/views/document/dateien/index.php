<html>

 <head>	 
 	   
  <!-- Definition von Ausgabe-Funktionen -->
 
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
     
     <? $env_dir = $flash['env']; ?>
     <? $env_dirname = $flash['env_dirname']; ?>
     <? $realname = $flash['realname']; ?>
     
     <colgroup>
     
      <col style="width:50;">
     
      <col style="width:10%;">
      
      <col style="width:40%;">
      
     </colgroup> 
       
     <tr>
     
      <td style="text-align:left";>
    
       <?= Assets::img('icons/16/blue/folder-full.png') ?>
       <? tab(1); ?>
       <?= '<span style="font-size:1.4em; color:#444">'. $env_dirname. ': '. $realname. '</span>' ?>
       <?= '<br>' ?> 
       <? //tab(6); ?>
       <?//= '<span style="font-size:11px; color:#444;">'. _(""). '</span>' ?>
    
      </td>
       
      <td>
      
       <?php
         
        if ($flash['up_dir'] != 'user_root'):
            $up_dir = $flash['up_dir'];
        
        ?>
       
       <a href="<?= $controller->url_for("document/dateien/up/$up_dir") ?>" title=""> up </a>
       
       <?php
       
        endif;
        
        ?>
        
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
    
      if ($max == -1):
      
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
      
       for ($i = 0; $i <= $max; $i++):

        $id = $flash['inhalt'][$i]['id'];
        $file_id = $flash['inhalt'][$i]['file_id'];
      
        if ($flash['inhalt'][$i]['type'] == 'Ordner')
          $color = "color:black;";
         else
          $color = "color:#1E3E70;";
      ?>
      
      <tr id="<?= $id; ?>" style="<?= $color ?>" >
       
       <td>
     
        <?php
                          
         if ($flash['inhalt'][$i]['type'] == 'Ordner'):
             
         ?>
       
        <a href="<?= $controller->url_for("document/dateien/openDir/$id") ?>" title="Öffnen">
         
         <?php
             
           print Assets::img('icons/16/blue/arr_1right.png');
          
          elseif($flash['inhalt'][$i]['type'] == 'Datei'):
          
           print Assets::img('icons/16/blue/file-pdf.png');
           
          endif;
                 
         ?>
       
        </a>
        
       </td>
           
       <td> <?= $flash['inhalt'][$i]['name']; ?> </td>
       
       <td>
        
        <?php
        
         if ($flash['inhalt'][$i]['lock'] == 'unlocked')
          //print '<span style="text-align:right;">'. Assets::img("icons/16/blue/lock-unlocked.png"). '</span>';
         
         ?>
           
       </td>
      
       <td> <?= $flash['inhalt'][$i]['autor'] ?> </td>
      
       <td> <?= $flash['inhalt'][$i]['date'] ?> </td>
       
       <td style="text-align:center;"> 
         
        <?php
   
         if (isset($flash['inhalt'][$i]['type'])):
                    
         ?>
            
        <!-- <a id="<?//= $id ?>" href="#" onClick="STUDIP.Document.freigeben(this.id,ref);" title="Freigeben"> -->       
                              
         <?php
             
          //print Assets::img("icons/16/blue/persons.png"); 
          //tab(2);
                 
         ?>
       
        </a>
                
        <a id="<?= $id ?>" href="#" onClick="STUDIP.Document.bearbeiten(this.id,ref);" title="Bearbeiten">
        
         <?php

          print Assets::img('icons/16/blue/visibility-checked.png'); 
          tab(2);
             
         ?>
       
        </a>
        
        <!-- <a id="<?= $id ?>" href="#" onClick="" title="Herunterladen"> -->
        
        <a href="<?= $controller->url_for("document/dateien/download/$file_id/$id/$env_dir") ?>" title="Herunterladen">
    
         <?php

          print Assets::img('icons/16/blue/download.png'); 
          tab(2);
             
         ?>
       
        </a>
        
        <!-- <a id="<?= $id ?>" href="#" onClick="STUDIP.Document.remove(this.id,ref);" title="Löschen"> -->
        
        <a href="<?= $controller->url_for("document/dateien/remove/$file_id/$env_dir") ?>" title="Löschen">
         
         <?php
             
          print Assets::img('icons/16/blue/trash.png');
              
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
	                'text' => "<a href=\"#\"". "onClick=\"STUDIP.Document.upload()\"". ">". 
	                          _('Datei hochladen')."</a>"),
	          array('icon' => '/images/icons/16/black/add/folder-empty.png', 
	                'text' => "<a href=\"#\"". "onClick=\"STUDIP.Document.addDir()\"". ">".
	                          _('Neuen Ordner erstellen')."</a>"),
              //array('icon' => '/images/icons/16/black/comment.png', 
              //      'text' => "<a href=\"#\"". "onClick=\"STUDIP.Document.edit()\"". ">".
	          //                _('Dateibereich beschreiben')."</a>"), //_("Dateibereich konfigurieren")."</a>"),
              //array('icon' => '/images/icons/16/blue/persons.png', 
              //      'text' => 'Dateibereich teilen'),
              array('icon' => '/images/icons/16/black/trash.png', 
                    'text' => "<a href=\"#\"". "onClick=\"STUDIP.Document.remove(-1,'nil')\"". ">".
	                          _('Dateibereich löschen')."</a>"))),
	  //array('kategorie' => _('Suche:'),
	  //      'eintrag' => array(
	  //        array('icon' => 'icons/16/black/search.png', 
	  //              'text' => 'Suche'))),
	  array('kategorie' => _('Export:'),
	        'eintrag' => array(
	          array('icon' => 'icons/16/black/download.png', 
	                'text' => _('Dateibereich herunterladen')))),
	  array('kategorie' => _('Information:'),
	        'eintrag' => array(
	          array('icon' => 'icons/16/black/info.png', 
	                'text' => _('Quota: '). $flash['quota']. " - ". _('belegt: 0%'))))
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
   
  <!-- Bearbeiten -->
  
  <div id="modalDialog" style="visibility:collapse;" class="ui-doc-dialog">
	
   <table>
		  
    <tr>
		
	 <td style="vertical-align:top;">
		  
 	  <?php
              
       print Assets::img('icons/48/blue/folder-full.png');
       cr(2);
       print '<b>'. _('Informationen:'). '</b>';       
       cr(1);
       print 'Root-Verzeichnis';
       cr(2);
       print '<b>'. _('Größe:'). '</b>';
       cr(1);
       print '1 MB';
       cr(2);
       print '<b>'. _('Erstellt:'). '</b>';
       cr(1);
       print '16.10.2013 - 14:10';
       cr(2);
       print '<b>'. _('Geändert:'). '</b>';
       cr(1);
       print '16.10.2013 - 14:10';
       cr(2);
       print '<b>'. _('Autor/in:'). '</b>';
       cr(1);
       print 'Martin Mustermann';
          
      ?>
       
     </td>
       
     <td style="vertical-align:top; padding-left:15px;"> 
              
      <?php
        
       line(18);
           
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
           
           print Assets::img('icons/16/blue/upload.png'); 
           tab(2);
           print '<b>'. _('Aktualisieren'). '</b>';
            
          ?>
           
         </td>
          
        </tr>
        
        <tr>
           
         <td style="border-bottom:0px;">
           
          <?php
           
           print Assets::img('icons/16/blue/edit.png'); 
           tab(2);
           print '<b>'. _('Bearbeiten'). '</b>';
            
          ?>
           
         </td>
          
        </tr>
           
        <tr>
           
         <td style="border-bottom:0px;">
           
          <?php
            
           print Assets::img('icons/16/blue/arr_2up.png'); 
           tab(2);
           print '<b>'. _('Verschieben'). '</b>';
            
          ?>
           
         </td>
          
        </tr>
           
        <tr>
           
         <td  style="border-bottom:0px;">
           
          <a href="" title="Kopieren">
                     
           <?php
    
            print Assets::img('icons/16/blue/export.png'); 
            tab(2);
            print '<b>'. _('Kopieren'). '</b>';
            
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
  
  <!-- Datei hochladen -->
  
  <div id="upload" style="visibility:collapse;" class="ui-doc-dialog">
 
   <table>
		  
    <tr>
		
	 <td style="vertical-align:top;">
		  
 	  <?php
              
       print Assets::img('icons/48/blue/upload.png');
       cr(2);
       print '<b>'. _('Upload-Ordner:'). '</b>';  
       cr(1);
       print $env_dirname;
       cr(2);
       //print '<b>'. _('Unzulässige Dateitypen:'). '</b>';
       //cr(1);
       //print 'EXE';
       //cr(2);
       //print '<b>'. _('Maximale Größe:'). '</b>';
       //cr(1);
       //print $flash['upload_quota'];
       //cr(2);
       print '<b>'. _('Autor/in'). '</b>';
       cr(1);
       print $realname;
          
      ?>
       
     </td>
       
     <td style="vertical-align:top; padding-left:15px;"> 
              
      <?php
        
       line(16);
           
       ?> 
       
     </td>
        
     <td style="vertical-align:top; padding-left:15px; width:72%;">
        
      <table>
          
       <colgroup>
          
        <col style="width:100%;">
           
       </colgroup>
          
       <thead> </thead>
          
       <tbody>
          
        <tr>
           
         <td style="border-bottom:0px;">
          
          <form enctype="multipart/form-data"
                method="post"
                action="<?= $controller->url_for("document/dateien/upload/$env_dir") ?>">  
       
           <?= CSRFProtection::tokenTag() ?>
           
           <p>
           
            <input name="upfile" type="file" required="required"/>
           
           </p>
           
           <p>
           
            <input type="text" name="title" value="Titel" size="50"/>  
                      
           </p>
           
           <p>
           
            <textarea cols="38" rows="4" name="description">Beschreibung</textarea>
           
           </p>
           
           <p>
           
            <input type="radio" name="protected" checked="checked" value="0">
           
            <?php
          
             print _('Ja, dieses Dokument ist frei von Rechten Dritter.');
             cr(1);
           
            ?>
                   
            <input type="radio" name="protected" value="1">
          
            <?php
          
             print _('Nein, dieses Dokument ist <u>nicht</u> frei von Rechten');
             cr(1);
             tab(5);
             print _('Dritter.');
           
            ?>
            
           </p>
          
           <?= Studip\Button::createAccept(_('Hochladen'), 'upload') ?>
           <?= Studip\LinkButton::createCancel(_('Abbrechen'), 
                   $controller->url_for("document/dateien/list/$env_dir")) ?>
  
  
          </form>
              
         </td>
         
        </tr>
               
       </tbody>
         
      </table>
        
     </td>
	   
    </tr>
	 
   </table>
  
  </div>
  
  <!-- Neuen Ordner erstellen -->
  
  <div id="addDir" style="visibility:collapse;" class="ui-doc-dialog">
 
   <table>
		  
    <tr>
		
	 <td style="vertical-align:top;">
		  
 	  <?php
              
       print Assets::img('icons/48/blue/folder-full.png');
       cr(2);
       print '<b>'. _('Ordner erstellen in:'). '</b>';  
       cr(1);
       print $env_dirname;
       cr(2);
       print '<b>'. _('Autor/in'). '</b>';
       cr(1);
       print $realname;
          
      ?>
       
     </td>
       
     <td style="vertical-align:top; padding-left:15px;"> 
              
      <?php
        
       line(10);
           
       ?> 
       
     </td>
        
     <td style="vertical-align:top; padding-left:15px; width:70%;">
        
      <table>
          
       <colgroup>
          
        <col style="width:100%;">
           
       </colgroup>
          
       <thead> </thead>
          
       <tbody>
          
        <tr>
           
         <td style="border-bottom:0px;">
          
          <form action="<?= $controller->url_for("document/dateien/addDir/$env_dir") ?>"  
                method="post">
           
           <?= CSRFProtection::tokenTag() ?>
                  
           <p>
           
            <input type="text" name="dirname" placeholder="Neuer Ordner" size="50" required="required"/> 
                      
           </p>
           
           <p>
           
            <textarea cols="38" rows="5" name="description">Beschreibung</textarea>
           
           </p>
          
           <?= Studip\Button::createAccept(_('Erstellen'), 'mkdir') ?>
           <?= Studip\LinkButton::createCancel(_('Abbrechen'), 
                   $controller->url_for("document/dateien/list/$env_dir")) ?>
  
          </form>
                   
         </td>
         
        </tr>
               
       </tbody>
         
      </table>
        
     </td>
	   
    </tr>
	 
   </table>
   
  </div>
  
  <!-- Dateibereich beschreiben -->
  
  <div id="edit" style="visibility:collapse;" class="ui-doc-dialog">
 
   <table>
		  
    <tr>
		
	 <td style="vertical-align:top;">
		  
 	  <?php
              
       print Assets::img('icons/48/blue/edit.png');
       cr(2);
       print '<b>'. _('Ordner erstellen in:'). '</b>';  
       cr(1);
       print $env_dirname;
       cr(2);
       print '<b>'. _('Autor/in'). '</b>';
       cr(1);
       print $flash['realname'];
          
      ?>
       
     </td>
       
     <td style="vertical-align:top; padding-left:15px;"> 
              
      <?php
        
       line(10);
           
       ?> 
       
     </td>
        
     <td style="vertical-align:top; padding-left:15px; width:70%;">
        
      <table>
          
       <colgroup>
          
        <col style="width:100%;">
           
       </colgroup>
          
       <thead> </thead>
          
       <tbody>
          
        <tr>
           
         <td style="border-bottom:0px;">
          
          <form enctype="multipart/form-data"
                method="post"
                action="<?//= $controller->url_for("document/dateien/edit/$env_dir") ?>">  
       
           <?= CSRFProtection::tokenTag() ?>
           
           <p>
           
            <textarea cols="38" rows="5" name="description">M&ouml;glicher Beschreibungstext des Dateibereichs</textarea>
           
           </p>
          
           <?= Studip\Button::createAccept(_('&Uuml;bernehmen'), 'edit') ?>
           <?= Studip\LinkButton::createCancel(_('Abbrechen'), 
                   $controller->url_for("document/dateien/list/$env_dir")) ?>
           
          </form>
              
         </td>
         
        </tr>
               
       </tbody>
         
      </table>
        
     </td>
	   
    </tr>
	 
   </table>
   
  </div>
  
  <!-- Loeschen -->
  
  <div id="remove" style="visibility:collapse;" class="ui-doc-dialog">
    
   <?= Assets::img('icons/48/red/question-circle.png')?>
   
   <span id="removeItem"></span>
   
    <table style="width:99%;">
    
     <colgroup>
     
      <col style="width:30%;">
     
      <col style="width:70%;">
            
     </colgroup> 
              
     <thead>
        
     </thead>
     
     <tbody>
     
      <tr>
      
       <td> </td>
       
       <td>
       
        <form action="<?= $controller->url_for("document/dateien/remove/$id") ?>"  
              method="post">
           
         <?= CSRFProtection::tokenTag() ?>
       
         <?= Studip\Button::createAccept(_('L&ouml;schen'), 'remove') ?>
         <?= Studip\LinkButton::createCancel(_('Abbrechen'), 
                 $controller->url_for("document/dateien/list/$env_dir")) ?>
        
        </form>
   
       </td>
       
      </tr>
      
    </table>
   
  </div>
  
  <!-- Meldungen: -->
      
  <div id="message" style="visibility:collapse;" class="ui-doc-dialog">
    
  </div>
  
 </body>
 
</html>

