<?php

class Point {

    private $x;
    private $y;

    function __construct($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }

    function getX() {
        return $this->x;
    }

    function getY() {
        return $this->y;
    }

    /*function isInPosition($x, $y) {
        return $x == $this->x && $y == $this->y;
    }*/

    function __toString() {
        return '(' . $this->x . ':' . $this->y . ')';
    }

}