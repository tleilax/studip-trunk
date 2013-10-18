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
      for ($i = 0; $i <= $tab_weite; $i++)
       print Assets::img("blank.gif");
     }
     
    function line($weite)
     {
      for ($i = 0; $i <= $weite; $i++)
       print Assets::img("line.gif");
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
       
        <!-- up -->
       
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
     
      <tr>
     
       <td> 
       
        <?php

         print Assets::img("icons/16/blue/arr_1right.png");
        
         ?>
        
        </td>
           
       <td> Test </td>
       
       <td>
        
        <?php
        
         print '<span style="text-align:right;">'. Assets::img("icons/16/blue/lock-unlocked.png"). '</span>';
        
         ?>
          
        </td>
      
       <td> Martin Mustermann </td>
      
       <td> 16.10.2013 </td>
       
       <td> 
       
        <?php
        
         print Assets::img("icons/16/blue/persons.png");
         tab(2);
         print Assets::img("icons/16/blue/visibility-checked.png");
         tab(2);
         print Assets::img("icons/16/blue/download.png");
         tab(2);
         print Assets::img("icons/16/blue/trash.png");
         
         ?>
       
       </td>
       
      </tr>
      
      <tr style="color:#1E3E70;">
      
       <td>
       
        <?php

         print Assets::img("icons/16/blue/file-pdf.png");
        
         ?>
       
       </td>
       
       <td> Hausarbeit.pdf </td>
       
       <td> </td>
       
       <td> Martin Mustermann </td>
       
       <td> 17.10.2013 </td>
       
       <td> 
       
        <?php
        
         print Assets::img("icons/16/blue/persons.png");
         tab(2);
         print Assets::img("icons/16/blue/visibility-checked.png");
         tab(2);
         print Assets::img("icons/16/blue/download.png");
         tab(2);
         print Assets::img("icons/16/blue/trash.png");
         
         ?>
       
       </td>
      
      </tr>
            
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
	  array("kategorie" => _("Ansichten:"),
	        "eintrag" => array(
	          array('icon' => 'icons/16/blue/checkbox-checked.png', "text" => 'Meine Dateien'),
	          array('icon' => 'icons/16/blue/checkbox-unchecked.png', "text" => 'Meine Veranstaltungen'),
	          array('icon' => 'icons/16/blue/checkbox-unchecked.png', "text" => 'Meine Texte'),
	          array('icon' => 'icons/16/blue/checkbox-unchecked.png', "text" => 'Mein E-Portfolio'),
	          array('icon' => 'icons/16/blue/checkbox-unchecked.png', "text" => 'Geteilte Dateien'),
	          )),
	  array("kategorie" => _("Aktionen:"),
	        "eintrag" => array(
	          array('icon' => '/images/icons/16/blue/upload.png', 'text' => 'Datei / Ordner hochladen'),
	          array('icon' => '/images/icons/16/blue/add/folder-empty.png', 'text' => 'Neuen Ordner erstellen'),
              array('icon' => '/images/icons/16/blue/comment.png', 'text' => 'Dateibereich bearbeiten'),
              array('icon' => '/images/icons/16/blue/persons.png', 'text' => 'Dateibereich teilen'),
              array('icon' => '/images/icons/16/blue/trash.png', 'text' => 'Dateibereich löschen'))),
	  array("kategorie" => _("Suche:"),
	        "eintrag" => array(
	          array('icon' => 'icons/16/black/search.png', "text" => "Suche"))),
	  array("kategorie" => _("Export:"),
	        "eintrag" => array(
	          array("icon" => 'icons/16/blue/download.png', 
	                "text" => "Dateibereich herunterladen"))),
	  array("kategorie" => _("Information:"),
	        "eintrag" => array(
	          array('icon' => 'icons/16/black/info.png', "text" => "Quota: 10 MB - belegt: 5%")))
      );
   
    ?>
        
   </aside>
  
  </div>
  
 </body>
 
</html>

