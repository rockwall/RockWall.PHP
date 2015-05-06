<?php

class response {

    function response($data = null, $error = null, $code = null) {
        if (is_string($error) || is_numeric($error) || is_bool($error)) {
            $this->error = $error;
            if (is_numeric($code)) $this->code = $code;
            return;
        }
        $this->data = $data;
    }

    public function push (response $response) {
        if (!isset($this->data) || !is_array($this->data)) $this->data = array();
        $idx = count($this->data);
        array_push($this->data, $response);
        if (isset($response->error)) {
            if (!isset($this->errors) || !is_object($this->errors)) $this->errors = new stdClass;
            $this->errors->{$idx} = $response;
            if (!isset($this->error)) {
                $this->error = $response->error;
                if (isset($response->code)) $this->code = $response->code;
            }
        }
        return $this;
    }

    public static
    function internalError () {
        return new response(null, 'Internal error', 500);
    }

    public static
    function invalidRequest () {
        return new response(null, 'Invalid request', 400);
    }

    public static
    function invalidResponse () {
        return new response(null, 'Invalid response', 444);
    }

    public static
    function notFound () {
        return new response(null, 'Not found', 404);
    }

    function __toString() {
        return json_encode($this);
    }

}