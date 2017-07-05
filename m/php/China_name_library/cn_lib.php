<?php
include 'name.php';
    /*rndChinaName.class.php*/
Class ChinaName
{
    private $arrXing,$numbXing;
    private $arrMing,$numbMing;
    function ChinaName()
    {
        $this->getXingList();
        $this->getMingList();
    }
    /* 获取姓列表 */
    private function getXingList()
    {
        global  $last_name;
        $this->arrXing=$last_name;
        $this->numbXing = count($this->arrXing); //姓总数
    }
    /* 获取名列表 */
    private function getMingList()
    {
        global  $first_name;
        $this->arrMing=$first_name;
        //名总数
        $this->numbMing = count($this->arrMing);
    }
    // 获取姓
private function getXing()
{
  // mt_rand() 比rand()方法快四倍，而且生成的随机数比rand()生成的伪随机数无规律。
return $this->arrXing[mt_rand(0,$this->numbXing-1)];
}
// 获取名字
private function getMing()
{
    //if(mt_rand(2,3)>2)
//    {
//        $name =   $this->arrMing[mt_rand(0,$this->numbMing-1)].$this->arrMing[mt_rand(0,$this->numbMing-1)];
//    }
//    else{
//        $name= $this->arrMing[mt_rand(0,$this->numbMing-1)];
//    }
    return $this->arrMing[mt_rand(0,$this->numbMing-1)];
}
private function get_total_name()
{
    global $chinese_name_lib_path;
    $files = glob($chinese_name_lib_path."/*.txt");
    $file = array_rand($files);
    $now_selected_file = $files[$file];
    $data = file($now_selected_file, FILE_IGNORE_NEW_LINES);
    $max = sizeof($data);
    return $data[mt_rand(0,$max-1)];
}
  // 获取名字
  public function getName($type=0)
  {
    $name = '' ;
    switch($type)
    {
        case 1:    //2字
            $name = $this->getXing().$this->getMing();
            break;
        case 2:    //随机2、3个字
            $name = $this->getXing().$this->getMing();
            if(mt_rand(0,100)>50)$name .= $this->getMing();
            break;
        case 3: //只取姓
            $name = $this->getXing();
            break;
        case 4: //只取名
            $name = $this->getMing();
            break;
        case 5: //从sougou库读取
            $name = $this->get_total_name();
            break;
        case 0:
        default: //默认情况 1姓+2名
            $name = $this->getXing().$this->getMing().$this->getMing();
    }
    return $name;
  }
}
?>