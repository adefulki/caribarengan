<?php

/**
 * Created by PhpStorm.
 * User: AdeFulki
 * Date: 8/31/2017
 * Time: 7:20 AM
 */
class Setting extends CI_Controller{
    private $channelAccessToken;
    private $channelSecret;
    private $googlePlaceApiKey;

    public function __construct(){
        parent::__construct();
        $this->channelAccessToken = "4IbUw3HlCI99mYRb8D1QLhHn4rq6tL9jhecnRFOUbcMjQ5175/6cZZxgxGRqWDRqSY7gyf60R1Rte7fehnYwpeN8KLyOg97YE9jED/Nfh8aNoGYj+EZy8EBblHNSlewAELU8q/EZjazohsyGFTSmLgdB04t89/1O/w1cDnyilFU=";
        $this->channelSecret = "52898fb424ef4f59b8e89594db1244d9";
        $this->googlePlaceApiKey = "AIzaSyC1-Vnifn2QkSTdrn0K0FxKHuW-h1Nemcs";
    }

    public function getChannelAccessToken(){
        return $this->channelAccessToken;
    }

    public function getChannelSecret(){
        return $this->channelSecret;
    }

    public function getGooglePlaceApiKey(){
        return $this->googlePlaceApiKey;
    }
}