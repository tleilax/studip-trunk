import './public-path.js'

import "../stylesheets/studip-jquery-ui.less"
import "highlight.js/styles/tomorrow.css"
import "chartist/dist/chartist.css"
import "gridstack/dist/gridstack.css"
import "../stylesheets/studip.less"
import "../stylesheets/widgets.less"

import "./vendor/modernizr-3.5.0.js"

import hljs from './studip-highlight.js'
window.hljs = hljs

import lodash from "lodash"
window._ = lodash

import "./l10n.js"

import chartist from "chartist"
window.Chartist = chartist

import QRCode from "./vendor/qrcode-04f46c6.js"
window.QRCode = QRCode

import "./jquery-bundle.js"

import "./init.js"
import "./studip-ui.js"
import "./bootstrap/tables.js" // Must be loaded before the Stud.IP helper attributes
import "./bootstrap/studip_helper_attributes.js"
import "./bootstrap/header_magic.js"
import "./bootstrap/header_navigation.js"
import "./bootstrap/personal_notifications.js"
import "./bootstrap/sidebar.js"
import "./bootstrap/smiley_picker.js"
import "./bootstrap/dialog.js"
import "./bootstrap/responsive.js"
import "./bootstrap/jsupdater.js"
import "./bootstrap/files.js"
import "./bootstrap/news.js"
import "./bootstrap/markup.js"
import "./bootstrap/messages.js"
import "./bootstrap/quick_search.js"
import "./multi_select.js"
import "./multi_person_search.js"
import "./skip_links.js"
import "./i18n_input.js"
import "./forms.js"
import "./calendar_dialog.js"
import "./drag_and_drop_upload.js"
import "./sem_classes.js"
import "./bootstrap/cronjobs.js"
import "./bootstrap/contentbox.js"
import "./dates.js"
import "./tour.js"
import "./questionnaire.js"
import "./qrcode.js"
import "./start.js"
import "./wiki.js"
import "./coursewizard.js"
import "./bootstrap/smiley.js"
import "./big-image-handler.js"
import "./bootstrap/opengraph.js"
import "./bootstrap/actionmenu.js"
import "./bootstrap/article.js"
import "./bootstrap/copyable_links.js"
import "./studip-selection.js"
import "./studip-secure-forms.js"
import "./studip-tooltip.js"
import "./studip-scroll.js"
import "./studip-selection-helper.js"
import "./studip-lightbox.js"
import "./bootstrap/application.js"
import "./globalsearch.js"
import "./mvv.js"
import "./bootstrap/mvv_difflog.js"
import "./members.js"

import "./avatar.js"
import "./studip-raumzeit.js"
import "./bootstrap/settings.js"
import "./subcourses.js"
import "./dialogs.js"
import "./userfilter.js"
import "./string-crc32.js"
import "./studip-widgetsystem.js"
import "./studip-widgets.js"
import "./studip-widgets-tabbable.js"
