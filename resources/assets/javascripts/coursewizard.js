STUDIP.CourseWizard = {

    /**
     * Adds a new participating institute to the course.
     * @param id Stud.IP institute ID
     * @param name Full name
     * @param inputName name of the for input to generate
     * @param elClass desired CSS class name
     * @param elId ID of the target container to append to
     * @param otherInput name of other inputs to check
     *
     *                   (e.g. deputies if adding a lecturer)
     */
    addParticipatingInst: function(id, name)
    {
        // Check if already set.
        if ($('input[name="participating[' + id + ']"]').length == 0) {
            var wrapper = $('<div>').addClass('institute');
            $('#wizard-participating').children('div.description').removeClass('hidden-js');
            var input = $('<input>').
                attr('type', 'hidden').
                attr('name', 'participating[' + id + ']').
                attr('id', id).
                attr('value', '1');
            var trash = $('<input>').
                attr('type', 'image').
                attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/trash.svg').
                attr('name', 'remove_participating[' + id + ']').
                attr('value', '1').
                attr('onclick', "return STUDIP.CourseWizard.removeParticipatingInst('" + id + "')");
            wrapper.append(input);
            var nametext = $('<span>').html(name).text();
            wrapper.append(nametext);
            wrapper.append(trash);
            $('#wizard-participating').append(wrapper);
        }
    },

    /**
     * Remove a participating institute from the list.
     * @param id ID of the institute to remove
     * @returns {boolean}
     */
    removeParticipatingInst: function(id)
    {
        var parent = $('input#' + id).parent();
        var grandparent = parent.parent();
        parent.remove();
        if (grandparent.children('div').length == 0) {
            grandparent.children('div.description').addClass('hidden-js');
        }
        return false;
    },


    /**
     * Adds a new person to the course.
     * @param id Stud.IP user ID
     * @param name Full name
     * @param inputName name of the for input to generate
     * @param elClass desired CSS class name
     * @param elId ID of the target container to append to
     * @param otherInput name of other inputs to check
     *
     *                   (e.g. deputies if adding a lecturer)
     */
    addPerson: function(id, name, inputName, elClass, elId, otherInput)
    {
        // Check if already set.
        if ($('input[name="' + inputName + '[' + id + ']"]').length == 0) {
            var wrapper = $('<div>').addClass(elClass);
            $('#' + elId).children('div.description').removeClass('hidden-js');
            var input = $('<input>').
                attr('type', 'hidden').
                attr('name', inputName + '[' + id + ']').
                attr('id', id).
                attr('value', '1');
            var trash = $('<input>').
                attr('type', 'image').
                attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/trash.svg').
                attr('name', 'remove_' + elClass + '[' + id + ']').
                attr('value', '1').
                attr('onclick', "return STUDIP.CourseWizard.removePerson('" + id + "')");
            wrapper.append(input);
            var nametext = $('<span>').html(name).text();
            wrapper.append(nametext);
            wrapper.append(trash);
            $('#' + elId).append(wrapper);
            // Remove as deputy if set.
            $('input[name="' + otherInput + '[' + id + ']"]').parent().remove();
        }
    },

    /**
     * Adds a new lecturer to the course.
     * @param id Stud.IP user ID
     * @param name Full name
     */
    addLecturer: function(id, name)
    {
        STUDIP.CourseWizard.addPerson(id, name, 'lecturers', 'lecturer', 'wizard-lecturers', 'deputies');
        // Add deputies if applicable.
        STUDIP.CourseWizard.addDefaultDeputies(id);
    },

    /**
     * Adds a new deputy to the course.
     * @param id Stud.IP user ID
     * @param name Full name
     */
    addDeputy: function(id, name)
    {
        STUDIP.CourseWizard.addPerson(id, name, 'deputies', 'deputy', 'wizard-deputies', 'lecturers');
    },

    addTutor: function(id, name)
    {
        STUDIP.CourseWizard.addPerson(id, name, 'tutors', 'tutor', 'wizard-tutors', 'lecturers');
    },

    /**
     * Adds the default deputies of given user to the course.
     * @param id Stud.IP user ID
     */
    addDefaultDeputies: function(id)
    {
        var lecturerDiv = $('#wizard-lecturers');
        if ($('input[name="deputy_id_parameter"]').length > 0 && lecturerDiv.data('default-enabled') == '1') {
            var params = 'step=' + $('input[name="step"]').val() +
                '&method=getDefaultDeputies' +
                '&parameter[]=' + id;
            $.ajax(
                lecturerDiv.data('ajax-url'),
                {
                    data: params,
                    success: function (data, status, xhr) {
                        if (data.length > 0) {
                            for (var i = 0 ; i < data.length ; i++) {
                                STUDIP.CourseWizard.addDeputy(data[i].id, data[i].name);
                            }
                        }
                    }
                }
            )
        }
    },

    /**
     * Remove a person (lecturer or deputy) from the list.
     * @param id ID of the person to remove
     * @returns {boolean}
     */
    removePerson: function(id)
    {
        var parent = $('input#' + id).parent();
        var grandparent = parent.parent();
        parent.remove();
        if (grandparent.children('div[class!="description"]').length == 0) {
            grandparent.children('div.description').addClass('hidden-js');
        }
        return false;
    },

    /**
     * Fetches the children of a given sem tree node.
     * @param node the ID of the parent.
     * @param assignable is the given node part of the
     *        full sem tree or the tree of already
     *        assigned nodes?
     * @returns {boolean}
     */
    getTreeChildren: function(node, assignable)
    {
        var target = $('.' + (assignable ? 'sem-tree-' : 'sem-tree-assign-') + node);
        if (!target.hasClass('tree-loaded') && target.find('.tree-loading').length == 0) {
            var params = 'step=' + $('input[name="step"]').val() +
                '&method=getSemTreeLevel' +
                '&parameter[]=' + $('#' + node).attr('id');
            $.ajax(
                $('#studyareas').data('ajax-url'),
                {
                    data: params,
                    beforeSend: function(xhr, settings) {
                        target.children('ul').append(
                            $('<li class="tree-loading">').html(
                                $('<img>').
                                    attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg').
                                    css('width', '16').
                                    css('height', '16')
                            )
                        );
                    },
                    success: function (data, status, xhr) {
                        target.find('li.sem-tree-result').remove();
                        var items = $.parseJSON(data);
                        target.find('.tree-loading').remove();
                        if (items.length > 0) {
                            var list = target.children('ul');
                            for (i = 0; i < items.length; i++) {
                                list.append(STUDIP.CourseWizard.createTreeNode(items[i], assignable));
                            }
                        }
                        target.addClass('tree-loaded');
                    },
                    error: function (xhr, status, error) {
                        alert(error);
                    }
                }
            );
        }
        if (!target.hasClass('tree-open'))
        {
            target.removeClass('tree-closed').addClass('tree-open');
        }
        else
        {
            target.removeClass('tree-open').addClass('tree-closed');
        }
        var checkbox = target.children('input[id="' + node + '"]');
        checkbox.prop('checked', !checkbox.prop('checked'));
        return false;
    },

    /**
     * Search the sem tree for a given term and show all matching nodes.
     * @returns {boolean}
     */
    searchTree: function()
    {
        var searchterm = $('#sem-tree-search').val();
        if (searchterm != '') {
            var params = 'step=' + $('input[name="step"]').val() +
                '&method=searchSemTree' +
                '&parameter[]=' + searchterm;
            $.ajax(
                $('#studyareas').data('ajax-url'),
                {
                    data: params,
                    beforeSend: function(xhr, settings) {
                        $('#sem-tree-search-start').css('display', 'none');
                        $('#sem-tree-search-start').parent().append(
                            $('<img>').
                                attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg').
                                attr('id', 'sem-tree-search-loading').
                                css('width', '16').
                                css('height', '16')
                        );
                        STUDIP.CourseWizard.loadingOverlay($('div#studyareas ul.css-tree'));
                    },
                    success: function (data, status, xhr) {
                        $('#loading-overlay').remove();
                        $('#sem-tree-search-loading').remove();
                        var items = $.parseJSON(data);
                        if (items.length > 0) {
                            $('#sem-tree-search-start').addClass('hidden-js');
                            $('#sem-tree-search-reset').removeClass('hidden-js').css('display', '');
                            $('#studyareas li input[type="checkbox"]').prop('checked', false);
                            $('#studyareas li').not('.keep-node').addClass('css-tree-hidden');
                            STUDIP.CourseWizard.buildPartialTree(items, true, '');
                            $('#sem-tree-assign-all').removeClass('hidden-js');
                            $('li.sem-tree-root input#root').prop('checked', true);
                        } else {
                            STUDIP.CourseWizard.resetSearch();
                            alert($('#studyareas').data('no-search-result'));
                        }
                    },
                    error: function (xhr, status, error) {
                        alert(error);
                    }
                }
            )
        }
        return false;
    },

    /**
     * Reset a search and restore the "normal" sem tree view.
     * @returns {boolean}
     */
    resetSearch: function() {
        $('li.css-tree-hidden').removeClass('css-tree-hidden');
        $('#sem-tree-search-start').removeClass('hidden-js').css('display', '');
        $('#sem-tree-search-reset').addClass('hidden-js');
        $('#sem-tree-search').val('');
        $('.css-tree-hidden').removeClass('css-tree-hidden');
        var notloaded = $('#studyareas li').not('.tree-loaded');
        notloaded.children('input[type="checkbox"]').prop('checked', false);
        notloaded.children('ul').empty();
        $('#sem-tree-assign-all').addClass('hidden-js');
        $('input[name="searchterm"]').remove();
        return false;
    },

    /**
     * Build a partial sem tree, containing (or showing) only selected nodes.
     * @param items items to show in the resulting tree
     * @param assignable are the nodes part of the full
     *        sem tree whose entries can be assigned?
     * @param source_node the single node that initiated the tree building,
     *        useful for marking elements.
     * @returns {boolean}
     */
    buildPartialTree: function(items, assignable, source_node)
    {
        if (assignable) {
            var classPrefix = 'sem-tree-';
        } else {
            var classPrefix = 'sem-tree-assigned-';
        }
        for (var i = 0 ; i < items.length ; i++)
        {
            var parent = $('.' + classPrefix + items[i].parent);
            var node = $('.' + classPrefix + items[i].id);
            if (node.length == 0) {
                if (!assignable && source_node == items[i].id) {
                    var selected = true;
                } else {
                    var selected = false;
                }
                var node = STUDIP.CourseWizard.createTreeNode(items[i], assignable, selected);
                parent.children('ul').append(node);
            } else {
                node.removeClass('css-tree-hidden');
                if (!assignable && items[i].id == source_node) {
                    var input = $('<input>').
                        attr('type', 'hidden').
                        attr('name', 'studyareas[]').
                        attr('value', items[i].id);
                    node.children('ul').before(input);
                    var unassign = $('<input>').
                        attr('type', 'image').
                        attr('name', 'unassign[' + items[i].id + ']').
                        attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/trash.svg').
                        attr('width', '16').
                        height('height', '16').
                        attr('onclick', "return STUDIP.CourseWizard.unassignNode('" + items[i].id + "')");
                    node.children('input[name="studyareas[]"]').before(unassign);
                }
            }
            node.children('input#' + items[i].id).prop('checked', true);
            if (items[i].assignable) {
                node.addClass('sem-tree-result');
            }
            parent.children('input[id="' + items[i].parent + '"]').attr('checked', true);
            if (items[i].has_children)
            {
                STUDIP.CourseWizard.buildPartialTree(items[i].children, assignable, source_node);
            }
        }
        return false;
    },

    /**
     * Creates a tree node element from given data.
     * @param values values for the node
     * @param assignable is the node part of the full
     *        sem tree whose entries can be assigned?
     * @returns {*|jQuery}
     */
    createTreeNode: function(values, assignable, selected)
    {
        // Node in "All study areas" tree.
        if (assignable) {
            var item = $('<li>').
                addClass('sem-tree-' + values.id);
            var assign = $('<input>').
                attr('type', 'image').
                attr('name', 'assign[' + values.id + ']').
                attr('src', STUDIP.ASSETS_URL + 'images/icons/yellow/arr_2left.svg').
                attr('width', '16').
                height('height', '16').
                attr('onclick', "return STUDIP.CourseWizard.assignNode('" + values.id + "')");
            if (values.assignable) {
                item.append(assign);
                item.append(document.createTextNode(' '));
            }
            if (values.has_children) {
                var input = $('<input>').
                    attr('type', 'checkbox').
                    attr('id', values.id);
                var label = $('<label>').
                    addClass('undecorated').
                    attr('for', values.id).
                    attr('onclick', "return STUDIP.CourseWizard.getTreeChildren('" +
                        values.id + "', true)");
                // Build link for opening the current node.
                var link = $('div#studyareas').data('forward-url');
                if (link.indexOf('?') > -1) {
                    link += '&open_node=' + values.id;
                } else {
                    link += '?open_node=' + values.id;
                }
                var openLink = $('<a>').
                    attr('href', link);
                openLink.html($('<div/>').text(values.name).html());
                label.append(openLink);
                item.append(input);
                item.append(label);
                if (values.has_children) {
                    item.append('<ul>');
                }
                if (values.assignable) {
                    if ($('#assigned li.sem-tree-assigned-' + values.id).length > 0) {
                        assign.css('display', 'none');
                    }
                }
            } else {
                if ($('#assigned li.sem-tree-assigned-' + values.id).length > 0) {
                    assign.css('display', 'none');
                }
                item.html(item.html() + $('<div/>').text(values.name).html());
                item.addClass('tree-node');
            }
        // Node in "assigned study areas" tree.
        } else {
            var item = $('<li>').
                addClass('sem-tree-assigned-' + values.id);
            item.html($('<div/>').text(values.name).html());
            if ((!values.has_children || values.assignable) && selected) {
                var unassign = $('<input>').
                    attr('type', 'image').
                    attr('name', 'unassign[' + values.id + ']').
                    attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/trash.svg').
                    attr('width', '16').
                    height('height', '16').
                    attr('onclick', "return STUDIP.CourseWizard.unassignNode('" + values.id + "')");
                item.append(unassign);
            }
            if (values.assignable && selected) {
                var input = $('<input>').
                    attr('type', 'hidden').
                    attr('name', 'studyareas[]').
                    attr('value', values.id);
                item.append(input);
            }
            item.append('<ul>');
        }
        $(item).data('id', values.id);
        return item;
    },

    /**
     * Assign a given node to the course.
     * @param id sem tree ID to assign
     * @returns {boolean}
     */
    assignNode: function(id)
    {
        var root = $('#sem-tree-assigned-nodes');
        var params = 'step=' + $('input[name="step"]').val() +
            '&method=getAncestorTree' +
            '&parameter[]=' + id;
        $.ajax(
            $('#studyareas').data('ajax-url'),
            {
                data: params,
                beforeSend: function(xhr, settings) {
                    STUDIP.CourseWizard.loadingOverlay($('div#assigned ul.css-tree'));
                },
                success: function (data, status, xhr) {
                    $('#loading-overlay').remove();
                    var items = $.parseJSON(data);
                    STUDIP.CourseWizard.buildPartialTree(items, false, id);
                    $('.sem-tree-assigned-root').removeClass('hidden-js');
                    $('input[name="assign[' + id + ']"]').hide();
                    $('svg[name="assign[' + id + ']"]').hide();
                },
                error: function (xhr, status, error) {
                    alert(error);
                }
            }
        );
        return false;
    },

    /**
     * Remove a node from the assigned ones.
     * @param id sem tree ID to unassign
     * @returns {boolean}
     */
    unassignNode: function(id)
    {
        var target = $('li.sem-tree-assigned-' + id);
        if (target.children('ul').children('li').length > 0) {
            target.children('input[name="studyareas[]"]').remove();
            target.children('input[name="unassign[' + id + ']"]').remove();
            target.children('a').remove();
        } else {
            STUDIP.CourseWizard.cleanupAssignTree(target);
        }
        $('input[name="assign[' + id + ']"]').show();
        $('svg[name="assign[' + id + ']"]').show();
        return false;
    },

    /**
     * Assign all visible nodes, e.g. search results.
     * The nodes to assign are marked by the class
     * "sem-tree-result".
     * @returns {boolean}
     */
    assignAllNodes: function()
    {
        $('.sem-tree-result').each(function(index, element)
        {
            STUDIP.CourseWizard.assignNode($(element).data('id'));
        });
        STUDIP.CourseWizard.resetSearch();
        return false;
    },

    /**
     * On unassigning a node, we need to check if the
     * parent node has other children which are still
     * assigned. If not, we can remove the parent node
     * as well.
     * @param element
     */
    cleanupAssignTree: function(element)
    {
        var parent = element.parent();
        var grandparent = parent.parent();
        if (parent.children('li').length == 1 && !grandparent.hasClass('keep-node') &&
                grandparent.children('input[type="hidden"][name="studyareas[]"]').length == 0) {
            STUDIP.CourseWizard.cleanupAssignTree(element.parent().parent());
        } else {
            element.remove();
        }
        var root = $('li.sem-tree-assigned-root');
        if (root.children('ul').children('li').length < 1)
        {
            root.addClass('hidden-js');
        }
    },

    /**
     * Show some visible indicator that there is
     * AJAX work in progress.
     * @param parent
     */
    loadingOverlay: function(parent)
    {
        var pos = parent.offset();
        var div = $('<div>').
            attr('id', 'loading-overlay').
            addClass('ui-widget-overlay').
            width($(parent).width()).
            height($(parent).height()).
            css({
                'position': 'absolute',
                'top': pos.top,
                'left': pos.left,
            });
        var loading = $('<img>').
            attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg').
            css({
                'width': 32,
                'height': 32,
                'margin-left': (div.width() / 2 - 32),
                'margin-top': (div.height() / 2 - 32)
            });
        div.append(loading);
        parent.append(div);
    }

}

$(function()
{
    if ($('.sem-tree-assigned-root').children('ul').children('li').length == 0)
    {
        $('.sem-tree-assigned-root').addClass('hidden-js');
    }
});

$(document).on('ready dialog-update', function () {
    $('.course-wizard-step-0 *:input:not(input[type=submit])').each(function (index) {
        $(this).attr('tabindex', $(this).closest('section,footer').css('order'));
    });
});

/* ------------------------------------------------------------------------
 * the local MVV namespace
 * ------------------------------------------------------------------------ */
window.MVV = window.MVV || {};

MVV.CourseWizard = {

    /**
     * Fetches the children of a given lvgroup.
     * @param node the ID of the parent.
     * @param assignable is the given lvgroup assignable?
     * @returns {boolean}
     */
    getTreeChildren: function(node, assignable, classtype)
    {
    	var target = $('.' + (assignable ? 'lvgroup-tree-' : 'lvgroup-tree-assign-') + node);
        if (!target.hasClass('tree-loaded')) {
            var params = 'step=' + $('input[name="step"]').val() +
                '&method=getLVGroupTreeLevel' +
                '&parameter[]=' + $('#' + node).attr('id') +
                '&parameter[]=' + classtype;
            $.ajax(
                $('#studyareas').data('ajax-url'),
                {
                    data: params,
                    beforeSend: function(xhr, settings) {
                        target.children('ul').append(
                            $('<li class="tree-loading">').html(
                                $('<img>').
                                    attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg').
                                    css('width', '16').
                                    css('height', '16')
                            )
                        );
                    },
                    success: function (data, status, xhr) {
                        var items = $.parseJSON(data);
                        target.find('.tree-loading').remove();
                        if (items.length > 0) {
                            var list = target.children('ul');
                            for (i = 0; i < items.length; i++) {
                                if (items[i].assignable || items[i].has_children) {
                                    list.append(MVV.CourseWizard.createTreeNode(items[i], assignable));
                                }
                            }
                        }
                        target.addClass('tree-loaded');
                        
                        var onode = $('<input>').
                        attr('type', 'hidden').
                        attr('name', 'open_lvg_nodes[]').
                        attr('value', node);
                        $('#lvgroup-tree-open-nodes').append(onode);
                        
                    },
                    error: function (xhr, status, error) {
                        alert(error);
                    }
                }
            );
        }
        if (!target.hasClass('tree-open'))
        {
            target.removeClass('tree-closed').addClass('tree-open');
        }
        else
        {
            target.removeClass('tree-open').addClass('tree-closed');
        }
        var checkbox = target.children('input[id="' + node + '"]');
        checkbox.prop('checked', !checkbox.prop('checked'));
        return false;
    },

    /**
     * Search the lvgruppen tree for a given term and show all matching groups.
     * @returns {boolean}
     */
    searchTree: function()
    {
        var searchterm = $('#lvgroup-tree-search').val();
        if (searchterm != '') {
            $.ajax(
                $('#studyareas').data('ajax-url'),
                {
                    data: {
                        'step': $('input[name="step"]').val(),
                        'method': 'searchLVGroupTree',
                        'parameter[]': searchterm
                    },
                    method: "POST",
                    beforeSend: function(xhr, settings) {
                        $('#lvgroup-tree-search-start').css('display', 'none');
                        $('#lvgroup-tree-search-start').parent().append(
                            $('<img>').
                                attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg').
                                attr('id', 'lvgroup-tree-search-loading').
                                css('width', '16').
                                css('height', '16')
                        );
                    },
                    success: function (data, status, xhr) {
                        $('#lvgroup-tree-search-loading').remove();
                        var items = $.parseJSON(data);
                        if (items.length > 0) {                        	
                        	$("#lvgsearchresults ul").empty();
                        	$("#lvgsearchresults").show();
                        	for (i = 0; i < items.length; i++) {
                                    lvgroup_html = $(items[i].html_string);
                                    if ($('#lvgroup-tree-assigned-' + items[i].id).length) {
                                        lvgroup_html.find('input').first().css('visibility', 'hidden');
                                    }
                                    $("#lvgsearchresults ul").append(lvgroup_html);
                        	}
                        	
                        } else {
                        	$("#lvgsearchresults ul").empty();
                        	$("#lvgsearchresults").hide();
                            MVV.CourseWizard.resetSearch();
                            alert($('#studyareas').data('no-search-result'));
                        }
                    },
                    error: function (xhr, status, error) {
                    	$('#lvgroup-tree-search-loading').remove();
                        alert(error);
                    }
                }
            )
        }
        return false;
    },

    /**
     * Reset a search and empty the search result.
     * @returns {boolean}
     */
    resetSearch: function() {    	
        $('#lvgroup-tree-search').val('');
        $('#lvgsearchresults ul').empty();
        $('#lvgsearchresults').hide();        
        return false;
    },
    
    /**
     * Creates a tree node element from given data.
     * @param values values for the node
     * @param assignable is the given lvgroup assignable?
     * @returns {*|jQuery}
     */
    createTreeNode: function(values, assignable, selected)
    {
        // Node in lvgroups tree.
        if (assignable) {
        	
        	var mvv_ids = values.id.split('-');
        	
            var item = $('<li>').
                addClass('lvgroup-tree-' + values.id);
            var assign = $('<input>').
                attr('type', 'image').
                attr('name', 'assign[' + values.id + ']').
                attr('src', STUDIP.ASSETS_URL + 'images/icons/yellow/arr_2left.svg').
                attr('width', '16').
                height('height', '16').
                attr('onclick', "return MVV.CourseWizard.assignNode('" + values.id + "')");
            if (values.assignable) {
                item.append(assign);
                item.append(document.createTextNode(' '));
            }
            if (values.has_children) {
                var input = $('<input>').
                    attr('type', 'checkbox').
                    attr('id', values.id);
                var label = $('<label>').
                    attr('for', values.id).
                    attr('onclick', "return MVV.CourseWizard.getTreeChildren('" +
                        values.id + "', true, '" + values.mvvclass + "')");
                // Build link for opening the current node.
                var link = $('div#studyareas').data('forward-url');
                if (link.indexOf('?') > -1) {
                    link += '&open_node=' + values.id;
                } else {
                    link += '?open_node=' + values.id;
                }
                var openLink = $('<a>').
                    attr('href', link);
                openLink.html(values.name);
                label.append(openLink);
                item.append(input);
                item.append(label);
                if (values.has_children) {
                    item.append('<ul>');
                }
                if (values.assignable) {
                    if ($('#lvgroup-tree-assigned-' + mvv_ids[0]).length > 0) {
                        assign.css('display', 'none');
                    }
                }
            } else {
            	if ($('#lvgroup-tree-assigned-' + mvv_ids[0]).length > 0) {
                    assign.css('display', 'none');
                }
                item.html(item.html() + values.name);
                item.addClass('tree-node');
            }
        
        }
        
        $(item).data('id', values.id);
        return item;
    },

    /**
     * Assign a given node to the course.
     * @param id lvgoup ID to assign
     * @returns {boolean}
     */
    assignNode: function(id)
    {
        var root = $('#lvgroup-tree-assigned-selected');
        var params = 'step=' + $('input[name="step"]').val() +
            '&method=getAncestorTree' +
            '&parameter[]=' + id;
        $.ajax(
            $('#studyareas').data('ajax-url'),
            {
                data: params,
                beforeSend: function(xhr, settings) {
                    MVV.CourseWizard.loadingOverlay($('div#assigned ul.css-tree'));
                },
                success: function (data, status, xhr) {
                    $('#loading-overlay').remove();
                    var items = $.parseJSON(data);
                    
                    var lvgid = id.split('-');
                    if ($('#lvgroup-tree-assigned-' + lvgid).length === 0) {
                        var input = $('<input>').
                        attr('type', 'hidden').
                        attr('name', 'lvgroups[]').
                        attr('value', items.id);
                        root.before(input);
                        root.append(items.html_string);
                    }
                    
                    $("input[name*='assign["+lvgid[0]+"']").each(function(){
                		$(this).hide();
                	});
                    $("svg[name*='assign["+lvgid[0]+"']").each(function(){
                		$(this).hide();
                	});
                },
                error: function (xhr, status, error) {
                    alert(error);
                }
            }
        );
        return false;
    },


    /**
     * Show some visible indicator that there is
     * AJAX work in progress.
     * @param parent
     */
    loadingOverlay: function(parent)
    {
        var pos = parent.offset();
        var div = $('<div>').
            attr('id', 'loading-overlay').
            addClass('ui-widget-overlay').
            width($(parent).width()).
            height($(parent).height()).
            css({
                'position': 'absolute',
                'top': pos.top,
                'left': pos.left,
            });
        var loading = $('<img>').
            attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg').
            css({
                'width': 32,
                'height': 32,
                'margin-left': (div.width() / 2 - 32),
                'margin-top': (div.height() / 2 - 32)
            });
        div.append(loading);
        parent.append(div);
    },
    
    /**
     * Show details of a node.
     * @param id lvgroup ID to unassign
     * @returns {boolean}
     */
    showDetails: function(id)
    {    	
    	if ($('#lvgruppe_selection_detail_'+id).is(":visible")) {    		
    		$('#lvgruppe_selection_detail_'+id).empty();
    		$('#lvgruppe_selection_detail_'+id).hide();	    		
    	} else {    		
    		$('#lvgruppe_selection_detail_'+id).empty();	        	        	
        	var params = 'step=' + $('input[name="step"]').val() +
            '&method=getLVGroupDetails' +
            '&parameter[]=' + id;
    	    $.ajax(
    	        $('#assigned').data('ajax-url'),
    	        {
    	            data: params,
    	            beforeSend: function(xhr, settings) {
    	                MVV.CourseWizard.loadingOverlay($('div#assigned ul.css-tree'));
    	            },
    	            success: function (data, status, xhr) {
    	                $('#loading-overlay').remove();
    	                var items = $.parseJSON(data);
    	                $('#lvgroup-tree-assigned-'+id+' ul').append(items.html_string);
    	            },
    	            error: function (xhr, status, error) {
    	                alert(error);
    	            }
    	        }
    	    );   
        	$('#lvgruppe_selection_detail_'+id).show();
    	}
		return false;    	
    },
    
    /**
     * Show details of a searchnode.
     * @param id lvgroup ID to unassign
     * @returns {boolean}
     */
    showSearchDetails: function(id)
    {    
		if ($('#lvgruppe_search_'+id+' ul').is(":visible")) {    		
    		$('#lvgruppe_search_'+id+' ul').remove();    		
    	} else {   		    	
	    	var params = 'step=' + $('input[name="step"]').val() +
	        '&method=getLVGroupDetails' +
	        '&parameter[]=' + id;
		    $.ajax(
		        $('#studyareas').data('ajax-url'),
		        {
		            data: params,
		            beforeSend: function(xhr, settings) {
		                MVV.CourseWizard.loadingOverlay($('div#lvgsearchresults ul.css-tree'));
		            },
		            success: function (data, status, xhr) {
		                $('#loading-overlay').remove();
		                var items = $.parseJSON(data);		                
		                $('#lvgruppe_search_'+id).append("<ul>"+items.html_string+"</ul>");		                
		            },
		            error: function (xhr, status, error) {
		                alert(error);
		            }
		        }
		    );		    
    	}    
    	
		return false;    	
    },
    
    /**
     * Remove a node from the assigned ones.
     * @param id lvgroup ID to unassign
     * @returns {boolean}
     */
    removeLVGroup: function(id)
    {
    	$('#lvgroup-tree-assigned-'+id).remove();    
    	$("input[name*='assign["+id+"']").each(function(){
    		$(this).show();
    	});
    	return false;    	
    }
    
}
