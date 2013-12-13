<html>
<head><title>语言比较</title></head>
<body>
<?php
	class PageInfo{
		public $name;
		public $lack;
		public $unused;
		public $same;
		public $changed;
		
		public function PageInfo( $name ){
			$this->name=$name;
			$this->lack=array(); //对比源语言，目标语言缺少的
			$this->unused=array(); //对比源语言，目标语言多出的
			$this->same=array(); //相同的词条
			$this->changed=array(); //发生改动的词条
			echo '开始比较'.$name."<br/>";
		}
		
		public function report(){
            $rep="";
			//$rep=$this->name."<br/>";
			$rep.="缺少".count($this->lack)."<br/>";
			$rep.="多余".count($this->unused)."<br/>";
			$rep.="未翻译".count($this->same)."<br/>";
			$rep.="已翻译".count($this->changed)."<br/>";
			return $rep;
		}
	}

	//$src_lang="";
	//$dst_lang="";
	
	//function compFile($)
	
	function comp($src, $dst){
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
        
		foreach ($pages as $pagekey => $pagename){
			$info = new PageInfo($pagekey);
			unset($LNG);
			$LNG=array();
			unset($SLNG);
			$SLNG=array();
			include("language/$src/$pagename.php");
			foreach ($LNG as $key => $value){
				$SLNG[$key]=$value;
			}
			unset($LNG);
			$LNG=array();
			include("language/$dst/$pagename.php");
			//检查缺少
			foreach ($SLNG as $key => $value){
				if ( isset( $LNG[ $key ] ) == false ){ //如果目标语言没有设置这个值
					$info->lack[] = $key;
				}
			}
			//检查多出、相同、改动
			foreach ($LNG as $key => $value){
				if ( isset( $SLNG[ $key ] ) == false ){ //如果源语言没有设置这个值
					$info->unused[] = $key;
				}
				else{ //现在检查相同
					if ( $SLNG[ $key ] != $LNG[ $key ] ){ //如果不同，代表可能已经翻译
						$info->changed[] = $key;
					}
					else{
						$info->same[] = $key;
					}
				}
			}
			
			echo $info->report();
			unset($info); //释放掉$info
			//$info=null;
		}
	}	

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
	<table border=1>
	<tr>
	<td></td>
	<td>源语言<?php echo $src_lang; ?></td>
	<td>目标语言<?php echo $dst_lang; ?></td>
	</tr>
	</table>
	<?php 
		echo comp($src_lang, $dst_lang);
	}
	
?>
</body>
</html>