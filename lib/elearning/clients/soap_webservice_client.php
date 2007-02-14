<?php 

require_once("webservice_client.php");
require_once("vendor/nusoap/nusoap.php");

class Soap_WebserviceClient extends WebserviceClient
{
	function Soap_WebserviceClient($webservice_url)
	{
		$this->client =& new soap_client($webservice_url);
		$this->client->response_timeout = 7600;
	}

	function call($method_name, &$args)
	{
		return $this->client->call($method_name, $args);
	}
}

