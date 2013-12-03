 
 <div>

  <a href="<?= URLHelper::getLink('index.php') ?>" title="Schließen" class="close" style="color:#ffffff;"> X </a>
  	
  <h3> Systemnachricht </h3>

  <table>
 
   <tr>
		
	<td style="vertical-align:top;">
		  
	 <?php
              
      print Assets::img("icons/48/blue/folder-full.png");
      cr(1);
      print "<b> Informationen </b>";       
      cr(1);
      print "Persönlicher Dateibereich";
      cr(1);
      print "<b> Größe: </b>";
      cr(1);
      print $flash['quota'];
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
    
    <td style="vertical-align:top; padding-left:15px; width:70%; color:red;">
    
     <?= $flash['lockMessage'] ?>
    
    </td>
  
   </tr>
  
  </table>
  
 </div>
         
