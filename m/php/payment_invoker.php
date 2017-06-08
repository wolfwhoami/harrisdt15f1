<?php
require_once ('Curl/ArrayUtil.php');
require_once ('Curl/CaseInsensitiveArray.php');
require_once ('Curl/Curl.php');
require_once ('Curl/MultiCurl.php');
use \Curl\Curl;
function gotcurl($url)
{
    $curl = new Curl();
    $curl->get($url);
    if ($curl->error) {
        return $mess = [
            'code' => $curl->errorCode.'00',
            'errMsg' => $curl->errorMessage
        ];
    } else {
        return $curl->response;
    }
}

function aa($url ,$value=0)
{
    $result = bb($url);
    if ($result==10) {
        echo "done<br>";
    }
    else{
        $value++;
        if ($value<10) {
            echo "now v is: ".$value."<br>";
            aa($url,$value);
        }
        else{
            echo "fail";
        }
    }
}