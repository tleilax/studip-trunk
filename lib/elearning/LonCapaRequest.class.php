<?php

class LonCapaRequest
{
    protected $options;
    protected $ch;

    public function __construct(){
        $this->ch = curl_init();
        $this->initOptions();
    }

    public function __destruct(){
        curl_close($this->ch);
    }


    public function initOptions(){
        $this->options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            //CURLOPT_CAINFO => '',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false 
        );
    }


    public function setOption($key, $value){
        $this->options[$key] = $value;
    }


    public function request($url, $postfields = null){
        $result = $this->sendRequest($url, $postfields);
        if($result['statusCode'] == 200){
            return $result['response'];
        }
        else{
            // fehlermeldung wÃ¤re schÃ¶ner
            return null;
        }
    }

    protected function sendRequest($url, $postfields = null){
        $options = $this->options;
        $options[CURLOPT_URL] = $url;

        if($postfields){
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $postfields;
        }

        curl_setopt_array($this->ch, $options);
        $response = curl_exec($this->ch);

        $statusCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
	
	if ($response === false) {
            $last_error = curl_error($this->ch);
            Log::error(__CLASS__ . ' curl_exec failed: ' . $last_error);
        }
        return array('statusCode' => $statusCode, 'response' => $response);
    }
}
