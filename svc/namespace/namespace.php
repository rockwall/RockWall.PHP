<?php

    class svc_namespace {

        private $var;
        private $inc = 0;
        private static $incStatic = 0;

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
            $this->inc++;

            $o = new stdClass;
            $o->date = db()->query('SELECT NOW() AS date')->fetchObject()->date;
            $o->cmd = $cmd;
            $o->directory = substr(__DIR__, strlen(config::dir()->svc));
            $o->file = basename(__FILE__);
            $o->class = __CLASS__;
            $o->method = __FUNCTION__;
            $o->args = $args;
            $o->var = $this->var;
            $o->increment = $this->inc;

            return new response($o);
        }

        public static
        function staticMethod (array $args, $cmd) {
            self::$incStatic++;

            $o = new stdClass;
            $o->cmd = $cmd;
            $o->directory = substr(__DIR__, strlen(config::dir()->svc));
            $o->file = basename(__FILE__);
            $o->class = __CLASS__;
            $o->method = __FUNCTION__;
            $o->args = $args;
            $o->incrementStatic = self::$incStatic;

            return new response($o);
        }

    }