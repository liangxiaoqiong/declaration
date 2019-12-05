<?php
/**
*调用实例：	
require_once('showRank.php');
//定义等级图片
$tImage1 = '';
$tImage2 = '';
$tImage3 = '';
//实例化并传输等级图片
$tShow = new showRank($tImage1,$tImage2,$tImage3);
//输入的活跃天数
$tScore = 1009;
$echo = $tShow->get_score($tScore);
*/
namespace Common\Lib;

class ShowRank {
	/**
	* 第一级图片显示字段
	*/
	public $mImage1;
	/**
	* 第二级图片显示字段
	*/
	public $mImage2;
	/**
	* 第三级图片显示字段
	*/
	public $mImage3;
	/**
	* 第四级图片显示字段
	*/
	public $mImage4;
	/**
	* 构造函数：传入图片值
	* @return 无
	*/
	function __construct($pImage1 = '★',$pImage2 = '▲',$pImage3 = '●',$pImage4 = '◆') {
		$this->mImage1 = $pImage1;
		$this->mImage2 = $pImage2;
		$this->mImage3 = $pImage3;
		$this->mImage4 = $pImage4;
	}
	/**
	* 根据活跃天数计算用户等级。(模仿QQ的升级方式)
	* @return int
	* @access public
	*/
	function get_rank($pScore) {
		$temp = $pScore+4;
		$tRank = sqrt($temp)-2;
		$tRank = floor($tRank);
		return $tRank;
	}
	/**
	* 用户等级标志,根据用户等级显示用户标志
	* 仿照QQ等级的四进制显示
	* @return str
	* @access public
	*/
	function get_score($pScore) {
		$str = '';
		//$tRank = $this->get_rank($pScore);//根据分数取得等级
		$tRank = $pScore;
		$tPicNum = base_convert($tRank,10,4);//转化为四进制
		$tPicNum = strrev($tPicNum);//翻转字符串
		$tArray = str_split($tPicNum);//转化为数组
		$tNum = count($tArray);
		if($tNum<=4) {
			for($i=$tNum-1;$i>=0;$i--){
				switch($i){
					case '0':
					for($j=0;$j<$tArray[$i];$j++){
						$str .= $this->mImage1;
					}
					break;
					case '1':
					for($j=0;$j<$tArray[$i];$j++){
						$str .= $this->mImage2;
					}
					break;
					case '2':
					for($j=0;$j<$tArray[$i];$j++){
						$str .= $this->mImage3;
					}
					break;
					case '3':
					for($j=0;$j<$tArray[$i];$j++){
						$str .= $this->mImage4;
					}
					break;
					default:
					//$str = ;
					break;
				}
			}
		}else {
			$str =    $this->mImage3.$this->mImage3.$this->mImage3.$this->mImage3;
		}
		return $str;
	}
}
?>