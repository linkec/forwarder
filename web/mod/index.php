<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Forwarder</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <link rel="stylesheet" href="/layui/css/layui.css"  media="all">
</head>
<body>
<style>
    .pad-10{
        padding:10px;
    }
</style>
<div style="margin:10px;">
    <blockquote class="layui-elem-quote">CAUTION：RESTART WILL DISCONNECT ALL CONNECTIONS</blockquote>
    <fieldset class="layui-elem-field">
        <legend>ACTION</legend>
        <div class="pad-10">
          <div class="layui-btn-group">
            <button type="button" class="layui-btn layui-btn-normal" id="addBtn">
              <i class="layui-icon layui-icon-add-1"></i>
            </button>
            <button type="button" class="layui-btn layui-btn-danger" id="delBtn">
              <i class="layui-icon layui-icon-delete"></i>
            </button>
            <button type="button" class="layui-btn layui-btn-normal" id="startBtn">
              <i class="layui-icon layui-icon-play"></i>
            </button>
            <button type="button" class="layui-btn layui-btn-danger" id="pauseBtn">
              <i class="layui-icon layui-icon-pause"></i>
            </button>
            <button type="button" class="layui-btn layui-btn-danger" id="restartBtn">
              <i class="layui-icon layui-icon-refresh"></i>
            </button>
          </div>
        </div>
    </fieldset>
    <table class="layui-hide" id="forwarder_table" lay-filter="forwarder_table"></table>
</div>
<script src="/layui/layui.js" charset="utf-8"></script>
<script>
layui.use(['table','form'], function(){
  var table = layui.table;
  var form = layui.form;
  var $ = layui.$;

  table.render({
    elem: '#forwarder_table'
    ,url:'/?ac=list'
    ,cellMinWidth: 80
    ,cols: [[
      {type:'checkbox'}
      ,{field:'protocol', title: 'Protocol', sort: true}
      ,{field:'local', title: 'Local Address', sort: true}
      ,{field:'localPort', title: 'Local Port', sort: true}
      ,{field:'remote', title: 'Remote Address', sort: true}
      ,{field:'remotePort', title: 'Remote Port', sort: true}
      ,{field:'traffic_in', title: 'Upload', sort: true}
      ,{field:'traffic_out', title: 'Download', sort: true}
      ,{field:'action', title: 'Actions',toolbar:'#subaction'}
    ]]
  });
  table.on('sort(forwarder_table)', function(obj){ 
    table.reload('forwarder_table', {
      initSort: obj
      ,where: {
        field: obj.field
        ,order: obj.type
      }
    });
  });
  table.on('tool(forwarder_table)', function(obj){
    var data = obj.data;
    var layEvent = obj.event;
  
    if(layEvent === 'pause'){
      var index = layer.msg('Waiting for response...', {icon: 16,shade: 0.3,time: 0});
      $.ajax({
        url:'/?ac=pause',
        type:'GET',
        data:"id="+data.id,
        dataType:'json',
        success:function(res){
          layer.close(index);
          if(res.error){
            var html = '';
            layui.each(res.errmsg, function(a,b,c){
              console.log(a,b,c);
                html += b;
            })
            layer.alert(html,{title:'Error',btn:['OK']});
            return;
          }
          layer.msg(res.msg);
          table.reload('forwarder_table');
        }
      })
    } else if(layEvent === 'start'){
      var index = layer.msg('Waiting for response...', {icon: 16,shade: 0.3,time: 0});
      $.ajax({
        url:'/?ac=start',
        type:'GET',
        data:"id="+data.id,
        dataType:'json',
        success:function(res){
          layer.close(index);
          if(res.error){
            var html = '';
            layui.each(res.errmsg, function(a,b,c){
              console.log(a,b,c);
                html += b;
            })
            layer.alert(html,{title:'Error',btn:['OK']});
            return;
          }
          layer.msg(res.msg);
          table.reload('forwarder_table');
        }
      })
    } else if(layEvent === 'del'){
      var index = layer.msg('Waiting for response...', {icon: 16,shade: 0.3,time: 0});
      $.ajax({
        url:'/?ac=del',
        type:'GET',
        data:"id="+data.id,
        dataType:'json',
        success:function(res){
          layer.close(index);
          if(res.error){
            var html = '';
            layui.each(res.errmsg, function(a,b,c){
              console.log(a,b,c);
                html += b;
            })
            layer.alert(html,{title:'Error',btn:['OK']});
            return;
          }
          layer.msg(res.msg);
          table.reload('forwarder_table');
        }
      })
    }
  });
  $('#addBtn').on('click',function(){
    var addPopup = layer.open({
      type: 1,
      title:'Add Forward Rule',
      area:['500px', '400px'],
      resize:false,
      move:false,
      content: $('#addTpl').html(),
      success:function(){
        form.render();
        form.on('submit(addGo)', function(data){
          var index = layer.msg('Waiting for response...', {icon: 16,shade: 0.3,time: 0});
          $.ajax({
            url:'/?ac=add',
            type:'POST',
            data:data.field,
            dataType:'json',
            success:function(res){
              layer.close(index);
              if(res.error){
                var html = '';
                layui.each(res.errmsg, function(a,b,c){
                  console.log(a,b,c);
                    html += b;
                })
                layer.alert(html,{title:'Error',btn:['OK']});
                return;
              }
              table.reload('forwarder_table');
              layer.close(addPopup);
              layer.msg(res.msg);
            }
          })
          return false;
        });
      }
    });
  })
});
</script>
<script type="text/html" id="subaction">
  <div>
    {{# if(d.switch){ }}
    <button type="button" class="layui-btn layui-btn-danger layui-btn-xs" lay-event="pause"><i class="layui-icon layui-icon-pause"></i></button>
    {{# }else{ }}
    <button type="button" class="layui-btn layui-btn-normal layui-btn-xs" lay-event="start"><i class="layui-icon layui-icon-play"></i></button>
    {{# } }}
    <button type="button" class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del"><i class="layui-icon layui-icon-delete"></i></button>
  </div>
</script>

<script type="text/html" id="addTpl">
  <form class="layui-form" action="" style="margin:30px;">
    <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">LocalAddr</label>
          <div class="layui-input-inline" style="width: 180px;">
            <input type="text" name="local" required  lay-verify="required" value="0.0.0.0" placeholder="Local Listen Address" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="number" name="local-port" required  lay-verify="required" value="1" min="1" max="65535" placeholder="Port" autocomplete="off" class="layui-input">
          </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">RemoteAddr</label>
          <div class="layui-input-inline" style="width: 180px;">
            <input type="text" name="remote" required  lay-verify="required" value="0.0.0.0" placeholder="Remote Address" autocomplete="off" class="layui-input">
          </div>
          <div class="layui-input-inline" style="width: 100px;">
            <input type="number" name="remote-port" required  lay-verify="required" value="1" min="1" max="65535" placeholder="Port" autocomplete="off" class="layui-input">
          </div>
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-inline">
          <label class="layui-form-label">Enable</label>
          <div class="layui-input-inline" style="width: 70px;">
            <input type="checkbox" name="switch" lay-skin="switch" checked>
          </div>
          <label class="layui-form-label">Thread</label>
          <div class="layui-input-inline" style="width: 60px;">
            <input type="number" name="thread" required  value=1 lay-verify="required" min="1" max="16" placeholder="Thread" autocomplete="off" class="layui-input">
          </div>
        </div>
    </div>
    <div class="layui-form-item">
      <label class="layui-form-label">Protocol</label>
      <div class="layui-input-block">
        <input type="radio" name="protocol" value="tcp" title="TCP" checked>
        <input type="radio" name="protocol" value="udp" title="UDP">
      </div>
    </div>
    <div class="layui-form-item">
      <div class="layui-input-block">
        <button class="layui-btn" lay-submit lay-filter="addGo">Go</button>
        <button type="reset" class="layui-btn layui-btn-primary">Reset</button>
      </div>
    </div>
  </form>
</script>

</body>
</html>