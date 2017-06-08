<style>
    table{
        width: 600px;
        border-left: 1px solid #444;
        border-bottom:  1px solid #444;
        border-spacing: 0;
    }
    th,td{
        border-right: 1px solid #444;border-top:  1px solid #444;
        height: 30px;
        line-height: 30px;
    }
    td{padding-left: 10px;}
</style>

<?php
header ( "Content-Type: text/html; charset=utf-8" );
require_once("config.php");
$beh = json_decode($clientA->getBehavior(),TRUE);
usort($objCode, "mys");
echo "<h2>发布函数说明：</h2>";
echo "<table><tr><th>Function</th><th>Notes</th></tr>";
foreach ($beh as $key => $v) {
    echo "<tr><td>{$key}</td><td>{$v}</td></tr>";
}
echo "</table>";
echo "<br />*******************************<br />";
echo "<h2>返回数据说明：</h2>";
echo "code: 状态码，接口调用转态，具体含义见下面状态码表。<br />";
echo "info: 返回数据，一般为查询数据库的数据，错误转态时是error=>错误信息，sql=>最后的sql语句。<br />";
echo "<h2>返回转态码说明：</h2>";
echo "<table><tr><th>Code</th><th>Message</th></tr>";
foreach ($objCode as $key => $v) {
    echo "<tr><td>{$v->code}</td><td>{$v->message}</td></tr>";
}
echo "</table>";
function mys($a,$b){
    if($a['code'] > $b['code']){
        return 1; 
    }else{
        return 0;
    }
}
//print_r(json_encode($objCode));

?>
