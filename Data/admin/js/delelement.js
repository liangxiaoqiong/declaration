layui.use(['layer','jquery','form'],function(){
  var layer = layui.layer
  , form = layui.form()
  ,jq = layui.jquery;
  
  form.render();
  
  form.on('checkbox(checkAll)', function(data){
    if(data.elem.checked){
      jq("input[lay-filter='checkOne']").prop('checked',true);
    }else{
      jq("input[lay-filter='checkOne']").prop('checked',false);
    }
    form.render('checkbox');
  });  

  form.on('checkbox(checkOne)', function(data){
    var is_check = true;
    if(data.elem.checked){
      jq("input[lay-filter='checkOne']").each(function(){
        if(!jq(this).prop('checked')){ is_check = false; }
      });
      if(is_check){
        jq("input[lay-filter='checkAll']").prop('checked',true);
      }
    }else{
      jq("input[lay-filter='checkAll']").prop('checked',false);
    } 
    form.render('checkbox');
  });
  
  jq('.elementdel').click(function(){
									 
		var url= jq(this).data('url');
		var page= jq('.pagination li.active span').html();
		var length= jq('.admin-table #content tr').length;
		
		layer.confirm('你确定要删除该条数据吗?', {icon: 3, title:'删除提示'}, function(index){
			loading = layer.load(2, {
			  shade: [0.2,'#000']
			});
	   
		  
			jq.getJSON(url,function(data){
				
			  if(data.status == 1){
				layer.close(loading);
				layer.msg(data.info, {icon: 1, time: 1000}, function(){
					 if(length-1>0){
						 location.reload();
					  }else{
						  if(page>1){
							  page=page-1;
						  }
						  location.href = data.url;
						  //location.href = window.location.href+'?page='+page;
					  }
				  
				});
			  }else{
				layer.close(loading);
				layer.msg(data.info, {icon: 2, anim: 6, time: 1000});
			  }
			});
		}); 
	  
	});
	
  jq('.elementrestore').click(function(){
									 
		var url= jq(this).data('url');
		var page= jq('.pagination li.active span').html();
		var length= jq('.admin-table #content tr').length;
		
		layer.confirm('你确定要还原吗?', {icon: 3, title:'系统提示'}, function(index){
			loading = layer.load(2, {
			  shade: [0.2,'#000']
			});
			
			jq.getJSON(url,function(data){
				
			  if(data.status == 1){
				layer.close(loading);
				layer.msg(data.info, {icon: 1, time: 1000}, function(){
					 if(length-1>0){
						 location.reload();
					  }else{
						  if(page>1){
							  page=page-1;
						  }
						  location.href = data.url;
					  }
				  
				});
			  }else{
				layer.close(loading);
				layer.msg(data.info, {icon: 2, anim: 6, time: 1000});
			  }
			});
		}); 
	  
	});


  form.on('submit(alldelete)', function(data){
    var is_check = false;
    jq("input[lay-filter='checkOne']").each(function(){
      if(jq(this).prop('checked')){ is_check = true; }
    });
    if(!is_check){
      layer.msg('请选择数据', {icon: 2,anim: 6,time: 1000});
      return false;
    }
    var url= jq(this).data('url');
   
    layer.confirm('确定批量删除数据吗?', function(index){
      loading = layer.load(2, {
        shade: [0.2,'#000']
      });
      var param = data.field;
	  /*var str = JSON.stringify(param); 
	  alert(str);
	  return true;*/
    
      jq.post(url,jq('#form_do').serialize(),function(data){
        if(data.status == 1){
          layer.close(loading);
          layer.msg(data.info, {icon: 1, time: 1000}, function(){
            location.reload();
          });
        }else{
          layer.close(loading);
          layer.msg(data.info, {icon: 2,anim: 6, time: 1000});
        }
      });
    });
    return false;
  });
  
  form.on('submit(allrestore)', function(data){
    var is_check = false;
    jq("input[lay-filter='checkOne']").each(function(){
      if(jq(this).prop('checked')){ is_check = true; }
    });
    if(!is_check){
      layer.msg('请选择数据', {icon: 2,anim: 6,time: 1000});
      return false;
    }
    var url= jq(this).data('url');
   
    layer.confirm('确定批量还原数据吗?', function(index){
      loading = layer.load(2, {
        shade: [0.2,'#000']
      });
      var param = data.field;
    
      jq.post(url,jq('#form_do').serialize(),function(data){
        if(data.status == 1){
          layer.close(loading);
          layer.msg(data.info, {icon: 1, time: 1000}, function(){
            location.reload();
          });
        }else{
          layer.close(loading);
          layer.msg(data.info, {icon: 2,anim: 6, time: 1000});
        }
      });
    });
    return false;
  });
  
  form.on('submit(import)', function(data){
    var is_check = false;
    jq("input[lay-filter='checkOne']").each(function(){
      if(jq(this).prop('checked')){ is_check = true; }
    });
    if(!is_check){
      layer.msg('请选择数据', {icon: 2,anim: 6,time: 1000});
      return false;
    }
    var url= jq(this).data('url');
   
    loading = layer.load(2, {
		shade: [0.2,'#000']
	  });
	  var param = data.field;
	
	  jq.post(url,jq('#form_do').serialize(),function(data){
		if(data.status == 1){
		  layer.close(loading);
		  layer.msg(data.info, {icon: 1, time: 1000}, function(){
			location.reload();
		  });
		}else{
		  layer.close(loading);
		  layer.msg(data.info, {icon: 2,anim: 6, time: 1000});
		}
	  });
    return false;
  });
  
  form.on('submit(backup)', function(data){
    var is_check = false;
    jq("input[lay-filter='checkOne']").each(function(){
      if(jq(this).prop('checked')){ is_check = true; }
    });
    if(!is_check){
      layer.msg('请选择数据', {icon: 2,anim: 6,time: 1000});
      return false;
    }
    var url= jq(this).data('url');
   
    loading = layer.load(2, {
		shade: [0.2,'#000']
	  });
	
	  jq.post(url,jq('#form_do').serialize(),function(data){
		if(data.status == 1){
		  layer.close(loading);
		  layer.msg(data.info, {icon: 1, time: 1000}, function(){
			location.href = data.url;
		  });
		}else{
		  layer.close(loading);
		  layer.msg(data.info, {icon: 2,anim: 6, time: 1000});
		}
	  });
    return false;
  });
  
  form.on('submit(optimize)', function(data){
    var is_check = false;
    jq("input[lay-filter='checkOne']").each(function(){
      if(jq(this).prop('checked')){ is_check = true; }
    });
    if(!is_check){
      layer.msg('请选择数据', {icon: 2,anim: 6,time: 1000});
      return false;
    }
    var url= jq(this).data('url');
   
    loading = layer.load(2, {
		shade: [0.2,'#000']
	  });
	
	  jq.post(url,jq('#form_do').serialize(),function(data){
		if(data.status == 1){
		  layer.close(loading);
		  layer.msg(data.info, {icon: 1, time: 1000}, function(){
			location.reload();
		  });
		}else{
		  layer.close(loading);
		  layer.msg(data.info, {icon: 2,anim: 6, time: 1000});
		}
	  });
    return false;
  });
  
  form.on('submit(repair)', function(data){
    var is_check = false;
    jq("input[lay-filter='checkOne']").each(function(){
      if(jq(this).prop('checked')){ is_check = true; }
    });
    if(!is_check){
      layer.msg('请选择数据', {icon: 2,anim: 6,time: 1000});
      return false;
    }
    var url= jq(this).data('url');
   
    loading = layer.load(2, {
		shade: [0.2,'#000']
	  });
	
	  jq.post(url,jq('#form_do').serialize(),function(data){
		if(data.status == 1){
		  layer.close(loading);
		  layer.msg(data.info, {icon: 1, time: 1000}, function(){
			location.reload();
		  });
		}else{
		  layer.close(loading);
		  layer.msg(data.info, {icon: 2,anim: 6, time: 1000});
		}
	  });
    return false;
  });
  
  form.on('submit(allcheck)', function(data){
    var is_check = false;
    jq("input[lay-filter='checkOne']").each(function(){
      if(jq(this).prop('checked')){ is_check = true; }
    });
    if(!is_check){
      layer.msg('请选择数据', {icon: 2,anim: 6,time: 1000});
      return false;
    }
    var url= jq(this).data('url');
   
    loading = layer.load(2, {
		shade: [0.2,'#000']
	  });
	
	  jq.post(url,jq('#form_do').serialize(),function(data){
		if(data.status == 1){
		  layer.close(loading);
		  layer.msg(data.info, {icon: 1, time: 1000}, function(){
			location.reload();
		  });
		}else{
		  layer.close(loading);
		  layer.msg(data.info, {icon: 2,anim: 6, time: 1000});
		}
	  });
    return false;
  });
  
  jq('#optimize').click(function(){
		var url= jq(this).data('url');
		loading = layer.load(2, {
		  shade: [0.2,'#000']
		});
		jq.getJSON(url,function(data){
		  if(data.status == 1){
			layer.close(loading);
			layer.msg(data.info, {icon: 1, time: 1000}, function(){
				 location.reload();
			});
		  }else{
			layer.close(loading);
			layer.msg(data.info, {icon: 2, anim: 6, time: 1000});
		  }
		});
	});
  
  jq('#repair').click(function(){
		var url= jq(this).data('url');
		loading = layer.load(2, {
		  shade: [0.2,'#000']
		});
		jq.getJSON(url,function(data){
		  if(data.status == 1){
			layer.close(loading);
			layer.msg(data.info, {icon: 1, time: 1000}, function(){
				 location.reload();
			});
		  }else{
			layer.close(loading);
			layer.msg(data.info, {icon: 2, anim: 6, time: 1000});
		  }
		});
	});
  
  jq('.btn-click-show').click(function(){
		var iUrl = jq(this).attr('data-url');
		if (iUrl.length > 0) {
			layer.open({
			  type: 2,
			  title: '系统提示',
			  shadeClose: true,
			  shade: false,
			  maxmin: true, //开启最大化最小化按钮
			  area: ['700px', '500px'],
			  content: iUrl
			});
		}
	});
  
});