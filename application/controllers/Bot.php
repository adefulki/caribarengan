<?php

/**
 * Created by PhpStorm.
 * User: AdeFulki
 * Date: 8/31/2017
 * Time: 8:43 AM
 */
class Bot extends CI_Controller{
    private $api;
    private $http;
    private $setting;

    public function __construct(){
        parent::__construct();
        require_once(APPPATH.'controllers/Webhook.php');
        require_once(APPPATH.'controllers/Event.php');
        require_once(APPPATH.'controllers/Source.php');
        require_once(APPPATH.'controllers/Message.php');
        require_once(APPPATH.'controllers/Setting.php');
        $this->setting = new Setting();
        require_once(APPPATH.'controllers/Api.php');
        $this->api = new Api();
        require_once(APPPATH.'controllers/Http.php');
        $this->http = new Http();
    }

    public function main(){
        $webhook = new Webhook();
        $event = new Event();
        $message = new Message();
        $source = new Source();
        if($webhook->isUserSource()){
            $source = $webhook->getUserSource();
            if(($this->User_model->isNotAvailable($source->getUserId())) && ($source->getUserId() != null)){
                $apiProfile = $this->api->getApiProfile($source->getUserId());
                $result = $this->http->httpGet($apiProfile);
                $profile=json_decode($result);
                $data = array(
                    'ID_USER' => $source->getUserId(),
                    'NAME_USER' => $profile->displayName,
                    'IMG_USER' => $profile->pictureUrl
                );
                $this->User_model->add($data);
            }

            $arrayAction = array();
            $arrayAction = $this->createArrayAction($source->getUserId());

            $tmpStringAction="";

            if($webhook->isMessageText()){
                $tmpStringAction = $this->createStringAction($arrayAction);
                $event = $text = $webhook->getMessageText();
                $message = $event->getMessage();
                $text = $message->getText();
                $text = strtoupper($text);
                switch ($text) {
                    case "MENU": {
                        $data = array(
                            'ACTION_USER' => "MENU>",
                            'TMP_ACTION_USER' => $tmpStringAction
                        );
                        $this->User_model->update($source->getUserId(),$data);
                        break;
                    }
                    default: break;
                }
            }

            $statusReply = false;
            switch ($arrayAction[0]){
                case "":{
                    if($webhook->isMessageText()){
                        $event = $text = $webhook->getMessageText();
                        $message = $event->getMessage();
                        $text = $message->getText();
                        $text = strtoupper($text);
                        if($text == "CARI" || $text == "POPULER" || $text == "BUAT" || $text == "PEMBERITAHUAN" || $text == "REGISTRASI" || $text == "MENU"){
                            array_pop($arrayAction);
                            var_dump($arrayAction);
                            array_push($arrayAction,$text);
                            array_push($arrayAction,"");
                            var_dump($arrayAction);
                            $this->insertArrayAction($source->getUserId(),$arrayAction);
                            $stringAction = $this->createStringAction($arrayAction);
                            $message = "____________________________________\xA Aksi: ".$stringAction."\xA____________________________________\xAKetik \"menu\" untuk kembali ke menu awal.";
                            $body["replyToken"]=$event->getReplyToken();
                            $body["messages"][0] = array(
                                "type" => "text",
                                "text" => $message
                            );
                            $this->http->httpPost($this->api->getApiReply(), $body);
                            $statusReply = true;
                        }
                    }
                    break;
                }
                case "CARI":{
                    switch ($arrayAction[1]) {
                        case "": {
                            if($webhook->isMessageText()) {
                                $event = $text = $webhook->getMessageText();
                                $message = $event->getMessage();
                                $text = $message->getText();
                                $text = strtoupper($text);

//                                $result = $this->http->httpGet2($this->api->getApiAutocompletePlace($text, $this->setting->getGooglePlaceApiKey()));
                                $result = $this->Trip_model->search($text);
                                var_dump($result);

                                array_pop($arrayAction);
                                var_dump($arrayAction);
                                array_push($arrayAction,"KATAKUNCI");
                                array_push($arrayAction,"");
                                var_dump($arrayAction);
                                $this->insertArrayAction($source->getUserId(),$arrayAction);
                                $stringAction = $this->createStringAction($arrayAction);
                                $message = "____________________________________\xA Aksi: ".$stringAction."\xA____________________________________\xAKetik \"bersihkan\" untuk membersihkan aksi.";
                                $body["replyToken"]=$event->getReplyToken();
                                $body["messages"][0] = array(
                                    "type" => "text",
                                    "text" => $message
                                );
                                $this->http->httpPost($this->api->getApiReply(), $body);
                            }
                            break;
                        }
                        case "KATAKUNCI":{
                            break;
                        }
                        default: break;
                    }
                    break;
                }
                case "POPULER":{
                    break;
                }
                case "BUAT":{
                    break;
                }
                case "PEMBERITAHUAN":{
                    break;
                }
                case "REGISTRASI":{
                    break;
                }
                case "MENU":{
                    switch ($arrayAction[1]) {
                        case "": {
                            $body["replyToken"]=$event->getReplyToken();
                            $body["messages"][0] = array(
                                "type" => "text",
                                "text" => $message,
                                "template" => array(
                                    "type" => "message",
                                    "text" => "Kembali ke menu utama ?",
                                    "actions" => array(
                                        array("type" => "message",
                                            "label" => "Ya",
                                            "text" => "ya"),
                                        array("type" => "message",
                                            "label" => "Tidak",
                                            "text" => "tidak")
                                    )
                                )
                            );
                            $this->http->httpPost($this->api->getApiReply(), $body);
                            break;
                        }
                        case "YA":{
                            $data = array(
                                'ACTION_USER' => "",
                                'TMP_ACTION_USER' => ""
                            );
                            $this->User_model->update($source->getUserId(),$data);
                            break;
                        }
                        case "TIDAK":{
                            $data = array(
                                'ACTION_USER' => $tmpStringAction,
                                'TMP_ACTION_USER' => ""
                            );
                            $this->User_model->update($source->getUserId(),$data);

                            $message = "____________________________________\xA Aksi: ".$tmpStringAction."\xA____________________________________\xAKetik \"bersihkan\" untuk membersihkan aksi.";
                            $body["replyToken"]=$event->getReplyToken();
                            $body["messages"][0] = array(
                                "type" => "text",
                                "text" => $message
                            );
                            $this->http->httpPost($this->api->getApiReply(), $body);
                            break;
                        }
                        default: break;
                    }
                    break;
                }
                default: break;
            }
            
//            if($statusReply == true){
//                $arrayAction = $this->createArrayAction($source->getUserId());
//                switch ($arrayAction[0]){
//                    case "CARI":{
//                        switch ($arrayAction[1]) {
//                            case "": {
//                                break;
//                            }
//                            case "KATAKUNCI":{
//                                break;
//                            }
//                            case "BERSIHKAN":{
//                                $data = array(
//                                    'ACTION_USER' => ""
//                                );
//                                $this->User_model->update($source->getUserId(),$data);
//                            }
//                        }
//                        break;
//                    }
//                    case "POPULER":{
//                        break;
//                    }
//                    case "BUAT":{
//                        break;
//                    }
//                    case "PEMBERITAHUAN":{
//                        break;
//                    }
//                    case "REGISTRASI":{
//                        break;
//                    }
//                }
//            }
//        }elseif($webhook->isGroupSource() || $webhook->isRoomSource()){
//            if($webhook->isGroupSource()){
//                $source = $webhook->getGroupSource();
//            }elseif($webhook->isRoomSource()){
//                $source = $webhook->getRoomSource();
//            }
//            if($this->User_model->isNotAvailable($source->getUserId()) && $source->getUserId() != null){
//                $apiProfile = $this->api->getApiProfile($source->getUserId());
//                $result = $this->http->httpGet($apiProfile);
//                $profile=json_decode($result);
//                $data = array(
//                    'ID_USER' => $source->getUserId(),
//                    'NAMA_USER' => $profile->displayName,
//                    'IMG_USER' => $profile->pictureUrl
//                );
//                $this->User_model->add($data);
//            }
//
//            $result=$this->User_model->getRecordsById($source->getUserId());
//            $stringAction = $result->{'ACTION_USER'};
//
//            if (is_null($stringAction)){
//
//            }
        }















//        if($webhook->isMessageText()){
//            $event = $webhook->getMessageText();
//            $source = $event->getSource();
//            $message = $event->getMessage();
//
//            $apiProfile = $this->api->getApiProfile($source->getUserId());
//            $data = $this->http->httpGet($apiProfile);
//            $profile=json_decode($data);
//
//            $pictureUrl = $profile->pictureUrl;
//            $displayName = $profile->displayName;
//
//            $ftp_server = "ftp.carmate.id";
//            $ftp_user_name = "pkl@carmate.id";
//            $ftp_user_pass = "Kam1selalu1";
//            $conn_id = ftp_connect($ftp_server);
//
//            $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
//
//            if ((!$conn_id) || (!$login_result)) {
//                echo "FTP connection has failed!";
//                echo "Attempted to connect to $ftp_server for user $ftp_user_name";
//                exit;
//            } else {
//                echo "Connected to $ftp_server, for user $ftp_user_name";
//            }
//
//            $source_img = $pictureUrl;
//            $destination_img = uniqid().".png";
//
//            $d = $this->setImageCompressionQuality($source_img, 90);
//
//            var_dump($d);
//
//            $destination_file = "/assets/images/".$destination_img;
//            $upload = ftp_put($conn_id, $destination_file, $d, FTP_BINARY);
//
//            if (!$upload) {
//                echo "FTP upload has failed!";
//            } else {
//                echo "Uploaded $pictureUrl to $ftp_server as $destination_file";
//            }
//            ftp_close($conn_id);
//
//            $api = $this->api->getApiReply();
//            $body["replyToken"]=$event->getReplyToken();
//            $body["messages"][0] = array(
//                "type" => "image",
//                "originalContentUrl" => "http://carmate.id/assets/images/59a94a94c8f07.jpg",
//                "previewImageUrl" => "http://carmate.id/assets/images/59a94a94c8f07.jpg"
//                );
//            $this->http->httpPost($api, $body);
//        }elseif($webhook->isMessageLocation()){
//            $event = $webhook->getMessageLocation();
//        }elseif($webhook->isFollowEvent()){
//            $event = $webhook->getFollowEvent();
//        }elseif($webhook->isUnfollowEvent()){
//            $event = $webhook->getUnfollowEvent();
//        }elseif($webhook->isJoinEvent()){
//            $event = $webhook->getJoinEvent();
//        }elseif($webhook->isLeaveEvent()){
//            $event = $webhook->getLeaveEvent();
//        }elseif($webhook->isPostbackEvent()){
//            $event = $webhook->getPostbackEvent();
//        }elseif($webhook->isBeaconEvent()){
//            $event = $webhook->getBeaconEvent();
//        }
//    }
//
//    private function setImageCompressionQuality($imagePath, $quality) {
//        $imagick = new \Imagick(realpath($imagePath));
//        $imagick->setImageCompressionQuality($quality);
//        header("Content-Type: image/jpg");
//        echo $imagick->getImageBlob();
//    }
    }

    public function createArrayAction($id){
        $result=$this->User_model->getRecordsById($id);
        $stringAction = $result->{'ACTION_USER'};
        $arrayAction = array();
        $arrayAction = explode('>',$stringAction);
        return $arrayAction;
    }
    
    public function createStringAction($arrayAction){
        $stringAction="";
        $stringAction = implode(">", $arrayAction);
        return $stringAction;
    }

    public function insertArrayAction($id, $arrayAction){
        $stringAction="";
        $stringAction = $this->createStringAction($arrayAction);
        $data = array(
            'ACTION_USER' => $stringAction
        );
        $this->User_model->update($id,$data);
    }
}