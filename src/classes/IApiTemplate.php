<?php

namespace CamelCase\VansoSMS\Classes;

interface IApiTemplate
{

    public function send($endpoint, $data = null);

}
