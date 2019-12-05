
var vm = new Vue({
  el: '#app',
  components: {
    'pagination': COMPONENT.PAGINATION,
    'link-info': COMPONENT.LINK_INFO,
  },
  data: function () {
    return {
      loadComplete: false,
      query: {
        page: 1,
        limit: 20,
        link_name: '',
        is_show: '0',
      },
      temp: {
        editData: {},
        linkDialog: {
          showDialog: false,
          dialogData: {},
          dialogType: ''
        }
      },
      tableData: {}
    }
  },
  filters: {
    /**时间过滤器*/
    formatDate: function (value) {
      return publicObj.formatDate(new Date(value * 1000));
    }
  },
  mounted: function () {
    this.getTableData();
  },
  methods: {
    getTableData: function (current) {
      var self = this;
      if (typeof current !== 'undefined') {
        if (typeof current.page !== 'undefined') {
          self.query.page = current.page;
        }
        if (typeof current.limit !== 'undefined') {
          self.query.limit = current.limit;
        }
      }
      AppUtil.ajaxRequest({
        url: APP + '/getLinkList',
        data: self.query,
      }).then(function (result) {
        self.tableData = result.data;
        self.loadComplete = true;
      })
    },
    //清空搜索条件
    resetSearch: function(){
      var query = {
        page: 1,
        limit: 20,
        link_name: '',
        is_show: '0',
      }
      this.query = query;
    },
    sortVerify: function(item){
      if (item.sort.length == 1) {
        item.sort = item.sort.replace(/[^0-9]/g, '')
      } else {
        item.sort = item.sort.replace(/\D/g, '')
      }
    },
    //修改排序
    changeSort: function(item){
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/changeLinkSort',
        data: {link_id: item.link_id, sort: item.sort},
      }).then(function (result) {
        self.getTableData();
        layer.msg('修改成功！', {icon: 1, time: 1000});
      }).catch(function () {
        self.getTableData();
      })
    },
    //修改显示||不显示
    changeLinkShow: function (item) {
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/changeLinkShow',
        data: {link_id: item.link_id, is_show: item.is_show},
      }).then(function (result) {
        self.getTableData();
        layer.msg('修改成功！', {icon: 1, time: 1000});
      }).catch(function () {
        self.getTableData();
      })
    },
    //添加||修改链接弹框
    showLinkInfoLayer: function (type, item) {
      var self = this;
      if(type === 'add'){
        item = {
          "link_url": "",
          "link_name": "",
          "sort": "1",
          "is_show": "1",
        }
      }
      var data = JSON.stringify(item);
      self.temp.linkDialog.dialogData = JSON.parse(data);
      self.temp.linkDialog.showDialog = true;
      self.temp.linkDialog.dialogType = type;
    },
    //删除链接
    delLink: function (item) {
      var self = this;
      layer.confirm('', {title: '确定删除该链接？', skin: 'layer-confirm'}, function (index) {
        AppUtil.ajaxRequest({
          url: APP + '/deleteLink',
          data: {link_id: item.link_id},
        }).then(function (result) {
          self.getTableData();
          layer.msg('删除成功！', {icon: 1, time: 1000});
          layer.close(index);
        }).catch(function () {
          self.getTableData();
        })
      })
    }
  }
})