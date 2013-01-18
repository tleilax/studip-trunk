/*jslint browser: true, white: true, undef: true, nomen: true, eqeqeq: true, plusplus: true, bitwise: true, newcap: true, immed: true, indent: 4, onevar: false */
/*global window, $, jQuery, _ */

/* ------------------------------------------------------------------------
 * Bedingungen zur Auswahl von Stud.IP-Nutzern
 * ------------------------------------------------------------------------ */

STUDIP.Conditions = {

    configureCondition: function (targetUrl) {
        var loading = 'Wird geladen'.toLocaleString();
        $('<div id="condition" title="Bedingung konfigurieren">'+loading+'</div>')
            .dialog({
                draggable: false,
                modal: true,
                resizable: false,
                position: ['center', 200],
                width: 450,
                close: function() {
                    $('#condition').remove();
                },
                open: function() {
                    $('#condition').load(targetUrl);
                    STUDIP.ajax_indicator = true;
                }
            });
        return false;
    },

    addCondition: function(containerId, targetId, targetUrl) {
        var children = $('#'+containerId).children('.conditionfield');
        query = '';
        for (var i=0 ; i<children.size() ; i++) {
            var current = $(children[i]);
            query += '&field[]='+
                current.children('.conditionfield_class').first().val()+
                '&compare_operator[]='+
                current.children('.conditionfield_compare_op').first().val()+
                '&value[]='+
                current.children('.conditionfield_value').first().val();
        }
        query += '&startdate='+$('#startdate').val()+
            '&starthour='+$('#starthour').val()+
            '&startminute='+$('#startminute').val()+
            '&enddate='+$('#enddate').val()+
            '&endhour='+$('#endhour').val()+
            '&endminute='+$('#endminute').val();
        $.ajax({
            type: 'post',
            url: targetUrl,
            data: query,
            dataType: 'html',
            success: function(data, textStatus, jqXHR) {
                var result = '';
                if ($('#noconditions').length > 0) {
                    $('#noconditions').remove();
                    $('#'+targetId).prepend('<div id="conditionlist"></div>');
                } else {
                    result += '<b>'+'oder'.toLocaleString()+'</b>';
                }
                result += data;
                $('#conditionlist').append(result);
            }
        });
        $('#condition').remove();
    },

    getConditionFieldConfiguration: function(element, targetUrl) {
        var target = jQuery(element).parent();
        jQuery.ajax({
            type: 'post',
            url: targetUrl,
            data: { 'fieldtype': jQuery(element).val() },
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
        jQuery.ajax({
            type: 'post',
            url: targetUrl,
            dataType: 'html',
            success: function(data, textStatus, jqXHR) {
                jQuery('#'+targetId).append(data);
            },
            error: function() {
                jQuery('#'+targetId).append('Something not work here.');
            }
        });
        return false;
    },

    removeConditionField: function(element) {
        element.remove();
        return false;
    }
};