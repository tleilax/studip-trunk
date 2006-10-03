<?php
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// Object.class.php
//
// Class to provide basic properties of an object in Stud.IP
//
// Copyright (c) 2003 Alexander Willner <mail@AlexanderWillner.de>
// +--------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +--------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +--------------------------------------------------------------------------+


# Include all required files ================================================ #

# =========================================================================== #


# Define all required constants ============================================= #
/**
 * @const INSTANCEOF_OBJECT Is instance of an object
 * @access public
 */
define ("INSTANCEOF_OBJECT", "Object");

define ("ERROR_NORMAL", "1");
define ("ERROR_CRITICAL", "2");
# =========================================================================== #


/**
 * Object.class.php
 *
 * Class to provide basic properties of an object in Stud.IP
 *
 * @author      Alexander Willner <mail@alexanderwillner.de>
 * @version     $Id$
 * @copyright   2003 Stud.IP-Project
 * @access      public
 * @package     studip_core
 * @modulegroup core
 */
class Object {

# Define all required variables ============================================= #
  /**
   * Holds the code and description of an internal error
   * @access   private
   * @var      array $errorArray
   */
  var $errorArray;

  /**
   * Holds the emailadress of the author
   * @access   private
   * @var      array $authorEmail
   */
  var $authorEmail;

  /**
   * Holds the name of the author
   * @access   private
   * @var      array $authorName
   */
  var $authorName;

  /**
   * Holds the type of object. See INSTANCEOF_*
   * @access   private
   * @var      string $instanceof
   */
  var $instanceof;

   
# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
  /**
   * Constructor
   * @access   public
   */
  function Object () {
 
    /* For good OOP: Set errorhandler and destruktor ----------------------- */
     
    // ((not very usefull in PHP 4))
    // restore_error_handler ();
    // $this->oldErrorhandler = set_error_handler (array (&$this, 
    //							'errorHandler'));
    // register_shutdown_function (array (&$this, 'finalize'));

    $this->instanceof = INSTANCEOF_OBJECT;
    /* --------------------------------------------------- end: errorhandler */

    /* Set default values -------------------------------------------------- */
    $this->errorArray       = array ();
    /* ------------------------------------------------------- end: defaults */
  }


  /**
   * Destructor. Should be used every time after an object is not longer
   * usefull!
   * @access   public
   */
  function finalize () {
    // ((not very usefull in PHP 4))
    // restore_error_handler ();
  }
# ========================================================== end: constructor #


# Define public functions =================================================== #
  /**
   * Sets the emailaddress of the author
   * @access  public
   * @param   string $email The emailaddress
   */
  function setAuthorEmail ($email) {
    $this->authorEmail = $email;
  }
   
  /**
   * Gets the emailaddress of the author
   * @access  public
   * @return  string The emailaddress
   */
  function getAuthorEmail () {
    return $this->authorEmail;
  }

  /**
   * Sets the name of the author
   * @access  public
   * @param   string $name The name
   */
  function setAuthorName ($name) {
    $this->authorNmae = $name;
  }
   
  /**
   * Gets the name of the author
   * @access  public
   * @return  string The name
   */
  function getAuthorName () {
    return $this->authorName;
  }
   
  /**
   * Gets the type of object
   * @access  public
   * @return  string The type of object. See INSTANCEOF_*
   */
  function x_instanceof () {
    // Anmerkung: Es existiert bereits die Funktion "is_a" und 
    //            "is_subclass_of" in PHP !
    return $this->instanceof;
  }

  /**
   * Gives the internal errorcode
   * @access public
   * @return boolean True if an error exists
   */
  function isError () {
    return (count($this->errorArray) != 0);
  }

  /**
   * Gives the codes and descriptions of the internal errors
   * @access  public
   * @return  array  The errors as an Array like "1" => "Could not open DB"
   */
  function getErrors () {
    return $this->errorArray;
  }

  /**
   * Resets the errorcodes and descriptions
   * @access public
   */
  function resetErrors () {
    $this->errorArray = array ();
  }

  /**
   * Sets the errorcode (internal)
   * @access  public
   * @param   integer $errcode    The code of the error
   * @param   string  $errstring  The description of the error
   * @param   integer $errline    The line
   * @param   string  $errfile    The file
   * @param   integer $errtype    Defines wheter the error is critical
   */
  function throwError ($errcode, $errstring, $errline = 0, $errfile = 0, 
		       $errtype = ERROR_NORMAL) {
    if (!is_array ($this->errorArray))
      $this->errorArray = array ();

    array_push ($this->errorArray, 
		array ("code" => $errcode,
		       "string" => $errstring,
		       "file" => $errfile,
		       "line" => $errline,
		       "type" => $errtype)
		);
    if ($errtype == ERROR_CRITICAL) {
	 @mail ($this->getAuthorEmail (),
		"Critical error in Stud.IP",
		"Hello ".$this->getAuthorName ()."\n\n".
		"there is an error in file ".$errfile." ".
		"in line ".$errline.". \n".
		"The code is ".$errcode."\n".
		"Description: ".$errstring.".\n\n\n".
		"regards, *an Object*\n\n");
    }
  }

  /**
   * Sets the errorcode from other classes (internal)
   * @access  private
   * @param   object   $class   The class with the error
   */
  function throwErrorFromClass (&$class) {
    $this->errorArray = $class->getErrors ();
    $class->resetErrors ();
  }
# ===================================================== end: public functions #


# Define static functions =================================================== #

# ===================================================== end: static functions #


# Define private functions ================================================== #
  /**
   * An errorHandler for PHP-errors
   * @access  private
   * @param   int    $no   Errornumber
   * @param   string $str  Errordescription
   * @param   string $file Filename
   * @param   int    $line Linenumber
   * @param   array  $ctx  All variables
   */
  function errorHandler ($no, $str, $file, $line, $ctx) {
    if (!($no & error_reporting ())) return;
    $this->throwError ($no, $str, $line, $file, ERROR_CRITICAL);
    echo createErrorReport ($this, "Schwerer PHP-Laufzeitfehler");
    return;
  }
# ==================================================== end: private functions #

}
?>
