<?php

/**
 * dateien.php 
 * 
 * Der Controller stellt angemeldeten Benutzer/innen ein Dateimanagement-
 * system fuer einen persoenlichen Dateibereich im Stud.IP zur Verfuegung.   
 *
 *
 * TODO:
 * 
 * - Lifter 010: Unterstuetzung einer barrierefreien Nutzung
 * 
 *
 * @author      Gerd Hoffmann <gerd.hoffmann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @version     3.0
 */


require_once 'app/controllers/authenticated_controller.php';


class Document_DateienController extends AuthenticatedController
 {
  private $userdir, $quota, $belegt;
 
  
  public function before_filter(&$action, &$args)
    {
     global $user, $USER_DOC_PATH, $_SESSION, $_FILES;
     
     parent::before_filter($action, $args);    
     Navigation::activateItem('/document/dateien');
     
     $alluserdir = $USER_DOC_PATH;
    
     if (!file_exists($alluserdir))
      mkdir($alluserdir, 0744, true); 
                
     $id = $GLOBALS['auth'] -> auth['uid'];
     $this -> userdir = $alluserdir . "/" . $id;
               
     if (!file_exists($userdir))
      mkdir($userdir, 0744, true);
   
     PageLayout::addStylesheet('./assets/stylesheets/jquery-ui-1.9.1.custom.css');
     PageLayout::addScript('./assets/javascripts/jquery/jquery-ui.1.9.1.custom.js');
     $this -> set_layout($GLOBALS['template_factory'] -> open('layouts/base'));
    }
  
 
  public function index_action()
   {    
    //$user_root = $this -> transformiere("encode", $this -> userdir);
    //$this -> redirect("document/dateien/list/$user_root");
   }
   

  public function list_action($dir)
   {        
    $verz = $this -> transformiere("decode", $dir);
    chdir($verz);
 
    if ($handle = opendir($verz))
     {             
      $i = 0;
      $inhalt[$i][0] = "leer";
      
      while (false != ($eintrag = readdir($handle))) 
       {
        if ($eintrag != "." && $eintrag != "..")
         {        
          $inhalt[$i][0] = $eintrag;          
          $inhalt[$i][1] = $this -> transformiere("encode", $eintrag);
                    
          if ($info = stat($eintrag))
           {
            if (is_dir($eintrag))
              $inhalt[$i][2] = "verzeichnis";    
             else if(is_file($eintrag))
              $inhalt[$i][2] = "datei"; 
             else
              $inhalt[$i][2] = "symbolischer Link";
                 
            $inhalt[$i][3] = $this -> formatiere($info[7]);
            $inhalt[$i][4] = $info[10];
            $inhalt[$i][5] = $this -> transformiere("encode", $verz);
            $inhalt[$i][6] = $this -> verzeichnisUI($verz);
           }           
          $i++;
         }
        }
       closedir($handle);
      }
  
    if ($inhalt[0][0] == "leer")
      {
       $inhalt[$i][3] = $this -> formatiere(0);
       $inhalt[$i][4] = time();
       $inhalt[$i][6] = $this -> verzeichnisUI($verz);
       $this -> flash['count'] = 0;
      }
     else
      {
       $this -> flash['count'] = $i-1;
      }
 
    $this -> flash['inhalt'] = $inhalt;
    $this -> flash['wDir'] = $dir;
    $this -> render_action('index');
   } 
   

  public function open_action($envDir, $subDir)
   { 
    $dir = $this -> transformiere("decode", $envDir);
    $subid = $this -> transformiere("decode", $subDir);
    $wDir = $dir. "/". $subid;
    $this -> flash['wDir'] = $this -> transformiere("encode", $wDir);
    $dir = $this -> transformiere("encode", $wDir); 
    $this -> redirect("document/meine_dateien/list/$dir");
   }
   

  public function jump_action($vektor, $index)
   {
    $wDir = $this -> transformiere(decode, $vektor);
    $verz = ltrim($wDir, "/");
    $token = explode("/", $verz);
    $token[0] = $this -> userdir;
    $newDir = implode("/", $token);
    $wDir = $this -> transformiere("encode", $newDir);
    
    $this -> redirect("document/meine_dateien/list/$wDir");
   }

  
  public function dialog_action($operator, $envDir, $id)
   {    
    switch ($operator)
     {
      case "addEnv":
       $this -> flash['addEnv'] = true;
       $this -> redirect("document/meine_dateien/list/$envDir");
       break;
       
      case "addSub":        
       $this -> flash['subid'] = $this -> transformiere("decode", $id);
       $this -> redirect("document/meine_dateien/list/$envDir");
       break;
       
      case "rmEnv":
       $this -> flash['rmEnvId'] = $this -> transformiere("decode", $id);
       $this -> redirect("document/meine_dateien/list/$envDir");
       break;

      case "rmSub":
       $this -> flash['rmSubId'] = $this -> transformiere("decode", $id);
       $this -> redirect("document/meine_dateien/list/$envDir");
       break;
       
      case "proEnv":
       $this -> flash['proEnvId'] = $this -> transformiere("decode", $id);
       $this -> redirect("document/meine_dateien/list/$envDir");
       break;
       
      case "proSub":
       $this -> flash['proSubId'] = $this -> transformiere("decode", $id);
       $this -> redirect("document/meine_dateien/list/$envDir");
       break;
       
      case "posEnv":
       $this -> flash['posEnvId'] = $this -> transformiere("decode", $id);
       $this -> redirect("document/meine_dateien/list/$envDir");
       break;
       
      case "posSub":
       $this -> flash['posSubId'] = $this -> transformiere("decode", $id);
       $this -> redirect("document/meine_dateien/list/$envDir");
       break;
       
    }    
   }
 
  
  public function addDir_action($item, $envDir, $subDir)
   {
    if(Request::submitted('mkdir'))
     {
      $dir = $this -> transformiere("decode", $envDir);
        
      switch ($item)
       {
        case "addEnv": 
         $this -> flash['dir'] = $envDir;
         $envDir = $dir;
         $newDir = $dir. "/". $_POST['dirname']. "/";
         break;
         
        case "addSub":
         $envDir = $dir;
         $subid = $this -> transformiere("decode", $subDir);
         $newDir = $dir. "/". $subid. "/". $_POST['dirname']. "/";
         break;
       }
            
      $ergebnis = $this -> kontrolliereName($_POST['dirname']);
      
      if($ergebnis == "ok") 
       {      
        if (file_exists($newDir))
          $ergebnis = "dir_exists";
         else
          if (!mkdir($newDir, 0744))
           $ergebnis = "mkFehler";
       }
      
      $this -> flash['addDir_MessageFlag'] = $ergebnis;
      $dir = $this -> transformiere("encode", $envDir); 
      $this -> redirect("document/meine_dateien/list/$dir");
     }
   }
   

  public function addFile_action($item, $envDir, $subDir)
   {          
    if(Request::submitted('upload'))
     {
      if (isset ($_FILES['upfile']['tmp_name']))
       {
        $dir = $this -> transformiere("decode", $envDir);
       
        switch ($item)
         {
          case "addEnv":
           $this -> flash['dir'] = $envDir;
           $envDir = $dir;
           $ziel_verz = $dir. "/". $_POST['dirname']. "/";
           break;
           
          case "addSub":
           $envDir = $dir;
           $subid = $this -> transformiere("decode", $subDir);
           $ziel_verz = $dir. "/". $subid. "/". $_POST['dirname']. "/";
           break;
         }

        $upfile = $_FILES['upfile']['name'];
        $groesse = $_FILES['upfile']['size'];
        $typ = $_FILES['upfile']['type'];
        $tmp_name = $_FILES['upfile']['tmp_name'];

        $env = rtrim($ziel_verz, "/");
        $ergebnis = $this -> kontrolliereDatei($env, $upfile); //$groesse, $typ); 

        switch ($ergebnis)
         {
          case "eintrag vorhanden":
           $upfile = $this -> renameEintrag($env, $upfile);
           break;
           
          case "datei zu gross":
           break;
           
          case "unerlaubter datei-typ":
           break;
           
          case "quota-ueberschreitung":
           break;
         }
         
        if (!move_uploaded_file($_FILES['upfile']['tmp_name'], $ziel_verz. $upfile))
         $this -> $ergebnis = "Upload_Fehler"; 
           
        //if ($ergebnis == "ok" && $typ == "application/zip")
        // $ergebnis = $this -> unzipEintrag($ziel_verz, $upfile);
         
        $this -> flash['addFile_MessageFlag'] = $ergebnis;
       }
      
      $dir = $this -> transformiere("encode", $envDir); 
      $this -> redirect("document/meine_dateien/list/$dir"); 
     }
   }
  

  public function share_action($envDir, $name)
   {
    //TODO: share_action()
    $dir = str_replace("/", "-", $_SESSION['userdir']);  
    $this -> redirect("document/meine_dateien/list/$dir");
   }
   
 
  public function seminarDocument_action($envDir, $name)
   {
    //TODO: seminarDocument_action()
    $dir = str_replace("/", "-", $_SESSION['userdir']);  
    $this -> redirect("document/meine_dateien/list/$dir");
   }
   

  public function properties_action($item, $envDir, $name)
   {
    $dir = $this -> transformiere("decode", $envDir);
    $dname = $this ->transformiere("decode", $name);
    
    $this -> flash['dir'] = $item;
    
    if(Request::submitted('rename'))
     {  
      $alt_name = $dname;
      
      if ($alt_name != $_POST['neu_name'])
        {
         $ergebnis = $this -> kontrolliereName($_POST['neu_name']);
      
         if($ergebnis == "ok")
          {
           $pfadname_neu = $dir. "/". $_POST['neu_name'];    
           $pfadname_alt = $dir. "/". $alt_name;
           rename($pfadname_alt, $pfadname_neu);
          }
         }
        else
         {
          $ergebnis = "name_exists";
         }
         
        $this -> flash['pro_MessageFlag'] = $ergebnis;
      }
    
    if (Request::submitted('zip'))
     $this -> zipEintrag($item, $dir, $dname);
       
    if (Request::submitted('unzip'))
     $erg = $this -> unzipEintrag($dir, $dname);
    
    $this -> flash['erg'] = $erg;
    
    $envDir = $this -> transformiere("encode", $dir); 
    $this -> redirect("document/meine_dateien/list/$envDir"); 
   } 
   
  
  public function position_action($envDir, $name, $newDir)
   {
    if(Request::submitted('kopiere'))
     {
      $dir = $this -> transformiere("decode", $envDir);
      $dname = $this -> transformiere("decode", $name);
      $verz = $dir. "/";
      
      chdir($verz);
      $handle = opendir($verz);
      $cp_name = $_POST['kopie_name'];

      if (is_file($dname))
        {
         copy($dname, $cp_name);
        }
       else if (is_dir($dname))
        {
         $fromDir = $verz. $dname;
         $toDir = $verz. $cp_name;
         $this -> kopiereVerzeichnis($fromDir, $toDir);
        }
      
      closedir($handle);
            
      $envDir = $this -> transformiere("encode", $verz); 
      $this -> redirect("document/meine_dateien/list/$envDir");
     } 
   }
   

  public function download_action($item, $envDir, $name)
   {
    $dir = $this -> transformiere("decode", $envDir);
    $dname = $this -> transformiere("decode", $name);
    
    switch ($item)
     {
      case "datei":
       chdir($dir); 
       $handle = opendir($dir);
        
       if (file_exists($dname))
        {                  
         header('Content-Type: application/unknown');
         header("Content-Disposition: attachment; filename = $dname");
         readfile($dname);
        }
         
       closedir($handle);
       break;
       
      case "verz":
       $zipDatei = $this -> zipEintrag("verzeichnis", $dir, $dname);
       chdir($verz); 
       $handle = opendir($verz);
       
       if (file_exists($zipDatei))
        {        
         if ($zip_name != "err")
          {
           header('Content-Type: application/zip');
           header("Content-Disposition: attachment; filename = $zipDatei");
           readfile($zipDatei);
          }
          
         unlink($zipDatei);
        }
       
       closedir($handle);
       break;
     }
   } 

 
  public function remove_action($item, $envDir, $name)
   {
    if(Request::submitted('rm_ok'))
     {
      $verz = $this -> transformiere("decode", $envDir);    
      $rm_name = $this -> transformiere("decode", $name);
    
      chdir($verz);
        
      switch ($item)
       {
        case "rmEnv":
         
         if (!unlink($rm_name))
           $this -> flash['rm_MessageFlag'] = "rmEnvFehler";
         break;
       
        case "rmSub":
         $rm_verz = $verz. "/". $rm_name;
         $this -> flash['rm_verz'] = $rm_verz;
         
         if (!$this -> loescheVerzeichnis($rm_verz))
           $this -> flash['rm_MessageFlag'] = "rmSubFehler";
         break;
       }

      $dir = $this -> transformiere("encode", $verz); 
      $this -> redirect("document/meine_dateien/list/$dir");
     }
     
    if(Request::submitted('rm_nok'))
     {
      $this -> redirect("document/meine_dateien/list/$envDir");
     }
   }
 
   
  private function formatiere($bytes)
   {
    if ($bytes >= 1073741824)
      {
       $groesse = number_format($bytes / 1073741824, 2) . ' GB';
      }
     elseif ($bytes >= 1048576)
      {
       $groesse = number_format($bytes / 1048576, 2) . ' MB';
      }
     elseif ($bytes >= 1024)
      {
       $groesse = number_format($bytes / 1024, 2) . ' KB';
      }
     elseif ($bytes > 1)
      {
       $groesse = $bytes . ' Bytes';
      }
     elseif ($bytes == 1)
      {
       $groesse = $bytes . ' Byte';
      }
     else
      {
       $groesse = '0 Byte';
      }
 
    return $groesse;
   }
  

  private function transformiere($order, $item)
   {
    switch ($order)
     {
      case "encode":
       $compress = gzcompress($item, 9);    

       for ($i=0; $i < strlen($compress); $i++)
        {
         $ascii .= ord($compress[$i]). "-";
        }
        
       $transmit = chop($ascii, "-");
       break;

      case "decode":
       $token = strtok($item, "-");
       
       while ($token !== false)
        {
         $compress .= chr($token);
         $token = strtok("-");
        }
        
       $transmit = gzuncompress($compress);
       break;
     }
            
    return $transmit;
   } 
  

  private function kontrolliereName($name)
   { 
    if (!(strpos($name, '/') === false))
      {
       $ergebnis = "slash";
      }
     else if(!(strpos($name, '\\') === false))
      {
       $ergebnis = "backslash";
      }
     else if(!(strpos($name, '(') === false) || !(strpos($name, ')') === false))
      {
       $ergebnis = "klammern";
      }
     else if(strlen($name) == 0)
      {
       $ergebnis = "leer";
      }
     else if (strlen($name) > 256)
      {
       $ergebnis = "max";
      }
     else
      {
       $ergebnis = "ok";
      }
      
    return $ergebnis;
   }
   
   
  private function kontrolliereDatei($envDir, $dname)
   {
    $eintrag = $envDir. "/". $dname;
    
    if (file_exists($eintrag))
      return "eintrag vorhanden";
     else
      return "ok";
      
    //Datei-Kontrollfunktionen:
    // -Quota
    // -Dateigroesse
    // -Dateityp
   }
   

  private function loescheVerzeichnis($rm_verz)
   {
    if (!is_dir($rm_verz) || is_link($rm_verz)) 
     return unlink($rm_verz); 

    foreach (scandir($rm_verz) as $item) 
     { 
      if ($item == '.' || $item == '..') 
       continue; 

      if (!$this -> loescheVerzeichnis($rm_verz . "/" . $item)) 
       { 
        chmod($rm_verz . "/" . $item, 0777); 

        if (!$this -> loescheVerzeichnis($rm_verz . "/" . $item)) 
         return false; 
       }; 
     } 

    return rmdir($rm_verz); 
   }
   
   
  private function verzeichnisUI($verz)
   {
    $dir = ltrim($verz, "/");
    $token = explode("/", $dir);
       
    $count = sizeof($token);
    $marker = array_search("user_doc", $token);
    $j = 0;
    
    for ($i = $marker + 1; $i <= $count; $i++)
     {
      $tokenUI[$j] = $token[$i];
      $j++;
     }
    
    $tokenUI[0] = "SomSem_2013";
    $verzUI = implode("/", $tokenUI);
      
    return $verzUI;
   }
     
  
  private function renameEintrag($envDir, $dname)
   {
    chdir($envDir);
    $handle = opendir($envDir);   
    
    $pos = strrpos($dname, ".");
    
    if ($pos !== false)
      {
       $pre = substr($dname, 0, $pos);
       $post = substr($dname, $pos);
           
       $i = 0;
       $cmp = $dname;
    
       while(file_exists($cmp))
        {
         $i++; 
         $cmp = $pre. "_". $i. $post;
        }
        
       $neuName = $cmp;
      }
     else
      {
       $i = 0;
       $cmp = $dname;
    
       while(file_exists($cmp))
        {
         $i++; 
         $cmp = $dname. "_". $i. ".zip";
        }
    
       $neuName = $cmp;
      }
      
    closedir($handle);
    
    return $neuName;
   }
   
   
  private function zipEintrag($item, $dir, $dname)
   {
    switch ($item)
     {
      case "datei":
      $verz = $dir. "/";
      chdir($verz); 
      $handle = opendir($verz);
      
      $zip_name = $this -> zipName($verz, $dname);
      $zip = new PclZip($zip_name);
      $error = $zip -> create($dname, PCLZIP_OPT_REMOVE_PATH, $dir, PCLZIP_OPT_ADD_PATH, "/");   

      if ($error == 0)
        $zip_name = "err";
     
      closedir($handle);  
      break;
       
      case "verzeichnis":
       $verz = $dir. "/". $dname;
       chdir($dir); //verz);
       $handle = opendir($dir); //verz);
    
       $zip_name = $this -> zipName($dir, $dname);
       $zip = new PclZip($zip_name);
       $error = $zip -> create($verz, PCLZIP_OPT_REMOVE_PATH, $dir, PCLZIP_OPT_ADD_PATH, "/");
       $zip -> delete(PCLZIP_OPT_BY_EREG, $zip_name);
                     
       if ($error == 0)
        $zip_name = "err";
     
       closedir($handle);
       //return $zip_name;
       break;
     }
   
    return $zip_name;
   }
   
 
  private function zipName($verz, $dname)
   {
    /*
    $max = strlen($dname);
        
    for ($i = 0; $i <= $max; $i++)
     if ($dname{$i} == ".")
       $marker = $i;
       
    if ($max - ($marker + 1) == 3)
      {
       $dname{$max - 3} = "z";
       $dname{$max - 2} = "i";
       $dname{$max - 1} = "p";
       $zip_name = $dname;
      }
     else
      {
       $zip_name = $dname. ".". "zip";
      }
     */
    
    chdir($verz);
    $handle = opendir($verz);
        
    $zip_name = $dname. ".". "zip";
      
    if (file_exists($zip_name)) //$verz. "/". $zip_name))
     {
      $neuName = $this -> renameEintrag($verz, $zip_name);
      $zip_name = $neuName;
     }
     
    closedir($handle);
    
    return $zip_name;
   }
   
   
  private function unzipEintrag($ziel_verz, $dname)
   {
    chdir($ziel_verz);
    $handle = opendir($ziel_verz);
    
    $max = strlen($dname);
    $eintrag = substr($dname, 0, $max - 4);
    
    if(file_exists($eintrag))
      {
       $zip_eintrag = $dname;
              
       $unzip = new PclZip($dname);
       $unzip -> extract();  
      }
     else
      {
       $zip_eintrag = $dname; //$ziel_verz. "/". $dname;
    
       $unzip = new PclZip($zip_eintrag);
       $unzip -> extract();
      }
    
    closedir($handle);
    
    return $zip_eintrag;
   }
   
   
  private function kopiereVerzeichnis($fromDir, $toDir)
   {
    mkdir($toDir, 0744);
      
    chdir($fromDir);
    $handle = opendir($fromDir);

    while (false != ($eintrag = readdir($handle))) 
     {
      if ($eintrag != "." && $eintrag != "..")
       {   
        if (is_dir($eintrag))
         {
          $this -> kopiereVerzeichnis($fromDir. "/". $eintrag, $toDir. "/". $eintrag);
          chdir($fromDir);
         }
           
        if (is_file($eintrag))
         {
          copy($fromDir. "/". $eintrag, $toDir. "/". $eintrag);
         }
       }
     }
             
    closedir($handle);
   }
 }
 
