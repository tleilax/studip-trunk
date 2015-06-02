STUDIP.CourseWizard = {

    /**
     * Fetches a quicksearch input form via AJAX. This is necessary as the
     * required QuickSearch needs an institute ID which is not known in
     * advance and is provided by JS here.
     */
    getLecturerSearch: function()
    {
        var params = 'step=' + $('input[name="step"]').val() +
            '&method=getSearch' +
            '&parameter[]=' + $('select[name="type"] option:selected').val() +
            '&parameter[]=' + $('select[name="institute"] option:selected').val() +
            '&parameter[][]=';
        $('input[name="lecturers[]"]').each(function (l) {
            params += '&parameter[][]=' + $(this).val();
        });
        $('span#lecturersearch').load(
            $('select[name="institute"]').data('ajax-url'),
            params
        );
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
            var wrapper = $('<div>').attr('class', elClass);
            var input = $('<input>').
                attr('type', 'hidden').
                attr('name', inputName + '[' + id + ']').
                attr('value', '1');
            var trashLink = $('<a>').
                attr('href', '').
                attr('onclick', 'return STUDIP.CourseWizard.removePerson(this)');
            var trash = $('<img>').
                attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/trash.svg');
            trashLink.append(trash);
            wrapper.append(input);
            wrapper.append(name);
            wrapper.append(trashLink);
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
        STUDIP.CourseWizard.addPerson(id, name, 'lecturers', 'lecturer', 'lecturers', 'deputies');
    },

    /**
     * Adds a new deputy to the course.
     * @param id Stud.IP user ID
     * @param name Full name
     */
    addDeputy: function(id, name)
    {
        STUDIP.CourseWizard.addPerson(id, name, 'deputies', 'deputy', 'deputies', 'lecturers');
    },

    /**
     * Remove a person (lecturer or deputy) from the list.
     * @param element clicked element (trash icon)
     * @returns {boolean}
     */
    removePerson: function(element)
    {
        $(element).parent().remove();
        return false;
    },

    getTreeChildren: function(node, assignable)
    {
        var target = $('.' + (assignable ? 'sem-tree-' : 'sem-tree-assign-') + node);
        if (!target.hasClass('tree-loaded')) {
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
        checkbox.attr('checked', !checkbox.attr('checked'));
        return false;
    },

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
                    },
                    success: function (data, status, xhr) {
                        var startLink = $('#sem-tree-search-start');
                        startLink.
                            attr('onclick', 'return STUDIP.CourseWizard.resetSearch()').
                            css('display', '').
                            empty().
                            append($('<img>').
                                attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/refresh.svg'));
                        $('#sem-tree-search-loading').remove();
                        $('#studyareas li input[type="checkbox"]').attr('checked', false);
                        $('#studyareas li').not('.keep-node').addClass('css-tree-hidden');
                        var items = $.parseJSON(data);
                        STUDIP.CourseWizard.buildPartialTree(items, 'sem-tree-', '');
                    },
                    error: function (xhr, status, error) {
                        alert(error);
                    }
                }
            )
        }
        return false;
    },

    buildPartialTree: function(items, assignable)
    {
        if (assignable) {
            var classPrefix = 'sem-tree-';
        } else {
            var classPrefix = 'sem-tree-assigned-';
        }
        for (var i = 0 ; i < items.length ; i++)
        {
            console.log(items[i]);
            var parent = $('.' + classPrefix + items[i].parent);
            if ($('.' + classPrefix + items[i].id).length == 0) {
                var node = STUDIP.CourseWizard.createTreeNode(items[i], assignable);
                node.addClass('css-tree-show');
                parent.children('ul').append(node);
            } else {
                $('.' + classPrefix + items[i].id).removeClass('css-tree-hidden');
            }
            parent.children('input[id="' + items[i].parent + '"]').attr('checked', true);
            if (items[i].has_children)
            {
                STUDIP.CourseWizard.buildPartialTree(items[i].children, assignable);
            }
        }
        return false;
    },

    createTreeNode: function(data, assignable)
    {
        if (assignable) {
            var item = $('<li>').
                addClass('sem-tree-' + data.id);
            var assign = $('<img>').
                attr('src', STUDIP.ASSETS_URL + 'images/icons/yellow/arr_2left.svg').
                css('width', '16').
                css('height', '16');
            var assignLink = $('<a>').
                attr('href', '').
                attr('onclick', "return STUDIP.CourseWizard.assignNode('" + data.id + "')");
            assignLink.append(assign);
            if (data.has_children) {
                var input = $('<input>').
                    attr('type', 'checkbox').
                    attr('id', data.id);
                var label = $('<label>').
                    attr('for', data.id).
                    attr('onclick', "return STUDIP.CourseWizard.getTreeChildren('" + data.id + "', " + assignable + ")");
                if (data.assignable) {
                    label.append(assign);
                }
                label.html(label.html() + data.name);
                item.append(input);
                item.append(label);
                item.append('<ul>');
                if (data.assignable) {
                    if ($('#assigned li.sem-tree-assigned-' + data.id).length > 0) {
                        assignLink.css('display', 'none');
                    }
                    item.append(assignLink);
                }
            } else {
                if ($('#assigned li.sem-tree-assigned-' + data.id).length > 0) {
                    assignLink.css('display', 'none');
                }
                item.append(assignLink);
                item.html(item.html() + data.name);
                item.addClass('tree-node');
            }
        } else {
            var item = $('<li>').
                addClass('sem-tree-assigned-' + data.id);
            item.html(data.name);
            if (!data.has_children) {
                var unassign = $('<img>').
                    attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/trash.svg').
                    css('width', '16').
                    css('height', '16');
                var unassignLink = $('<a>').
                    attr('href', '').
                    attr('onclick', "return STUDIP.CourseWizard.unassignNode('" + data.id + "')");
                unassignLink.append(unassign);
                item.append(unassignLink);
            }
            if (data.assignable) {
                var input = $('<input>').
                    attr('type', 'hidden').
                    attr('name', 'studyareas[]').
                    attr('value', data.id);
                item.append(input);
            }
            item.append('<ul>');
        }
        return item;
    },

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
                /*beforeSend: function(xhr, settings) {
                    $('#sem-tree-search-start').css('display', 'none');
                    $('#sem-tree-search-start').parent().append(
                        $('<img>').
                            attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg').
                            attr('id', 'sem-tree-search-loading').
                            css('width', '16').
                            css('height', '16')
                    );
                },*/
                success: function (data, status, xhr) {
                    //$('#sem-tree-search-start').css('display', 'inline');
                    //$('#sem-tree-search-loading').remove();
                    var items = $.parseJSON(data);
                    STUDIP.CourseWizard.buildPartialTree(items, false);
                    $('.sem-tree-assigned-root').css('display', '');
                    $('li.sem-tree-' + id).children('a').css('display', 'none');
                },
                error: function (xhr, status, error) {
                    alert(error);
                }
            }
        );
        return false;
    },

    unassignNode: function(id)
    {
        var target = $('li.sem-tree-assigned-' + id);
        if (target.children('ul').children('li').length > 0) {
            target.children('input[name="studyareas[]"]').remove();
            target.children('a').remove();
        } else {
            STUDIP.CourseWizard.cleanupAssignTree(target);
        }
        $('li.sem-tree-' + id).children('a').css('display', '');
        return false;
    },

    cleanupAssignTree: function(element)
    {
        if (element.parent().children('li').length == 1 && !element.parent().parent().hasClass('keep-node')) {
            STUDIP.CourseWizard.cleanupAssignTree(element.parent().parent());
        } else {
            element.remove();
        }
        var root = $('li.sem-tree-assigned-root');
        if (root.children('ul').children('li').length < 1)
        {
            root.css('display', 'none');
        }
    },

    resetSearch: function() {
        $('li.css-tree-hidden').removeClass('css-tree-hidden');
        var startLink = $('#sem-tree-search-start');
        startLink.
            attr('onclick', 'return STUDIP.CourseWizard.searchTree()').
            empty().
            append($('<img>').
                attr('src', STUDIP.ASSETS_URL + 'images/icons/blue/search.svg'));
        $('#sem-tree-search').val('');
        $('.css-tree-hidden').removeClass('css-tree-hidden');
        var notloaded = $('li').not('.tree-loaded');
        notloaded.children('input[type="checkbox"]').attr('checked', false);
        notloaded.children('ul').empty();
        return false;
    }

}

$(function()
{
    if ($('.sem-tree-assigned-root').children('ul').children('li').length == 0)
    {
        $('.sem-tree-assigned-root').css('display', 'none');
    }
});