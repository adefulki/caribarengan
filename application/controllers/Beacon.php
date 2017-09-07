<?php

/**
 * Created by PhpStorm.
 * User: AdeFulki
 * Date: 8/31/2017
 * Time: 9:15 AM
 */
class Beacon{
    private $hwid;
    private $type;

    /**
     * @return mixed
     */
    public function getHwid(){
        return $this->hwid;
    }

    /**
     * @param mixed $hwid
     */
    public function setHwid($hwid){
        $this->hwid = $hwid;
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

}