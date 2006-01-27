<?php
/**
	Enth�lt f�r die Pluginschnittstelle wichtige Umgebungsinformationen
*/
class Environment {
	var $basepath;			// Basispfad der Anwendung (bspw. Absolute_Path_StudIP)
	var $tmppath;			// Tempor�res Verzeichnis
	var $packagebasepath; 	// Basispfad der Plugin-Packages
	var $relativepackagepath; 	// relativer Pfad des Plugin-Packages
	
	function Environment(){
		$this->basepath = "";
		$this->tmppath = "";
		$this->packagebasepath = "plugins/packages";
		$this->relativepackagepath = "plugin/packages";
	}
	
	function setBasepath($newbasepath){
		$this->basepath = $newbasepath;
	}
	
	function getBasepath(){
		return $this->basepath;
	}
	
	function setTmppath($newtmppath){
		$this->tmppath = $newtmppath;
	}
	
	function getTmppath(){
		return $this->tmppath;
	}
	
	function setPackagebasepath($newpackagebasepath){
		$this->packagebasepath = $newpackagebasepath;
	}
	
	function getPackagebasepath(){
		return $this->packagebasepath;
	}
	
	function setRelativepackagepath($newpackagepath){
		$this->relativepackagepath = $newpackagepath;
	}
	
	function getRelativepackagepath(){
		return $this->relativepackagepath;
	}
}

?>