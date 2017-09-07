<?php

/**
 * Created by PhpStorm.
 * User: AdeFulki
 * Date: 8/31/2017
 * Time: 9:08 AM
 */
class Postback{
    private $data;

    /**
     * @return mixed
     */
    public function getData(){
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data){
        $this->data = $data;
    }
}