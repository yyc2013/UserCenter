<?php

$mysql = new SaeMysql();
$sql = "select * from user ";
$result = $mysql->getData($sql);
if($result){
	exit();
}else{

$submit=$_POST['submit'];
if($submit){
	$email = $_POST['email'];
	$password = $_POST['password'];
	$password2 = $_POST['password2'];
	if($email&&$password&&($password==$password2)){
		$sql2=  array(
		
		"app" =>"CREATE TABLE IF NOT EXISTS `app` (
		  `id` bigint(20) NOT NULL AUTO_INCREMENT,
		  `appid` varchar(16) NOT NULL,
		  `secret` varchar(64) NOT NULL,
		  `create_time` int(11) NOT NULL,
		  `email` varchar(64) NOT NULL,
		  `name` varchar(64) DEFAULT NULL,
		  `description` text,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `appid` (`appid`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=38 ;" , 

	    "code"=>"CREATE TABLE IF NOT EXISTS `code` (
		  `id` bigint(20) NOT NULL AUTO_INCREMENT,
		  `appid` varchar(16) NOT NULL,
		  `code` varchar(64) NOT NULL DEFAULT '',
		  `redirect_uri` varchar(300) NOT NULL DEFAULT '',
		  `expires` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=200 ;",

		"token"=>"CREATE TABLE IF NOT EXISTS `token` (
		  `id` bigint(20) NOT NULL AUTO_INCREMENT,
		  `appid` varchar(16) NOT NULL,
		  `access_token` varchar(64) NOT NULL DEFAULT '',
		  `refresh_token` varchar(64) NOT NULL DEFAULT '',
		  `expires` int(11) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `appid` (`appid`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37 ;",

		"user"=>"CREATE TABLE IF NOT EXISTS `user` (
		  `id` bigint(20) NOT NULL AUTO_INCREMENT,
		  `email` varchar(64) NOT NULL,
		  `password` varchar(64) NOT NULL,
		  `nickname` varchar(64) DEFAULT NULL,
		  `birthday` date DEFAULT NULL,
		  `appcount` int(4) DEFAULT '0',
		  `icon` varchar(20) DEFAULT NULL,
		  `identifier` char(1) DEFAULT 'u',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `email` (`email`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;",

		"weibo_user"=>"CREATE TABLE IF NOT EXISTS `weibo_user` (
		  `id` bigint(21) NOT NULL AUTO_INCREMENT,
		  `weibo_email` varchar(64) NOT NULL,
		  `email` varchar(64) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `weibo_email` (`weibo_email`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;"	
			);
		foreach ($sql2 as $key => $value) {
			$mysql->runSql($value);
		}

		$sql3 = "insert into user(email,password,identifier) values('".$email."','".$password."','a')";
		$mysql->runSql($sql3);

		echo "<script>location.href='http://".$_SERVER['HTTP_HOST']."';</script>";

	}else{
		echo "密码不一致或填写账户密码填写不完整！"；
	}

}

}


?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<link href="/App/Public/css/bootstrap.min.css" rel="stylesheet" type ="text/css"/>
</head>
<body>
<br><br><br><br>
<div class="container col-md-6 col-md-offset-3">
<form role="form" action="" method="post">
<div class="form-group">
<label>Email:</label>
<input type="text" class="form-control" name="email"/><br>
</div>
<div class="form-group">
<label>Password:</label>
<input type="password" class="form-control" name="password"/><br>
</div>
<label>Password Again:</label>
<input type="password2" class="form-control" name="password"/><br>
</div>


<input type="submit"  class="btn btn-primary col-md-offset-8"name="submit" value="提交"/>&nbsp;&nbsp;&nbsp;&nbsp;
</form>	

</div>

<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script> 
<script src="/App/Public/js/bootstrap.min.js"></script> 
</body>
</html>
