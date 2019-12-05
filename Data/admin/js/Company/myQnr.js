
var vm = new Vue({
  el: '#app',
  components: {
    'pagination': COMPONENT.PAGINATION,
    'questionnaire-info': COMPONENT.QUESTIONNAIRE_INFO
  },
  data: function () {
    return {
      loadComplete: false,
      query: {
        page: 1,
        limit: 20,
        qnr_name: '',
        min_create_time: '',
        max_create_time: ''
      },
      temp:{
        quesDialog: {
          showDialog: false,
          dialogData: {},
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
        url: APP + '/getMyQnrList',
        type: 'get',
        data: self.query,
      }).then(function (result) {
        self.tableData = result.data;
        self.loadComplete = true;
      })
    },
    quesAction: function (item) {
      var self = this;
      self.temp.quesDialog.dialogData = item;
      self.temp.quesDialog.showDialog = true;
      self.$nextTick(function () {
        self.$refs.myqnrLayer.loadInit();
      })
    }
  }
})