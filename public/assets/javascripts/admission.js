/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

/* ------------------------------------------------------------------------
 * Anmeldeverfahren und -sets
 * ------------------------------------------------------------------------ */

STUDIP.Admission = {

    getCourses: function(source, targetId, targetUrl) {
        var selected = jQuery('.'+source+':checked');
        var query = '';
        for (var i=0 ; i<selected.length ; i++) {
            query += '&institutes[]='+selected[i].value;
        }
        var loading = 'Wird geladen'.toLocaleString();
        $('#'+targetId).empty();
        $('<img/>', {
            src: STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'
        }).appendTo('#'+targetId);
        $('#'+targetId).append(loading);
        $('#'+targetId).load(targetUrl, query);
    },

    configureRule: function (ruleType, targetUrl) {
        var loading = 'Wird geladen'.toLocaleString();
        if (ruleType == null || $('#configurerule').length == 0) {
            $('<div id="configurerule" title="Anmelderegel konfigurieren">'+loading+'</div>')
                .dialog({
                    draggable: false,
                    modal: true,
                    resizable: false,
                    position: ['center', 150],
                    width: 500,
                    close: function() {
                        $('#configurerule').remove();
                    },
                    open: function() {
                        $('#configurerule').load(targetUrl);
                        STUDIP.ajax_indicator = true;
                    }
                });
        }
        if (ruleType != null) {
            $('#configurerule').html(loading);
            $('#configurerule').load(targetUrl);
        }
        return false;
    },

    saveRule: function(ruleId, targetId, targetUrl) {
        if ($('#action').val() != 'cancel') {
            $.ajax({
                type: 'post',
                url: targetUrl,
                data: $('#ruleform').serialize(),
                dataType: 'html',
                success: function(data, textStatus, jqXHR) {
                    if (data != '') {
                        var result = '';
                        if ($('#norules').length > 0) {
                            $('#norules').remove();
                            $('#'+targetId).prepend('<div id="rulelist"></div>');
                        }
                        result += data;
                        if ($('#rule_'+ruleId).length != 0) {
                            $('#rule_'+ruleId).replaceWith(result);
                        } else {
                            $('#rulelist').append(result);
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Status: '+textStatus+"\nError: "+errorThrown);
                }
            });
        }
        $('#configurerule').remove();
        return false;
    },

    removeRule: function(targetId, containerId) {
        var parent = $('#'+targetId).parent();
        $('#'+targetId).remove();
        if (parent.children('div').size() == 0) {
            parent.remove();
            var norules = 'Sie haben noch keine Anmelderegeln festgelegt.';
            $('#'+containerId).prepend('<span id="norules">'+
                '<i>'+norules+'</i></span>');
        }
        STUDIP.Dialogs.closeConfirmDialog();
    },

    toggleRuleDescription: function(targetId) {
        $('#'+targetId).toggle();
        return false;
    },

    toggleDetails: function(arrowId, detailId) {
        var oldSrc = $('#'+arrowId).attr('src');
        var newSrc = $('#'+arrowId).attr('rel');
        $('#'+arrowId).attr('src', newSrc);
        $('#'+arrowId).attr('rel', oldSrc);
        $('#'+detailId).slideToggle();
        return false;
    }

};