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
    /*添加、编辑角色弹框*/
    roleInfoLayer:function () {
      publicObj.layerShow(2,'添加、编辑角色','/admin.php?s=/Index/roleInfo')
    },
    /*删除角色*/
    roleDel:function () {
      layer.confirm('如有该角色的员工，删除后将无角色权限，可编辑员工重新选择角色！',{title:'删除角色？',skin:'layer-confirm'},function (index) {
        publicObj.layerMsg('删除成功',1);
      })
    }
  }
})