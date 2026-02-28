<?php

function respond_with_error($message, $status_code = 400)
{
    http_response_code($status_code);
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
    exit;
}
