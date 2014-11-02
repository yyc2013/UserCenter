<?php

class IndexAction extends Action{
	protected function _initialize() {
        header("Content-Type:text/html; charset=utf-8");
        /*$mysql = new SaeMysql();
        $file = fopen($_SERVER['DOCUMENT_ROOT']."/App/Conf/admin.txt","r");
		$str = fgets($file);
		$emails=explode('=',$str);
		$email=trim($emails[1]);
		$str = fgets($file);
		$passwords = explode('=', $str);
		$password=trim($passwords[1]);
		fclose($file);
		if($email&&$password){
			$sql = "select * from user where email='".$email."'";
			$result = $mysql->getData($sql);
			if(!$result){
				$sql = "insert into user(email,password,identifier)values('".$email."','".$password."','a')";
				$mysql->runSql($sql);
			}	
		}else{
			$this->error("请下载代码到本地配置管理员账户信息，之后同步到SAE！配置文件/Conf/admin.txt");
		}		*/
		
    }
	private function verify(){
		if($_SESSION['weibo_email']&&!$_SESSION['email']){
			$this->error("您尚未拥有本站账号，请先注册本站账号！");
		}else if(!$_SESSION['weibo_email']&&!$_SESSION['email']){
			$this->redirect('index');
		}
	}
	private function getAppid(){
		$str = "0123456789";
		$appid = null;
		$mysql = new SaeMysql();
		do{	
			for($i=0;$i<16;$i++){
				$tmp = $str[rand(0,9)];
				$appid = $appid.$tmp;
			}
			$sql="select * from app where appid='".$appid."'";
			$res=$mysql->getData($sql);
			if($res){
				$appid=null;
			}
		}while($res);
		return $appid;
	}
	private function getSecret(){
		$str = "0123456789abcdef";
		$secret = null;
		$mysql = new SaeMysql();
			for($i=0;$i<32;$i++){
				$tmp = $str[rand(0,15)];
				$secret = $secret.$tmp;
			}
		return $secret;
	}
	public function test(){
		$filename=$_SERVER['DOCUMENT_ROOT']."/app_UserCenter.sql";
		$sql = file_get_contents($filename);
		$sql = str_replace("\r", "\n", $sql );
		

	}

	public function register(){
		$submit=$_POST['submit'];
		if($submit){
			$email=$_POST['email'];
			$password=$_POST['password'];
			$password2=$_POST['password2'];
			if($password==$password2){
				$mysql = new SaeMysql();
				$sql = "select * from user where email= '".$email."'";
				$exist=$mysql->getData($sql);
				if($exist==false){
					$sql = "insert into user (email,password) values('".$email."','".$password."')";
					$mysql->runSql($sql);
					echo "<script>alert('注册成功！');</script>";
					$this->redirect('index');
				}else{
					$this->error("Email has been registered!");
				}
			}else{
				$this->error("Password not consistent!");
			}
		}else{
			$this->display();
		}
	}

	public function userCenter(){
		$this->verify();
		import('@.ORG.UploadFile');
		$submit = $_POST['submit'];
		$submit2 = $_POST['submit2'];

		$mysql = new SaeMysql();
		$email = $_SESSION['email'];
		$data['user_name']=$email;
		$data['identifier']=$_SESSION['identifier'];
		
		if($submit){
			$password = $_POST['password'];
			$password2 = $_POST['password2'];
			$nickname=$_POST['nickname'];
			$birthday=$_POST['birthday'];
			$sql = "update user set ";
			$flag=0;
			if($password||$password2){
				if($password==$password2){
					$sql = $sql." password = '".$password."'";
					$flag++;
				}else{
					$this->error("两次密码不一致！");
				}	
			}
			if($nickname){
				$sql = $sql.($flag>0?",":" ");
				$sql= $sql."nickname = '".$nickname."'";	
				$flag++;
			}
			if($birthday){
				$sql = $sql.($flag>0?",":" ");
				$sql= $sql."birthday = '".$birthday."'";	
				$flag++;
			}
			$sql= $sql." where email = '".$email."'";	
			$mysql->runSql($sql);
			echo "<script>alert('信息更新成功！');</script>";
			
			$this->redirect('userCenter');					
		}
		if($submit2&&!empty($_FILES)){
			$config=array(
                'allowExts'=>array('jpg','gif','png','jpeg'),
                'savePath'=>'./Public/upload/img/icon/',
                'saveRule'=>'time',
            );
            $upload = new UploadFile($config);
			if($upload->upload()){
				echo "Success";
				$info = $upload->getUploadFileInfo();
				$sql= "update user set icon = '".$info[0]['savename']."' where email ='".$email."'";
				$mysql->runSql($sql);
				$this->redirect('userCenter');
			}else{
				$this->error("Failed to upload icon!");
			}
		}
		$sql="select * from user where email ='".$email."'";
		$result=$mysql->getData($sql);
		if($result[0]['icon']!=null){
			$data['icon'] = $result[0]['icon'];
		}

		$this->assign($data);
		$this->display();
	}
	public function adminUser(){
		$this->verify();
		$submit = $_POST['submit'];
		$mysql = new SaeMysql();
		$sql = "select * from user ";
		if($submit){
			$keytype = $_POST['keytype'];
			$key = $_POST['key'];
			$sql=$sql." where ".$keytype."= '".$key."'"; 
		}
		$result= $mysql->getData($sql);
		$data['user_name'] = $_SESSION['email'];
		$data['identifier'] = $_SESSION['identifier'];
		$this->assign($data);
		$this->assign('app',$result);
		$this->display();
	}
	public function upgradeUser(){

	}
	public function deleteUser(){
		$this->verify();
		$email = $_GET['email'];
		$mysql = new SaeMysql();
		$sql="select appid from app where email = '".$email."'";
		$result=$mysql->getData($sql);
		foreach ($result as $item) {
			$sql="delete from token where appid ='".$item['appid']."'";
			$mysql->runSql($sql);
			$sql="delete from code where appid ='".$item['appid']."'";
			$mysql->runSql($sql);
		}
		$sql="delete from app where email = '".$email."'";
		$mysql->runSql($sql);
		$sql = "delete from user where email = '".$email."'";
		$mysql->runSql($sql);
		$sql = "delete from weibo_user where email = '".$email."'";
		$mysql->runSql($sql);
		$this->redirect('adminUser');
	}
	public function adminApp(){
		$this->verify();
		$submit=$_POST['submit'];
		$mysql = new SaeMysql();
		if($submit){
			$keytype=$_POST['keytype'];
			$key = $_POST['key'];
			$sql="select * from app where ";
			if($keytype=="appname"){
				$sql = $sql."appname like %".$key."%";
			}else if($keytype=="appid"){
				$sql = $sql."appid = '".$key."'";
			}else{
				$sql = $sql."email = '".$key."'";
			}
			
		}else{
			$sql="select * from app";
		}
		$data['user_name']=$_SESSION['email'];
		$data['identifier'] = $_SESSION['identifier'];
		$result=$mysql->getData($sql);
		$this->assign("app",$result);
		$this->assign($data);						
		$this->display();
	}
	public function index(){
		$email=$_POST['email'];
		$password=$_POST['password'];
		$submit = $_POST['submit'];
		if($submit&&$email&&$password){
			$mysql = new SaeMysql();
			$sql = "select * from user where email='".$email."'";
			$result = $mysql->getData($sql);
			
			if($result != false && $result[0]['password'] == $password){
				$_SESSION['email']=$email;
				$_SESSION['identifier']=$result[0]['identifier'];
				echo $_SESSION['identifier'];
				$this->redirect("userCenter");
			}else{
				print_r($result);
			}
		}
		$filename = $_SERVER['DOCUMENT_ROOT']."/App/Conf/weibo_login_config.txt";
		$param = parse_ini_file($filename);
		$data['key']=$param['AppKey'];
		$data['redirect_uri']="http://".$_SERVER['HTTP_HOST']."/Index/weiboCode";
		$data['identifier'] = $_SESSION['identifer'];
		$this->assign($data);
		$this->display();
	}

	public function logout(){
		$this->verify();
		session_unset();
		session_destroy();
		$this->redirect('index');
	}

	public function deauthorize(){
			$this->verify();
			$appid=$_GET['appid'];
			$sql="update token set access_token = null where appid='".$appid."'";
			$mysql= new SaeMysql();
			$mysql->runSql($sql);
			$sql = "select identifier from user where email = '".$_SESSION['email']."'";
			$result=$mysql->getData($sql);
			if($result[0]['identifier']=='u'){
				$this->redirect('appList');
			}else{
				$this->redirect('adminApp');
			} 
	}

	public function deleteApp(){
		    $this->verify();
			$appid=$_GET['appid'];
			$mysql= new SaeMysql();
			$sql="delete from token  where appid='".$appid."'";
			$mysql->runSql($sql);
			$sql="delete from code  where appid='".$appid."'";
			$mysql->runSql($sql);
			$sql="delete from app where appid='".$appid."'";
			$mysql->runSql($sql);
			$sql = "select identifier from user where email = '".$_SESSION['email']."'";
			$result=$mysql->getData($sql);
			
			if($result[0]['identifier']=='u'){
				$this->redirect('appList');
			}else{
				$this->redirect('adminApp');
			} 
	}
	public function accessRedirect(){
		echo "Hello! This weibo redirect page with access_token!";
	}
	public function weiboCode(){
		$code=$_GET['code'];
		$filename = $_SERVER['DOCUMENT_ROOT']."/App/Conf/weibo_login_config.txt";
		$param = parse_ini_file($filename);

		//redirect_uri跳转？
		$redirect_uri = "http://".$_SERVER['HTTP_HOST']."/Index/accessRedirect";
		
		$ch = curl_init();
		$url="https://api.weibo.com/oauth2/access_token?client_id=".$param['AppKey']."&client_secret=".$param['AppSecret']."&grant_type=authorization_code&redirect_uri=".$redirect_uri."&code=".$code;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_POST,1);
		$result=json_decode(curl_exec($ch),true);

		if($result['uid']){
			//$ch = curl_init();
			//$url="https://api.weibo.com/2/account/profile/email.json";
		/*	获取uid对应的微博邮箱，接口暂时不能使用，审核未通过
			$url="http://centertest1.sinaapp.com/index.php/User/test";
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch,CURLOPT_HEADER,0);
			curl_close($ch);
			$result=curl_exec($ch);
			print_r($result);	*/
			$_SESSION['weibo_email'] = $result['uid'];
			$mysql = new SaeMysql();
			$sql = "select * from weibo_user where weibo_email ='".$_SESSION['weibo_email']."'";
			$result = $mysql->getData($sql);
			$_SESSION['identifer']='u';
			if(!$result){
				$sql = "insert into weibo_user (weibo_email) values('".$_SESSION['weibo_email']."')";
				$mysql->runSql($sql);
				$this->redirect("register2");
			}else if(!$result[0]['email']){
				$this->redirect("register2");
			}else{
				$_SESSION['email']=$result[0]['email'];
				$this->redirect('userCenter');
			}
		}
	}
	public function register2(){
		if(!$_SESSION['weibo_email']){
			$this->error("尚未登陆！");
		}
		$submit=$_POST['submit'];
		$mysql = new SaeMysql();
		if($submit){
			$password = $_POST['password'];
			$password2 = $_POST['password2'];
			if($password&&$password==$password2){
				$_SESSION['email'] = $_POST['email'];
				$sql= "insert into user(email,password) value('".$_SESSION['email']."','".$password."')";
				$mysql->runSql($sql);
				$sql = "update weibo_user set email='".$_SESSION['email']."' where weibo_email='".$_SESSION['weibo_email']."'";
				$mysql->runSql($sql);
				$this->redirect('userCenter');
			}else{
				$this->error("Password Nost Consistent!");
			}
		}
		$data['user_name']=$_SESSION['email']?$_SESSION['email']:$_SESSION['weibo_email'];
		$this->assign($data);	
		$this->display();
	}

	public function doc(){
		$this->verify();
		$data['user_name'] = $_SESSION['email'];
		$data['identifier'] = $_SESSION['identifier'];
		$this->assign($data);
		$this->display();
	}
	public function appList(){
		$this->verify();
		$data['email']=$_SESSION['email'];
		$data['user_name']=$_SESSION['email'];
		$data['identifier'] = $_SESSION['identifier'];
		$mysql = new SaeMysql();
		$sql = "select * from app where email = '".$data['email']."'";
		$result = $mysql->getData($sql);
		$this->assign('app',$result);
		$this->assign($data);
		$this->display();
	}

	public function createApp(){
		$this->verify();
		$data['user_name']=$_SESSION['email'];
		$data['identifier'] = $_SESSION['identifier'];
		$submit = $_POST['submit'];
		if($submit){
			$name=$_POST['appname'];
			$description=$_POST['description'];
			$mysql = new SaeMysql();
			$appid = $this->getAppid();
			$secret= $this->getSecret();
			$time = time();
			$sql = "insert into app(appid,secret,create_time,email,name,description) values('".$appid."','".$secret."',".$time.",'".$_SESSION['email']."','".$name."','".$description."')";
			$mysql->runSql($sql);	
			$sql = "insert into code(appid) values('".$appid."')";
			$mysql->runSql($sql);	
			$sql = "insert into token(appid) values('".$appid."')";
			$mysql->runSql($sql);
			$this->redirect('appList');	
		}
		$this->assign($data);
		$this->display();
	}
	public function appContent(){
		$this->verify();
		$data['appid']=$_GET['appid'];
		$mysql = new SaeMysql();
		$sql="select * from app where appid='".$data['appid']."'";
		$result = $mysql->getData($sql);		
		$data['secret']=$result[0]['secret'];
		$data['name']=$result[0]['name'];
		$data['email']=$result[0]['email'];
		$data['time']=date("Y-m-d H:i:s",$result[0]['create_time']);
		$data['description']=$result[0]['description'];
		$data['identifier'] = $_SESSION['identifier'];
		$this->assign($data);
		$this->display();
	}

}
?>
