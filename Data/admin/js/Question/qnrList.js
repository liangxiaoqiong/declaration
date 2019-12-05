
var vm = new Vue({
  el: '#app',
  components: {
    'pagination': COMPONENT.PAGINATION,
    'datetime-range': COMPONENT.DATETIME_RANGE,
    'qnr-set-info': COMPONENT.QUESTIONNAIRE_SET_INFO,
    'qnr-answer-list': COMPONENT.QNR_ANSWER_LIST
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
  data: function () {
    return {
      loadComplete: false,
      query: {
        page: 1,
        limit: 20,
        qnr_name: '',
        min_create_time: '',
        max_create_time: '',
        search_time: ''
      },
      temp:{
        //问卷弹框
        quesSetDialog: {
          showDialog: false,
          dialogData: {},
          dialogType: '',//edit,add
        },
        //答卷列表弹框
        qnrAnswerData: {
          showDialog: false,
          dialogData: {},
        }
      },
      tableData: {}
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
        url: APP + '/getQnrList',
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
        qnr_name: '',
        min_create_time: '',
        max_create_time: '',
        search_time: ''
      }
      this.$emit('pick', '')
      this.query = query;
    },
    quesSetAction: function (actionType, item) {
      var self = this;
      var data = JSON.stringify(item);
      self.temp.quesSetDialog.dialogData = JSON.parse(data);
      self.temp.quesSetDialog.showDialog = true;
      self.temp.quesSetDialog.dialogType = actionType;
      self.$nextTick(function () {
        self.$refs.qnrLayer.loadInit();
      })
    },
    //修改开启状态
    changeOpen: function (item) {
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/changeQnrOpen',
        data: JSON.stringify({qnr_id: item.qnr_id, is_open: item.is_open}),
        contentType: AppUtil.CONTENT_TYPE_JSON,
      }).then(function (result) {
        if(+result.code !== 200){
          item.is_open = +item.is_open === 1 ? 0 : 1;
        }
      }).catch(function () {
        item.is_open = +item.is_open === 1 ? 0 : 1;
      })
    },
    //修改强制状态
    changeForce: function (item) {
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/changeQnrForce',
        data: JSON.stringify({qnr_id: item.qnr_id, is_force: item.is_force}),
        contentType: AppUtil.CONTENT_TYPE_JSON,
      }).then(function (result) {
        if(+result.code !== 200){
          item.is_force = +item.is_force === 1 ? 0 : 1;
        }
      }).catch(function () {
        item.is_force = +item.is_force === 1 ? 0 : 1;
      })
    },
    //显示答卷列表tank
    showQnrAnswer: function (item) {
      var self = this;
      if(+item.num > 0){
        self.temp.qnrAnswerData.dialogData = item;
        self.temp.qnrAnswerData.showDialog = true;
        self.$nextTick(function () {
          self.$refs.qnrAnswerLayer.loadInit();
        })
      }
    }
  }
})