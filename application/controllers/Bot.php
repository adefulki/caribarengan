<?php

/**
 * Created by PhpStorm.
 * User: AdeFulki
 * Date: 8/31/2017
 * Time: 8:43 AM
 */
class Bot extends CI_Controller{

    private $webhook;
    private $event;
    private $source;
    private $message;
    private $api;
    private $http;

    public function __construct(){
        parent::__construct();
        require_once(APPPATH.'controllers/Webhook.php');
        $this->webhook = new Webhook();
        require_once(APPPATH.'controllers/Event.php');
        $this->event = new Event();
        require_once(APPPATH.'controllers/Source.php');
        $this->source = new Source();
        require_once(APPPATH.'controllers/Message.php');
        $this->message = new Message();
        require_once(APPPATH.'controllers/Api.php');
        $this->api = new Api();
        require_once(APPPATH.'controllers/Http.php');
        $this->http = new Http();
        $this->load->database();
    }

    public function main(){
        if($this->webhook->isUserSource()){
            $this->source = $this->webhook->getUserSource();
            if($this->User_model->isNotAvailable($this->source->getUserId()) && $this->source->getUserId() != null){
                $apiProfile = $this->api->getApiProfile($this->source->getUserId());
                $result = $this->http->httpGet($apiProfile);
                $profile=json_decode($result);
                $data = array(
                    'ID_USER' => $this->source->getUserId(),
                    'NAME_USER' => $profile->displayName,
                    'IMG_USER' => $profile->pictureUrl
                );
                $this->User_model->add($data);
            }

            $result=$this->User_model->getRecordsById($this->source->getUserId());
            $action = $result->{'ACTION_USER'};

            if ($action==""){
                if($this->webhook->isMessageText()){
                    echo "messagetext";
                    $this->event = $text = $this->webhook->getMessageText();
                    $this->message = $this->event->getMessage();
                    $text = $this->message->getText();
                    $text = strtoupper($text);
                    if($text == "CARI"){
//                        $data = array(
//                            'ACTION_USER' => $text
//                        );
//                        $this->User_model->update($this->source->getUserId(),$data);
                        $action = "Aksi: ".$text.">";
                        //echo $action;
                        $message = "asnfioewnfioesnfiesonfiosenfioesnfg\xA\xA\xA____________________________________\xA".$action."\xA____________________________________\xAKetik \"bersihkan\" untuk membersihkan aksi.";
                        $api = $this->api->getApiReply();
                        $body["replyToken"]=$this->event->getReplyToken();
                        $body["messages"][0] = array(
                            "type" => "text",
                            "text" => $message
                            );
                        $this->http->httpPost($api, $body);
                    }
                }
            }
        }elseif($this->webhook->isGroupSource() || $this->webhook->isRoomSource()){
            if($this->webhook->isGroupSource()){
                $this->source = $this->webhook->getGroupSource();
            }elseif($this->webhook->isRoomSource()){
                $this->source = $this->webhook->getRoomSource();
            }
            if($this->User_model->isNotAvailable($this->source->getUserId()) && $this->source->getUserId() != null){
                $apiProfile = $this->api->getApiProfile($this->source->getUserId());
                $result = $this->http->httpGet($apiProfile);
                $profile=json_decode($result);
                $data = array(
                    'ID_USER' => $this->source->getUserId(),
                    'NAMA_USER' => $profile->displayName,
                    'IMG_USER' => $profile->pictureUrl
                );
                $this->User_model->add($data);
            }

            $result=$this->User_model->getRecordsById($this->source->getUserId());
            $action = $result->{'ACTION_USER'};

            if (is_null($action)){

            }
        }















//        if($this->webhook->isMessageText()){
//            $this->event = $this->webhook->getMessageText();
//            $this->source = $this->event->getSource();
//            $this->message = $this->event->getMessage();
//
//            $apiProfile = $this->api->getApiProfile($this->source->getUserId());
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
//            $body["replyToken"]=$this->event->getReplyToken();
//            $body["messages"][0] = array(
//                "type" => "image",
//                "originalContentUrl" => "http://carmate.id/assets/images/59a94a94c8f07.jpg",
//                "previewImageUrl" => "http://carmate.id/assets/images/59a94a94c8f07.jpg"
//                );
//            $this->http->httpPost($api, $body);
//        }elseif($this->webhook->isMessageLocation()){
//            $this->event = $this->webhook->getMessageLocation();
//        }elseif($this->webhook->isFollowEvent()){
//            $this->event = $this->webhook->getFollowEvent();
//        }elseif($this->webhook->isUnfollowEvent()){
//            $this->event = $this->webhook->getUnfollowEvent();
//        }elseif($this->webhook->isJoinEvent()){
//            $this->event = $this->webhook->getJoinEvent();
//        }elseif($this->webhook->isLeaveEvent()){
//            $this->event = $this->webhook->getLeaveEvent();
//        }elseif($this->webhook->isPostbackEvent()){
//            $this->event = $this->webhook->getPostbackEvent();
//        }elseif($this->webhook->isBeaconEvent()){
//            $this->event = $this->webhook->getBeaconEvent();
//        }
//    }
//
//    private function setImageCompressionQuality($imagePath, $quality) {
//        $imagick = new \Imagick(realpath($imagePath));
//        $imagick->setImageCompressionQuality($quality);
//        header("Content-Type: image/jpg");
//        echo $imagick->getImageBlob();
//    }
}}