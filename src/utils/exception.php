<?php

class HTTPException extends Exception {
    private $status_code;

    public function __construct($message, $status_code = 500) {
        parent::__construct($message);
        $this->status_code = $status_code;
    }

    public function get_status_code() {
        return $this->status_code;
    }
}
