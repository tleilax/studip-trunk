
 <div>
		
  <a href="" title="Schließen" class="close" style="color:#ffffff;"> X </a>
  
  <?php
  
   $entity = $flash['admin'];
  
   switch ($entity)
    {
     case "ordner":
      $headerText = "Ordner verwalten";
      break;
      
     case "datei":
	  $headerText = "Datei verwalten";
	  break;
    }
	 
   ?>
	 
  <h3 style="padding-left:10px; width:97%; background:#1E3E70; color:#ffffff;"> <?= $headerText ?> </h3>
	
  <table>
		  
   <tr>
		
	<td style="vertical-align:top;">
		  
	 <?php
              
      print Assets::img("icons/48/blue/folder-full.png");
      cr(1);
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
        
      line(13);
          
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
         