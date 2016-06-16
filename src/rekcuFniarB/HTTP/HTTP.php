<?php

/* CURL wrapper class */

namespace rekcuFniarB\HTTP;

class HTTP {
    // cURL descriptor
    protected $descriptor;
    // last http request response code
    protected $last_response_code;
    // possibe errors list
    protected $errors;
    // cookie file
    protected $cookie;
    // Instance id
    protected $id;
    
    public function __construct($id = 'php-curl')
    {
        $this->descriptor = null;
        $this->id = $id;
        
        // Possible http error list
        $this->errors = array(
            301 => 'Moved permanently',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        );
        
        // define cookie file
        if (is_dir('/dev/shm'))
            // store cookie in the shared memory if /dev/shm exists
            $this->cookie = "/dev/shm/{$this->id}.cookie";
        // define cookie file
        elseif (is_dir('/tmp'))
            // store cookie in the shared memory if /tmp exists
            $this->cookie = "/tmp/{$this->id}.cookie";
        else
            $this->cookie = __DIR__ . "/{$this->id}.cookie";
        
        $this->descriptor = curl_init();
        curl_setopt($this->descriptor, CURLOPT_RETURNTRANSFER, true);
        // Don't include header in the output
        curl_setopt($this->descriptor, CURLOPT_HEADER, false);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->descriptor, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($this->descriptor, CURLOPT_COOKIEJAR,  $this->cookie);
    }
    
    /**
     * Set http request header
     * @param array $header Headers (e.g. array('Content-Type: application/json'))
     */
    public function set_header($header)
    {
        curl_setopt($this->descriptor, CURLOPT_HTTPHEADER, $header);
    }
    
    /**
     * Get last http response code
     * @return int
     */
    public function http_code()
    {
        return $this->last_response_code;
    }
    
    /**
     * Make http request
     * @param string $url    URL
     * @param string $method  http method (GET|POST)
     * @param mixed $data     data to send (string or array)
     * @return string
     */
    public function query($url, $data = null, $method = 'GET')
    {
        curl_setopt($this->descriptor, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($method == 'GET' && $data !== null)
        {
            if (is_array($data))
                $data = http_build_query($data);
            
            $http_query = parse_url($url, PHP_URL_QUERY);
            // check if there are GET params in URL
            if ($http_query !== null)
                // append to existing GET params
                $url = "$url&$data";
            else
                // append GET params to URL
                $url = "$url?$data";
        }
        elseif ($method == 'POST')
        {
            curl_setopt($this->descriptor, CURLOPT_POSTFIELDS, $data);
        }
        
        curl_setopt($this->descriptor, CURLOPT_URL, $url);
        
        $response = curl_exec($this->descriptor);
        
        $this->last_response_code = (int) curl_getinfo($this->descriptor, CURLINFO_HTTP_CODE);
        $code = $this->http_code();
        
        if ($code == 200 || $code == 204)
            return $response;
        else
            return $this->error($code, $response);
    } // query()
    
    /**
     * Close cURL resource
     */
    public function close()
    {
        return curl_close($this->descriptor);
    } // close()
    
    /**
     * Show last error message
     */
    public function error()
    {
        if (isset($this->errors[$this->last_response_code]))
            return("[{$this->last_response_code}] {$this->errors[$this->last_response_code]}");
        else
            return("[{$this->last_response_code}] Unknown error.");
    } // error()
}
