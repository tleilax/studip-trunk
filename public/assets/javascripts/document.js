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
    	    draggable: false,
    	    resizable: false,
    	    position: 'top+17%',
    	    width: 500,
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
    	    draggable: false,
    	    resizable: false,
            position: 'top+17%',
     	    width: 500,
    	    dialogClass: "ui-doc-dialog"
 		});
    },
    
    verwalten: function(id,ref) {
        var div = document.getElementById("modalDialog");
    	div.style.visibility = "visible";
    	
    	var inhalt = $.parseJSON(ref);
    	var typ = inhalt[id][2];
    	
    	$("div#modalDialog").dialog({
    	    modal: true,
    	    title: typ + " verwalten",
    	    draggable: false,
    	    resizable: false,
            position: 'top+17%',
    	    width: 500,
    	    dialogClass: "ui-doc-dialog"
 		});	
    },
    
    loeschen: function(id,ref) {
    	var div = document.getElementById("modalDialog");
    	div.style.visibility = "visible";
    	
    	var inhalt = $.parseJSON(ref);
    	var typ = inhalt[id][2];
    	
    	$("div#modalDialog").dialog({
    	    modal: true,
    	    title: typ + " löschen",
    	    draggable: false,
    	    resizable: false,
            position: 'top+17%',
    	    width: 500,
    	    dialogClass: "ui-doc-dialog"
 		});
    },

    melden: function() {
    	var div = document.getElementById("message");
    	div.style.visibility = "visible";
    	
    	$("div#message").dialog({
    	    modal: true,
    	    title: "Meldung",
    	    draggable: false,
    	    resizable: false,
    	    position: 'top+17%',
    	    width: 500,
    	    dialogClass: "ui-doc-dialog",

    	    close: function () {
    		    var url = STUDIP.URLHelper.getURL("dispatch.php/document/dateien/list");
                onClick(document.location = url); 
            }
 		 });
    }
};
