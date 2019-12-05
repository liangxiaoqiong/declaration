
var vm = new Vue({
  el: '#app',
  components: {
    'pagination': COMPONENT.PAGINATION,
    'member-title-info': COMPONENT.MEMBER_TITLE_INFO,
  },
  data: function () {
    return {
      loadComplete: false,
      queryData: {},
      query: {
        page: 1,
        limit: 20,
        is_show: '0',
      },
      temp: {
        memTitleDialog: {
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
  watch: {
    'query.search_time': function (val) {
      var self = this;
      if (val !== '') {
        self.query.min_create_time = val[0];
        self.query.max_create_time = val[1];
      } else {
        self.query.min_create_time = '';
        self.query.max_create_time = '';
      }
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
        url: APP + '/getMemberTitleList',
        data: self.query,
        contentType: AppUtil.CONTENT_TYPE_JSON,
        type: 'get'
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
    changeMemberTitleSort: function(item){
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/changeMemberTitleSort',
        data: {title_id: item.title_id, sort: item.sort},
      }).then(function (result) {
        self.getTableData();
        layer.msg('修改成功！', {icon: 1, time: 1000});
      }).catch(function () {
        self.getTableData();
      })
    },
    //修改显示||不显示
    changeMemberTitleShow: function (item) {
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/changeMemberTitleShow',
        data: {title_id: item.title_id, is_show: item.is_show},
      }).then(function (result) {
        self.getTableData();
        layer.msg('修改成功！', {icon: 1, time: 1000});
      }).catch(function () {
        self.getTableData();
      })
    },
    //添加||修改弹框
    showInfoLayer: function (type, item) {
      var self = this;
      if(type === 'add'){
        item = {
          "title_name": "",
          "is_show": "1",
          "sort": "1",
        }
      }
      var data = JSON.stringify(item);
      self.temp.memTitleDialog.dialogData = JSON.parse(data);
      self.temp.memTitleDialog.dialogType = type;
      self.temp.memTitleDialog.showDialog = true;
    },
    //删除
    delMemberTitle: function (item) {
      var self = this;
      layer.confirm('', {title: '确定删除该职务？', skin: 'layer-confirm'}, function (index) {
        AppUtil.ajaxRequest({
          url: APP + '/deleteMemberTitle',
          data: {title_id: item.title_id},
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