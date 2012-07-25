<?php

namespace Wordcraft;

class Wordcraft_Comment extends Wordcraft_Core {

    private $wc;

    protected $data_types = array(
        "comment_id" => FILTER_SANITIZE_NUMBER_INT,
        "post_id" => FILTER_SANITIZE_NUMBER_INT,
        "name" => FILTER_SANITIZE_STRING,
        "email" => FILTER_VALIDATE_EMAIL,
        "url" => FILTER_VALIDATE_URL,
        "comment_date" => array(
            'filter'  => FILTER_CALLBACK,
            'options' => array('callback' => array("WordCraft_Data", "validate_time"))
        ),
        "comment" => FILTER_UNSAFE_RAW,
        "ip_address" => FILTER_VALIDATE_IP,
        "status" => FILTER_SANITIZE_STRING,
        "linkback" => FILTER_VALIDATE_BOOLEAN
    );

    public function __construct() {
        $this->wc = Wordcraft::init();
    }

}

