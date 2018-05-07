/*jslint browser: true, unparam: true */
/*global jQuery, STUDIP */

(function ($, STUDIP) {
    'use strict';

    function searchMoreFiles(button) {
        var table = $(button).closest("table"),
            loading = $('<div class="loading" style="padding: 10px">').html(
                $('<img>')
                    .attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg')
                    .css('width', '24')
                    .css('height', '24')
            );

        $(button).replaceWith(loading);

        $.get(button.href).done(function (output) {
            table.find('tbody').append($('tbody tr', output));
            table.find('tfoot').replaceWith($('tfoot', output));
        });

        return false;
    }

    STUDIP.Files = {
        openAddFilesWindow: function (folder_id) {
            if ($('.files_source_selector').length > 0) {
                STUDIP.Dialog.show($('.files_source_selector').html(), {
                    title: 'Datei hinzufügen'.toLocaleString(),
                    size: 'auto'
                });
            } else {
                STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL('dispatch.php/file/add_files_window/' + folder_id), {
                    title: 'Datei hinzufügen'.toLocaleString(),
                    size: 'auto'
                });
            }
        },
        validateUpload: function (file) {
            if (!STUDIP.Files.uploadConstraints) {
                return true;
            }
            if (file.size > STUDIP.Files.uploadConstraints.filesize) {
                return false;
            }
            var ending = file.name.lastIndexOf('.') !== -1
                       ? file.name.substr(file.name.lastIndexOf('.') + 1)
                       : '';

            if (STUDIP.Files.uploadConstraints.type === 'allow') {
                return $.inArray(ending, STUDIP.Files.uploadConstraints.file_types) === -1;
            }

            return $.inArray(ending, STUDIP.Files.uploadConstraints.file_types) !== -1;
        },
        upload: function (filelist) {
            var files = 0,
                folder_id = $('.files_source_selector').data('folder_id'),
                data = new FormData();

            //Open upload-dialog
            $('.file_upload_window .filenames').html('');
            $('.file_upload_window .errorbox').hide();
            $('.file_upload_window .messagebox').hide();
            $.each(filelist, function (index, file) {
                if (file.size > 0) {
                    if (STUDIP.Files.validateUpload(file)) {
                        data.append('file[]', file, file.name);
                        $('<li/>').text(file.name).appendTo('.file_upload_window .filenames');
                        files += 1;
                    } else {
                        $('.file_upload_window .errorbox').show();
                        $('.file_upload_window .errorbox .errormessage').text('Datei ist zu groß oder hat eine nicht erlaubte Endung.'.toLocaleString());
                    }
                }
            });
            if ($('.file_uploader').length > 0) {
                STUDIP.Dialog.show($('.file_uploader').html(), {
                    title: 'Datei hochladen'.toLocaleString()
                });
            } else {
                STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL('dispatch.php/file/upload_window'), {
                    title: 'Datei hochladen'.toLocaleString()
                });
            }

            //start upload
            $('form.drag-and-drop.files').removeClass('hovered');
            if (files > 0) {
                $('.file_upload_window .uploadbar').show().css('background-size', '0% 100%');
                $.ajax({
                    url: STUDIP.URLHelper.getURL('dispatch.php/file/upload/' + folder_id),
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    type: 'POST',
                    xhr: function () {
                        var xhr = $.ajaxSettings.xhr();
                        if (xhr.upload) {
                            xhr.upload.addEventListener('progress', function (event) {
                                var percent = 0,
                                    position = event.loaded || event.position,
                                    total = event.total;
                                if (event.lengthComputable) {
                                    percent = Math.ceil(position / total * 100);
                                }
                                //Set progress
                                $('.file_upload_window .uploadbar').css('background-size', percent + '% 100%');
                            }, false);
                        }
                        return xhr;
                    }
                }).done(function (json) {
                    $('.file_upload_window .uploadbar').css('background-size', '100% 100%');
                    if (json.redirect) {
                        STUDIP.Dialog.fromURL(json.redirect, {
                            title: json.window_title
                                || (json.new_html.length > 1 ? 'Lizenz auswählen: %s Dateien'.toLocaleString().replace('%s', json.new_html.length) : 'Lizenz auswählen'.toLocaleString())
                        });
                    } else if (json.message) {
                        $('.file_upload_window .uploadbar').hide().parent().append(json.message);
                    } else {
                        $.each(json.new_html, function (index, tr) {
                            STUDIP.Files.addFile(tr, index * 200);
                        });
                        STUDIP.Dialog.close();
                    }
                });
            } else {
                $('.file_upload_window .uploadbar').hide();
            }
        },
        addFile: function (payload, delay) {
            if (delay === undefined) {
                delay = 0;
            }
            var redirect = false,
                html = [];

            if (payload.hasOwnProperty('html') && payload.html !== undefined) {
                redirect = payload.redirect;
                html = payload.html;
            }

            if (!redirect) {
                window.setTimeout(STUDIP.Dialog.close, 20);
            } else {
                STUDIP.Dialog.fromURL(redirect);
            }

            if ($('table.documents').length > 0) {
                // on files page

                if (typeof html !== 'array' && typeof html !== 'object') {
                    html = [html];
                }
                $.each(html, function (i, value) {
                    var tr = $(value).attr('id');
                    if ($(document.getElementById(tr)).length > 0) {
                        $(document.getElementById(tr)).replaceWith(value);
                    } else {
                        $(value).hide().appendTo('.documents[data-folder_id] tbody.files').delay(500 + delay + i * 200).fadeIn(300);
                    }
                });

                $('.subfolders .empty').hide('fade');

                // update tablesorter cache
                $('table.documents').trigger('update');
                $('table.documents').trigger('sorton', [
                    $('table.documents').get(0).config.sortList
                ]);

            } else {
                //not on files page

                if (payload.url) {
                    STUDIP.Dialog.handlers.header['X-Location'](payload.url);
                }
            }

            $(document).trigger('refresh-handlers');
        },
        removeFile: function (fileref_id) {
            $.post(STUDIP.URLHelper.getURL('dispatch.php/file/delete/' + fileref_id))
                .done(function () {
                    $('.documents tbody.files > tr#fileref_' + fileref_id).fadeOut(300, function () {
                        $(this).remove();
                        if ($('.subfolders > *').length + $('.files > *').length < 2) {
                            $('.subfolders .empty').show('fade');
                        }

                        $(document).trigger('refresh-handlers');
                    });
                });
        },
        reloadPage: function () {
            STUDIP.Dialog.close();
            location.reload();
        },
        getFolders: function (name) {
            var element_name = 'folder_select_' + name,
                context =  $('#' + element_name + '-destination').val(),
                range = null;

            if ($.inArray(context, ['courses']) > -1) {
                range = $('#' + element_name + '-range-course > div > input').first().val();
            } else if ($.inArray(context, ['institutes']) > -1) {
                range = $('#' + element_name + '-range-inst > div > input').first().val();
            } else if ($.inArray(context, ['myfiles']) > -1) {
                range = $('#' + element_name + '-range-user_id').val();
            }

            if (range !== null) {
                $.post(STUDIP.URLHelper.getURL('dispatch.php/file/getFolders'), {range: range}, function (data) {
                    if (data) {
                        $('#' + element_name + '-subfolder select').empty();
                        $.each(data, function (index, value) {
                            $.each(value, function (label, folder_id) {
                                $('#' + element_name + '-subfolder select').append('<option value="' + folder_id + '">' + label + '</option>');
                            });
                        });
                    }
                }, 'json').done(function () {
                    $('#' + element_name + '-subfolder').show();
                });
            }
        },

        changeFolderSource: function (name) {
            var element_name = 'folder_select_' + name,
                elem = $('#' + element_name + '-destination');

            $('#' + element_name + '-range-course').hide();
            $('#' + element_name + '-range-inst').hide();
            $('#' + element_name + '-subfolder').hide();
            $('#' + element_name + '-subfolder select').empty();

            if ($.inArray(elem.val(), ['courses']) > -1) {
                $('#' + element_name + '-range-course').show();
            } else if ($.inArray(elem.val(), ['institutes']) > -1) {
                $('#' + element_name + '-range-inst').show();
            } else if ($.inArray(elem.val(), ['myfiles']) > -1) {
                $('#' + element_name + '-subfolder').show();
                STUDIP.Files.getFolders(name);
            }
        },

        updateTermsOfUseDescription: function (e) {
            //make all descriptions invisible:
            $('div.terms_of_use_description_container > section').addClass('invisible');

            var selected_id = $(this).val();

            $('#terms_of_use_description-' + selected_id).removeClass('invisible');
        }
    };

    $(function () {
        $('form.drag-and-drop.files')
            .on('dragover dragleave', function (event) {
                $(this).toggleClass('hovered', event.type === 'dragover');
                return false;
            });
        $('form.drag-and-drop.files').on('drop', function (event) {
            event.preventDefault();

            var filelist = event.originalEvent.dataTransfer.files || {};
            STUDIP.Files.upload(filelist);
        });
        $('form.drag-and-drop.files').on('click', function () {
            $('.file_selector input[type=file]').first().click();
        });

        // workaround to wait for tables.js to be executed first
        $(function () {
            if ($.fn.hasOwnProperty('filterTable')) {
                $('table.documents.flat').filterTable({
                    highlightClass: 'filter-match',
                    ignoreColumns: [0, 1, 3, 5, 6],
                    inputSelector: '.sidebar .tablesorterfilter',
                    minChars: 1,
                    minRows: 1
                });
            }

            $(document).trigger('refresh-handlers');
        });

        $(document).on('click', '#file_license_chooser_1 > input[type=radio]', STUDIP.Files.updateTermsOfUseDescription);

        $(document).on('click', '.files-search-more', function (event) {
            event.preventDefault();
            return searchMoreFiles(this);
        });
    });

}(jQuery, STUDIP));
