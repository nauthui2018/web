<?php

namespace App\Utils;

class AppError
{
    private $code;

    private $message;

    public function __construct($message, int $code = 0)
    {
        if (is_array($message)) {
            $this->message = $message['message'] ?? null;
            $this->code = $message['code'] ?? 0;
        } else {
            $this->message = $message;
            $this->code = $code;
        }
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array<string>
     */
    public function getErrorData(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}
