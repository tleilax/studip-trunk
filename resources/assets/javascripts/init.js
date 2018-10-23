import HeaderMagic from './lib/header_magic.js';
import NavigationShrinker from './lib/navigation_shrinker.js';
import PersonalNotifications from './lib/personal_notifications.js';
import Sidebar from './lib/sidebar.js';
import SmileyPicker from './lib/smiley_picker.js';
import Table from './lib/table.js';
import Toolbar from './lib/toolbar.js';

window.STUDIP = _.assign(window.STUDIP || {}, {
    HeaderMagic,
    NavigationShrinker,
    PersonalNotifications,
    Sidebar,
    SmileyPicker,
    Table,
    Toolbar
});
