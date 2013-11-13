/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

/* ------------------------------------------------------------------------
 * Bedingungen zur Auswahl von Stud.IP-Nutzern
 * ------------------------------------------------------------------------ */

STUDIP.Conditions = {

    configureCondition: function (targetId, targetUrl) {
        var loading = 'Wird geladen'.toLocaleString();
        $('<div id="'+targetId+'" title="Bedingung konfigurieren">'+loading+'</div>')
            .dialog({
                draggable: false,
                modal: true,
                resizable: false,
                position: ['center', 200],
                width: 450,
                close: function() {
                    $('#'+targetId).remove();
                },
                open: function() {
                    $('#'+targetId).empty();
                    $('<img/>', {
                        src: STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'
                    }).appendTo('#'+targetId);
                    $('#'+targetId).append(loading);
                    $('#'+targetId).load(targetUrl);
                }
            });
        return false;
    },

    addCondition: function(containerId, targetUrl) {
        var children = $('.conditionfield');
        var query = '';
        for (var i=0 ; i<children.size() ; i++) {
            var current = $(children[i]);
            if (query != '') {
                query += '&';
            }
            query += 'field[]='+
                encodeURIComponent(current.children('.conditionfield_class:first').val())+
                '&compare_operator[]='+
                encodeURIComponent(current.children('.conditionfield_compare_op:first').val())+
                '&value[]='+
                encodeURIComponent(current.children('.conditionfield_value:first').val());
        }
        $.ajax({
            type: 'post',
            url: targetUrl,
            data: query,
            dataType: 'html',
            success: function(data, textStatus, jqXHR) {
                var result = '';
                if ($('#'+containerId).children('.noconditions').length > 0) {
                    $('#'+containerId).children('.noconditions').remove();
                    $('#'+containerId).prepend('<div class="conditionlist"></div>');
                } else {
                    result += '<b>'+'oder'.toLocaleString()+'</b>';
                }
                result += data;
                $('#'+containerId).find('.conditionlist').append(result);
            }
        });
        $('#condition').remove();
    },

    getConditionFieldConfiguration: function(element, targetUrl) {
        var target = $(element).parent();
        $.ajax({
            type: 'post',
            url: targetUrl,
            data: { 'fieldtype': $(element).val() },
            dataType: 'html',
            success: function(data, textStatus, jqXHR) {
                target.children('.conditionfield_compare_op').remove();
                target.children('.conditionfield_value').remove();
                target.children('.conditionfield_delete').first().before(data);
            },
            error: function() {
                target.before('Something not work here.');
            }
        });
        return false;
    },

    addConditionField: function(targetId, targetUrl) {
        $.ajax({
            type: 'post',
            url: targetUrl,
            dataType: 'html',
            success: function(data, textStatus, jqXHR) {
                $('#'+targetId).append(data);
            },
            error: function() {
                $('#'+targetId).append('Something not work here.');
            }
        });
        return false;
    },

    removeConditionField: function(element) {
        element.remove();
        STUDIP.Dialogs.closeConfirmDialog();
        return false;
    }
};