<?php

namespace PiedWeb\Curl;

/**
 * This class is an example.
 */
class MultipleCheckInHeaders
{
    protected $code;

    public function check($line)
    {
        if (Helper::checkStatusCode($line)) {
            $this->code = 200;
        }

        return 200 == $this->code && Helper::checkContentType($line);
    }
}
