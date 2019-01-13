<?php

namespace PiedWeb\Curl;

/**
 * This class is an example.
 */
class MultipleCheckInHeaders
{
    protected $expectedCode;
    protected $expectedType;
    protected $code;

    public function __construct(int $expectedCode = 200, string $expectedType = 'text/html')
    {
        $this->expectedCode = $expectedCode;
        $this->expectedType = $expectedType;
    }

    public function check($line)
    {
        if (Helper::checkStatusCode($line, $this->expectedCode)) {
            $this->code = 200;
        }

        return 200 == $this->code && Helper::checkContentType($line, $this->expectedType);
    }
}
