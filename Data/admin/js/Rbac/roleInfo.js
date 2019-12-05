/**
 * @author   liangxiaoqiong
 * @version  1.0
 * @date 2018-04-20.
 */
'use strict';
var vm = new Vue({
  el:'#app',
  data:function () {
    return {}
  },
  mounted:function () {
  },
  methods:{
    //点击确定保存角色
    saveRole:function () {

    },
    selectAction:function (event) {
      var el = event.currentTarget;
      /*if($(el).prop('checked')){

      }*/
      console.log($(el).prop('checked'));
    }
  }
})

