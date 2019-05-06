<?php
// 2019-05-07 00:00:13 http://www.php.cn/blog/detail/8605.html
// 2019-05-07 00:00:23 牛逼的代码来自网络， 而我是把它用到极致的那个人，哈哈
// 微博 @荣泽东

// 下面代码自动处理清理图片中重复上传的，然后把默认的解析不存在的文件直接解析到 index.php文件
// 在 index.php里将查询表格（由本排重代码生成的重复文件列表，把已经排重的文件用之前的文件代替并删除重复的文件）
// 定期运行本程序，可以有效排重并将重复文件直接干掉
// 建立一个 small图片目录，放弃原生直接上传的文件，每次都压缩后并返回压缩后的图片
// 就是说保留一份上传的原生文件 用于下次继续上传的重复文件比对用，
// 同时生成该文件的压缩版本，每次调用返回该压缩后的文件

// exec("find . -type f", $lines);
// aliyun的虚拟主机，无法使用exec
$json_file = realpath('.').'/update_duplicate_images.json';



$json = file_get_contents($json_file);
$arr_json = json_decode($json, true);
// var_dump($arr_json);

if($_SERVER['PHP_SELF'] == '/update_duplicate_images.php' ||
$_SERVER['SCRIPT_NAME'] == '/update_duplicate_images.php' ||
$_SERVER['REQUEST_URI'] == '/update_duplicate_images.php')
{
	//2019-05-07 01:01:39
	// 直接调用

	echo('duplicate image '.count($arr_json).' lines.');

	/**
	 * 使用scandir 遍历目录
	 * https://blog.csdn.net/niedewang/article/details/80875510
	 *
	 * @param $path
	 * @return array
	 */
	function getDir($path)
	{
		//判断目录是否为空
		if(!file_exists($path)) {
			return array();
		}

		// $files = scandir(realpath($path));
		$files = scandir($path);
		$fileItem = array();
		foreach($files as $v) {
			$newPath = $path .DIRECTORY_SEPARATOR . $v;
			if(is_dir($newPath) && $v != '.' && $v != '..') {
				$fileItem = array_merge($fileItem, getDir($newPath));
			}else if(is_file($newPath)){
				$fileItem[] = $newPath;
			}
		}

		return $fileItem;
	}

	// $path = realpath('./uploadfile/');
	$path = 'uploadfile';
	// echo('<pre>');
	// var_dump(getDir($path));
	$lines = getDir($path);
	// exit;

	$arr = array();
	$del = array();

	$n = 0;

	// json

	foreach($lines as $line){
		$line = trim($line);
		if(!$line){
			continue;
		}

		$md5 = md5_file($line);

		if(isset($arr[$md5])){
			$del[] = $line;
			echo "{$n} del {$line} use {$arr[$md5]} replace it.<br>";
			$arr_json[$line]=$arr[$md5];
			// 需要删除的，列出并存入列表，然后再删除
			unlink("{$line}");
			$n ++;
		}else{
			$arr[$md5] = $line;
		}
	}

	// 2019-05-07 00:55:06 update update_duplicate_images db.
	if($n){
		file_put_contents($json_file, json_encode($arr_json));
		echo "del " . $n . " files<br>";
	}
	//echo join("/n", $del);
	// print_r($arr_json);


}else{
	// include
	// $arr_json = json_decode(file_get_contents('update_duplicate_images.json'), true);

	// //强制缓存一个月页面


	$uri = substr($_SERVER['REQUEST_URI'], 1);
	// print_r($uri);
	if(isset($arr_json[$uri])){
		// echo("req: {$uri}, return: {$arr_json[$uri]}");
		header("Cache-Control: public");
		header("Pragma: cache");

		$offset = 30*60*60*24; // cache 1 month
		$ExpStr = "Expires: ".gmdate("D, d M Y H:i:s", time() + $offset)." GMT";
		header($ExpStr);

		$modified_time = @$_SERVER['HTTP_IF_MODIFIED_SINCE'];
		if( strtotime($modified_time)+$offset > time() ){ 
		header("HTTP/1.1 304"); 
		exit; 
		}
		readfile($arr_json[$uri]);
		exit;
	}
}
