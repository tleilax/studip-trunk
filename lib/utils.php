<?php namespace Utils;
/**
 * utils.php - Various utility functions.
 *
 * These functions where originally implemented as part of the RichTextPlugin 
 * and are required by some parts of the WYSIWYG editor implementation.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Robert Costa <rcosta@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
require_once('bootstrap.php');
// TODO replace dependence on bootstrap.php by actually used scripts
//
// Partial list of scripts included by bootstrap.php and why they are needed:
//
//   classes/Request.class.php      Request::isPost
//   phplib_local.inc.php           $GLOBALS['perm']

/**
 * Get the current URL as called by the web client.
 * taken from http://stackoverflow.com/a/2820771
 *
 * @return string  The current URL.
 */
function getUrl() {
    $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Get the file name of the currently executed PHP script.
 *
 * @return string  Filename of currently executed PHP script.
 */
function getBasename() {
    return basename($_SERVER['PHP_SELF']);
}

/**
 * Get the base URL including the directory path, excluding file name, 
 * query string, etc.
 *
 * return string  Base URL of client request.
 */
function getBaseUrl() {
    $url = getUrl();
    $pos = \strpos($url, getBasename());
    // remove current script name, query, etc.
    // only keep host URL and directory part of path
    return \substr($url, 0, $pos);
}

/**
 * Return id of currently selected seminar.
 * Return false, if no seminar is selected.
 *
 * @return mixed  seminar_id or false
 */
function getSeminarId() {
    if (!\Request::option('cid')) {
        if ($GLOBALS['SessionSeminar']) {
            \URLHelper::bindLinkParam('cid', $GLOBALS['SessionSeminar']);
            return $GLOBALS['SessionSeminar'];
        }
        return false;
    }
    return \Request::option('cid');
}

function getFolder($folder_id) {
    $db = \DBManager::get();
    return $db->query('SELECT * FROM folder WHERE folder_id = '
        . $db->quote($folder_id)
    )->fetch(\PDO::FETCH_COLUMN, 0);
}

/**
 * Get ID of a Stud.IP folder, create the folder if it doesn't exist.
 * @param string $name        Name of the folder.
 * @param string $description Description of the folder (optional and only 
 *                            used if folder doesn't exist).
 * @return string  Stud.IP document folder identifier. An MD5 hash created
 *                 from the folder's name and the seminar's identifier).
 */
function getFolderId($name, $description=null) {
    $seminar_id = getSeminarId();
    $folder_id = \md5($name . '_' . $seminar_id);
    $db = \DBManager::get();
    $db->exec('INSERT IGNORE INTO folder SET '
        . 'folder_id = ' . $db->quote($folder_id)
        . ', range_id = ' . $db->quote($seminar_id)
        . ', user_id = ' . $db->quote($GLOBALS['user']->id)
        . ', name = ' . $db->quote($folder_name)
        . ', permission = ' . $db->quote(7)
        . ', mkdate = ' . $db->quote(time())
        . ', chdate = ' . $db->quote(time())
        . ', description = ' . $db->quote($description) 
    );
    return $folder_id;
}

/**
 * Normalize $_FILES for HTML array upload of multiple files.
 * 
 * @return array  Each entry contains an associative array for a single file
 *                with name, type, tmp_name, error, and size keys set.
 */
function FILES(){
    foreach($_FILES['files'] as $key => $fileList){
        foreach($fileList as $fileIndex => $value){
            $files[$fileIndex][$key] = $value;
        }
    }
    return $files;
}

/**
 * Create a new Stud.IP document from an uploaded file.
 *
 * @param array  $file      Metadata of uploaded file.
 * @param string $folder_id ID of Stud.IP folder to which file is uploaded.
 *
 * @return StudipDocument   The created Stud.IP document.
 * @throws AccessDeniedException if file is forbidden or upload failed.
 */
function uploadFile($file, $folder_id) {
    verifyUpload($file); // throw exception if file forbidden

    $newfile = \StudipDocument::createWithFile(
        $file['tmp_name'],
        getStudipDocumentData($folder_id, $file));

    if (!$newfile) { // file creation failed
        throw new \AccessDeniedException(
            _('Stud.IP-Dokument konnte nicht erstellt werden.'));
    }

    return $newfile;
}

/**
 * Verify that it is allowed to upload the file.
 * @param Array $file PHP file info array of uploaded file.
 * @throws AccessDeniedException if file is forbidden by Stud.IP settings.
 */
function verifyUpload($file) {
    $GLOBALS['msg'] = ''; // validate_upload will store messages here
    if (!\validate_upload($file)) { // upload is forbidden
        // remove error pattern from message
        $error_pattern = \utf8_decode('/error§(.+)§/');
        $message = \preg_replace($error_pattern, '$1', $GLOBALS['msg']);

        // clear global messages and throw exception
        $GLOBALS['msg'] = '';
        throw new \AccessDeniedException(\studip_utf8encode(\decodeHTML($message)));
    }
}

/**
 * Initialize Stud.IP metadata array for creating a new Stud.IP document.
 *
 * @param string $folder_id     ID of Stud.IP folder in which the document
 *                              is generated.
 * @param array  $file          Array containing metadata of the uploaded
 *                              file.
 *
 * @return array    Stud.IP document metadata
 */
function getStudipDocumentData($folder_id, $file) {
    $filename = \studip_utf8decode($file['name']);
    $document['name'] = $document['filename'] = $filename;
    $document['user_id'] = $GLOBALS['user']->id;
    $document['author_name'] = \get_fullname();
    $document['seminar_id'] = getSeminarId();
    $document['range_id'] = $folder_id;
    $document['filesize'] = $file['size'];
    return $document;
}

/**
 * Test if string starts with prefix.
 *
 * @param string $string Tested string.
 * @param string $prefix Prefix of tested string.
 *
 * @return boolean  True if string starts with prefix, otherwise False.
 */
function startsWith($string, $prefix) {
    return \substr($string, 0, \strlen($prefix)) === $prefix;
}

/**
 * Test if string ends with suffix.
 *
 * @param string $string  Tested string.
 * @param string $suffix  Suffix of tested string.
 *
 * @return boolean  True if string ends with suffix, otherwise False.
 */
function endsWith($string, $suffix) {
    return \substr($string, \strlen($string) - \strlen($suffix)) === $suffix;
}

/**
 * Remove prefix from string.
 *
 * Does not change the string if it has a different prefix.
 *
 * @param string $string The string that must start with the prefix.
 * @param string $prefix The prefix of the string.
 *
 * @return string String without prefix.
 */
function removePrefix($string, $prefix) {
    if (startsWith($string, $prefix)) {
        return \substr($string, \strlen($prefix));
    }
    return $string;
}

function testMediaUrl($a, $b) {
    $c = getMediaUrl($a);
    \assert($c == $b, "getMediaUrl($a)\n== $c\n!= $b\n");
}

function testGetMediaUrl() {
    \header('Content-type: text/plain; charset=utf-8');

    // studip must be at localhost:8080/studip for tests to work
    // LOAD_EXTERNAL_MEDIA must be set to 'proxy'
    $studip_document = 'http://localhost:8080/studip/sendfile.php?type=0&file_id=abc123&file_name=test.jpg';
    $studip_document_ip = 'http://127.0.0.1:8080/studip/sendfile.php?type=0&file_id=abc123&file_name=test.jpg';
    $external_document = 'http://pflanzen-enzyklopaedie.eu/wp-content/uploads/2012/11/Sumpfdotterblume-multiplex-120x120.jpg';
    $proxy_document = 'http://localhost:8080/studip/dispatch.php/media_proxy?url=http%3A%2F%2Fpflanzen-enzyklopaedie.eu%2Fwp-content%2Fuploads%2F2012%2F11%2FSumpfdotterblume-multiplex-120x120.jpg';
    $studip_document_no_domain = '/studip/sendfile.php?type=0&file_id=abc123&file_name=test.jpg';

    testMediaUrl($studip_document, $studip_document);
    testMediaUrl('invalid url', NULL);
    testMediaUrl($studip_document_ip, $studip_document);
    testMediaUrl($external_document, $proxy_document);
    testMediaUrl($proxy_document, $proxy_document);
    testMediaUrl($studip_document_no_domain, $studip_document);
}

/**
 * Check if media proxy should be used and if so return the respective URL.
 *
 * @param string $url   URL to media file.
 * @return mixed        URL string to media file (possibly 'proxied')
 *                      or NULL if URL is invalid.
 */
function getMediaUrl($url) {

    // handle internal media links
    $url = decodeMediaProxyUrl($url);
    if (isStudipMediaUrl($url)) {
        return removeStudipDomain($url);
    }
    if (isStudipUrl($url)) {
        $GLOBALS['msg'][] = 'Invalid internal link removed: ' . \htmlentities($url);
        return NULL; // invalid internal link ==> remove <img src> attribute
    }

    // handle external media links
    $external_media = \Config::GetInstance()->getValue('LOAD_EXTERNAL_MEDIA');
    if ($external_media === 'proxy' && \Seminar_Session::is_current_session_authenticated()) {
        // NOTE will fail if media proxy has external link
        return removeStudipDomain(encodeMediaProxyUrl($url));
    }
    if ($external_media === 'allow') {
        return $url;
    }
    $GLOBALS['msg'][] = 'External media denied: ' . \htmlentities($url);
    return NULL; // deny external media ==> remove <img src> attribute
}

/**
 * Removes scheme, domain and authentication information from internal
 * Stud.IP URLs. Leaves external URLs untouched.
 * @param string $url   The URL from which to remove internal domain.
 * @returns string      URL without internal domain or the exact same
 *                      value as $url for external URLs.
 */
function removeStudipDomain($url) {
    if (!isStudipUrl($url)) {
        return $url;
    }
    $parsed_url = \parse_url(tranformInternalIdnaLink($url));
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return $path . $query . $fragment;
}

function tranformInternalIdnaLink($url) {
    return \idna_link(\TransformInternalLinks($url));
}

function encodeMediaProxyUrl($url) {
    $base_url = $GLOBALS['ABSOLUTE_URI_STUDIP'];
    $media_proxy = $base_url . 'dispatch.php/media_proxy?url=';
    return tranformInternalIdnaLink(
        $media_proxy . \urlencode(\idna_link($url)));
}

/**
 * Test if an URL points to a valid internal Stud.IP media path.
 *
 * @param string $url Internal Stud.IP URL.
 * @returns boolean TRUE for internal media link URLs.
 *                  FALSE otherwise.
 */
function isStudipMediaUrl($url) {
    if (!isStudipUrl($url)) {
        return FALSE; # external link
    }
    return isStudipMediaUrlPath(getStudipRelativePath($url));
}

/**
 * Returns a URL's path component with the absolute Stud.IP path removed.
 *
 * NOTE: If the URL is not an internal Stud.IP URL, the path component will
 * nevertheless be returned without issuing an error message.
 *
 * Example:
 * >>> getStudipRelativePath('http://localhost:8080'
 *      . '/studip/sendfile.php?type=0&file_id=ABC123&file_name=nice.jpg')
 * 'sendfile.php'
 *
 * @param string $url   The URL from which to return the Stud.IP-relative 
 *                      path component.
 * returns string Stud.IP-relative path component of $url.
 */
function getStudipRelativePath($url) {
    $parsed_url = \parse_url(tranformInternalIdnaLink($url));
    $parsed_studip_url = getParsedStudipUrl();
    return removePrefix($parsed_url['path'], $parsed_studip_url['path']);
}

/**
 * Extracts the original URL from a media proxy URL.
 * @param string $url The media proxy URL.
 * return string The original URL. If $url does not point to the media 
 *               proxy then this is the exact same value given by $url.
 */
function decodeMediaProxyUrl($url) {
    $base_url = $GLOBALS['ABSOLUTE_URI_STUDIP'];
    $media_proxy = $base_url . 'dispatch.php/media_proxy?url=';

    $transformed_url = tranformInternalIdnaLink($url);
    if (startsWith($transformed_url, $media_proxy)) {
        return \urldecode(removePrefix($transformed_url, $media_proxy));
    }
    return $url;
}

/**
 * Test if given URL points to an internal Stud.IP resource.
 * @param string $url   The URL that is tested.
 * @return boolean      TRUE if URL points to internal Stud.IP resource,
 *                      otherwise FALSE.
 */
function isStudipUrl($url) {
    $studip_url = getParsedStudipUrl();
    \assert(\is_array($studip_url)); // otherwise something's wrong with studip

    $parsed_url = \parse_url(tranformInternalIdnaLink($url));
    if ($parsed_url === FALSE) {

        return FALSE; // url is seriously malformed
    }

    $studip_schemes = array($studip_url['scheme'], 'http', 'https', \NULL);
    $studip_hosts = array($studip_url['host'], \NULL);
    $studip_ports = array($studip_url['port'], \NULL);

    $is_scheme = \in_array($parsed_url['scheme'], $studip_schemes);
    $is_host = \in_array($parsed_url['host'], $studip_hosts);
    $is_port = \in_array($parsed_url['port'], $studip_ports);
    $is_path = startsWith($parsed_url['path'], $studip_url['path']);
    $is_studip = $is_scheme && $is_host && $is_port && $is_path;

    return $is_studip;
}

/**
 * Return an associative array containing the Stud.IP URL elements.
 * see also: http://php.net/manual/en/function.parse-url.php
 * @returns mixed The exact same values that PHP's parse_url() returns.
 */
function getParsedStudipUrl() {
    return \parse_url($GLOBALS['ABSOLUTE_URI_STUDIP']);
}

/**
 * Test if given URL path is valid for internal Stud.IP media files.
 * @params string $path The path component of an URL.
 * return boolean       TRUE for valid media paths, FALSE otherwise.
 */
function isStudipMediaUrlPath($path) {
    list($path_head) = \explode('/', $path);
    $valid_paths = array('sendfile.php', 'download', 'assets', 'pictures');
    return \in_array($path_head, $valid_paths);
}

function hasPermission($permission) {
    $perm = new \Seminar_Perm();
    return $perm->have_studip_perm($permission, getSeminarId());
}

/**
 * Verify that user has needed permission.
 * @param string $permission Minimum requested permission level.
 * @throws AccessDeniedException if user does not have permission.
 */
function verifyPermission($permission) {
    if (!hasPermission($permission)) {
        throw new \AccessDeniedException(\studip_utf8decode(
            \_("Es werden mindestens $permission-Zugriffsrechte benötigt.")));
    }
}

/**
 * Verify that HTTP request was send as HTTP POST
 * @throws AccessDeniedException if request was not send as HTTP POST.
 */
function verifyPostRequest() {
    if (!\Request::isPost()) {
        throw new \AccessDeniedException(\studip_utf8decode(
            _('Die Anfrage muss als HTTP POST gestellt werden.')));
    }
}

/**
 * Decodes a UTF-8 encoded POST variable.
 *
 * @params string  variable    POST variable's name.
 * @params boolean must_exist  Throw an exception if variable not posted.
 *
 * @return mixed value  The variable's decoded value as string or NULL if the 
 *                      variable has not been posted and must_exist is FALSE.
 */
function utf8POST($variable, $must_exist=FALSE) {
    if (isset($variable)) {
        return studip_utf8decode($_POST[$variable]);
    }
    if ($must_exist) {
        throw new Exception("POST variable $variable not set.");
    }
    return NULL;
}

/**
 * Read the value of a global configuration entry from the database.
 *
 * @param string $name Identifier of the configuration entry.
 * @returns string Value of the configuration entry.
 */
function getConfigValue($name) {
    return \Config::GetInstance()->getValue($name);
}

/**
 * Send the HTTP response as a JSON-encoded string.
 * @param mixed $response The value that should be sent as response.
 */
function sendAsJson($response) {
    negotiateJsonContent();
    echo json_encode($response);
}

/**
 * Set content-type to application/json if client accepts it.
 *
 * If client doesn't accept JSON then set text/plain.
 * Also tell proxies/caches that content depends on what client accepts.
 */
function negotiateJsonContent() {
    header('Vary: Accept');
    if (httpAcceptsJson()) {
        header('Content-type: application/json; charset=utf-8');
    } else {
        header('Content-type: text/plain; charset=utf-8');
    }
}

/**
 * Checks if application/json is set in HTTP_ACCEPT.
 * @returns boolean TRUE if JSON response is accepted, FALSE otherwise.
 */
function httpAcceptsJson() {
    return isset($_SERVER['HTTP_ACCEPT'])
        && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}
