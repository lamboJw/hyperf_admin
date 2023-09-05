<?php


namespace App\Event;


class RequestDone {

    public $res;

    /**
     * RequestDone constructor.
     * @param $res
     */
    public function __construct($res) {
        $this->res = $res;
    }


}