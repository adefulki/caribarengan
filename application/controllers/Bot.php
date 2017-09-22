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
        require_once(APPPATH.'controllers/Postback.php');
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
        $postback = new Postback();
        $source = new Source();
        if($webhook->isUserSource()){
            $source = $webhook->getUserSource();
            if(($this->User_model->isUserIdNotAvailable($source->getUserId())) && ($source->getUserId() != null)){
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

            $statusMainAction = false;
            if($webhook->isMessageText()){
                $tmpStringAction = $this->createStringAction($arrayAction);
                $event = $text = $webhook->getMessageText();
                $message = $event->getMessage();
                $text = $message->getText();
                $text = strtoupper($text);
                switch ($text) {
                    case "MENU": {
                        $result = $this->User_model->getRecordsById($source->getUserId());
                        var_dump($result);
                        if($result->{'ACTION_USER'}!=""){
                            $data = array(
                                'ACTION_USER' => "MENU>BACK>",
                                'TMP_ACTION_USER' => $tmpStringAction
                            );
                            $this->User_model->update($source->getUserId(),$data);

                            $body["to"]=$source->getUserId();
                            $body["messages"][0] = array(
                                "type" => "template",
                                "altText" => "clear confirm",
                                "template" => array(
                                    "type" => "confirm",
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
                            var_dump($body);
                            $this->http->httpPost($this->api->getApiPush(), $body);
                            $statusMainAction = true;
                        }
                        break;
                    }
                    default: break;
                }
            }

            if($statusMainAction==false){
                switch ($arrayAction[0]){
                    case "":{
                        if($webhook->isMessageText()){
                            $event = $text = $webhook->getMessageText();
                            $message = $event->getMessage();
                            $text = $message->getText();
                            $text = strtoupper($text);
                            $body["replyToken"]=$event->getReplyToken();
                            switch($text){
                                case "CARI":{
                                    array_pop($arrayAction);
                                    array_push($arrayAction,"CARI");
                                    array_push($arrayAction,"");
                                    $this->insertArrayAction($source->getUserId(),$arrayAction);
                                    $body["messages"][0] = array(
                                        "type" => "text",
                                        "text" => "Masukan kata kunci trip yang anda cari \xA\xA contoh: \"Gunung Rinjani\""
                                    );
                                    var_dump($body);
                                    $this->http->httpPost($this->api->getApiReply(), $body);
                                    break;
                                }
                                case "POPULER":{
                                    array_pop($arrayAction);
                                    array_push($arrayAction,"POPULER");
                                    array_push($arrayAction,"");
                                    $this->insertArrayAction($source->getUserId(),$arrayAction);
                                    $result = $this->Trip_model->getPopular();
                                    if ($result != null){
                                        var_dump($result);
                                        $body["replyToken"]=$event->getReplyToken();
                                        for($i = 0; $i < sizeof($result);$i++)
                                        {
                                            $postbackGabung=array();
                                            if($this->Request_model->isNotAvailable($result[$i]->{"ID_TRIP"}, $source->getUserId())){
                                                $postbackGabung = array("type" => "postback",
                                                    "label" => "Gabung",
                                                    "data" => "action=GABUNG&id=".$result[$i]->{"ID_TRIP"});
                                            }else{
                                                $postbackGabung = array("type" => "postback",
                                                    "label" => "Batal Gabung",
                                                    "data" => "action=BATALGABUNG&id=".$result[$i]->{"ID_TRIP"});
                                            }
                                            $date=date_create($result[$i]->{"START_DATE_TRIP"});
                                            $columns[$i] = array(
                                                "thumbnailImageUrl" => $result[$i]->{"IMG_DESTINATION"},
                                                "title" => $result[$i]->{"TITLE_TRIP"},
                                                "text" => date_format($date,"d F Y")."\xA".$result[$i]->{"DESCRIPTION_TRIP"},
                                                "actions" => array(
                                                    array("type" => "postback",
                                                        "label" => "Detil",
                                                        "data" => "action=DETIL&id=".$result[$i]->{"ID_TRIP"}),
                                                    $postbackGabung
                                                )
                                            );
                                        }
                                        $body["messages"][0] = array(
                                            "type" => "template",
                                            "altText" => "list trip",
                                            "template" => array(
                                                "type" => "carousel",
                                                "columns" => $columns
                                            )
                                        );
                                        var_dump($body);
                                        $this->http->httpPost($this->api->getApiReply(), $body);
                                    }
                                    break;
                                }
                                case "BUAT":{
                                    $body["messages"][0] = array(
                                        "type" => "text",
                                        "text" => "Untuk membuat trip, anda harus membuat group atau room terlebih dahulu, lalu invite CariBarengan pada group atau room tersebut"
                                    );
                                    var_dump($body);
                                    $this->http->httpPost($this->api->getApiReply(), $body);
                                    break;
                                }
                                case "PEMBERITAHUAN":{
                                    array_pop($arrayAction);
                                    array_push($arrayAction,"PEMBERITAHUAN");
                                    array_push($arrayAction,"");
                                    $this->insertArrayAction($source->getUserId(),$arrayAction);
                                    break;
                                }
                                case "REGISTRASI":{
                                    array_pop($arrayAction);
                                    array_push($arrayAction,"REGISTRASI");
                                    array_push($arrayAction,"");
                                    $this->insertArrayAction($source->getUserId(),$arrayAction);
                                    $resultUser = $this->User_model->getRecordsById($source->getUserId());
                                    if ($resultUser->{"ID_LINE"} == "") {
                                        array_pop($arrayAction);
                                        array_push($arrayAction,"INPUTLINEID");
                                        array_push($arrayAction,"");
                                        $this->insertArrayAction($source->getUserId(),$arrayAction);
                                        $stringAction = $this->createStringAction($arrayAction);
                                        $body["replyToken"]=$event->getReplyToken();
                                        $body["messages"][0] = array(
                                            "type" => "text",
                                            "text" => "Masukan Line id anda"
                                        );
                                        var_dump($body);
                                        $this->http->httpPost($this->api->getApiReply(), $body);
                                    } else {
                                        $resultUser = $this->User_model->getRecordsById($source->getUserId());
                                        $lineId = $resultUser->{'ID_LINE'};
                                        $body["replyToken"]=$event->getReplyToken();
                                        $body["messages"][0] = array(
                                            "type" => "template",
                                            "altText" => "confirm line id",
                                            "template" => array(
                                                "type" => "confirm",
                                                "text" => "Line id anda " . $lineId . ". Apakah ingin merubahnya ?",
                                                "actions" => array(
                                                    array("type" => "postback",
                                                        "label" => "Ya",
                                                        "data" => "action=YA"),
                                                    array("type" => "postback",
                                                        "label" => "Tidak",
                                                        "data" => "action=TIDAK")
                                                )
                                            )
                                        );
                                        var_dump($body);
                                        $this->http->httpPost($this->api->getApiReply(), $body);
                                    }
                                    break;
                                }
                                case "MENU":{
                                    $body["to"]=$source->getUserId();
                                    $body["messages"][0] = array(
                                        "type" => "template",
                                        "altText" => "menu utama",
                                        "template" => array(
                                            "type" => "carousel",
                                            "columns" => array(
                                                array("text" => "Cari",
                                                    "actions" => array(
                                                        array("type" => "message",
                                                            "label" => "Cari",
                                                            "text" => "CARI")
                                                    )),
                                                array("text" => "Populer",
                                                    "actions" => array(
                                                        array("type" => "message",
                                                            "label" => "Populer",
                                                            "text" => "POPULER")
                                                    )),
                                                array("text" => "Buat",
                                                    "actions" => array(
                                                        array("type" => "message",
                                                            "label" => "Buat",
                                                            "text" => "BUAT")
                                                    )),
                                                array("text" => "Pemberitahuan",
                                                    "actions" => array(
                                                        array("type" => "message",
                                                            "label" => "Pemberitahuan",
                                                            "text" => "PEMBERITAHUAN")
                                                    )),
                                                array("text" => "Registrasi",
                                                    "actions" => array(
                                                        array("type" => "message",
                                                            "label" => "Registrasi",
                                                            "text" => "REGISTRASI")
                                                    ))
                                            )
                                        )
                                    );
                                    var_dump($body);
                                    $this->http->httpPost($this->api->getApiPush(), $body);
                                    break;
                                }
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
                                    var_dump($text);
//                                $result = $this->http->httpGet2($this->api->getApiAutocompletePlace($text, $this->setting->getGooglePlaceApiKey()));
                                    $columns = array();
                                    $result = $this->Trip_model->search($text);
                                    if ($result != null){
                                        var_dump($result);
                                        $body["replyToken"]=$event->getReplyToken();
                                        for($i = 0; $i < sizeof($result);$i++)
                                        {
                                            $postbackGabung=array();
                                            if($this->Request_model->isNotAvailable($result[$i]->{"ID_TRIP"}, $source->getUserId())){
                                                $postbackGabung = array("type" => "postback",
                                                    "label" => "Gabung",
                                                    "data" => "action=GABUNG&id=".$result[$i]->{"ID_TRIP"});
                                            }else{
                                                $postbackGabung = array("type" => "postback",
                                                    "label" => "Batal Gabung",
                                                    "data" => "action=BATALGABUNG&id=".$result[$i]->{"ID_TRIP"});
                                            }
                                            $date=date_create($result[$i]->{"START_DATE_TRIP"});
                                            $columns[$i] = array(
                                                "thumbnailImageUrl" => $result[$i]->{"IMG_DESTINATION"},
                                                "title" => $result[$i]->{"TITLE_TRIP"},
                                                "text" => date_format($date,"d F Y")."\xA".$result[$i]->{"DESCRIPTION_TRIP"},
                                                "actions" => array(
                                                    array("type" => "postback",
                                                        "label" => "Detil",
                                                        "data" => "action=DETIL&id=".$result[$i]->{"ID_TRIP"}),
                                                    $postbackGabung
                                                )
                                            );
                                        }
                                        $body["messages"][0] = array(
                                            "type" => "template",
                                            "altText" => "list trip",
                                            "template" => array(
                                                "type" => "carousel",
                                                "columns" => $columns
                                            )
                                        );
                                        var_dump($body);
                                        $this->http->httpPost($this->api->getApiReply(), $body);
                                    }

                                }else if($webhook->isPostbackEvent()) {
                                    $event = $webhook->getPostbackEvent();
                                    $postback = new Postback();
                                    $postback = $event->getPostback();
                                    $data = $postback->getData();
                                    $obj_data = explode("&",$data);
                                    var_dump($obj_data);
                                    $obj_action = explode("=",$obj_data[0]);
                                    $action = $obj_action[1];
                                    $obj_id = explode("=",$obj_data[1]);
                                    $id = $obj_id[1];
                                    switch($action) {
                                        case "DETIL": {
                                            $result = $this->Trip_model->getRecordsById($id);
                                            var_dump($result);
                                            $title = $result->{'TITLE_TRIP'};
                                            $desc = $result->{'DESCRIPTION_TRIP'};
                                            $address = $result->{'ADDRESS_DESTINATION'};
                                            $date=date_create($result->{"START_DATE_TRIP"});
                                            $leader = $result->{'ID_LINE'};
                                            $lat = $result->{'LAT_DESTINATION'};
                                            $lng = $result->{'LNG_DESTINATION'};

                                            $body["replyToken"]=$event->getReplyToken();
                                            $body["messages"][0] = array(
                                                "type" => "text",
                                                "text" => "- Judul: ".$title."\xA- Deskripsi: ".$desc."\xA- Destinasi: ".$address."\xA- Tanggal Mulai: ".date_format($date,"d F Y")."\xA- Ketua: ".$leader
                                            );
                                            $body["messages"][1] = array(
                                                "type" => "location",
                                                "title" => "Destinasi",
                                                "address" => $address,
                                                "latitude" => $lat,
                                                "longitude" => $lng
                                            );
                                            var_dump($body);
                                            $this->http->httpPost($this->api->getApiReply(), $body);
                                            break;
                                        }
                                        case "GABUNG": {
                                            if($this->Request_model->isNotAvailable($id, $source->getUserId())){
                                                $body["replyToken"]=$event->getReplyToken();
                                                $resultUser = $this->User_model->getRecordsById($source->getUserId());
                                                if ($resultUser->{"ID_LINE"} == "") {
                                                    $body["messages"][0] = array(
                                                        "type" => "text",
                                                        "text" => "Harap registrasi terlebih dahulu."
                                                    );
                                                    var_dump($body);
                                                    $this->http->httpPost($this->api->getApiReply(), $body);
                                                } else {
                                                    array_pop($arrayAction);
                                                    array_push($arrayAction,"GABUNG");
                                                    array_push($arrayAction,"");
                                                    $this->insertArrayAction($source->getUserId(),$arrayAction);
                                                    $resultUser = $this->User_model->getRecordsById($source->getUserId());
                                                    $lineId = $resultUser->{'ID_LINE'};
                                                    $body["messages"][0] = array(
                                                        "type" => "template",
                                                        "altText" => "confirm gabung",
                                                        "template" => array(
                                                            "type" => "confirm",
                                                            "text" => "Apakah anda ingin bergabung ?",
                                                            "actions" => array(
                                                                array("type" => "postback",
                                                                    "label" => "Ya",
                                                                    "data" => "action=YA&data=" . $id),
                                                                array("type" => "postback",
                                                                    "label" => "Tidak",
                                                                    "data" => "action=TIDAK&data=" . $id)
                                                            )
                                                        )
                                                    );
                                                    var_dump($body);
                                                    $this->http->httpPost($this->api->getApiReply(), $body);
                                                }
                                            }else {
                                                $body["replyToken"] = $event->getReplyToken();
                                                $body["messages"][0] = array(
                                                    "type" => "text",
                                                    "text" => "Anda telah bergabung"
                                                );
                                                var_dump($body);
                                                $this->http->httpPost($this->api->getApiReply(), $body);
                                            }
                                            break;
                                        }
                                        case "BATALGABUNG":{
                                            if(!$this->Request_model->isNotAvailable($id, $source->getUserId())) {
                                                array_pop($arrayAction);
                                                array_push($arrayAction, "BATALGABUNG");
                                                array_push($arrayAction, "");
                                                $this->insertArrayAction($source->getUserId(), $arrayAction);
                                                $stringAction = $this->createStringAction($arrayAction);
                                                $body["replyToken"]=$event->getReplyToken();
                                                $body["messages"][0] = array(
                                                    "type" => "template",
                                                    "altText" => "confirm batal gabung",
                                                    "template" => array(
                                                        "type" => "confirm",
                                                        "text" => "Apakah anda ingin membatalkan permintaan bergabung ?",
                                                        "actions" => array(
                                                            array("type" => "postback",
                                                                "label" => "Batal Gabung",
                                                                "data" => "action=YA&id=" . $id),
                                                            array("type" => "postback",
                                                                "label" => "TIDAK",
                                                                "data" => "action=TIDAK&id=" . $id)
                                                        )
                                                    )
                                                );
                                                var_dump($body);
                                                $this->http->httpPost($this->api->getApiReply(), $body);
                                            }else{
                                                $body["replyToken"] = $event->getReplyToken();
                                                $body["messages"][0] = array(
                                                    "type" => "text",
                                                    "text" => "Anda belum bergabung"
                                                );
                                                var_dump($body);
                                                $this->http->httpPost($this->api->getApiReply(), $body);
                                            }
                                            break;
                                        }
                                        default: break;
                                    }
                                }
                                break;
                            }
                            case "GABUNG":{
                                switch ($arrayAction[2]) {
                                    case "": {
                                        if($webhook->isPostbackEvent()) {
                                            $event = $webhook->getPostbackEvent();
                                            $postback = new Postback();
                                            $postback = $event->getPostback();
                                            $data = $postback->getData();
                                            $obj_data = explode("&", $data);
                                            var_dump($obj_data);
                                            $obj_action = explode("=", $obj_data[0]);
                                            $action = $obj_action[1];
                                            $obj_data = explode("=", $obj_data[1]);
                                            $id = $obj_data[1];
                                            switch ($action) {
                                                case "YA": {
                                                    $data = array(
                                                        'ACTION_USER' => "",
                                                        'TMP_ACTION_USER' => ""
                                                    );
                                                    $this->User_model->update($source->getUserId(),$data);

                                                    $resultUser = $this->User_model->getRecordsById($source->getUserId());
                                                    $resultTrip = $this->Trip_model->getRecordsById($id);

                                                    $data = array(
                                                        'COUNT_REQUEST' => $resultTrip++
                                                    );
                                                    $this->Trip_model->update($id,$data);

                                                    $body["to"] = array($resultTrip->{"ID_GROUP"});
                                                    $body["messages"][0] = array(
                                                        "type" => "text",
                                                        "text" => "Line ID ".$resultUser->{'ID_LINE'}." ingin bergabung dengan trip ".$resultTrip->{'TITLE_TRIP'}
                                                    );
                                                    var_dump($body);
                                                    $this->http->httpPost($this->api->getApiReply(), $body);

                                                    $data = array(
                                                        'ID_USER' => $source->getUserId(),
                                                        'ID_TRIP' => $id
                                                    );
                                                    $this->Request_model->add($data);

                                                    $body["replyToken"]=$event->getReplyToken();
                                                    $body["messages"][0] = array(
                                                        "type" => "text",
                                                        "text" => "Permintaan gabung anda telah terkirim"
                                                    );
                                                    $body["messages"][1] = array(
                                                        "type" => "template",
                                                        "altText" => "menu utama",
                                                        "template" => array(
                                                            "type" => "carousel",
                                                            "columns" => array(
                                                                array("text" => "Cari",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Cari",
                                                                            "text" => "CARI")
                                                                    )),
                                                                array("text" => "Populer",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Populer",
                                                                            "text" => "POPULER")
                                                                    )),
                                                                array("text" => "Buat",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Buat",
                                                                            "text" => "BUAT")
                                                                    )),
                                                                array("text" => "Pemberitahuan",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Pemberitahuan",
                                                                            "text" => "PEMBERITAHUAN")
                                                                    )),
                                                                array("text" => "Registrasi",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Registrasi",
                                                                            "text" => "REGISTRASI")
                                                                    ))
                                                            )
                                                        )
                                                    );
                                                    var_dump($body);
                                                    $this->http->httpPost($this->api->getApiReply(), $body);
                                                    break;
                                                }
                                                case "TIDAK": {
                                                    array_pop($arrayAction);
                                                    array_pop($arrayAction);
                                                    array_push($arrayAction,"");
                                                    $this->insertArrayAction($source->getUserId(),$arrayAction);
                                                    break;
                                                }
                                            }
                                        }
                                        break;
                                    }
                                    default: break;
                                }
                                break;
                            }
                            case "BATALGABUNG":{
                                switch ($arrayAction[2]) {
                                    case "": {
                                        if($webhook->isPostbackEvent()) {
                                            $event = $webhook->getPostbackEvent();
                                            $postback = new Postback();
                                            $postback = $event->getPostback();
                                            $data = $postback->getData();
                                            $obj_data = explode("&", $data);
                                            var_dump($obj_data);
                                            $obj_action = explode("=", $obj_data[0]);
                                            $action = $obj_action[1];
                                            $obj_id = explode("=", $obj_data[1]);
                                            $id = $obj_id[1];
                                            switch ($action) {
                                                case "YA": {
                                                    $data = array(
                                                        'ACTION_USER' => "",
                                                        'TMP_ACTION_USER' => ""
                                                    );
                                                    $this->User_model->update($source->getUserId(),$data);

                                                    $resultUser = $this->User_model->getRecordsById($source->getUserId());
                                                    $resultTrip = $this->Trip_model->getRecordsById($id);
                                                    $resultRequest = $this->Request_model->getRecordsByUserIdTripId($source->getUserId(),$id);
                                                    $body["to"] = array($resultTrip->{"ID_GROUP"});
                                                    $body["messages"][0] = array(
                                                        "type" => "text",
                                                        "text" => "Line ID ".$resultUser->{'ID_LINE'}." ingin membatalkan bergabung dengan trip ".$resultTrip->{'TITLE_TRIP'}
                                                    );
                                                    var_dump($body);
                                                    $this->http->httpPost($this->api->getApiReply(), $body);

                                                    $this->Request_model->delete($resultRequest->{"ID_REQUEST"});
                                                    $body["replyToken"]=$event->getReplyToken();
                                                    $body["messages"][0] = array(
                                                        "type" => "text",
                                                        "text" => "Membatalkan ikut gabung"
                                                    );
                                                    $body["messages"][1] = array(
                                                        "type" => "template",
                                                        "altText" => "menu utama",
                                                        "template" => array(
                                                            "type" => "carousel",
                                                            "columns" => array(
                                                                array("text" => "Cari",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Cari",
                                                                            "text" => "CARI")
                                                                    )),
                                                                array("text" => "Populer",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Populer",
                                                                            "text" => "POPULER")
                                                                    )),
                                                                array("text" => "Buat",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Buat",
                                                                            "text" => "BUAT")
                                                                    )),
                                                                array("text" => "Pemberitahuan",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Pemberitahuan",
                                                                            "text" => "PEMBERITAHUAN")
                                                                    )),
                                                                array("text" => "Registrasi",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Registrasi",
                                                                            "text" => "REGISTRASI")
                                                                    ))
                                                            )
                                                        )
                                                    );
                                                    $this->http->httpPost($this->api->getApiReply(), $body);
                                                    break;
                                                }
                                                case "TIDAK": {
                                                    array_pop($arrayAction);
                                                    array_pop($arrayAction);
                                                    array_push($arrayAction,"");
                                                    $this->insertArrayAction($source->getUserId(),$arrayAction);
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                                break;
                            }
                            default: break;
                        }
                        break;
                    }
                    case "POPULER":{
                        switch ($arrayAction[1]) {
                            case "": {
                                if ($webhook->isPostbackEvent()) {
                                    $event = $webhook->getPostbackEvent();
                                    $postback = new Postback();
                                    $postback = $event->getPostback();
                                    $data = $postback->getData();
                                    $obj_data = explode("&", $data);
                                    var_dump($obj_data);
                                    $obj_action = explode("=", $obj_data[0]);
                                    $action = $obj_action[1];
                                    $obj_id = explode("=", $obj_data[1]);
                                    $id = $obj_id[1];
                                    switch ($action) {
                                        case "DETIL": {
                                            $result = $this->Trip_model->getRecordsById($id);
                                            var_dump($result);
                                            $title = $result->{'TITLE_TRIP'};
                                            $desc = $result->{'DESCRIPTION_TRIP'};
                                            $address = $result->{'ADDRESS_DESTINATION'};
                                            $date = date_create($result->{"START_DATE_TRIP"});
                                            $leader = $result->{'ID_LINE'};
                                            $lat = $result->{'LAT_DESTINATION'};
                                            $lng = $result->{'LNG_DESTINATION'};

                                            $body["replyToken"] = $event->getReplyToken();
                                            $body["messages"][0] = array(
                                                "type" => "text",
                                                "text" => "- Judul: " . $title . "\xA- Deskripsi: " . $desc . "\xA- Destinasi: " . $address . "\xA- Tanggal Mulai: " . date_format($date, "d F Y") . "\xA- Ketua: " . $leader
                                            );
                                            $body["messages"][1] = array(
                                                "type" => "location",
                                                "title" => "Destinasi",
                                                "address" => $address,
                                                "latitude" => $lat,
                                                "longitude" => $lng
                                            );
                                            var_dump($body);
                                            $this->http->httpPost($this->api->getApiReply(), $body);
                                            break;
                                        }
                                        case "GABUNG": {
                                            if ($this->Request_model->isNotAvailable($id, $source->getUserId())) {
                                                $body["replyToken"] = $event->getReplyToken();
                                                $resultUser = $this->User_model->getRecordsById($source->getUserId());
                                                if ($resultUser->{"ID_LINE"} == "") {
                                                    $body["messages"][0] = array(
                                                        "type" => "text",
                                                        "text" => "Harap registrasi terlebih dahulu."
                                                    );
                                                    var_dump($body);
                                                    $this->http->httpPost($this->api->getApiReply(), $body);
                                                } else {
                                                    array_pop($arrayAction);
                                                    array_push($arrayAction, "GABUNG");
                                                    array_push($arrayAction, "");
                                                    $this->insertArrayAction($source->getUserId(), $arrayAction);
                                                    $resultUser = $this->User_model->getRecordsById($source->getUserId());
                                                    $lineId = $resultUser->{'ID_LINE'};
                                                    $body["messages"][0] = array(
                                                        "type" => "template",
                                                        "altText" => "confirm gabung",
                                                        "template" => array(
                                                            "type" => "confirm",
                                                            "text" => "Apakah anda ingin bergabung ?",
                                                            "actions" => array(
                                                                array("type" => "postback",
                                                                    "label" => "Ya",
                                                                    "data" => "action=YA&data=" . $id),
                                                                array("type" => "postback",
                                                                    "label" => "Tidak",
                                                                    "data" => "action=TIDAK&data=" . $id)
                                                            )
                                                        )
                                                    );
                                                    var_dump($body);
                                                    $this->http->httpPost($this->api->getApiReply(), $body);
                                                }
                                            } else {
                                                $body["replyToken"] = $event->getReplyToken();
                                                $body["messages"][0] = array(
                                                    "type" => "text",
                                                    "text" => "Anda telah bergabung"
                                                );
                                                var_dump($body);
                                                $this->http->httpPost($this->api->getApiReply(), $body);
                                            }
                                            break;
                                        }
                                        case "BATALGABUNG": {
                                            if (!$this->Request_model->isNotAvailable($id, $source->getUserId())) {
                                                array_pop($arrayAction);
                                                array_push($arrayAction, "BATALGABUNG");
                                                array_push($arrayAction, "");
                                                $this->insertArrayAction($source->getUserId(), $arrayAction);
                                                $stringAction = $this->createStringAction($arrayAction);
                                                $body["replyToken"] = $event->getReplyToken();
                                                $body["messages"][0] = array(
                                                    "type" => "template",
                                                    "altText" => "confirm batal gabung",
                                                    "template" => array(
                                                        "type" => "confirm",
                                                        "text" => "Apakah anda ingin membatalkan permintaan bergabung ?",
                                                        "actions" => array(
                                                            array("type" => "postback",
                                                                "label" => "Batal Gabung",
                                                                "data" => "action=YA&id=" . $id),
                                                            array("type" => "postback",
                                                                "label" => "TIDAK",
                                                                "data" => "action=TIDAK&id=" . $id)
                                                        )
                                                    )
                                                );
                                                var_dump($body);
                                                $this->http->httpPost($this->api->getApiReply(), $body);
                                            } else {
                                                $body["replyToken"] = $event->getReplyToken();
                                                $body["messages"][0] = array(
                                                    "type" => "text",
                                                    "text" => "Anda belum bergabung"
                                                );
                                                var_dump($body);
                                                $this->http->httpPost($this->api->getApiReply(), $body);
                                            }
                                            break;
                                        }
                                        default:
                                            break;
                                    }
                                }
                                break;
                            }
                            case "GABUNG":{
                                switch ($arrayAction[2]) {
                                    case "": {
                                        if($webhook->isPostbackEvent()) {
                                            $event = $webhook->getPostbackEvent();
                                            $postback = new Postback();
                                            $postback = $event->getPostback();
                                            $data = $postback->getData();
                                            $obj_data = explode("&", $data);
                                            var_dump($obj_data);
                                            $obj_action = explode("=", $obj_data[0]);
                                            $action = $obj_action[1];
                                            $obj_data = explode("=", $obj_data[1]);
                                            $id = $obj_data[1];
                                            switch ($action) {
                                                case "YA": {
                                                    $data = array(
                                                        'ACTION_USER' => "",
                                                        'TMP_ACTION_USER' => ""
                                                    );
                                                    $this->User_model->update($source->getUserId(),$data);

                                                    $resultUser = $this->User_model->getRecordsById($source->getUserId());
                                                    $resultTrip = $this->Trip_model->getRecordsById($id);

                                                    $data = array(
                                                        'COUNT_REQUEST' => $resultTrip->{"COUNT_REQUEST"}++
                                                    );
                                                    $this->Trip_model->update($id,$data);

                                                    $body["to"] = array($resultTrip->{"ID_GROUP"});
                                                    $body["messages"][0] = array(
                                                        "type" => "text",
                                                        "text" => "Line ID ".$resultUser->{'ID_LINE'}." ingin bergabung dengan trip ".$resultTrip->{'TITLE_TRIP'}
                                                    );
                                                    var_dump($body);
                                                    $this->http->httpPost($this->api->getApiReply(), $body);

                                                    $data = array(
                                                        'ID_USER' => $source->getUserId(),
                                                        'ID_TRIP' => $id
                                                    );
                                                    $this->Request_model->add($data);

                                                    $body["replyToken"]=$event->getReplyToken();
                                                    $body["messages"][0] = array(
                                                        "type" => "text",
                                                        "text" => "Permintaan gabung anda telah terkirim"
                                                    );
                                                    $body["messages"][1] = array(
                                                        "type" => "template",
                                                        "altText" => "menu utama",
                                                        "template" => array(
                                                            "type" => "carousel",
                                                            "columns" => array(
                                                                array("text" => "Cari",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Cari",
                                                                            "text" => "CARI")
                                                                    )),
                                                                array("text" => "Populer",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Populer",
                                                                            "text" => "POPULER")
                                                                    )),
                                                                array("text" => "Buat",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Buat",
                                                                            "text" => "BUAT")
                                                                    )),
                                                                array("text" => "Pemberitahuan",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Pemberitahuan",
                                                                            "text" => "PEMBERITAHUAN")
                                                                    )),
                                                                array("text" => "Registrasi",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Registrasi",
                                                                            "text" => "REGISTRASI")
                                                                    ))
                                                            )
                                                        )
                                                    );
                                                    var_dump($body);
                                                    $this->http->httpPost($this->api->getApiReply(), $body);
                                                    break;
                                                }
                                                case "TIDAK": {
                                                    array_pop($arrayAction);
                                                    array_pop($arrayAction);
                                                    array_push($arrayAction,"");
                                                    $this->insertArrayAction($source->getUserId(),$arrayAction);
                                                    break;
                                                }
                                            }
                                        }
                                        break;
                                    }
                                    default: break;
                                }
                                break;
                            }
                            case "BATALGABUNG":{
                                switch ($arrayAction[2]) {
                                    case "": {
                                        if($webhook->isPostbackEvent()) {
                                            $event = $webhook->getPostbackEvent();
                                            $postback = new Postback();
                                            $postback = $event->getPostback();
                                            $data = $postback->getData();
                                            $obj_data = explode("&", $data);
                                            var_dump($obj_data);
                                            $obj_action = explode("=", $obj_data[0]);
                                            $action = $obj_action[1];
                                            $obj_id = explode("=", $obj_data[1]);
                                            $id = $obj_id[1];
                                            switch ($action) {
                                                case "YA": {
                                                    $data = array(
                                                        'ACTION_USER' => "",
                                                        'TMP_ACTION_USER' => ""
                                                    );
                                                    $this->User_model->update($source->getUserId(),$data);

                                                    $resultUser = $this->User_model->getRecordsById($source->getUserId());
                                                    $resultTrip = $this->Trip_model->getRecordsById($id);
                                                    $resultRequest = $this->Request_model->getRecordsByUserIdTripId($source->getUserId(),$id);
                                                    $body["to"] = array($resultTrip->{"ID_GROUP"});
                                                    $body["messages"][0] = array(
                                                        "type" => "text",
                                                        "text" => "Line ID ".$resultUser->{'ID_LINE'}." ingin membatalkan bergabung dengan trip ".$resultTrip->{'TITLE_TRIP'}
                                                    );
                                                    var_dump($body);
                                                    $this->http->httpPost($this->api->getApiReply(), $body);

                                                    $this->Request_model->delete($resultRequest->{"ID_REQUEST"});
                                                    $body["replyToken"]=$event->getReplyToken();
                                                    $body["messages"][0] = array(
                                                        "type" => "text",
                                                        "text" => "Membatalkan ikut gabung"
                                                    );
                                                    $body["messages"][1] = array(
                                                        "type" => "template",
                                                        "altText" => "menu utama",
                                                        "template" => array(
                                                            "type" => "carousel",
                                                            "columns" => array(
                                                                array("text" => "Cari",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Cari",
                                                                            "text" => "CARI")
                                                                    )),
                                                                array("text" => "Populer",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Populer",
                                                                            "text" => "POPULER")
                                                                    )),
                                                                array("text" => "Buat",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Buat",
                                                                            "text" => "BUAT")
                                                                    )),
                                                                array("text" => "Pemberitahuan",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Pemberitahuan",
                                                                            "text" => "PEMBERITAHUAN")
                                                                    )),
                                                                array("text" => "Registrasi",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Registrasi",
                                                                            "text" => "REGISTRASI")
                                                                    ))
                                                            )
                                                        )
                                                    );
                                                    $this->http->httpPost($this->api->getApiReply(), $body);
                                                    break;
                                                }
                                                case "TIDAK": {
                                                    array_pop($arrayAction);
                                                    array_pop($arrayAction);
                                                    array_push($arrayAction,"");
                                                    $this->insertArrayAction($source->getUserId(),$arrayAction);
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                                break;
                            }
                            default: break;
                        }
                    }
                    case "BUAT":{
                        break;
                    }
                    case "PEMBERITAHUAN":{
                        break;
                    }
                    case "REGISTRASI":{
                        switch ($arrayAction[1]) {
                            case "":{
                                if($webhook->isPostbackEvent()) {
                                    $event = $webhook->getPostbackEvent();
                                    $postback = new Postback();
                                    $postback = $event->getPostback();
                                    $data = $postback->getData();
                                    $obj_action = explode("=", $data);
                                    $action = $obj_action[1];
                                    switch ($action) {
                                        case "YA": {
                                            array_pop($arrayAction);
                                            array_push($arrayAction, "INPUTLINEID");
                                            array_push($arrayAction, "");
                                            $this->insertArrayAction($source->getUserId(), $arrayAction);
                                            $body["replyToken"]=$event->getReplyToken();
                                            $body["messages"][0] = array(
                                                "type" => "text",
                                                "text" => "Masukan Line id anda"
                                            );
                                            var_dump($body);
                                            $this->http->httpPost($this->api->getApiReply(), $body);
                                            break;
                                        }
                                        case "TIDAK":{
                                            $data = array(
                                                'ACTION_USER' => "",
                                                'TMP_ACTION_USER' => ""
                                            );
                                            $this->User_model->update($source->getUserId(),$data);
                                        }
                                    }
                                }
                                break;
                            }
                            case "INPUTLINEID": {
                                switch ($arrayAction[2]) {
                                    case "":{
                                        if($webhook->isMessageText()) {
                                            $event = $text = $webhook->getMessageText();
                                            $message = $event->getMessage();
                                            $text = $message->getText();
                                            var_dump($text);
                                            array_pop($arrayAction);
                                            array_pop($arrayAction);
                                            array_push($arrayAction,"CONFIRMLINEID");
                                            array_push($arrayAction,"");
                                            $this->insertArrayAction($source->getUserId(),$arrayAction);
                                            $body["replyToken"] = $event->getReplyToken();
                                            $body["messages"][0] = array(
                                                "type" => "template",
                                                "altText" => "confirm line id",
                                                "template" => array(
                                                    "type" => "confirm",
                                                    "text" => "Registrasi Line id " . $text . " ?",
                                                    "actions" => array(
                                                        array("type" => "postback",
                                                            "label" => "Ya",
                                                            "data" => "action=YA&data=" . $text),
                                                        array("type" => "postback",
                                                            "label" => "Tidak",
                                                            "data" => "action=TIDAK&data=" . $text)
                                                    )
                                                )
                                            );
                                            var_dump($body);
                                            $this->http->httpPost($this->api->getApiReply(), $body);
                                        }
                                        break;
                                    }
                                    default: break;
                                }
                                break;
                            }
                            case "CONFIRMLINEID":{
                                switch ($arrayAction[2]) {
                                    case "": {
                                        if($webhook->isPostbackEvent()) {
                                            $event = $webhook->getPostbackEvent();
                                            $postback = new Postback();
                                            $postback = $event->getPostback();
                                            $data = $postback->getData();
                                            $obj_data = explode("&", $data);
                                            var_dump($obj_data);
                                            $obj_action = explode("=", $obj_data[0]);
                                            $action = $obj_action[1];
                                            $obj_data = explode("=", $obj_data[1]);
                                            $lineId = $obj_data[1];
                                            switch ($action) {
                                                case "YA": {
                                                    $data = array(
                                                        'ACTION_USER' => "",
                                                        'TMP_ACTION_USER' => "",
                                                        'ID_LINE' => $lineId
                                                    );
                                                    $this->User_model->update($source->getUserId(),$data);

                                                    $body["replyToken"]=$event->getReplyToken();
                                                    $body["messages"][0] = array(
                                                        "type" => "text",
                                                        "text" => "Line id ".$lineId." telah terdaftar."
                                                    );
                                                    $body["messages"][1] = array(
                                                        "type" => "template",
                                                        "altText" => "menu utama",
                                                        "template" => array(
                                                            "type" => "carousel",
                                                            "columns" => array(
                                                                array("text" => "Cari",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Cari",
                                                                            "text" => "CARI")
                                                                    )),
                                                                array("text" => "Populer",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Populer",
                                                                            "text" => "POPULER")
                                                                    )),
                                                                array("text" => "Buat",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Buat",
                                                                            "text" => "BUAT")
                                                                    )),
                                                                array("text" => "Pemberitahuan",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Pemberitahuan",
                                                                            "text" => "PEMBERITAHUAN")
                                                                    )),
                                                                array("text" => "Registrasi",
                                                                    "actions" => array(
                                                                        array("type" => "message",
                                                                            "label" => "Registrasi",
                                                                            "text" => "REGISTRASI")
                                                                    ))
                                                            )
                                                        )
                                                    );
                                                    var_dump($body);
                                                    $this->http->httpPost($this->api->getApiReply(), $body);
                                                    break;
                                                }
                                                case "TIDAK": {
                                                    array_pop($arrayAction);
                                                    array_pop($arrayAction);
                                                    array_push($arrayAction,"INPUTLINEID");
                                                    array_push($arrayAction,"");
                                                    $this->insertArrayAction($source->getUserId(),$arrayAction);
                                                    $body["replyToken"] = $event->getReplyToken();
                                                    $body["messages"][0] = array(
                                                        "type" => "text",
                                                        "text" => "Masukan Line id anda"
                                                    );
                                                    var_dump($body);
                                                    $this->http->httpPost($this->api->getApiReply(), $body);
                                                    break;
                                                }
                                            }
                                        }
                                        break;
                                    }
                                }
                                break;
                            }
                        }
                        break;
                    }
                    case "MENU":{
                        switch ($arrayAction[1]) {
                            case "BACK":{
                                if($webhook->isMessageText()) {
                                    $event = $text = $webhook->getMessageText();
                                    $message = $event->getMessage();
                                    $text = $message->getText();
                                    $text = strtoupper($text);
                                    switch ($text){
                                        case "YA":{
                                            $data = array(
                                                'ACTION_USER' => "",
                                                'TMP_ACTION_USER' => ""
                                            );
                                            $this->User_model->update($source->getUserId(),$data);
                                            $body["replyToken"]=$event->getReplyToken();
                                            $body["messages"][0] = array(
                                                "type" => "template",
                                                "altText" => "menu utama",
                                                "template" => array(
                                                    "type" => "carousel",
                                                    "columns" => array(
                                                        array("text" => "Cari",
                                                            "actions" => array(
                                                                array("type" => "message",
                                                                    "label" => "Cari",
                                                                    "text" => "CARI")
                                                            )),
                                                        array("text" => "Populer",
                                                            "actions" => array(
                                                                array("type" => "message",
                                                                    "label" => "Populer",
                                                                    "text" => "POPULER")
                                                            )),
                                                        array("text" => "Buat",
                                                            "actions" => array(
                                                                array("type" => "message",
                                                                    "label" => "Buat",
                                                                    "text" => "BUAT")
                                                            )),
                                                        array("text" => "Pemberitahuan",
                                                            "actions" => array(
                                                                array("type" => "message",
                                                                    "label" => "Pemberitahuan",
                                                                    "text" => "PEMBERITAHUAN")
                                                            )),
                                                        array("text" => "Registrasi",
                                                            "actions" => array(
                                                                array("type" => "message",
                                                                    "label" => "Registrasi",
                                                                    "text" => "REGISTRASI")
                                                            ))
                                                    )
                                                )
                                            );
                                            var_dump($body);
                                            $this->http->httpPost($this->api->getApiReply(), $body);
                                            break;
                                        }
                                        case "TIDAK":{
                                            $result = $this->User_model->getRecordsById($source->getUserId());
                                            var_dump($result);
                                            $data = array(
                                                'ACTION_USER' => $result->{"TMP_ACTION_USER"},
                                                'TMP_ACTION_USER' => ""
                                            );
                                            $this->User_model->update($source->getUserId(),$data);
                                            break;
                                        }
                                        default: break;
                                    }
                                }
                                break;
                            }
                            default: break;
                        }
                        break;
                    }
                    default: break;
                }
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