
var vm = new Vue({
  el: '#app',
  components: {
    'pagination': COMPONENT.PAGINATION,
    'datetime-range': COMPONENT.DATETIME_RANGE,
    'expert-info': COMPONENT.EXPERT_INFO,
  },
  data: function () {
    return {
      loadComplete: false,
      query: {
        page: 1,
        limit: 20,
        expert_name: '',//专家姓名
        expert_title: '',//专家职称
        is_show: '0',
        min_create_time: '',
        max_create_time: '',
        search_time: '',
      },
      temp: {
        expertDialog: {
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
        url: APP + '/getExpertList',
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
        title_id: '',//职务ID
        member_name: '',//会员名称
        short_name: '',//会员简称
        is_show: '0',
        is_recommend: '',
        min_create_time: '',
        max_create_time: '',
        search_time: '',
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
    changeExpertSort: function(item){
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/changeExpertSort',
        data: {expert_id: item.expert_id, sort: item.sort},
      }).then(function (result) {
        self.getTableData();
        layer.msg('修改成功！', {icon: 1, time: 1000});
      }).catch(function () {
        self.getTableData();
      })
    },
    //修改显示||不显示
    changeExpertShow: function (item) {
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/changeExpertShow',
        data: {expert_id: item.expert_id, is_show: item.is_show},
      }).then(function (result) {
        self.getTableData();
        layer.msg('修改成功！', {icon: 1, time: 1000});
      }).catch(function () {
        self.getTableData();
      })
    },
    //添加||修改弹框
    showExpertInfoLayer: function (type, item) {
      var self = this;
      if(type === 'add'){
        item = {
          "expert_name": "",
          "expert_title": "",
          "expert_logo": "",
          "expert_desc": "",
          "is_show": "1",
          "sort": "1",
        }
      }
      var data = JSON.stringify(item);
      self.temp.expertDialog.dialogData = JSON.parse(data);
      self.temp.expertDialog.dialogType = type;
      self.temp.expertDialog.showDialog = true;
    },
    //删除
    delExpert: function (item) {
      var self = this;
      layer.confirm('', {title: '确定删除该专家？', skin: 'layer-confirm'}, function (index) {
        AppUtil.ajaxRequest({
          url: APP + '/deleteExpert',
          data: {
            expert_id: item.expert_id
          },
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