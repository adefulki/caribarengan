<?php

/**
 * Created by PhpStorm.
 * User: AdeFulki
 * Date: 8/31/2017
 * Time: 8:31 AM
 */
class Source{
    private $type;
    private $userId;
    private $groupId;
    private $roomId;

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
    public function getUserId(){
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId){
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getGroupId(){
        return $this->groupId;
    }

    /**
     * @param mixed $groupId
     */
    public function setGroupId($groupId){
        $this->groupId = $groupId;
    }

    /**
     * @return mixed
     */
    public function getRoomId(){
        return $this->roomId;
    }

    /**
     * @param mixed $roomId
     */
    public function setRoomId($roomId){
        $this->roomId = $roomId;
    }
}