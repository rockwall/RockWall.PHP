<?php

class Buffer {

    private $raw = null;

    function start () { ob_start(); }

    function stop () {
        $chunk = ob_get_contents();
        ob_end_clean();
        $this->raw .= $chunk;
        return $chunk;
    }

    function get () { return $this->raw; }

    function clear () { $this->raw = null; }

}

class tools {

    public static
    function parseData($data) {
        if (is_array($data)) {
            foreach($data as $k => $v) $data[$k] = self::parseData($v);
            return $data;
        }
        if (is_object($data)) {
            foreach($data as $k => $v) $data-> $k = self::parseData($v);
            return $data;
        }
        $o = array("true" => true, "false" => false, "null" => null, "undefined" => null, "NaN" => null);
        foreach($o as $k => $v) {
            if ($data == $k) return $v;
        }
        if (is_numeric($data)) {
            if (preg_match('/\./', $data)) return floatval($data);
            else return intval($data);
        }
        return $data;
    }

}