 
 <div>

  <a href="<?= URLHelper::getLink('index.php') ?>" title="Schlie�en" class="close" style="color:#ffffff;"> X </a>
  	
  <h3> Benachrichtigung </h3>

  <table>
 
   <tr>
		
	<td style="vertical-align:top;">
		  
	 <?php
              
      print Assets::img("icons/48/red/exclaim.png");
      cr(2);
      print "<b> Informationen </b>";       
      cr(1);
      print "Pers�nlicher Dateibereich";
      cr(1);
      print "<b> Gr��e: </b>";
      cr(1);
      print $flash['quota'];
      cr(1);
      print "<b> Erstellt: </b>";
      cr(1);
      print "16.10.2013 - 14:10";
      cr(1);
      print "<b> Ge�ndert: </b>";
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
        
      line(15);
          
      ?> 
       
    </td>
    
    <td style="vertical-align:top; padding-left:15px; width:70%;">
    
     <?= $flash['notification'] ?>
    
    </td>
  
   </tr>
  
  </table>
  
 </div>
         
