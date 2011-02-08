<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
class StudipSoapClient
{
    var $soap_client;
    var $error;

    function StudipSoapClient($path)
    {
        global $RELATIVE_PATH_SOAP, $SOAP_ENABLE;
        require_once("vendor/nusoap/nusoap.php");

        $this->soap_client = new soap_client($path, true);

        $err = $this->soap_client->getError();
        if ($err)
            $this->error = "<b>Soap Constructor Error</b><br>" . $err . "<br><br>";
    }

    function call($method, $params)
    {
        $this->faultstring = "";
        $result = $this->soap_client->call($method, $params);
        
        if ($this->soap_client->fault) 
        {
            $this->faultstring = $result["faultstring"];
            if ($this->faultstring != "Session not valid")
                $this->error .= "<b>" . sprintf(_("SOAP-Fehler, Funktion \"%s\":"), $method) . "</b> " . $result["faultstring"] . " (" . $result["faultcode"] . ")<br>"; //.implode($params,"-");
        }
        else 
        {
            $err = $this->soap_client->getError();
            if ($err)
                $this->error .= "<b>" . sprintf(_("SOAP-Fehler, Funktion \"%s\":"), $method) . "</b> " . $err . "<br>"; //.implode($params,"-") . htmlspecialchars($this->soap_client->response, ENT_QUOTES);
            else
                return $result;
        }
        echo $this->error;
        return false;
    }

    function getError()
    {
         $error = $this->error;
         $this->error = "";
         if ($error != "")
             return $error;
        else
            return false;
    }
}
?>
