import Arbeitsgruppen from './lib/arbeitsgruppen.js';
import Audio from './lib/audio.js';
import Browse from './lib/browse.js';
import createURLHelper from './lib/url_helper.js';
import Dialog from './lib/dialog.js';
import Files from './lib/files.js';
import Filesystem from './lib/filesystem.js';
import Folders from './lib/folders.js';
import HeaderMagic from './lib/header_magic.js';
import JSUpdater from './lib/jsupdater.js';
import Markup from './lib/markup.js';
import Messages from './lib/messages.js';
import NavigationShrinker from './lib/navigation_shrinker.js';
import News from './lib/news.js';
import Overlay from './lib/overlay.js';
import parseOptions from './lib/parse_options.js';
import PersonalNotifications from './lib/personal_notifications.js';
import QuickSearch from './lib/quick_search.js';
import RESTAPI from './lib/restapi.js';
import Sidebar from './lib/sidebar.js';
import SmileyPicker from './lib/smiley_picker.js';
import study_area_selection from './lib/study_area_selection.js';
import Table from './lib/table.js';
import Toolbar from './lib/toolbar.js';

const api = new RESTAPI();
const configURLHelper = _.get(window, 'STUDIP.URLHelper', {});
const URLHelper = createURLHelper(configURLHelper);

window.STUDIP = _.assign(window.STUDIP || {}, {
    api,
    Arbeitsgruppen,
    Audio,
    Browse,
    Dialog,
    Files,
    Filesystem,
    Folders,
    HeaderMagic,
    JSUpdater,
    Markup,
    Messages,
    NavigationShrinker,
    News,
    Overlay,
    parseOptions,
    PersonalNotifications,
    QuickSearch,
    RESTAPI,
    Sidebar,
    SmileyPicker,
    study_area_selection,
    Table,
    Toolbar,
    URLHelper
});
