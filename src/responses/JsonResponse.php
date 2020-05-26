<?php 
namespace equilibrium\responses;

class JsonResponse extends AbstractHttpResponse {
    protected $payload = '';
    
    /**
     * Set json payload
     * @param mixed $v
     * @return \equilibrium\responses\JsonResponse
     */
    public function setPayload($v) {
        $this->payload = $v;
        return $this;
    }
    
    /**
     * Return json payload
     * @return string
     */
    public function getPayload() {
        return $this->payload;
    }
    
    public function execute()
    {
        parent::execute();
        header('Content-type: application/json');
        echo json_encode($this->payload);
    }
}