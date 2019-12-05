/**
 * @author   liangxiaoqiong
 * @version  1.0
 * @date 2018-04-20.
 */
'use strict';
var vm = new Vue({
  el:'#app',
  data:function () {
    return{}
  },
  mounted:function () {

  },
  methods:{
    /*添加、编辑员工弹框*/
    staffInfoLayer:function () {
      publicObj.layerShow(2,'添加、编辑员工','/admin.php?s=/Index/staffInfo')
    },
    /*删除员工*/
    staffDel:function () {
      layer.confirm('',{title:'确定删除该员工账号？',skin:'layer-confirm'},function (index) {
        publicObj.layerMsg('删除成功',1);
      })
    }
  }
})