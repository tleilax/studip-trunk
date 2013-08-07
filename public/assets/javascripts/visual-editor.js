/**
 * visual-editor.js - Activate visual editing of textarea contents.
 *
 * This file contains the plugin's main class.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Robert Costa <zabbarob@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
jQuery(function($){
    $('textarea.add_toolbar').on('focus', function(){
        $('.editor_toolbar > .buttons').remove();
        if (!CKEDITOR.instances[this]) {
            CKEDITOR.replace(this);
        }
    });
});
