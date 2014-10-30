
function deleteUser(){
	if(confirm("确认删除？该用户所有应用将被同时删除！")){
		return true;
	}else{
		return false;
	}
}

function deleteApp(){
	if(confirm("确认删除？")){
		return true;
	}else{
		return false;
	}
}

function deauthorize(){
	if(confirm("确认取消授权？")){
		return true;
	}else{
		return false;
	}	
}