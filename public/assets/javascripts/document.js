/* 
 * Funktionen zur Unterstuetzung der Benutzer-Interaktion im persoenlichen
 * Dateibereich 
 */

STUDIP.Document = {
       	
    addDir: function() {
	    var div = document.getElementById("addDir");
	    div.style.visibility = "visible";
	    	
	    //var inhalt = $.parseJSON(ref);
	    //var typ = inhalt[id][2];
	    	    
	    $("div#addDir").dialog({
	        modal: true,
	        title: "Neuen Ordner erstellen",
	        draggable: true,
	        resizable: false,
	        position: 'top+17%',
	        width: 550,
	        dialogClass: "ui-doc-dialog"
	 	}).prev().find(".ui-dialog-titlebar-close").hide();
	    
	},
	
	edit: function(ord,ref) {
    	var div = document.getElementById("edit");
    	div.style.visibility = "visible";
    	
    	if (ord == -1) {
    		var typ = "Dateibereich";
    		var name = typ;
    		$("#displayItem").html(name);	
    	}
    	else {
          	var inhalt = $.parseJSON(ref); 	
    	    var typ = inhalt[ord]["type"];
    	    var name = inhalt[ord]["name"];
    	    $("#displayItem").html(name);	
    	}
    	    
    	$("div#edit").dialog({
    	    modal: true,
    	    title: typ + " beschreiben",
    	    draggable: true,
    	    resizable: false,
            position: 'top+16%',
    	    width: 550,
    	    dialogClass: "ui-doc-dialog"
 		}).prev().find(".ui-dialog-titlebar-close").hide();
    
    },
    
    remove: function(ord,ref) {    	
    	var div = document.getElementById("remove");    	   	
    	div.style.visibility = "visible";
    	
    	if (ord == -1) {
    		var typ = "Dateibereich";
        	$("#removeItem").html("M&ouml;chten Sie Ihren <b> gesamten pers&ouml;nlichen Dateibereich </b> wirklich löschen?");
    	}
    	else {
          	var inhalt = $.parseJSON(ref); 	
    	    var typ = inhalt[ord]["type"];
    	    var name = inhalt[ord]["name"];
    	     
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
    	    dialogClass: "ui-doc-dialog"
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
    	    dialogClass: "ui-doc-dialog"
 		}).prev().find(".ui-dialog-titlebar-close").hide();
    
    }
};
