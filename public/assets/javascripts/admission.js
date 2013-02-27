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
        jQuery('#'+targetId).empty();
        jQuery('<img/>', {
            src: STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'
        }).appendTo('#'+targetId);
        jQuery('#'+targetId).append(loading);
        jQuery('#'+targetId).load(targetUrl, query);
    },

    configureRule: function (ruleType, targetUrl) {
        var loading = 'Wird geladen'.toLocaleString();
        if (ruleType == null || jQuery('#configurerule').length == 0) {
            jQuery('<div id="configurerule" title="Anmelderegel konfigurieren">'+loading+'</div>')
                .dialog({
                    draggable: false,
                    modal: true,
                    resizable: false,
                    position: ['center', 150],
                    width: 500,
                    close: function() {
                        jQuery('#configurerule').remove();
                    },
                    open: function() {
                        jQuery('#configurerule').load(targetUrl);
                        STUDIP.ajax_indicator = true;
                    }
                });
        }
        if (ruleType != null) {
            jQuery('#configurerule').html(loading);
            jQuery('#configurerule').load(targetUrl);
        }
        return false;
    },

    saveRule: function(ruleId, targetId, targetUrl) {
        if (jQuery('#action').val() != 'cancel') {
            jQuery.ajax({
                type: 'post',
                url: targetUrl,
                data: jQuery('#ruleform').serialize(),
                dataType: 'html',
                success: function(data, textStatus, jqXHR) {
                    if (data != '') {
                        var result = '';
                        if (jQuery('#norules').length > 0) {
                            jQuery('#norules').remove();
                            jQuery('#'+targetId).prepend('<div id="rulelist"></div>');
                        }
                        result += data;
                        if (jQuery('#rule_'+ruleId).length != 0) {
                            jQuery('#rule_'+ruleId).replaceWith(result);
                        } else {
                            jQuery('#rulelist').append(result);
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Status: '+textStatus+"\nError: "+errorThrown);
                }
            });
        }
        jQuery('#configurerule').remove();
        return false;
    },

    removeRule: function(targetId, containerId) {
        var parent = jQuery('#'+targetId).parent();
        jQuery('#'+targetId).remove();
        if (parent.children('div').size() == 0) {
            parent.remove();
            var norules = 'Sie haben noch keine Anmelderegeln festgelegt.';
            jQuery('#'+containerId).prepend('<span id="norules">'+
                '<i>'+norules+'</i></span>');
        }
        return false;
    },

    toggleRuleDescription: function(targetId) {
        jQuery('#'+targetId).toggle();
        return false;
    },

    toggleDetails: function(arrowId, detailId) {
        var oldSrc = jQuery('#'+arrowId).attr('src');
        var newSrc = jQuery('#'+arrowId).attr('rel');
        jQuery('#'+arrowId).attr('src', newSrc);
        jQuery('#'+arrowId).attr('rel', oldSrc);
        jQuery('#'+detailId).slideToggle();
        return false;
    }

};