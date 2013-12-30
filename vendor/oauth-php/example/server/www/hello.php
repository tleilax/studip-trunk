<?php

/**
 * oauth-php: Example OAuth server
 *
 * An example service, http://hostname/hello. You will only get the
 * 'Hello, world!' string back if you have signed your request with
 * oauth.
 *
 * @author Arjan Scherpenisse <arjan@scherpenisse.net>
 *
 * 
 * The MIT License
 * 
 * Copyright (c) 2007-2008 Mediamatic Lab
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

require_once '../core/init.php';

$authorized = false;
$server = new OAuthServer();
try
{
	if ($server->verifyIfSigned())
	{
		$authorized = true;
	}
}
catch (OAuthException2 $e)
{
}

if (!$authorized)
{
	header('HTTP/1.1 401 Unauthorized');
	header('Content-Type: text/plain');
	
	echo "OAuth Verification Failed: " . $e->getMessage();
	die;
}

// From here on we are authenticated with OAuth.

header('Content-type: text/plain');
echo 'Hello, world!';

?>