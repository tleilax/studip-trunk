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
       print Assets::img("blank.gif");
     }
      
    
    function cr($cr_weite)
     {
      for ($i = 0; $i < $cr_weite; $i++)
       print "<br>";
     }
     
     
    function line($weite)
     {
      for ($i = 0; $i <= $weite; $i++)
       print Assets::img("line2.gif"). "<br>";
     }
     
     
    function entityID($operator)
     {
      $token = explode("-", $operator);
      return $token[1];
     }
     
  ?>  
   
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
     
      <col style="width:30%;">
      
      <col style="width:10%;">
        
      <col style="witdh:25%;">
      
      <col style="width:15%;">
      
      <col style="width:15%;">
      
     </colgroup> 
              
     <thead>
     
      <tr>
     
       <th style="border-top:0px;"> Typ </th>
       
       <th style="border-top:0px;"> Name </th>
       
       <th style="border-top:0px;"> </th>
       
       <th style="border-top:0px;"> Autor </th>
       
       <th style="border-top:0px;"> Datum </th>
       
       <th style="border-top:0px;"> </th>      
        
      </tr>
     
     </thead>
     
     <tbody class="toggleable">
     
     <?php 
      
      $max = $flash['count']; 
         
      for ($i = 0; $i <= $max; $i++):

       if ($flash['inhalt'][$i][0] == "ordner")
         $color = "color:black;";
        else
         $color = "color:#1E3E70;";
      ?>
      
      <tr style="<?= $color ?>" >
     
       <td>
     
        <?php
                          
         if ($flash['inhalt'][$i][0] == "ordner"):
             
         ?>
       
        <a href="<?//= $controller->url_for("document/dateien/open") ?>" title="Öffnen">
         
         <?php
             
           print Assets::img("icons/16/blue/arr_1right.png");
          
          else:
          
           print Assets::img("icons/16/blue/file-pdf.png");
           
          endif;
                 
         ?>
       
        </a>
        
        </td>
           
       <td> <?= $flash['inhalt'][$i][1] ?> </td>
       
       <td>
        
        <?php
        
         if ($flash['inhalt'][$i][2] == "unlocked")
          print '<span style="text-align:right;">'. Assets::img("icons/16/blue/lock-unlocked.png"). '</span>';
        
         ?>
          
        </td>
      
       <td> <?= $flash['inhalt'][$i][3] ?> </td>
      
       <td> <?= $flash['inhalt'][$i][4] ?> </td>
       
       <td>
       
        <?php
         
         $type = $flash['inhalt'][$i][0];
         
        ?>
         
        <a href="<?= $controller->url_for("document/dateien/teilen/$type") ?>" title="Mit anderen teilen">        
               
         <?php
             
          print Assets::img("icons/16/blue/persons.png"); 
          tab(2);
                 
         ?>
       
        </a>
                
        <a href="<?= $controller->url_for("document/dateien/bearbeiten/$type") ?>" title="Bearbeiten">
         
         <?php

          print Assets::img("icons/16/blue/visibility-checked.png"); 
          tab(2);
             
         ?>
       
        </a>
        
        <a href="<?= $controller->url_for("document/dateien/verwalten/$type") ?>" title="Verwalten">
         
         <?php
             
          print Assets::img("icons/16/blue/archive3.png"); 
          tab(2);
                 
         ?>
       
        </a>
        
        <a href="<?= $controller->url_for("document/dateien/loeschen/$type") ?>" title="Löschen">
         
         <?php
             
          print Assets::img("icons/16/blue/trash.png"); 
                 
         ?>
       
        </a>
       
       </td>  
       
      </tr>
      
      <?php

       endfor;
  
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
   
     $quickSearch = new SQLSearch("SELECT username, Nachname " .
      "FROM auth_user_md5 " .
      "WHERE Nachname LIKE :input " .
      "LIMIT 5", _("Nachname"), "username");
     
     //print QuickSearch::get("username", $quickSearch) -> setInputStyle("width: 160px") -> render();
     
     $infobox['picture'] = '/images/infobox/folders.jpg';
     $infobox['content'] = array(
	  array('kategorie' => _('Ansichten:'),
	        'eintrag' => array(
	          array('icon' => 'icons/16/blue/checkbox-checked.png', 
	                'text' => 'Meine Dateien'),
	          array('icon' => 'icons/16/blue/checkbox-unchecked.png', 
	                'text' => 'Meine Veranstaltungen'),
	          array('icon' => 'icons/16/blue/checkbox-unchecked.png', 
	                'text' => 'Mein E-Portfolio'),
	          array('icon' => 'icons/16/blue/checkbox-unchecked.png', 
	                'text' => 'Mein Repositorium'),
	          array('icon' => 'icons/16/blue/checkbox-unchecked.png', 
	                'text' => 'Geteilte Dateien'),
	          )),
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
	                          _("Dateibereich konfigurieren")."</a>"),
              //array('icon' => '/images/icons/16/blue/persons.png', 
              //      'text' => 'Dateibereich teilen'),
              array('icon' => '/images/icons/16/black/trash.png', 
                    'text' => "<a href=\"". $controller->url_for("document/dateien/loeschen/bereich"). "\">".
	                          _("Dateibereich löschen")."</a>"))),
	  array('kategorie' => _('Suche:'),
	        'eintrag' => array(
	          array('icon' => 'icons/16/black/search.png', 
	                'text' => 'Suche'))),
	  array('kategorie' => _('Export:'),
	        'eintrag' => array(
	          array('icon' => 'icons/16/black/download.png', 
	                'text' => "Dateibereich herunterladen"))),
	  array('kategorie' => _('Information:'),
	        'eintrag' => array(
	          array('icon' => 'icons/16/black/info.png', 
	                'text' => "Quota: 10 MB - belegt: 5%")))
      );
   
    ?>
        
   </aside>
  
  </div>
  
  <!-- Modale Dialoge: -->
  
  <?//= $flash['workOn'] ?> 
  
  <?php

   if (isset($flash['share'])):
   
   ?>
    
    <div id="modal">
       
     <?= $this -> render_partial('document/dateien/_teilen'); ?>
    
    </div>
    
   <?php 
   
    elseif (isset($flash['workOn'])):
    
    ?>
    
     <div id="modal">
       
      <?= $this -> render_partial('document/dateien/_bearbeiten'); ?>
    
     </div>
   
   <?php 
   
    elseif (isset($flash['admin'])):
    
    ?>
    
     <div id="modal">
       
      <?= $this -> render_partial('document/dateien/_verwalten'); ?>
    
     </div>
     
   <?php 
   
    elseif (isset($flash['delete'])):
    
    ?>
    
     <div id="modal">
       
      <?= $this -> render_partial('document/dateien/_loeschen'); ?>
    
     </div>
     
  <?php

   endif;
   
  ?>
      
 </body>
 
</html>

