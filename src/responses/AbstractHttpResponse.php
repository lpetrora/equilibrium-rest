<?php 
namespace equilibrium\responses;

use equilibrium\HttpError;

abstract class AbstractHttpResponse extends AbstractResponse {
    protected $code = null;
    
    /**
     * set http code
     * @param integer $v
     * @return \equilibrium\responses\HttpResponse
     */
    public function setCode($v) {
        $this->code = $v;
        return $this;
    }
    
    /**
     * Return http code
     * @return number
     */
    public function getCode() {
        return $this->code;
    }
        
    public function execute()
    {
        if ($this->code === null) $this->code = HttpError::HTTP_NO_CONTENT;
        http_response_code($this->code);
    }

}