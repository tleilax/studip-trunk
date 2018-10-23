import HeaderMagic from './lib/header_magic.js';
import NavigationShrinker from './lib/navigation_shrinker.js';

window.STUDIP = _.assign(window.STUDIP || {}, {
    HeaderMagic,
    NavigationShrinker
});
