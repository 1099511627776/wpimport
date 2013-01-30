function createUser(path) {
	ls.ajax(path,{},function(data){	
		ls.msg.notice(data.status);
	});
}
function createCat(path) {
	ls.ajax(path,{},function(data){	
		ls.msg.notice(data.status);
	});
}
function createPost(path) {
	ls.ajax(path,{},function(data){	
		ls.msg.notice(data.status);
	});
}
