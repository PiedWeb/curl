<?php

namespace PiedWeb\Curl;

trait UserAgentTrait
{

    abstract public function setUserAgent(string $ua);

    /**
     * An self::setUserAgent()'s alias to add an user-agent wich correspond to a Destkop PC.
     *
     * @return self
     */
    public function setDestkopUserAgent()
    {
        $this->setUserAgent('Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:64.0) Gecko/20100101 Firefox/64.0');

        return $this;
    }

    /**
     * An self::setUserAgent()'s alias to add an user-agent wich correspond to a mobile.
     *
     * @return self
     */
    public function setMobileUserAgent()
    {
        $this->setUserAgent('Mozilla/5.0 (Linux; Android 7.0; SM-G892A Build/NRD90M; wv) AppleWebKit/537.36'
            .' (KHTML, like Gecko) Version/4.0 Chrome/60.0.3112.107 Mobile Safari/537.36');

        return $this;
    }

    /**
     * An self::setUserAgent()'s alias to add an user-agent wich correspond to a webrowser without javascript.
     *
     * @return self
     */
    public function setLessJsUserAgent()
    {
        $this->setUserAgent('NokiaN70-1/5.0609.2.0.1 Series60/2.8 Profile/MIDP-2.0 Configuration/CLDC-1.1 '
            .'UP.Link/6.3.1.13.0');

        return $this;
    }
}
