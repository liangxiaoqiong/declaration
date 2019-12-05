var vm = new Vue({
  el: '#app',
  components: {
    'pagination': COMPONENT.PAGINATION,
    'datetime-range': COMPONENT.DATETIME_RANGE,
    'send-email': COMPONENT.SEND_EMAIL_COMPONENTS,
    'audit-info': COMPONENT.COMPANYMANAGE_AUDIT_INFO
  },
  data: function () {
    return {
      loadComplete: false,
      query: {
        page: 1,
        limit: 20,
        audit_status: '',//审核状态0-全部 1-待审核 2-已通过 3-拒绝
        company_name: '',//企业名称
        data_apply_status: '',//申报状态0-全部 1-未申报 2-已申报
        min_auth_time: '',//认证时间。最小值。YYYY-MM-DD H:i:s
        max_auth_time: '',//认证时间。最大值。YYYY-MM-DD H:i:s
        search_time: '',//认证时间
      },
      temp: {
        auditDialog: {
          showDialog: false,
          dialogData: {},
        },
        sendEmailDialog: {
          showDialog: false,
          company_id: ''
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
        self.query.min_auth_time = val[0];
        self.query.max_auth_time = val[1];
      } else {
        self.query.min_auth_time = '';
        self.query.max_auth_time = '';
      }
    }
  },
  mounted: function () {
    this.getTableData();
  },
  methods: {
    //获取审核列表
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
        url: APP + '/getAuditList',
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
        data_apply_status: '',
        min_auth_time: '',
        max_auth_time: '',
        search_time: ''
      }
      this.query = query;
    },
    //导出审核列表
    exportAuditList: function () {
      var self = this;
      var searchData = '&audit_status=' + self.query.audit_status + '&company_name' + self.query.company_name + '&min_auth_time=' + self.query.min_auth_time + '&max_auth_time=' + self.query.max_auth_time;
      window.location.href = APP + '/exportAuditList' + searchData;
    },
    //查看审核列表
    declareView: function (item) {
      var self = this;
      self.temp.auditDialog.dialogData = item;
      self.temp.auditDialog.showDialog = true;
      self.$nextTick(function () {
        self.$refs.auditLayer.loadInit();
      })
    },
    //发送邮件
    sendEmail: function (item) {
      var self = this;
      self.temp.sendEmailDialog.showDialog = true;
      self.temp.sendEmailDialog.company_id = item.company_id;
    }
  }
})