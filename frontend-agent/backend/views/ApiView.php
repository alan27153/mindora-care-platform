<?php

namespace Views;

class ApiView
{
    public function sendResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    public function sendError($message, $statusCode = 400, $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        $this->sendResponse($response, $statusCode);
    }

    public function sendSuccess($message = 'Operación exitosa', $data = [], $statusCode = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];
        if (!empty($data)) {
            $response['data'] = $data;
        }
        $this->sendResponse($response, $statusCode);
    }
}
