$(document).ready(function () {
    $(".openmultipersonsearch").click(function(){
        STUDIP.MultiPersonSearch.dialog($(this).attr("data-dialogname"));
        return false;
    });
});

STUDIP.MultiPersonSearch = {
    
    dialog: function (name) {
        
        $( "#" + name ).dialog({
            height: 450,
            width: 720,
            modal: true
        });
        
        this.name = name;
        
        $( "#" + name + " button[name='" + name + "_button_abort']").click(function() {
            $( "#" + name ).dialog( "close" );
            return false;
        });
        
        $('#' + name + '_selectbox').multiSelect({
            selectableHeader: "<div>" + "Suchergebnisse".toLocaleString() + "</div>",
            selectionHeader: "<div>Sie haben <span id='" + this.name + "_count'>0</span> " + "Personen ausgewählt".toLocaleString() + ".</div>",
            selectableFooter: '<a href="javascript:STUDIP.MultiPersonSearch.selectAll();">' + 'Alle hinzufügen'.toLocaleString() + '</a>',
            selectionFooter: '<a href="javascript:STUDIP.MultiPersonSearch.unselectAll();">' + 'Alle entfernen'.toLocaleString() + '</a>'
        });
        //STUDIP.MultiPersonSearch.restoreDefaults();
        
        $("#" + this.name).on("keyup keypress", function(e) {
            var code = e.keyCode || e.which; 
            if (code  == 13) {               
            e.preventDefault();
            STUDIP.MultiPersonSearch.search();
            return false;
            }
        });
        
        $("#" + this.name + "_selectbox").change(function() {
            STUDIP.MultiPersonSearch.count();
        });
        
        $("#" + this.name + " .quickfilter").click(function() {
            STUDIP.MultiPersonSearch.loadQuickfilter($(this).attr("data-quickfilter"));
            return false;
        });
    },
    
    // Restore the pre(un)selected options
    restoreDefaults: function() {
        STUDIP.MultiPersonSearch.removeAll();
        
        $('#' + this.name + '_selectbox_default option').each(function() {
           STUDIP.MultiPersonSearch.append($(this).val(), $(this).text(), $(this).is(':selected'));
        });
        $('#' + this.name + '_selectbox').multiSelect('refresh');
    },
    
    loadQuickfilter: function(title) {
        STUDIP.MultiPersonSearch.removeAllNotSelected();
        
        var count = 0;
        $('#' + this.name + '_quickfilter_' + title + ' option').each(function() {
           count += STUDIP.MultiPersonSearch.append($(this).val(), $(this).text(), STUDIP.MultiPersonSearch.isAlreadyMember($(this).val()));
        });
        $('#' + this.name + '_selectbox').multiSelect('refresh');
        
        if (count == 0) {
            $("#" + this.name + "_quickfilter_message_box").show();
        } else {
            $("#" + this.name + "_quickfilter_message_box").hide();
        }
        $("#" + this.name + "_search_message_box").hide();
    },
    
    isAlreadyMember: function(user_id) {
        if ($('#' + this.name + '_selectbox_default option[value="' + user_id + '"]').length > 0) {
            return true;
        } else {
            return false;
        }
    },
    
    search: function () {
        var searchterm = $("#" + this.name + "_searchinput").val();
        var name = this.name;
        $.getJSON(  STUDIP.URLHelper.getURL("dispatch.php/multipersonsearch/ajax_search/" + this.name + "/"  + searchterm), function( data ) {
            STUDIP.MultiPersonSearch.removeAllNotSelected();
            var searchcount = 0;
            $.each( data, function( i, item ) {
                searchcount += STUDIP.MultiPersonSearch.append(item.user_id, item.avatar + ' -- ' + item.text, item.member)
            });
            
            if (searchcount == 0) {
                $("#" + name + "_search_message_box").show();
            } else {
                $("#" + name + "_search_message_box").hide();
            }
            $("#" + name + "_quickfilter_message_box").hide();
        });
        return false;
    },
    
    selectAll: function () {
       $('#' + this.name + '_selectbox option').prop('selected', true);
       $('#' + this.name + '_selectbox').multiSelect('refresh');
       STUDIP.MultiPersonSearch.count();
    },
    
    unselectAll: function () {
        $('#' + this.name + '_selectbox option').prop('selected', false);
        $('#' + this.name + '_selectbox').multiSelect('refresh');
        STUDIP.MultiPersonSearch.count();
    },
    
    removeAll: function () {
        $('#' + this.name + '_selectbox option').remove();
        $('#' + this.name + '_selectbox').multiSelect('refresh');
    },
    
    removeAllNotSelected: function () {
        $('#' + this.name + '_selectbox option:not(:selected)').remove();
        $('#' + this.name + '_selectbox').multiSelect('refresh');
    },
    
    append: function (value, text, selected) {
        if ($('#' + this.name + '_selectbox option[value=' + value + ']').length == 0) {
            var option;
            if (selected) {
                option = $('<option value="' + value + '" disabled>' + text + '</option>');
            } else {
                option = $('<option value="' + value + '">' + text + '</option>');
            }
            $('#' + this.name + '_selectbox').append(option);
            $('#' + this.name + '_selectbox').multiSelect('refresh');
            STUDIP.MultiPersonSearch.count();
            return 1;
        }
        return 0;
    },
    
    count: function () {
        $('#' + this.name + '_count').text($('#' + this.name + '_selectbox option:selected').length); 
    }
    
};
