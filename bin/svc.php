<?php

class svc {

    private static $_init;

    public static
    function init () {
        if (self::$_init) return;
        self::$_init = true;
        if (!class_exists('config')) exit(new response(null, 'Not configured yet', 500));
        @register_shutdown_function("svc::shutdownHandler");
        exit(Process::request());
    }

    public static
    function shutdownHandler () {
        $errors = array(E_ERROR, E_WARNING, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING);
        $error = error_get_last();
        if ($error != null && in_array($error['type'], $errors, true)) {
            error_log("The system was halted because an error occurred. code: {$error['type']}, message: {$error['message']}, file: {$error['file']}, line: {$error['line']}");
            @ob_end_clean();
            header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
            exit(response::internalError());
        }
    }
}