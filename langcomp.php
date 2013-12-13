<html>
<head><title>语言比较</title></head>
<body>
<?php

class LangDiff
{
    public $src_only;
    public $dst_only;
    public $same;
    public $diff;
    
    public function LangDiff()
    {
        $this->src_only=array();
        $this->dst_only=array();
        $this->same=array();
        $this->diff=array();
    }
    
    public function stack($lang_diff)
    {
        /*
        echo "合并(".
            count($this->src_only)."+".count($lang_diff->src_only).",".
            count($this->dst_only)."+".count($lang_diff->dst_only).",".
            count($this->same)    ."+".count($lang_diff->same)    .",".
            count($this->diff)    ."+".count($lang_diff->diff)    .   
        ")<br/>";
        */
        $this->src_only = array_merge($this->src_only, $lang_diff->src_only);
        $this->dst_only = array_merge($this->dst_only, $lang_diff->dst_only);
        $this->same     = array_merge($this->same,     $lang_diff->same);
        $this->diff     = array_merge($this->diff,     $lang_diff->diff);
        /*
        echo "合并完成值(".
            count($this->src_only).",".
            count($this->dst_only).",".
            count($this->same)    .",".
            count($this->diff)    .
        ")<br/>";
        echo "-------------------<br/>";
        */
    }
}

function keyAmount($arr)
{
    $amount = 0;
    if (is_array($arr)==false) return 1;
    foreach ($arr as $key => $value)
    {
        if ( is_array($arr[$key]) )
        {
            $amount+=keyAmount($arr[$key]);
        }
        else
        {
            $amount++;
        }
    }
    return $amount;
}

function keyList($arr, $common_name, &$list)
{
    if (is_array($arr)==false) return;
    foreach ($arr as $key => $value)
    {
        if ( is_array($arr[$key]) )
        {
            keyList( $arr[$key], $common_name."[".$key."]", $list );
        }
        else
        {
            $list[]=$common_name."[".$key."]";
        }
    }
}

function compArray($src, $dst, $common_name="")
{
    $lang_diff = new LangDiff();
    //检测源语言中独有的键
    foreach ($src as $key => $value)
    {
        if ( !array_key_exists($key, $dst) )
        {
            $lang_diff->src_only[] = $common_name."[".$key."]";
            continue;
        }
        
        if ( is_array($src[$key]) && is_array($dst[$key]) )
        {
            $lang_diff->stack( compArray($src[$key], $dst[$key], $common_name."[".$key."]") );
        }
        else if ( is_array($src[$key])==true && is_array($dst[$key])==false )
        {
            
        }
        else if ( is_array($src[$key])==false && is_array($dst[$key])==true )
        {
            
        }
        else
        {
            if ( $src[$key] == $dst[$key] )
            {
                $lang_diff->same[] = $common_name."[".$key."]";
            }
            else
            {
                $lang_diff->diff[] = $common_name."[".$key."]";
            }
        }
    }
    //检测目标语言中独有的键
    foreach ($dst as $key => $value)
    {
        if ( !array_key_exists($key, $src) )
        {
            //$lang_diff->dst_only[] = $common_name."[".$key."]";
            $addition_keys = array();
            keyList( $dst[$key], $common_name."[".$key."]", $addition_keys );
            array_merge($lang_diff->dst_only, $addition_keys);
        }
    }
    
    return $lang_diff;
}

function comp($src_lang, $dst_lang)
{
    $pages=array(); //页面名称
    $pages['admin']="ADMIN";
    $pages['banner']="BANNER";
    $pages['faq']="FAQ";
    $pages['fleet']="FLEET";
    $pages['ingame']="INGAME";
    $pages['install']="INSTALL";
    $pages['l18n']="L18N";
    $pages['public']="PUBLIC";
    $pages['tech']="TECH";
    $output="<table>";
    $output.="<tr><td>文件名</td><td>已翻译</td><td>总条目数</td><td>完成进度</td></tr>";
    $total_diff = 0;
    $total_amount = 0;
    
    foreach ($pages as $pagekey => $pagename)
    {
        unset($LNG);
        $LNG = array();
        
        unset($SRC_LNG);
        $SRC_LNG=array();
        include("language/$src_lang/$pagename.php");
        foreach ($LNG as $key => $value){
            $SRC_LNG[$key]=$value;
        }
        
        unset($DST_LNG);
        $DST_LNG=array();
        include("language/$dst_lang/$pagename.php");
        foreach ($LNG as $key => $value){
            $DST_LNG[$key]=$value;
        }
        
        $diff = compArray($SRC_LNG, $DST_LNG);
        $srcKeyList = array();
        $dstKeyList = array();
        keyList($SRC_LNG, "", $srcKeyList);
        keyList($DST_LNG, "", $dstKeyList);
        //echo "$pagename.php"."";
        
        //echo "总词数：".keyAmount($SRC_LNG)." : ".keyAmount($DST_LNG)."<br/>";
        //echo "总词数：".count($srcKeyList)." : ".count($dstKeyList)."<br/>";
        //echo $src_lang."特有：".count($diff->src_only)."<br/>";
        //echo $dst_lang."特有：".count($diff->dst_only)."<br/>";
        //echo "相　同：".count($diff->same)."<br/>";
        //echo "不　同：".count($diff->diff)."<br/><br/>";
        $diffcounter = count($diff->diff);
        $amount = count($srcKeyList);
        $percentage = (int)( $diffcounter * 10000 / $amount );
        $percentage= ($percentage/100)."%";
        //echo "完成进度： ".count($diff->diff)."/".count($srcKeyList)."(".$percentage."%)"."<br/>";
        $output.="<tr><td>".$pagename.".php</td><td>".$diffcounter."</td><td>".$amount."</td><td>".$percentage."</td></tr>";
        $total_diff += $diffcounter;
        $total_amount += $amount;
    }
    $percentage = (int)( $total_diff * 10000 / $total_amount );
    $percentage= ($percentage/100)."%";
    $output.="<tr><td>总量</td><td>".$total_diff."</td><td>".$total_amount."</td><td>".$percentage."</td></tr>";
    $output.="</table>";
    return $output;
}

//以下是页面部分
$src_lang=isset($_POST['srclang'])?$_POST['srclang']:"";
$dst_lang=isset($_POST['dstlang'])?$_POST['dstlang']:"";

if ($src_lang=="" || $dst_lang=="") { ?>
<form action="langcomp.php" method="post">
<input type="text" name="srclang" value="en" />
<input type="text" name="dstlang" value="cn" />
<input type="submit" />
</form>
<?php
}
else { ?>
<!--
<table border=1>
<tr>
<td></td>
<td>源语言<?php echo $src_lang; ?></td>
<td>目标语言<?php echo $dst_lang; ?></td>
</tr>
</table>
-->
<?php 
	echo comp($src_lang, $dst_lang);
}

?>
</body>
</html>