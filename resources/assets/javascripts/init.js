import Admission from './lib/admission.js';
import Arbeitsgruppen from './lib/arbeitsgruppen.js';
import Archive from './lib/archive.js';
import Audio from './lib/audio.js';
import Avatar from './lib/avatar.js';
import BigImageHandler from './lib/big_image_handler.js';
import Browse from './lib/browse.js';
import Cache from './lib/cache.js';
import Calendar from './lib/calendar.js';
import CalendarDialog from './lib/calendar_dialog.js';
import CourseWizard from './lib/course_wizard.js';
import createURLHelper from './lib/url_helper.js';
import CSS from './lib/css.js';
import Dates from './lib/dates.js';
import Dialog from './lib/dialog.js';
import enrollment from './lib/enrollment.js';
import extractCallback from "./lib/extract_callback.js"
import Files from './lib/files.js';
import Filesystem from './lib/filesystem.js';
import FilesDashboard from './lib/files_dashboard.js';
import Folders from './lib/folders.js';
import HeaderMagic from './lib/header_magic.js';
import Instschedule from './lib/instschedule.js';
import JSUpdater from './lib/jsupdater.js';
import Markup from './lib/markup.js';
import Messages from './lib/messages.js';
import NavigationShrinker from './lib/navigation_shrinker.js';
import News from './lib/news.js';
import OldUpload from './lib/old_upload.js';
import Overlay from './lib/overlay.js';
import parseOptions from './lib/parse_options.js';
import PersonalNotifications from './lib/personal_notifications.js';
import QuickSearch from './lib/quick_search.js';
import register from './lib/register.js';
import RESTAPI from './lib/restapi.js';
import Schedule from './lib/schedule.js';
import Scroll from './lib/scroll.js';
import Sidebar from './lib/sidebar.js';
import SmileyPicker from './lib/smiley_picker.js';
import study_area_selection from './lib/study_area_selection.js';
import Table from './lib/table.js';
import Toolbar from './lib/toolbar.js';
import UserFilter from './lib/user_filter.js';
import WidgetSystem from './lib/widget_system.js';

const api = new RESTAPI();
const configURLHelper = _.get(window, 'STUDIP.URLHelper', {});
const URLHelper = createURLHelper(configURLHelper);

window.STUDIP = _.assign(window.STUDIP || {}, {
    Admission,
    api,
    Arbeitsgruppen,
    Archive,
    Audio,
    Avatar,
    BigImageHandler,
    Browse,
    Cache,
    Calendar,
    CalendarDialog,
    CourseWizard,
    CSS,
    Dates,
    Dialog,
    enrollment,
    extractCallback,
    Files,
    Filesystem,
    FilesDashboard,
    Folders,
    HeaderMagic,
    Instschedule,
    JSUpdater,
    Markup,
    Messages,
    NavigationShrinker,
    News,
    OldUpload,
    Overlay,
    parseOptions,
    PersonalNotifications,
    QuickSearch,
    register,
    RESTAPI,
    Schedule,
    Scroll,
    Sidebar,
    SmileyPicker,
    study_area_selection,
    Table,
    Toolbar,
    URLHelper,
    UserFilter,
    WidgetSystem
});
