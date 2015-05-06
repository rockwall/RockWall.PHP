<?php

class config {

    public static
    function db () { //optional
        $o = new stdClass;
        $o->host = 'Your DB Host';
        $o->user = 'Your DB User';
        $o->pasw = 'Your DB Password';
        $o->name = 'Your DB Name';
        return $o;
    }

    public static
    function dir () {
        $o = new stdClass;
        $o->svc = __DIR__.'/svc/'; // Enter your path to directory where located your services
        return $o;
    }

    public static
    function main () {
        $o = new stdClass;
        $o->prefix = 'svc'; // The classname prefix. Format: {prefix}_{namespace}[_{class}]
        $o->defaultModifier = '&'; // @ : The new instance | & : Singleton | ! : Rewrite Singleton instance
        $o->strictModifier = false; // Use default modifier for all or not
        return $o;
    }

}