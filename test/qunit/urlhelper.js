
STUDIP.URLHelper.gestURL = function (url, param_object) {
    if (param_object === undefined) {
      param_object = {};
    }
    url = STUDIP.URLHelper.resolveURL(url);
    // splitting the url:
    url = url.split("#");
    var anchor = (url.length > 1) ? url[url.length - 1] : "";
    url = url[0].split("?");
    var url_parameters = (url.length > 1) ? url[url.length - 1].split("&") : [];
    var parameters = {};
    jQuery.each(url_parameters, function (index, value) {
      var assoc = value.split("=");
      parameters[assoc[0]] = assoc[1];
    });
    url = url[0];
    //merging in the param_object - as you see this has got priority:
    parameters = jQuery.extend(parameters, param_object);
    // glueing together:
    var param_strings = [];
    jQuery.each(parameters, function (param, value) {
      param_strings.push(param + "=" + value);
    });
    if (param_strings.length > 0) {
      url += "?" + param_strings.join("&");
    }
    if (anchor !== "") {
      url += "#" + anchor;
    }
    return url;
  };

module("URLHelper#resolveURL", {
    setup: function () {
        this.original_uri = STUDIP.ABSOLUTE_URI_STUDIP;
        this.setStudipUri("");
        STUDIP.URLHelper.base_url = null;

    },
    teardown: function () {
        this.setStudipUri(this.original_uri);
    },
    setStudipUri: function (url) {
        STUDIP.ABSOLUTE_URI_STUDIP = url;
    }
});

/* TODO
test("ABSOLUTE_URI_STUDIP is undefined", function () {
    delete STUDIP.ABSOLUTE_URI_STUDIP;
    var url = "identity";
    equals(STUDIP.URLHelper.resolveURL(url), url);
});
*/

test("ABSOLUTE_URI_STUDIP is empty string", function () {
    this.setStudipUri("");
    var url = "identity";
    equals(STUDIP.URLHelper.resolveURL(url), url);
});

test("ABSOLUTE_URI_STUDIP is well-formed url", function () {
    var url = "proto:identity"; // could be upper case?
    equals(STUDIP.URLHelper.resolveURL(url), url);
});

/*
test("ABSOLUTE_URI_STUDIP is strange url", function () {
    var url = "S-t.r+ang3:identity";
    equals(STUDIP.URLHelper.resolveURL(url), url);
});
*/

test("resolveURL w/o base_url", function () {
    var url = "?identity=";
    equals(STUDIP.URLHelper.resolveURL(url), url);
});


test("with host + server relative url", function () {
    var host = "http://host",
    studip = host + "/path/",
    url = "/url";
    this.setStudipUri(studip);
    equals(STUDIP.URLHelper.resolveURL(url), host + url);
});

test("with host + relative url", function () {
    var host = "http://host",
    studip = host + "/path/",
    url = "url";
    this.setStudipUri(studip);
    equals(STUDIP.URLHelper.resolveURL(url), studip + url);
});

test("w/o host + server relative url", function () {
    var host = "",
    studip = host + "/path/",
    url = "/url";
    this.setStudipUri(studip);
    equals(STUDIP.URLHelper.resolveURL(url), url);
});

test("w/o host + relative url", function () {
    var host = "",
    studip = host + "/path/",
    url = "url";
    this.setStudipUri(studip);
    equals(STUDIP.URLHelper.resolveURL(url), studip + url);
});

///////////////////////////////////////////////////////////////////////////////

module("URLHelper#getURL", {
    setup: function () {
        this.original_uri = STUDIP.ABSOLUTE_URI_STUDIP;
        this.setStudipUri("");
        STUDIP.URLHelper.base_url = null;
    },
    teardown: function () {
        this.setStudipUri(this.original_uri);
    },
    setStudipUri: function (url) {
        STUDIP.ABSOLUTE_URI_STUDIP = url;
    }
});

test("no link param", function () {

    equals(STUDIP.URLHelper.getURL(''), '?');
    equals(STUDIP.URLHelper.getURL('x'), 'x');
    equals(STUDIP.URLHelper.getURL('#x'), '?#x');

    STUDIP.URLHelper.base_url = '/dir/';

    equals(STUDIP.URLHelper.getURL(''), '?');
    equals(STUDIP.URLHelper.getURL('#x'), '?#x');
    equals(STUDIP.URLHelper.getURL('?a=b'), '?a=b');

    equals(STUDIP.URLHelper.getURL('', {a:1,b:2}), '?a=1&b=2');
});

test("getURL", function () {
    var url, params, expected;
    url = 'abc#top';
    params = {a: 'b', c: 'd'};
    equals(STUDIP.URLHelper.getURL(url, params), 'abc?a=b&c=d#top');

    url = 'abc?foo=test';
    expected = 'abc?foo=test';
    equals(STUDIP.URLHelper.getURL(url), expected);

    url = 'abc';
    params = {foo: 'test'};
    expected = 'abc?foo=test';
    equals(STUDIP.URLHelper.getURL(url, params), expected);

    url = 'abc?baz=on';
    params = {baz: 'off'};
    expected = 'abc?baz=off';
    equals(STUDIP.URLHelper.getURL(url, params), expected);
});
