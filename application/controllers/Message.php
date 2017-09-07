<?php

/**
 * Created by PhpStorm.
 * User: AdeFulki
 * Date: 8/31/2017
 * Time: 7:20 AM
 */
class Message{
    private $id;
    private $type;
    private $text;
    private $fileName;
    private $fileSize;
    private $title;
    private $address;
    private $latitude;
    private $longitude;
    private $packageId;
    private $stickerId;

    /**
     * @return mixed
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id){
        $this->id = $id;
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
    public function getText(){
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text){
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getFileName(){
        return $this->fileName;
    }

    /**
     * @param mixed $fileName
     */
    public function setFileName($fileName){
        $this->fileName = $fileName;
    }

    /**
     * @return mixed
     */
    public function getFileSize(){
        return $this->fileSize;
    }

    /**
     * @param mixed $fileSize
     */
    public function setFileSize($fileSize){
        $this->fileSize = $fileSize;
    }

    /**
     * @return mixed
     */
    public function getTitle(){
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title){
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getAddress(){
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address){
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getLatitude(){
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     */
    public function setLatitude($latitude){
        $this->latitude = $latitude;
    }

    /**
     * @return mixed
     */
    public function getLongitude(){
        return $this->longitude;
    }

    /**
     * @param mixed $longitude
     */
    public function setLongitude($longitude){
        $this->longitude = $longitude;
    }

    /**
     * @return mixed
     */
    public function getPackageId(){
        return $this->packageId;
    }

    /**
     * @param mixed $packageId
     */
    public function setPackageId($packageId){
        $this->packageId = $packageId;
    }

    /**
     * @return mixed
     */
    public function getStickerId(){
        return $this->stickerId;
    }

    /**
     * @param mixed $stickerId
     */
    public function setStickerId($stickerId){
        $this->stickerId = $stickerId;
    }
}