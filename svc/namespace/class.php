<?php

    class svc_namespace_class {

        private $var;

        public
        function __construct () {
            $buffer = new Buffer;

            $buffer->start();
            echo "Hello";
            $buffer->stop();

            echo "Out of buffer range";

            $buffer->start();
            echo " World!";
            $buffer->stop();

            $this->var = $buffer->get();
        }

        public
        function method (array $args, $cmd) {
            $o = new stdClass;
            $o->date = db()->query('SELECT NOW() AS date')->fetchObject()->date;
            $o->cmd = $cmd;
            $o->directory = substr(__DIR__, strlen(config::dir()->svc));
            $o->file = basename(__FILE__);
            $o->class = __CLASS__;
            $o->method = __FUNCTION__;
            $o->args = $args;
            $o->var = $this->var;

            return new response($o);
        }

    }