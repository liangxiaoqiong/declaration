var vm = new Vue({
  el: '#app',
  components: {
    'pagination': COMPONENT.PAGINATION,
    'apply-info': COMPONENT.APPLY_INFO,
  },
  data: function () {
    return {
      loadComplete: false,
      query: {
        page: 1,
        limit: 20,
        audit_status: '',//审核结果
        company_name: '',//企业名称
      },
      temp: {
        applyDialog: {
          showDialog: false,
          dialogData: {},
          dialogType: '',//edit,view,add
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
        url: APP + '/getOldMangeDataApplyList',
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
        audit_status: '',
        company_name: '',
      }
      this.query = query;
    },
    //导出数据申报列表
    exportApplyList: function () {
      var self = this;
      var searchData = '&audit_status=' + self.query.audit_status + '&company_name' + self.query.company_name;
      window.location.href = APP + '/exportOldManageDataApplyList' + searchData;
    },
    dataApplyView: function (item) {
      this.temp.applyDialog.dialogData = item;
      this.temp.applyDialog.showDialog = true;
    }
  }
})