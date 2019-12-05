<?php

/**
 * 返回节点权限列表(多维数组)
 * @param array $node 节点数据数组
 * @param array $access 权限数据数组
 * @param integer $pid 父级id
 * @return array
 */
function node2layer($node, $access = null, $pid = 0) {
	
	if($node == '') return array();
	$arr = array();

	foreach ($node as $v) {
		if (is_array($access)) {
			
			$v['access'] =in_array($v['id'], $access)? 1 : 0;
		}
		if ($v['pid'] == $pid) {
			$v['child'] = node2layer($node, $access, $v['id']);
			$arr[] =$v;
		}
	}

	return $arr;
}

/**
 * 返回自定义属性名称|值列表
 * @param integer $flag 自定义属性值
 * @param string $delimiter 分割符
 * @param boolean $iskey 是否返回key
 * @param boolean $isarray 是否返回数组
 * @return array|string
 */
//返回
function flag2Str($flag, $delimiter=' ', $iskey = false, $isarray = false) {
	if (empty($flag)) {
		return $isarray? array(): '';
	}
	$flagStr = array();
	$flagtype = get_item('flagtype');//文档属性
	foreach ($flagtype as $k => $v) {
		if ($flag & $k) {
			$flagStr[] = $iskey? $k : $v;
		}
	}
	if ($isarray) {
		return $flagStr;
	} else {
		return implode($delimiter, $flagStr);
	}

}


/**
* 检查栏目权限
* @param integer $catid 栏目ID
* @param string $action 动作
* @param integer $roleid 角色
* @param boolean $flag 是否为管理组[0会员组,1管理员组]
* @return boolean 
*/
function check_category_access($catid, $action, $roleid, $flag = 1) {
	$value = false;
	static $access = null;
	static $access_cid = 0;
	if (!is_array($access) || $access_cid != $catid) {
		$access = M('categoryAccess')->where(array('catid' => $catid))->select();
		if (empty($access)) {
			$access = array();
		}
		$access_cid = $catid;
	}	
	
	foreach ($access as $v) {
		if($v['flag']==$flag && $v['roleid']==$roleid && $v['action']==$action) {
			$value = true;
			break;
		}
	}
	return $value;
}

/**
* 返回有权限的栏目(添加文档或修改文档时)
* @param array $cate 栏目数组
* @param string $action 动作
* @return array   
*/
function get_category_access($cate, $action = 'add') {
	if (empty($cate)) {
		return array();
	}
	//权限检测//超级管理员
	if (!empty($_SESSION[C('ADMIN_AUTH_KEY')])) {
    	return $cate;
    }

    $where = array('flag' => 1, 'roleid' => (int)session('ADMIN.roleid'));
    if (!empty($action)) {
    	$where['action'] = $action;
    } 
    
	$checkaccess = M('categoryAccess')->distinct(true)->where($where)->getField('catid', true);
    if(empty($checkaccess)) { 
		$checkaccess= array(); 
	}      

	$array = array();
	foreach ($cate as $v) {
		if (in_array($v['id'], $checkaccess) ) {				
			$array[] = $v;
		}
	}
	return $array;
}

/**
 * 快速文件数据读取和保存(原数据)-针对简单类型数据 字符串、数组
 * @param string $name 缓存名称
 * @param mixed $value 缓存值
 * @param string $path 缓存路径
 * @return mixed
 */
function rw_data($name, $value='', $path = CONF_PATH) {

    static $_cache  = array();
    $filename       = $path . $name . '.php';
    if ('' !== $value) {
        if (is_null($value)) {
            // 删除缓存
            return false !== strpos($name,'*')?array_map("unlink", glob($filename)):unlink($filename);
        } else {
            // 缓存数据
            $dir            =   dirname($filename);
            // 目录不存在则创建
            if (!is_dir($dir))
                mkdir($dir,0755,true);
            $_cache[$name]  =   $value;
            return file_put_contents($filename, strip_whitespace("<?php\treturn " . var_export($value, true) . ";?>"));
        }
    }
    if (isset($_cache[$name]))
        return $_cache[$name];
    // 获取缓存数据
    if (is_file($filename)) {
        $value          =   include $filename;
        $_cache[$name]  =   $value;
    } else {
        $value          =   false;
    }
    return $value;
}



/**
 * 返回内容中附件id数组
 * @param string $content 内容 in
 * @param string $firstpic 第一张缩略图 out
 * @param boolean $flag 是否获取第一张缩略图
 * @return mixed
 */
function get_att_content(&$content, &$firstpic = null, $flag = false) {

	//内容中的图片
	$img_arr = array();
	$reg = "/<img[^>]*src=\"((.+)\/(.+)\.(jpg|gif|bmp|png))\"/isU";		
	preg_match_all($reg, $content, $img_arr, PREG_PATTERN_ORDER);
	// 匹配出来的不重复图片
	$img_arr = array_unique($img_arr[1]);
	$attid_array = array();
	
	if (!empty($img_arr)) {

	   
	    $baseurl = get_url_path(get_cfg_value('CFG_UPLOAD_ROOTPATH'), true);
	    $baseurl2 = get_url_path(get_cfg_value('CFG_UPLOAD_ROOTPATH'));//不带域名
	    /*
	    foreach ($img_arr as $k => $v) {
	    	$img_arr[$k] = str_replace(array($baseurl,$baseurl2), array('',''), $v);//清除域名前缀			    	
	    }
	    */
	    $img_arr = str_replace(array($baseurl,$baseurl2), array('',''), $img_arr);//清除域名前缀	

	   
		$attid = M('attachment')->field('id,filepath')->where(array('filepath' => array('in', $img_arr)))->select();
		
		if ($attid) {

			//只有缩略图为空时,才提取第一张图片
			if ($flag && isset($firstpic)) {
				//取出本站内的第一张图
				foreach ($img_arr as $v) {
					foreach ($attid as $v2) {
						if ($v == $v2['filepath']) {
							$imgtbSize = explode(',', get_cfg_value('CFG_IMGTHUMB_SIZE'));//配置缩略图第一个参数
			                $imgTSize = explode('X', $imgtbSize[0]);
			                $firstpic =  get_picture($baseurl2.$v2['filepath'], intval($imgTSize[0]), intval($imgTSize[1]));
							break 2;
						}
					}
				}
			}

			//attid 数组
			foreach ($attid as $v) {
				$attid_array[] = $v['id'];
			}
		}
		
	}

	return $attid_array;
}

/**
 * 返回附件id数组
 * @param string|array $attachment 附件内容
 * @param boolean $flag 是否是缩略图
 * @return mixed
 */
function get_att_attachment($attachment,$flag = false) {

	
	if (empty($attachment)) {
		return array();
	}
	$attid_array = array();
	$baseurl = get_url_path(get_cfg_value('CFG_UPLOAD_ROOTPATH'));

	//清除缩略图的!200X200.jpg后缀
	if ($flag) {
		$attachment = preg_replace(array('#!(\d+)X(\d+)\.jpg$#i','#^'.$baseurl.'#i'), array('',''), $attachment);
	}else {
		$attachment = str_replace($baseurl, '', $attachment);
	}
	
	$attid = M('attachment')->where(array('filepath' => array('IN', $attachment)))->getField('id',true);
	if($attid){
		$attid_array = $attid;
	}

	return $attid_array;
}

/**
 * 返回保存到attachmentindex表
 * @param integer|array $attid 附件id
 * @param integer $attid 附件id
 * @param integer $modelid 模型id 
 * @param string $modelname 模型名称(唯一标志符)
 * @return mixed
 */
function insert_att_index($attid, $arcid, $modelid, $modelname = '') {
	if (empty($attid) || empty($arcid)) {
		return false;
	}
	if (empty($modelid) && $modelname == '') {
		return false;
	}

	if (is_array($attid)) {
		$attid_array = array_unique($attid);
	} else {
		$attid_array = array($attid);
	}

	//mysql,支持addAll
	if (in_array(strtolower(C('DB_TYPE')), array('mysql','mysqli','mongo'))) {
		
		$dataAtt = array();
		foreach ($attid_array as $v) {
			if ($modelid>0) {
				$dataAtt[] = array('attid' => $v,'arcid' => $arcid, 'modelid' => $modelid);
			} else {
				$dataAtt[] = array('attid' => $v,'arcid' => $arcid, 'desc' => $modelname);
			}		
		}
		M('attachmentindex')->addAll($dataAtt);
	} else {

		foreach ($attid_array as $v) {
			if ($modelid>0) {
				M('attachmentindex')->add(array('attid' => $v,'arcid' => $arcid, 'modelid' => $modelid));
			} else {
				M('attachmentindex')->add(array('attid' => $v,'arcid' => $arcid, 'desc' => $modelname));
			}		
		}
	}
		

	return true;
}


/**
 * 返回保存到attachmentindex表
 * @param string $name 元素名称
 * @param integer $typeid 类型
 * @param string $tvalue 表单类型和可选值 
 * @param string|integer $vaule 值
 * @return mixed
 */

function get_element_html($name,$typeid, $tvalue = '', $vaule = '',$key) {

	if (empty($name) || empty($typeid)) {
		return '';
	}

	switch ($typeid) {
		case 1:
			$type = 'text';
			$vaule = intval($vaule);
			break;
		case 2:
			$type = 'text';
			break;
		case 3:
			$type = 'textarea';
			break;
		case 4:
			$type = 'radio';			
			$vaule = intval($vaule);
			break;
		case 5:
			$type = 'file';
			break;
		default:
			$type = 'text';
			break;
	}
	
	
	if (!empty($tvalue)) {
		$array = explode("\n", str_replace("\r\n", "\n", trim($tvalue,"\r\n")));
		if (in_array($array[0], array('select','radio','checkbox','text','textarea','file'))) {
			$type = $array[0]; 
			unset($array[0]);
			if(strpos($tvalue,'|')){
				$tvalue  = array();
				foreach ($array as $val) {
				    list($k, $v) = explode('|', $val);
				    $tvalue[$k]   = $v;
				}
			}else{
				foreach ($array as $val) {
				    $tvalue[$val]   = $val;
				}
			}
		}else {

		}
		
	}
	$str = '';
	switch ($type) {
		case 'text':
			$str = '<input type="text"  class="form-control" name="'.$name.'" value="'.$vaule.'">';
			break;
		case 'textarea':
			$str = '<textarea name="'.$name.'" id="'.$name.'" class="form-control" rows="5">'.$vaule.'</textarea>';
			break;
		case 'radio':
			if (!is_array($tvalue)) {
				$tvalue = array(1=>'是',0=>'否');
			}
			foreach ($tvalue as $k => $v) {
				$str .= '<label class="radio-inline"><input type="radio" name="'.$name.'" value="'.$k.'" ';
				if ($vaule == $k) {
					$str .= 'checked="checked" ';
				}
				$str .=	'/>'.$v.'</label>';
			}

			break;
		case 'checkbox':
			if (!is_array($tvalue)) {
				break;
			}
			foreach ($tvalue as $k => $v) {
				$str .= '<label class="checkbox-inline"><input type="checkbox" name="'.$name.'" value="'.$k.'" ';
				if ($vaule == $k) {
					$str .= 'checked="checked" ';					
				}
				$str .=	'/>'.$v.'</label>';
			}
			break;			
		case 'select':
			
			if (!is_array($tvalue) && false !== strpos($name, 'CFG_THEMESTYLE')) {
				$tmp = get_file_folder_List('./Public/Home/' , 1);
				$tvalue = array();
				foreach ($tmp as $key => $value) {
					$tvalue[$value] = $value;
				}
			}elseif (!is_array($tvalue) && false !== strpos($name, 'CFG_DEFAULT_TPL')) {
				$tmp = get_file_folder_List('./Tpl/Home/' , 1);
				$tvalue = array();
				foreach ($tmp as $key => $value) {
					$tvalue[$value] = $value;
				}
			}elseif (!is_array($tvalue) && false !== strpos($name, 'CFG_DEFAULT_MOBILE_TPL')) {
				$tmp = get_file_folder_List('./Tpl/Mobile/' , 1);
				$tvalue = array();
				foreach ($tmp as $key => $value) {
					$tvalue[$value] = $value;
				}
			}elseif (!is_array($tvalue) && false !== strpos($name, 'CFG_MOBILE_THEMESTYLE')) {
				$tmp = get_file_folder_List('./Public/Mobile/' , 1);
				$tvalue = array();
				foreach ($tmp as $key => $value) {
					$tvalue[$value] = $value;
				}
			}
			if (!is_array($tvalue)) {
				$tvalue = array();
			}

			$str .= '<select name="'.$name.'" class="form-control">';
			foreach ($tvalue as $k => $v) {
				$str .= '<option value="'.$k.'" ';
				if ($vaule == $k) {
					$str .= 'selected="selected" ';					
				}
				$str .=	'>'.$v.'</option>';
			}
				
			$str .= '</select>';
			break;
		case 'file':
			//$str = '<input type="text"  class="form-control" name="'.$name.'" value="'.$vaule.'">';
			$str = '<div class="btn btn-success up-picture-btn" id="up-picture-btn'.$key.'">
				<span><em class="glyphicon glyphicon-plus-sign"></em>添加图片</span>										        
				<input id="fileupload'.$key.'" type="file" name="mypic" /></div>';
			break;
		default:
			$str = '';
			break;
	}

	return $str;
}

function get_element_nhtml($name,$typeid, $tvalue = '', $vaule = '',$key) {

	if (empty($name) || empty($typeid)) {
		return '';
	}

	switch ($typeid) {
		case 1:
			$type = 'text';
			$vaule = intval($vaule);
			break;
		case 2:
			$type = 'text';
			break;
		case 3:
			$type = 'textarea';
			break;
		case 4:
			$type = 'radio';			
			$vaule = intval($vaule);
			break;
		case 5:
			$type = 'file';
			break;
		default:
			$type = 'text';
			break;
	}
	
	
	if (!empty($tvalue)) {
		$array = explode("\n", str_replace("\r\n", "\n", trim($tvalue,"\r\n")));
		if (in_array($array[0], array('select','radio','checkbox','text','textarea','file'))) {
			$type = $array[0]; 
			unset($array[0]);
			if(strpos($tvalue,'|')){
				$tvalue  = array();
				foreach ($array as $val) {
				    list($k, $v) = explode('|', $val);
				    $tvalue[$k]   = $v;
				}
			}else{
				foreach ($array as $val) {
				    $tvalue[$val]   = $val;
				}
			}
		}else {

		}
		
	}
	$str = '';
	switch ($type) {
		case 'text':
			$str = '<input type="text"  class="layui-input" name="'.$name.'" value="'.$vaule.'">';
			break;
		case 'textarea':
			$str = '<textarea name="'.$name.'" id="'.$name.'" class="layui-textarea">'.$vaule.'</textarea>';
			break;
		case 'radio':
			if (!is_array($tvalue)) {
				$tvalue = array(1=>'是',0=>'否');
			}
			foreach ($tvalue as $k => $v) {
				$str .= '<input type="radio" name="'.$name.'" value="'.$k.'" ';
				if ($vaule == $k) {
					$str .= 'checked="checked" ';
				}
				$str .=	'title="'.$v.'" />';
			}

			break;
		case 'checkbox':
			if (!is_array($tvalue)) {
				break;
			}
			foreach ($tvalue as $k => $v) {
				$str .= '<input type="checkbox" name="'.$name.'" value="'.$k.'" ';
				if ($vaule == $k) {
					$str .= 'checked="checked" ';					
				}
				$str .=	'title="'.$v.'" />';
			}
			break;			
		case 'select':
			
			if (!is_array($tvalue) && false !== strpos($name, 'CFG_THEMESTYLE')) {
				$tmp = get_file_folder_List('./Public/Home/' , 1);
				$tvalue = array();
				foreach ($tmp as $key => $value) {
					$tvalue[$value] = $value;
				}
			}elseif (!is_array($tvalue) && false !== strpos($name, 'CFG_DEFAULT_TPL')) {
				$tmp = get_file_folder_List('./Tpl/Home/' , 1);
				$tvalue = array();
				foreach ($tmp as $key => $value) {
					$tvalue[$value] = $value;
				}
			}elseif (!is_array($tvalue) && false !== strpos($name, 'CFG_DEFAULT_MOBILE_TPL')) {
				$tmp = get_file_folder_List('./Tpl/Mobile/' , 1);
				$tvalue = array();
				foreach ($tmp as $key => $value) {
					$tvalue[$value] = $value;
				}
			}elseif (!is_array($tvalue) && false !== strpos($name, 'CFG_MOBILE_THEMESTYLE')) {
				$tmp = get_file_folder_List('./Public/Mobile/' , 1);
				$tvalue = array();
				foreach ($tmp as $key => $value) {
					$tvalue[$value] = $value;
				}
			}
			if (!is_array($tvalue)) {
				$tvalue = array();
			}

			$str .= '<select name="'.$name.'" class="form-control">';
			foreach ($tvalue as $k => $v) {
				$str .= '<option value="'.$k.'" ';
				if ($vaule == $k) {
					$str .= 'selected="selected" ';					
				}
				$str .=	'>'.$v.'</option>';
			}
				
			$str .= '</select>';
			break;
		case 'file':
			/*$str = '<div class="btn btn-success up-picture-btn" id="up-picture-btn'.$key.'">
				<span><em class="glyphicon glyphicon-plus-sign"></em>添加图片</span>										        
				<input id="fileupload'.$key.'" type="file" name="mypic" /></div>';*/
				$str = '<div class="layui-input-inline">
							<input type="file" name="myimage" class="layui-upload-file" id="image'.$key.'">
						</div>';
			break;
		default:
			$str = '';
			break;
	}

	return $str;




}

/**
 * 返回文档url,主要针对模型下的文章[或者必须有flag,jumpurl字段的文档]
 * @param array $arc 文档内容
 * @param integer $typeid 类型
 * @param string $tvalue 表单类型和可选值 
 * @param string|integer $vaule 值
 * @return mixed
 */

function view_url($arc , $act = 'Show/index') {
	if (($arc['flag'] & B_JUMP) && !empty($arc['jumpurl'])) {
		$url = go_link($arc['jumpurl']);
	} else {
		$url = go_link(C('DEFAULT_MODULE'). '/'. $act. '?cid='. $arc['cid']. '&id='. $arc['id'], 1);
	}
	return $url;
}

/*function get_username($userid = 0) {
	$user_name = '';
	if($userid > 0){
		$map['id'] = $userid;
		$rs = M('member')->field('email')->where($map)->find();
		$user_name = $rs['email'];
	}else{
		$user_name = '游客';
	}
	
	return $user_name;
}*/

function get_catename($cid = 0) {
	$catename = '';
	if($cid > 0){
		$map['id'] = $cid;
		$rs = M('category')->field('name')->where($map)->find();
		/*print_r($rs['email']);
	exit;*/
		$catename = $rs['name'];
	}else{
		$catename = '无分类';
	}
	
	return $catename;
}

/**
 * 导入excel文件
 * @param  string $file excel文件路径
 * @return array        excel文件内容数组
 */
function import_excel($file){
    // 判断文件是什么格式
    $type = pathinfo($file); 
    $type = strtolower($type["extension"]);
    //$type=$type==='csv' ? $type : 'Excel5';
	if($type === 'xlsx'){
		$type = 'Excel2007';
	}
	elseif($type === 'csv'){
		$type = $type;
	}else{
		$type = 'Excel5';
	}
    ini_set('max_execution_time', '0');
    Vendor('PHPExcel.PHPExcel');
    // 判断使用哪种格式
    $objReader = PHPExcel_IOFactory::createReader($type);
    $objPHPExcel = $objReader->load($file); 
    $sheet = $objPHPExcel->getSheet(0); 
    // 取得总行数 
    $highestRow = $sheet->getHighestRow();     
    // 取得总列数      
    $highestColumn = $sheet->getHighestColumn(); 
    //循环读取excel文件,读取一条,插入一条
    $data=array();
    //从第一行开始读取数据
    for($j=1;$j<=$highestRow;$j++){
        //从A列读取数据
        for($k='A';$k<=$highestColumn;$k++){
            // 读取单元格
            $data[$j][]=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
        } 
    }  
    return $data;
}

?>