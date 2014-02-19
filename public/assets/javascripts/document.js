/* 
 * Funktionen zur Unterstuetzung der Benutzer-Interaktion im persoenlichen
 * Dateibereich 
 */

STUDIP.Document = {
       
    freigeben: function(id,ref) {
    	var div = document.getElementById("modalDialog");
    	div.style.visibility = "visible";
    	
    	var inhalt = $.parseJSON(ref);
    	var typ = inhalt[id][2];
    	
    	$("div#modalDialog").dialog({
    	    modal: true,
    	    title: typ + " teilen",
    	    draggable: true,
    	    resizable: false,
    	    position: 'top+16%',
    	    width: 550,
    	    dialogClass: "ui-doc-dialog"
 		});
    },
    
    bearbeiten: function(id,ref) {
        var div = document.getElementById("modalDialog");
    	div.style.visibility = "visible";
    	
    	var inhalt = $.parseJSON(ref);
    	var typ = inhalt[id][2];
    	
    	$("div#modalDialog").dialog({
    	    modal: true,
    	    title: typ + " bearbeiten",
    	    draggable: true,
    	    resizable: false,
            position: 'top+16%',
     	    width: 550,
    	    dialogClass: "ui-doc-dialog"
 		});
    },
    
    loeschen: function(id,ref) {	    	
    	var div = document.getElementById("remove");    	   	
    	div.style.visibility = "visible";
    	
    	if (id == -1) {
    		var typ = "Dateibereich";
        	$("#removeItem").html("M&ouml;chten Sie Ihren <b> gesamten pers&ouml;nlichen Dateibereich </b> wirklich löschen?");
    	}
    	else {
          	var inhalt = $.parseJSON(ref);
    	    var typ = inhalt[id][2];
    	    var name = inhalt[id][3];
    	    
    	    switch (typ) {
    	        case "Datei":
    	        	$("#removeItem").html("M&ouml;chten Sie die Datei <b>" + name + "</b> wirklich löschen?");
    	    	    break;
    	        case "Ordner":
    	        	$("#removeItem").html("M&ouml;chten Sie den Ordner <b>" + name + "</b> wirklich löschen?");
    	    	    break;
    	    }
    	}	    	
    	
    	$("div#remove").dialog({
    	    modal: true,
    	    title: typ + " löschen",
    	    draggable: true,
    	    resizable: false,
            position: 'top+19%',
    	    width: 550,
    	    dialogClass: "ui-doc-dialog",
    	    buttons: {
    	    	"Löschen": function() {
    	    		
    	    	},
    	    	"Abbrechen": function() {
    	    		$("div#remove").dialog("close");
    	    	}
    	    }
 		}).prev().find(".ui-dialog-titlebar-close").hide();
    },
    
    upload: function() {
    	var div = document.getElementById("upload");
    	div.style.visibility = "visible";
    	
    	//var inhalt = $.parseJSON(ref);
    	//var typ = inhalt[id][2];
    	    
    	$("div#upload").dialog({
    	    modal: true,
    	    title: "Datei hochladen",
    	    draggable: true,
    	    resizable: false,
            position: 'top+16%',
    	    width: 550,
    	    dialogClass: "ui-doc-dialog",
    	    buttons: {
    	    	"Hochladen": function() {
    	    		
    	    	},
    	    	"Abbrechen": function() {
    	    		$("div#upload").dialog("close");
    	    	}
    	    }
 		}).prev().find(".ui-dialog-titlebar-close").hide();
    
    },
    
    createDir: function() {
    	var div = document.getElementById("createDir");
    	div.style.visibility = "visible";
    	
    	//var inhalt = $.parseJSON(ref);
    	//var typ = inhalt[id][2];
    	    
    	$("div#createDir").dialog({
    	    modal: true,
    	    title: "Neuen Ordner erstellen",
    	    draggable: true,
    	    resizable: false,
            position: 'top+17%',
    	    width: 550,
    	    dialogClass: "ui-doc-dialog",
    	    buttons: {
    	    	"Erstellen": function() {
    	    		
    	    	},
    	    	"Abbrechen": function() {
    	    		$("div#createDir").dialog("close");
    	    	}
    	    }
 		}).prev().find(".ui-dialog-titlebar-close").hide();
    
    },
    
    edit: function() {
    	var div = document.getElementById("edit");
    	div.style.visibility = "visible";
    	
    	//var inhalt = $.parseJSON(ref);
    	//var typ = inhalt[id][2];
    	    
    	$("div#edit").dialog({
    	    modal: true,
    	    title: "Dateibereich beschreiben",
    	    draggable: true,
    	    resizable: false,
            position: 'top+17%',
    	    width: 550,
    	    dialogClass: "ui-doc-dialog",
    	    buttons: {
    	    	"Übernehmen": function() {
    	    		
    	    	},
    	    	"Abbrechen": function() {
    	    		$("div#edit").dialog("close");
    	    	}
    	    }
 		}).prev().find(".ui-dialog-titlebar-close").hide();
    
    },
    
    melden: function() {
    	var div = document.getElementById("message");
    	div.style.visibility = "visible";
    	
    	$("div#message").dialog({
    	    modal: true,
    	    title: "Meldung",
    	    draggable: true,
    	    resizable: false,
    	    position: 'top+18%',
    	    width: 500,
    	    dialogClass: "ui-doc-dialog",

    	    close: function () {
    		    var url = STUDIP.URLHelper.getURL("dispatch.php/document/dateien/list");
                onClick(document.location = url); 
            }
 		 });
    }
};
