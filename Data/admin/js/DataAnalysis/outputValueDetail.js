
var vm = new Vue({
  el: '#app',
  components: {
    'pagination': COMPONENT.PAGINATION
  },
  data: function () {
    return {
      companyQuery: {},
      query: {
        company_form: "",//string,公司性质id
        is_high_new: "",//"string,是否为高薪企业(1:高薪)",
        capital_form: "",//"string,公司所有制形式id",
        is_hot: "",//"string,是否为热门企业(1:热门)",
        area_id_1: "350000",//"string,省id",
        area_id_2: "0",//"string,市id",
        area_id_3: "0",//"string,区id",
        start_time: "",//"string,搜索开始时间",
        end_time: "",//"string,搜索结束时间",
        unit: '',
        page: 1,//"string,分页",
        limit: 20,//"string,每页数量",
      },
      tableData: {}
    }
  },
  filters: {
    /**时间过滤器*/
    formatDate: function (value) {
      return publicObj.formatDate(new Date(value * 1000));
    },
    /**时间过滤器*/
    formatDate_: function (value) {
      return publicObj.dateTime_Str((new Date(+value * 1000)), 'date');
    }
  },
  mounted: function(){
    this.load();
  },
  methods: {
    load: function(){
      var self = this;
      self.getSelectData().then(function (result) {
        self.companyQuery = result.data;
        self.getTableData();
      })
    },
    //获取选项值
    getSelectData: function () {
      return AppUtil.ajaxRequest({
        url: APP + '/getSelectData',
      })
    },
    //获取表格数据
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
        url: APP + '/getOutputValueDetailData',
        data: self.query,
      }).then(function (result) {
        self.tableData = result.data;
        self.$nextTick(function () {
          setcity('province', 'city', 'area', self.query.area_id_1, self.query.area_id_2, self.query.area_id_3);
        })
      })
    },
    //清空搜索框
    resetSearch: function(){
      var self = this;
      var query = {
        company_form: "",//string,公司性质id
        is_high_new: "",//"string,是否为高薪企业(1:高薪)",
        capital_form: "",//"string,公司所有制形式id",
        is_hot: "",//"string,是否为热门企业(1:热门)",
        area_id_1: "350000",//"string,省id",
        area_id_2: "0",//"string,市id",
        area_id_3: "0",//"string,区id",
        start_time: "",//"string,搜索开始时间",
        end_time: "",//"string,搜索结束时间",
        unit: '',
      }
      self.query = query;
    },
    changeEndTime: function(){
      var self = this;
      var time_1 = self.query.start_time.split('-');
      var time_2 = self.query.end_time.split('-');
      if(self.query.start_time === ''){
        layer.msg('请先选择起始年份', {icon: 2, anim: 6, time: 1000});
        self.query.end_time = '';
        return false;
      }
      if(self.query.start_time !== '' && self.query.end_time !== ''){
        if(+time_1[0] !== +time_2[0]){
          layer.msg('起始年份和结束年份年度必须一致', {icon: 2, anim: 6, time: 1000});
          self.query.end_time = '';
          return false;
        }
      }
    },
    //导出表格
    exportOutputValueDetailData: function () {
      var self = this;
      var searchData = '&company_form=' + self.query.company_form + '&is_high_new' + self.query.is_high_new + '&capital_form=' + self.query.capital_form + '&is_hot=' + self.query.is_hot + '&area_id_1=' + self.query.area_id_1 + '&area_id_2=' + self.query.area_id_2 + '&area_id_3=' + self.query.area_id_3 + '&start_time=' + self.query.start_time + '&end_time=' + self.query.end_time;
      window.location.href = APP + '/exportOutputValueDetailData' + searchData;
    }
  }
})