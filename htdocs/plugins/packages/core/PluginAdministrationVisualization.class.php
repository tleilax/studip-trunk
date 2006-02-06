<?php
/**
 * The visualization of the plugin administration plugin
 * @author Dennis Reil <Dennis.Reil@offis.de>
 * @version $Revision$
 * $Id$
 */
require_once("PluginAdministration.class.php");
class PluginAdministrationVisualization extends AbstractStudIPPluginVisualization {
	
	// the constructor
	function PluginAdministrationVisualization($adminplugin){
		parent::AbstractStudIPPluginVisualization($adminplugin);
	}
	
	function showMessage($errorcode) {
		switch ($errorcode) {
			case PLUGIN_INSTALLATION_SUCCESSFUL:
				StudIPTemplateEngine::showSuccessMessage(_("Die Installation des Plugins war erfolgreich"));
				break;
			case PLUGIN_UPLOAD_ERROR: 
				StudIPTemplateEngine::showErrorMessage(sprintf(_("Der Upload des Plugins ist fehlgeschlagen.")));
				break;
			case PLUGIN_MANIFEST_ERROR: 
				StudIPTemplateEngine::showErrorMessage(sprintf(_("Das Manifest des Plugins ist nicht korrekt.")));
				break;			
			case PLUGIN_MISSING_MANIFEST_ERROR: 
				StudIPTemplateEngine::showErrorMessage(sprintf(_("Das Manifest des Plugins fehlt.")));
				break;
			case PLUGIN_ALLREADY_INSTALLED_ERROR: 
				StudIPTemplateEngine::showErrorMessage(sprintf(_("Das Plugin ist bereits installiert.")));
				break;
			default:
				break;			
		}	
	}
	
	function showPluginPackageDownloadView($packagelink){			
		 StudIPTemplateEngine::showSuccessMessage(sprintf(_("Das Plugin-Paket erfolgreich erzeugt.<br>Sie k�nnen das Pluginpaket <a href=\"%s\">hier herunterladen.</a>"),$packagelink));		 
	}
	
	function showPluginList($plugins){		
		$relativepath = $this->pluginref->getPluginpath();
		?>
			<tr>
				<th align="left"><?= _("Name")?></th>
				<th align="left"><?= _("Typ") ?></th>
				<th align="left"><?= _("Verf�gbarkeit") ?></th>
			</tr>
		<?php

		foreach($plugins as $plugin){
			if (($plugin->getPluginname() == "PluginAdministration") || ($plugin->getPluginid() == 1)){
				continue;
			}
		?>
			<tr>
				<td width="35%" align="left"><?= $plugin->getPluginname() ?></td>
				<td width="5%" align="left"><?= PluginEngine::getTypeOfPlugin($plugin) ?></td>
				<td align="left"><img src="<?= $relativepath?>/img/haken.gif" border="0" /><?= _("Aktiviert") ?></td>
			</tr>
		<?php
		}		
	}
	
	function showPluginAdministrationList($plugins,$msg=""){
		$cssSw = new cssClassSwitcher();									// Klasse f�r Zebra-Design
		$cssSw->enableHover();
		echo "\n" . $cssSw->GetHoverJSFunction() . "\n";
		$cssSw->resetClass();
		// default view
		$nav = $this->pluginref->getTopNavigation();	
		$this->showMessage($msg);
		
		$relativepath = $this->pluginref->getPluginpath();
		?>
		  	<form action="<?= PluginEngine::getLink($this->pluginref) ?>" method="post">
		  	<input type="hidden" name="action" value="config" />
		  				
			<tr>
				<th align="left" width="2%"></th>
				<th align="left"><?= _("Name")?></th>
				<th align="left"><?= _("Typ") ?></th>
				<th align="center"><?= _("Verf�gbarkeit") ?></th>				
				<th align="right"><?= _("Position") ?></th>
				<th align="right"><?= _("Package") ?></th>
			</tr>
			
		<?php
		
		$absenden = makeButton("speichern","input",_("Einstellungen speichern"));
		$lasttype = "";
		foreach($plugins as $plugin){
			$cssSw->switchClass();
			if (($plugin->getPluginname() == "PluginAdministration") || ($plugin->getPluginid() == 1)){
				continue;
			}
			$type = PluginEngine::getTypeOfPlugin($plugin);
			if ($type != $lasttype){
			   ?>
			<tr>
				<td colspan="7" height="10"></td>
			</tr>
			   
			   <?php
			}
			$lasttype = $type;
			$pluginid = $plugin->getPluginid();
		?>
			<tr <?=$cssSw->getHover()?>>
				<td align="left" class="<?=$cssSw->getClass()?>"><a href="<?= PluginEngine::getLink($this->pluginref,array("deinstall" => $pluginid)) ?>"><img src="<?= $relativepath?>/img/trash.gif" border="0" alt="<?= _("Deinstallieren") ?>"/></a>&nbsp;</td>
				<td width="35%" align="left"class="<?=$cssSw->getClass()?>"><a href="<?= PluginEngine::getLink($plugin,array(),"showDescriptionalPage") ?>"><?= $plugin->getPluginname() ?></a>&nbsp;
				<?php
				if (PluginEngine::getTypeOfPlugin($plugin) == "Standard"){
				?>
					<a href="<?= PluginEngine::getLink($plugin,array(),"showConfigurationPage") ?>"><?= _("(Default-Aktivierung)")?></a></td>
				<?php
				}
				?>
				<td width="5%" align="left" class="<?=$cssSw->getClass()?>"><?= $type ?></td>
				<td align="center" class="<?=$cssSw->getClass()?>">
					<select name="available_<?= $pluginid?>"> 
						<option <? if ($plugin->isEnabled()) echo ("selected") ?>><?= _("an")?></option>
						<option <? if (!($plugin->isEnabled())) echo ("selected") ?>><?= _("aus")?></option>
					</select>
				</td>				
				<td align="right" width="5%" class="<?=$cssSw->getClass()?>"><input name="navposition_<?= $pluginid?>" type="text" size="2" value="<?= $plugin->getNavigationPosition()?>"></td>
				<td align="right" class="<?=$cssSw->getClass()?>"><a href="<?= PluginEngine::getLink($this->pluginref,array("zip" => $pluginid)) ?>"><img src="<?= $relativepath?>/img/icon-disc.gif" border="0" alt="<?= _("Plugin zippen")?>"/></a></td>
			</tr>
		<?php
		}
		?>
		  	 <tr>
		  	 	 <td colspan="7" height="5"></td>
		  	 </tr>
		  	 <tr>
		  	 	 <td colspan="7" align="center"><?= $absenden ?></td>
		  	 </tr>
		  	 <tr>		  	 	
		  	 	 <td colspan="7" height="10"></td>
		  	 </tr>		  	 
			 </form>
			 <tr>
			 	
			 	<td colspan="7">
			 		<table border="0" width="100%">
			 			<tr><th align="left"><?=_("Installation neuer Plugins")?></th> </tr>
			 			<tr>
			 				<td>
							 	<form action="<?= PluginEngine::getLink($this->pluginref)?>" enctype="multipart/form-data" method="post">
								<input type="hidden" value="install" name="action">
								<input name="upload_file" type="file" size="50" maxlength="100000">								
								<?= makeButton("hinzufuegen","input",_("neues Plugin installieren")) ?><br>
								<input type="checkbox" name="update" value="force"><?= _("Aktualisieren, falls Plugin schon vorhanden.")?>								
								</form>
							</td>
						</tr>
					</table>
			 	</td>
			 </tr>
		<?php
		
		StudIPTemplateEngine::createInfoBoxTableCell();
		
		$infobox = array	(	
						array  ("kategorie"  => _("Hinweise:"),
								"eintrag" => array	(	
									array (	"icon" => "pictures/ausruf_small.gif",
													"text"  => _("Verf�gbarkeit bedeutet bei Standard-Plugins, dass sie vom Dozenten in Veranstaltungen und Einrichtungen aktiviert werden k�nnen. Bei System- und Administrationsplugins wird zwischen Aktivierung und Verf�gbarkeit nicht unterschieden.")
									),
									array (	"icon" => "pictures/ausruf_small.gif",
													"text"  => _("Per Default-Aktivierung lassen sich Standard-Plugins automatisch in allen Veranstaltungen einer Einrichtung aktivieren.")
									),
									array (	"icon" => "pictures/ausruf_small.gif",
													"text"  => _("Position gibt die Reihenfolge des Plugins in der Navigation an. <b>Erlaubt sind nur Werte gr��er 0.</b>")
									)
								)
						)
				);
		print_infobox ($infobox,$relativepath . "/img/modules.jpg");
		StudIPTemplateEngine::endInfoBoxTableCell();		
	}
}
?>