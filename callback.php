<?php
	file_put_contents(date('d-m-y').".log",
        date('H:i:s d.m.y')." ".file_get_contents("php://input")."GET ->".
        print_r($_GET,1)." POST ->". print_r($_POST,1)."\n",FILE_APPEND);
?>