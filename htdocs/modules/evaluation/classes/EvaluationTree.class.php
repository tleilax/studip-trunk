<?php
/**
 * The treeclass for an evaluation.
 *
 * @author  mcohrs
 * @version $Id$
 *
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 * @modulegroup evaluation_modules
 *
 */

// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// Copyright (C) 2001-2004 Stud.IP
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
require_once($ABSOLUTE_PATH_STUDIP . "modules/evaluation/evaluation.config.php");
require_once( EVAL_FILE_EVAL );
require_once( EVAL_FILE_GROUP );
# ====================================================== end: including files #


class EvaluationTree extends TreeAbstract {
# Define all required variables ============================================= #

 /**
  * Holds the Evaluation object
  * @access   public
  * @var      object Evaluation $eval
  */
  var $eval;
  
 /**
  * Holds the Evaluation ID
  * @access   public
  * @var      string $evalID
  */
  var $evalID;
  
 /**
  * Holds the eval constructor load mode
  * @access   public
  * @var      integer $load_mode
  */
  var $load_mode;

# ============================================================ end: variables #


# Define constructor and destructor ========================================= #
  /**
    * Constructor
    * @access   public
    * @param    string  the eval's ID (optional - if not given, it must be in $_REQUEST).
    * @param    string  the load mode for the eval (optional).
    */
  function EvaluationTree( $evalID = null, $load_mode = EVAL_LOAD_ALL_CHILDREN ) {

      if (isset($evalID))
	  $this->evalID = $evalID;
      else
	  $this->evalID = $_REQUEST["evalID"];
	  
      $this->load_mode = $load_mode;

      if (empty($this->evalID)){
	  print _("Fehler in EvaluationTree: Es wurde keine evalID �bergeben");
	  exit ();
      }

      /* ------------------------------------------------------------------- */
      parent::TreeAbstract();
  }
# =========================================== end: constructor and destructor #


# Define public functions =================================================== #

  /**
   * initializes the tree
   * store rows from evaluation tables in array $tree_data
   * @access public
   */
  function init() {
      /* create the evaluation -------------------> */
      $this->eval = new Evaluation( $this->evalID, NULL, $this->load_mode );
      $this->root_name = $this->eval->getTitle();
      $this->root_content = $this->eval->getText();

      /* create the tree structure ---------------> */
      parent::init();

      foreach( $this->eval->getChildren() as $group ) {
	  $this->recursiveInit( $group );

	  $this->tree_data[$group->getObjectID()]["text"] = $group->getText();
	  $this->tree_data[$group->getObjectID()]["object"] = $group;
	  $this->storeItem( $group->getObjectID(), "root",
			    $group->getTitle(), $group->getPosition() );
      }
      /* <---------------------------------------- */
  }

  
  /**
   * initialize the sub-groups.
   * 
   * @access  private
   * @param   object EvaluationGroup  the current group to be initialized.
   */
  function recursiveInit( $group ) {
      // only groups are interesting here.
      if( $group->instanceof() != INSTANCEOF_EVALGROUP )
	  return;

      if( $children = $group->getChildren() ) {
	  foreach( $children as $child ) {
	      $this->recursiveInit( $child );
	  }
      }

      // store current object itself
      $this->tree_data[$group->getObjectID()]["object"] = $group;

      $this->storeItem( $group->getObjectID(), $group->getParentID(),
			$group->getTitle(), $group->getPosition() );

  }

# ===================================================== end: public functions #


}

?>
