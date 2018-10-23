import Dialog from './lib/dialog.js';
import HeaderMagic from './lib/header_magic.js';
import NavigationShrinker from './lib/navigation_shrinker.js';
import Overlay from './lib/overlay.js';
import parseOptions from './lib/parse_options.js';
import PersonalNotifications from './lib/personal_notifications.js';
import RESTAPI from './lib/restapi.js';
import Sidebar from './lib/sidebar.js';
import SmileyPicker from './lib/smiley_picker.js';
import Table from './lib/table.js';
import Toolbar from './lib/toolbar.js';

const api = new RESTAPI()

window.STUDIP = _.assign(window.STUDIP || {}, {
    api,
    Dialog,
    HeaderMagic,
    NavigationShrinker,
    Overlay,
    parseOptions,
    PersonalNotifications,
    RESTAPI,
    Sidebar,
    SmileyPicker,
    Table,
    Toolbar
});
