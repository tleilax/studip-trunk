/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

/* ------------------------------------------------------------------------
 * Anmeldeverfahren und -sets
 * ------------------------------------------------------------------------ */

STUDIP.Admission = {

    configureRule: function (ruleType, targetUrl) {
        var loading = 'Wird geladen'.toLocaleString();
        if (ruleType == null) {
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
        } else {
            $('#configurerule').html(loading);
            $('#configurerule').load(targetUrl);
        }
        return false;
    },

    addRule: function(targetId, targetUrl) {
        $.ajax({
            type: 'post',
            url: targetUrl,
            data: $('#ruleform').serialize(),
            dataType: 'html',
            success: function(data, textStatus, jqXHR) {
                var result = '';
                if ($('#norules').length > 0) {
                    $('#norules').remove();
                    $('#'+targetId).prepend('<div id="rulelist"></div>');
                }
                result += data;
                $('#rulelist').append(result);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Status: '+textStatus+"\nError: "+errorThrown);
            }
        });
        $('#ruleform')
        $('#configurerule').remove();
    },

    toggleRuleDescription: function(targetId) {
        $('#'+targetId).toggle();
        return false;
    },

    toggleRuleDetails: function(arrowId, detailId) {
        var oldSrc = $('#'+arrowId).attr('src');
        var newSrc = $('#'+arrowId).attr('rel');
        $('#'+arrowId).attr('src', newSrc);
        $('#'+arrowId).attr('rel', oldSrc);
        $('#'+detailId).slideToggle();
        return false;
    }
};