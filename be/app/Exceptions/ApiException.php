<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected $errorCode;
    protected $customMessage;
    protected $customDetails;
    protected $validationErrors;

    public function __construct(
        string $errorCode,
        string $customMessage = null,
        string $customDetails = null,
        array $validationErrors = null
    ) {
        $this->errorCode = $errorCode;
        $this->customMessage = $customMessage;
        $this->customDetails = $customDetails;
        $this->validationErrors = $validationErrors;

        parent::__construct($customMessage ?? $errorCode);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getCustomMessage(): ?string
    {
        return $this->customMessage;
    }

    public function getCustomDetails(): ?string
    {
        return $this->customDetails;
    }

    public function getValidationErrors(): ?array
    {
        return $this->validationErrors;
    }
}