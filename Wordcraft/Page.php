<?php

namespace Wordcraft;

class Wordcraft_Page extends Wordcraft_Core {

    private $wc;

    protected $data_types = array(
        "page_id" => FILTER_SANITIZE_NUMBER_INT,
        "title" => FILTER_SANITIZE_STRING,
        "body" => FILTER_UNSAFE_RAW,
        "nav_label" => FILTER_SANITIZE_STRING,
        "uri" => FILTER_SANITIZE_STRING
    );

    public function __construct() {
        $this->wc = Wordcraft::init();
    }

}


?>
