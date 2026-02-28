<?php

class HTTPException extends Exception {
    private $statusCode;

    public function __construct($message, $statusCode = 500) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }
}