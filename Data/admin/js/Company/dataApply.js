
var vm = new Vue({
  el: '#app',
  components: {
    'pagination': COMPONENT.PAGINATION,
    'my-apply-info': COMPONENT.MY_APPLY_INFO,
    'datetime-range': COMPONENT.DATETIME_RANGE,
    'my-apply-modify': COMPONENT.MY_APPLY_MODIFY,
  },
  data: function () {
    return {
      loadComplete: false,
      query: {
        page: 1,
        limit: 20,
        audit_status: '',//0-全部 1-待审核 2-通过 3-拒绝
        min_apply_time: '',
        max_apply_time: '',
      },
      temp:{
        myApplyDialog: {
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
      return publicObj.dateTime_Str(new Date(value * 1000));
    },
    /**时间过滤器*/
    formatMouth: function (value) {
      return publicObj.dateTime_Str((new Date(+value * 1000)), 'mouth');
    }
  },
  mounted: function(){
    this.getTableData();
  },
  methods: {
    getTableData: function (current) {
      var self = this;
      if(typeof current !== 'undefined'){
        if(typeof current.page !== 'undefined'){
          self.query.page = current.page;
        }
        if(typeof current.limit !== 'undefined'){
          self.query.limit = current.limit;
        }
      }
      AppUtil.ajaxRequest({
        url: APP + '/getDataApplyList',
        data: self.query,
      }).then(function (result) {
        self.tableData = result.data;
        self.loadComplete = true;
      })
    },
    //导出数据申报列表
    exportMyApplyList: function(){
      var self = this;
      var searchData = '&audit_status='+self.query.audit_status+'&min_apply_time='+self.query.min_apply_time+'&max_apply_time='+self.query.max_apply_time;
      window.location.href = 'admin.php?s=/exportDataApplyList'+searchData;
    },
    myApplyAction: function (actionType, item) {
      var self  =this;
      switch (actionType) {
        case 'add':
          var date = (new Date(publicObj.getPreMonth()))/1000;
          self.temp.myApplyDialog.dialogData = {
            "apply_time": date,//"string,申报时间。年月的时间戳",
            "income_output_value": "",//"string,营业收入产值(万元)。整数",
            "export_value": "",//"string,出口额总额(万元)。整数",
            "investment_value": "",//"string,投资额总价(万元)",
            "profit_value": "",//"string,利润总额(万元)",
            "taxes_value": "",//"string,纳税总额(万元)",
            "dev_people_value": "",//"string,本月研发人员数",
            "invention_patent_value": "",//"string,发明专利",
            "utility_patent_value": "",//"string,实用新型专利",
            "facade_patent_value": "",//"string,外观设计专利",
            "software_copyright_value": "",//"string,软件著作权",
            "other_value": "",//"string,其他",
          };
          break;
        case 'edit':
          var data = JSON.stringify(item);
          self.temp.myApplyDialog.dialogData = JSON.parse(data);
          break;
        case 'view':
          self.temp.myApplyDialog.dialogData = item;
          break;
        default:
          break;
      }
      self.temp.myApplyDialog.dialogType = actionType;
      self.temp.myApplyDialog.showDialog = true;
    }
  }
})