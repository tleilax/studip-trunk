import HeaderMagic from './lib/header_magic.js';
import NavigationShrinker from './lib/navigation_shrinker.js';
import Table from './lib/table.js';

window.STUDIP = _.assign(window.STUDIP || {}, {
    HeaderMagic,
    NavigationShrinker,
    Table
});
