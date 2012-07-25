<?php

namespace Wordcraft;

class Wordcraft_User extends Wordcraft_Data {

    private $wc;

    protected $data_types = array(
        "user_id" => FILTER_SANITIZE_NUMBER_INT,
        "user_name" => FILTER_SANITIZE_STRING,
        "first_name" => FILTER_SANITIZE_STRING,
        "last_name" => FILTER_SANITIZE_STRING,
        "email" => FILTER_VALIDATE_EMAIL,
        "about" => FILTER_UNSAFE_RAW,
        "password" => FILTER_UNSAFE_RAW
    );

    public function __construct() {
        $this->wc = Wordcraft::init();
    }

}

?>
