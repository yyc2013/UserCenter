<?php
import("ORG.OAuth.ThinkOauth2)");

class OauthAction extends Action{
	 protected function _initialize() {
        header("Content-Type:text/html; charset=utf-8");
    }
	private function getOauthCode(){
		$str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$code = null;
		for($i=0;$i<32;$i++){
			$tmp = $str[rand(0,25)];
			$code = $code.$tmp;
		}
		return $code;
	}

	public function login(){
		$data['appid'] = $_GET['client_id'];
		$data['callback'] = $_GET['redirect_uri'];
		$this->assign($data);
		$this->display();
	}
	public function grant(){
		$email = $_POST['email'];
		$password = $_POST['password'];
		
		$mysql = new SaeMysql();
		$sql = "select password from user where email = '".$email."'";
		$result = $mysql->getData($sql);
		
		if($result[0]['password'] == $password){
			$appid = $_POST['appid'];
			$callback = $_POST['callback'];
			$sql = "select * from app where appid = '".$appid."'"; 			
			$result = $mysql->getData($sql);
			
			if($result != false){
				$code=$this->getOauthCode();
				$expires=time()+3600;
				$sql="update code set code='".$code."',expires = ".$expires.", redirect_uri='".$callback."' where appid= '".$appid."'";
				$mysql->runSql($sql);
				echo "<script language='javascript'>location.href='".$callback."?code=".$code."'</script>";		
			} else{
				$this->error("无效的client_id".$appid);
			}
		
		}else{
			$this->error("用户名不存在或者密码错误！");
		}


	}
	public function grantAccessToken(){
		$appid=$_POST['client_id'];
		$code=$_POST['code'];
		$mysql= new SaeMysql();
		$sql="select code,expires from code where appid ='".$appid."'";
		$result = $mysql->getData($sql);
		if($result[0]['code']==$code&&$result[0]['expires']>time()){
			$sql="update code set code ='',redirect_uri='',scope='',expires=0, where appid='".$appid."'";
			$mysql->runSql($sql);
			$str=$this->getOauthCode();
			$token=$str;
			$sql="update token set access_token = '".$token."' where appid='".$appid."'";
			$mysql->runSql($sql);
			exit(json_encode(array("accessToken"=>$token)));
		}else{
			$this->error("无效的code!");
		}
	}
 
	public function getUserInfo(){
		$appid=$_POST['client_id'];
		$token=$_POST['access_token'];
		$mysql = new SaeMysql();
		$sql = "select * from token where appid= '".$appid."'";
		$result = $mysql->getData($sql);
		if($result[0]['access_token']&&$result[0]['access_token']==$token){
			
			$sql = "select * from app where appid = '".$appid."'";
			$result = $mysql->getData($sql);
			if($result[0]['email']){
				$sql = "select * from user where email ='".$result[0]['email']."'";
				$result=$mysql->getData($sql);
				if($result[0]){
					$storage = new SaeStorage();
					$result_ = array(
						"email"=>$result[0]['email'],
						"nickname"=>$result[0]['nickname'],
						"birthday"=>$result[0]['birthday'],
						"icon"=>$storage->getCDNUrl("Public","upload/img/icon/".$result[0]['icon'])
						);
					exit(json_encode($result_));
				}else{
					$error=array("info"=>"数据库错误，未找到用户信息，应用数据与用户信息数据不一致，请联系网站管理员！");
			    exit(json_encode($error));		
				}
			}else{
				$error=array("info"=>"数据库错误，未找到应用授权邮箱，请联系网站管理员！");
			    exit(json_encode($error));	
			}

		}else{
			$error=array("info"=>"client_id不存在或者access_token不正确");
			exit(json_encode($error));
		}
		
	}
/*
	public function testGrantAccessToken(){
		
		$code=$_GET['code'];
		$appid = "test1";
		$postdata=array(
			"appid"=>$appid,
			"code"=>$code
			);
		$ch = curl_init();
		$url="http://centertest1.sinaapp.com/index.php/Oauth/grantAccessToken";
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata);
		$output=curl_exec($ch);
		curl_close($ch);
		
		echo $output;
		
	}*/
}

?>