<?php

/**
 * Created by PhpStorm.
 * User: AdeFulki
 * Date: 8/31/2017
 * Time: 7:27 AM
 */
class Api extends CI_Controller{
    public function __construct(){
        parent::__construct();
    }

    public function getApiReply(){
        $api = "https://api.line.me/v2/bot/message/reply";
        return $api;
    }

    public function getApiPush(){
        $api = "https://api.line.me/v2/bot/message/push";
        return $api;
    }

    public function getApiMulticast(){
        $api = "https://api.line.me/v2/bot/message/multicast";
        return $api;
    }

    public function getApiContent($messageId){
        $api = "https://api.line.me/v2/bot/message/".$messageId."/content";
        return $api;
    }

    public function getApiProfile($userId){
        $api = "https://api.line.me/v2/bot/profile/".$userId;
        return $api;
    }

    public function getApiGroupMemberProfile($groupId, $userId){
        $api = "https://api.line.me/v2/bot/group/".$groupId."/member/".$userId;
        return $api;
    }

    public function getApiGroupMemberId($groupId, $continuationToken){
        $api = "https://api.line.me/v2/bot/group/".$groupId."/members/ids?start=".$continuationToken;
        return $api;
    }

    public function getApiLeaveGroup($roomId){
        $api = "https://api.line.me/v2/bot/room/".$roomId."/leave";
        return $api;
    }
    public function getApiAutocompletePlace($input, $key){
        $api = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input=".$input."&types=geocode&key=".$key;
        return $api;
    }
}