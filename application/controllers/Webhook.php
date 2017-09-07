<?php

/**
 * Created by PhpStorm.
 * User: AdeFulki
 * Date: 8/31/2017
 * Time: 7:17 AM
 */

class Webhook extends CI_Controller{

    private $webhookResponse;
    private $webhookEventObject;
    private $setting;
    private $api;
    private $http;
    private $event;
    private $source;
    private $message;
    private $postback;
    private $beacon;

    public function __construct(){
        parent::__construct();
        require_once(APPPATH.'controllers/Setting.php');
        $this->setting = new Setting();
        require_once(APPPATH.'controllers/Api.php');
        $this->api = new Api();
        require_once(APPPATH.'controllers/Http.php');
        $this->http = new Http();
        require_once(APPPATH.'controllers/Event.php');
        $this->event = new Event();
        require_once(APPPATH.'controllers/Source.php');
        $this->source = new Source();
        require_once(APPPATH.'controllers/Message.php');
        $this->message = new Message();
        require_once(APPPATH.'controllers/Postback.php');
        $this->postback = new Postback();
        require_once(APPPATH.'controllers/Beacon.php');
        $this->beacon = new Beacon();
        $this->webhookResponse = file_get_contents('php://input');
        $this->webhookEventObject = json_decode($this->webhookResponse, true);
    }

    public function isMessageText(){
        $webhook = $this->webhookEventObject;
        if($webhook["events"][0]["type"] == "message"){
            if ($webhook["events"][0]["message"]["type"] == "text"){
                return true;
            }else return false;
        }else return false;
    }

    public function getMessageText(){
//        {
//          "replyToken": "nHuyWiB7yP5Zw52FIkcQobQuGDXCTA",
//          "type": "message",
//          "timestamp": 1462629479859,
//          "source": {
//            "type": "user",
//            "userId": "U206d25c2ea6bd87c17655609a1c37cb8"
//          },
//          "message": {
//            "id": "325708",
//            "type": "text",
//            "text": "Hello, world"
//          }
//        }
        $webhook = $this->webhookEventObject;
        $event = new Event();
        $event->setReplyToken($webhook["events"][0]["replyToken"]);
        $event->setType($webhook["events"][0]["type"]);
        $event->setTimestamp($webhook["events"][0]["timestamp"]);
        $message = new Message();
        $message->setId($webhook["events"][0]["message"]["id"]);
        $message->setType($webhook["events"][0]["message"]["type"]);
        $message->setText($webhook["events"][0]["message"]["text"]);
        $event->setMessage($message);
        return $event;
    }

    public function isMessageLocation(){
        $webhook = $this->webhookEventObject;
        if($webhook["events"][0]["type"] == "message"){
            if ($webhook["events"][0]["message"]["type"] == "location"){
                return true;
            }else return false;
        }else return false;
    }

    public function getMessageLocation(){
//        {
//          "replyToken": "nHuyWiB7yP5Zw52FIkcQobQuGDXCTA",
//          "type": "message",
//          "timestamp": 1462629479859,
//          "source": {
//            "type": "user",
//            "userId": "U206d25c2ea6bd87c17655609a1c37cb8"
//          },
//          "message": {
//            "id": "325708",
//            "type": "location",
//            "title": "my location",
//            "address": "〒150-0002 東京都渋谷区渋谷２丁目２１−１",
//            "latitude": 35.65910807942215,
//            "longitude": 139.70372892916203
//          }
//        }
        $webhook = $this->webhookEventObject;
        $event = new Event();
        $event->setReplyToken($webhook["events"][0]["replyToken"]);
        $event->setType($webhook["events"][0]["type"]);
        $event->setTimestamp($webhook["events"][0]["timestamp"]);
        $message = new Message();
        $message->setId($webhook["events"][0]["message"]["id"]);
        $message->setType($webhook["events"][0]["message"]["type"]);
        $message->setTitle($webhook["events"][0]["message"]["title"]);
        $message->setAddress($webhook["events"][0]["message"]["address"]);
        $message->setLatitude($webhook["events"][0]["message"]["latitude"]);
        $message->setLongitude($webhook["events"][0]["message"]["longitude"]);
        $event->setMessage($message);
        return $event;
    }

    public function isFollowEvent(){
        $webhook = $this->webhookEventObject;
        if($webhook["events"][0]["type"] == "follow"){
            return true;
        }else return false;
    }

    public function getFollowEvent(){
//        {
//          "replyToken": "nHuyWiB7yP5Zw52FIkcQobQuGDXCTA",
//          "type": "follow",
//          "timestamp": 1462629479859,
//          "source": {
//            "type": "user",
//            "userId": "U206d25c2ea6bd87c17655609a1c37cb8"
//          }
//        }
        $webhook = $this->webhookEventObject;
        $event = new Event();
        $event->setReplyToken($webhook["events"][0]["replyToken"]);
        $event->setType($webhook["events"][0]["type"]);
        $event->setTimestamp($webhook["events"][0]["timestamp"]);
        return $event;
    }

    public function isUnfollowEvent(){
        $webhook = $this->webhookEventObject;
        if($webhook["events"][0]["type"] == "unfollow"){
            return true;
        }else return false;
    }

    public function getUnfollowEvent(){
//        {
//          "type": "unfollow",
//          "timestamp": 1462629479859,
//          "source": {
//            "type": "user",
//            "userId": "U206d25c2ea6bd87c17655609a1c37cb8"
//          }
//        }
        $webhook = $this->webhookEventObject;
        $event = new Event();
        $event->setType($webhook["events"][0]["type"]);
        $event->setTimestamp($webhook["events"][0]["timestamp"]);
        return $event;
    }

    public function isJoinEvent(){
        $webhook = $this->webhookEventObject;
        if($webhook["events"][0]["type"] == "join"){
            return true;
        }else return false;
    }

    public function getJoinEvent(){
//        {
//          "replyToken": "nHuyWiB7yP5Zw52FIkcQobQuGDXCTA",
//          "type": "join",
//          "timestamp": 1462629479859,
//          "source": {
//            "type": "group",
//            "groupId": "cxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
//          }
//        }
        $webhook = $this->webhookEventObject;
        $event = new Event();
        $event->setReplyToken($webhook["events"][0]["replyToken"]);
        $event->setType($webhook["events"][0]["type"]);
        $event->setTimestamp($webhook["events"][0]["timestamp"]);
        return $event;
    }

    public function isLeaveEvent(){
        $webhook = $this->webhookEventObject;
        if($webhook["events"][0]["type"] == "leave"){
            return true;
        }else return false;
    }

    public function getLeaveEvent(){
//        {
//          "type": "leave",
//          "timestamp": 1462629479859,
//          "source": {
//            "type": "group",
//            "groupId": "cxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
//          }
//        }
        $webhook = $this->webhookEventObject;
        $event = new Event();
        $event->setType($webhook["events"][0]["type"]);
        $event->setTimestamp($webhook["events"][0]["timestamp"]);
        return $event;
    }

    public function isPostbackEvent(){
        $webhook = $this->webhookEventObject;
        if($webhook["events"][0]["type"] == "postback"){
            return true;
        }else return false;
    }

    public function getPostbackEvent(){
//        {
//          "replyToken": "nHuyWiB7yP5Zw52FIkcQobQuGDXCTA",
//          "type": "postback",
//          "timestamp": 1462629479859,
//          "source": {
//            "type": "user",
//            "userId": "U206d25c2ea6bd87c17655609a1c37cb8"
//          },
//          "postback": {
//            "data": "action=buyItem&itemId=123123&color=red"
//          }
//        }
        $webhook = $this->webhookEventObject;
        $event = new Event();
        $event->setReplyToken($webhook["events"][0]["replyToken"]);
        $event->setType($webhook["events"][0]["type"]);
        $event->setTimestamp($webhook["events"][0]["timestamp"]);
        $postback = new Postback();
        $postback->setData($webhook["events"][0]["postback"]["data"]);
        $event->setPostback($postback);
        return $event;
    }

    public function isBeaconEvent(){
        $webhook = $this->webhookEventObject;
        if($webhook["events"][0]["type"] == "beacon"){
            return true;
        }else return false;
    }

    public function getBeaconEvent(){
//        {
//          "replyToken": "nHuyWiB7yP5Zw52FIkcQobQuGDXCTA",
//          "type": "beacon",
//          "timestamp": 1462629479859,
//          "source": {
//            "type": "user",
//            "userId": "U206d25c2ea6bd87c17655609a1c37cb8"
//          },
//          "beacon": {
//            "hwid": "d41d8cd98f",
//            "type": "enter"
//          }
//        }
        $webhook = $this->webhookEventObject;
        $event = new Event();
        $event->setReplyToken($webhook["events"][0]["replyToken"]);
        $event->setType($webhook["events"][0]["type"]);
        $event->setTimestamp($webhook["events"][0]["timestamp"]);
        $beacon = new Beacon();
        $beacon->setHwid($webhook["events"][0]["beacon"]["hwid"]);
        $beacon->setHwid($webhook["events"][0]["beacon"]["type"]);
        $event->setBeacon($beacon);
        return $event;
    }

    public function isUserSource(){
        $webhook = $this->webhookEventObject;
        if($webhook["events"][0]["source"]["type"] == "user"){
            return true;
        }else return false;
    }

    public function getUserSource(){
//        {
//          "replyToken": "nHuyWiB7yP5Zw52FIkcQobQuGDXCTA",
//          "type": "beacon",
//          "timestamp": 1462629479859,
//          "source": {
//            "type": "user",
//            "userId": "U206d25c2ea6bd87c17655609a1c37cb8"
//          }
//        }
        $webhook = $this->webhookEventObject;
        $source = new Source();
        $source->setType($webhook["events"][0]["source"]["type"]);
        $source->setUserId($webhook["events"][0]["source"]["userId"]);
        return $source;
    }

    public function isRoomSource(){
        $webhook = $this->webhookEventObject;
        if($webhook["events"][0]["source"]["type"] == "room"){
            return true;
        }else return false;
    }

    public function getRoomSource(){
//        {
//          "replyToken": "nHuyWiB7yP5Zw52FIkcQobQuGDXCTA",
//          "type": "beacon",
//          "timestamp": 1462629479859,
//          "source": {
//            "type": "user",
//            "userId": "U206d25c2ea6bd87c17655609a1c37cb8"
//          }
//        }
        $webhook = $this->webhookEventObject;
        $source = new Source();
        $source->setType($webhook["events"][0]["source"]["type"]);
        $source->setRoomId($webhook["events"][0]["source"]["roomId"]);
        $source->setUserId($webhook["events"][0]["source"]["userId"]);
        return $source;
    }

    public function isGroupSource(){
        $webhook = $this->webhookEventObject;
        if($webhook["events"][0]["source"]["type"] == "room"){
            return true;
        }else return false;
    }

    public function getGroupSource(){
//        {
//          "replyToken": "nHuyWiB7yP5Zw52FIkcQobQuGDXCTA",
//          "type": "beacon",
//          "timestamp": 1462629479859,
//          "source": {
//            "type": "user",
//            "userId": "U206d25c2ea6bd87c17655609a1c37cb8"
//          }
//        }
        $webhook = $this->webhookEventObject;
        $source = new Source();
        $source->setType($webhook["events"][0]["source"]["type"]);
        $source->setGroupId($webhook["events"][0]["source"]["groupId"]);
        $source->setUserId($webhook["events"][0]["source"]["userId"]);
        return $source;
    }
}