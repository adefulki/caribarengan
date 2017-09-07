<?php

/**
 * Created by PhpStorm.
 * User: AdeFulki
 * Date: 8/31/2017
 * Time: 8:16 AM
 */

class Event{
    private $replyToken;
    private $type;
    private $timestamp;
    private $source;
    private $message;
    private $beacon;
    private $postback;

    /**
     * @return mixed
     */
    public function getReplyToken(){
        return $this->replyToken;
    }

    /**
     * @param mixed $replyToken
     */
    public function setReplyToken($replyToken){
        $this->replyToken = $replyToken;
    }

    /**
     * @return mixed
     */
    public function getType(){
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type){
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getTimestamp(){
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp){
        $this->timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getSource(){
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source){
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getMessage(){
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message){
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getBeacon(){
        return $this->beacon;
    }

    /**
     * @param mixed $beacon
     */
    public function setBeacon($beacon){
        $this->beacon = $beacon;
    }

    /**
     * @return mixed
     */
    public function getPostback(){
        return $this->postback;
    }

    /**
     * @param mixed $postback
     */
    public function setPostback($postback){
        $this->postback = $postback;
    }
}