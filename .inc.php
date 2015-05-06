<?php
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    require_once(dirname(__FILE__).'/bin/response.php');
    require_once(dirname(__FILE__).'/bin/tools.php');
    require_once(dirname(__FILE__).'/bin/db.php');
    require_once(dirname(__FILE__).'/bin/process.php');
    require_once(dirname(__FILE__).'/bin/svc.php');