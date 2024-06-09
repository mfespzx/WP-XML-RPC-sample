<?php

include_once('IXR_Library.php');

class post {

	function __construct() {
		$this->post();
	}

	function post() {

		$post_content = "<p>" .$_POST['post_content']. "</p><br>";
		$post_content = str_replace("\n", '<br>', $post_content);
		$post_title = $_POST['post_title'];
		$thumb_array = array($_POST['form1'], $_POST['form2'], $_POST['form3']);
		$thumb_dir_ary = array('thumb1', 'thumb2', 'thumb3');

		$cont = 0;
		foreach($thumb_array as $thumb){
			if (!$thumb) break;
			preg_match('/([^\/]+?)([\?#].*)?$/', $thumb, $match);
			$uploadfile_array[] = 'http://example.com/upload2/' .$thumb_dir_ary[$cont]. '/' .$match[0];
			$uploadfile = __DIR__ . '/upload2/' .$thumb_dir_ary[$cont]. '/' .$match[0];
			$thumb_dir = __DIR__ . '/upload/' .$thumb_dir_ary[$cont]. '/' .$match[0];
			$this->imageresize($thumb_dir);
			rename($thumb_dir, $uploadfile);
			$cont++;
		}
		$client = new IXR_Client("http://example.com/xmlrpc.php");
		if($uploadfile_array) {
			foreach ($uploadfile_array as $img) {
				$post_content .= '<img src="' .$img. '">';
			}
		}

		$status = $client->query(
			"wp.newPost", 
			1, 
			'admin', 
			'adminadmin', 
			array(
				'post_author' => 1, 
				'post_status' => 'publish', 
				'post_title' => $post_title, 
				'post_content' => $post_content, 
				'terms' => array('category' => array("category_id")) 
			)
		);

		if(!$status){
			die('Something went wrong - '.$client->getErrorCode().' : '.$client->getErrorMessage());
		} else {
			$post_id = $client->getResponse();
		}

		header('location: http://exmaple.com/post_form.php');
	}


	function imageresize($img) {
		$width = 650;
		$targetImage = $img;
		$image = imagecreatefromjpeg($targetImage);
		list($image_w, $image_h) = getimagesize($targetImage);
		$height = intval($width / ($image_w / $image_h));
		$canvas = imagecreatetruecolor($width, $height);

		imagecopyresampled(
			$canvas, 
			$image, 
			0, 
			0, 
			0, 
			0, 
			$width, 
			$height, 
			$image_w, 
			$image_h
		);

		imagejpeg(
			$canvas, 
			$targetImage, 
			100
		);
		imagedestroy($canvas);
	}
}
$post = new post;
?>
