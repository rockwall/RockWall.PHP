<?php

class Process {

    public $cmd, $args; // Constructor arguments
    public $filename, $namespace, $class, $method; // Result of $cmd parsing
    public $result; // Execution result (instance of "response")

    // SVC request processing
    public static
    function request () {
        $request = isset($_REQUEST['request']) ? $_REQUEST['request'] : array();
        $response = new response;
        foreach($request as $o) {
            $cmd = isset($o['cmd']) && is_string($o['cmd']) ? $o['cmd'] : '';
            $args = isset($o['args']) && is_array($o['args']) ? $o['args'] : array();
            $process = new Process($cmd, $args);
            $response->push($process->result);
        }
        return $response;
    }

    public
    function Process ( $cmd , $args=array() ) {
        $this->cmd  = is_string($cmd) ? $cmd : '';
        $this->args = is_array($args) ? tools::parseData($args) : array();

        $this->parse();
        $o = $this->safe('validate');
        $this->result = ($o instanceof response) ? $o : $this->safe('execute');
        $this->result = ($this->result instanceof response) ? $this->result : response::invalidResponse();
    }

    private
    function parse () {
        $cmd = preg_replace('/^\/+|\/+$/', '', $this->cmd);
        if (preg_match('/^([@&!])/i', $cmd, $mod)) {
            $cmd = substr($cmd, 1);
            $this->modifier = $mod[1];
        }
        if (!preg_match('/^([a-z0-9]+)\/([a-z0-9]+)\/?([a-z0-9]+)?$/i', $cmd, $list)) return;

        if (count($list) == 3) list(, $class, $method) = $list;
        else list(, $namespace, $class, $method) = $list;

        $this->filename = (isset($namespace) ? $namespace : $class) . DIRECTORY_SEPARATOR . $class . '.php';
        $this->class = config::main()->prefix . '_' . (isset($namespace) ? $namespace . '_' : '') . $class;
        $this->method = $method;
    }

    private
    function safe ($name) {
        ob_start();
        $o;
        try { $o = $this->{$name}(); }
        catch (Error $e) { $o = response::internalError(); }
        catch (Exception $e) { $o = response::internalError(); }
        catch (ErrorException $e) { $o = response::internalError(); }
        catch (BadFunctionCallException $e) { $o = response::internalError(); }
        catch (BadMethodCallException $e) { $o = response::internalError(); }
        catch (DomainException $e) { $o = response::internalError(); }
        catch (InvalidArgumentException $e) { $o = response::internalError(); }
        catch (LengthException $e) { $o = response::internalError(); }
        catch (LogicException $e) { $o = response::internalError(); }
        catch (OutOfBoundsException $e) { $o = response::internalError(); }
        catch (OutOfRangeException $e) { $o = response::internalError(); }
        catch (OverflowException $e) { $o = response::internalError(); }
        catch (RangeException $e) { $o = response::internalError(); }
        catch (ReflectionException $e) { $o = response::internalError(); }
        catch (RuntimeException $e) { $o = response::internalError(); }
        catch (UnderflowException $e) { $o = response::internalError(); }
        catch (UnexpectedValueException $e) { $o = response::internalError(); }
        ob_end_clean();
        return $o;
    }

    private
    function validate () {
        if (empty($this->cmd)) return response::invalidRequest();
        if (!isset($this->filename, $this->class, $this->method)) return response::invalidRequest();

        $path = config::dir()->svc . $this->filename;
        if (!is_readable($path)) return response::notFound();
        @include_once($path);
        if (!class_exists($this->class) || !method_exists($this->class, $this->method)) return response::notFound();
    }

    private
    function execute () {
        @include_once(config::dir()->svc . $this->filename);

        // Makes static call of method if the method is static
        if ((new ReflectionMethod($this->class, $this->method))->isStatic()) {
            $result = call_user_func(
                array($this->class, $this->method),
                $this->args,
                $this->cmd
            );
        }
        // Makes method call by external modifiers
        else {
            $class = $this->instance();
            if ($class) $result = $class->{$this->method}($this->args, $this->cmd);
        }

        return (isset($result) && $result instanceof response) ? $result : response::invalidResponse();
    }

    private
    function modifier () {
        if (config::main()->strictModifier) return config::main()->defaultModifier;
        return empty($this->modifier) ? config::main()->defaultModifier : $this->modifier;
    }

    private static $instances = array();

    private
    function instance () {
        /**
         * Command prefixes:
         * @ : The new instance
         * & : Singleton
         * ! : Rewrite Singleton instance
         */
        $instance = null; // If static call
        $modifier = $this->modifier();
        if ($modifier === '@' || $modifier === '!' || !isset(self::$instances[$this->class])) {
            $instance = new $this->class();
        }
        if ($modifier === '!' || !isset(self::$instances[$this->class])) {
            self::$instances[$this->class] = $instance;
        }
        if ($modifier === '&') $instance = self::$instances[$this->class];
        return $instance;
    }

}