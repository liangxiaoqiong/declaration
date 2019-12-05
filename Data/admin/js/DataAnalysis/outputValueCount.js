var myChart;
var option;
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
        page: 1,//"string,分页",
        limit: 20,//"string,每页数量",
      },
      tableData: {},
      eChartData: {
        'timeList': [''],
        'dataList': [
          {
            name: '',
            type: 'bar',
            data: [/*210000, 30000, 20000, 10000, 40000*/],
          }
        ]
      }
    }

  },
  filters: {
    /**时间过滤器*/
    formatDate: function (value) {
      return publicObj.formatDate(new Date(value * 1000));
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
    eChart: function(){
      var self = this;
      self.$set(self.eChartData['dataList'][0].data, 0, self.tableData.total_income_output_value)
      self.$set(self.eChartData['dataList'][0].data, 1, self.tableData.total_export_value)
      self.$set(self.eChartData['dataList'][0].data, 2, self.tableData.total_investment_value)
      self.$set(self.eChartData['dataList'][0].data, 3, self.tableData.total_profit_value)
      self.$set(self.eChartData['dataList'][0].data, 4, self.tableData.total_taxes_value)
      self.$set(self.eChartData['dataList'][0].data, 5, 0)
      self.$set(self.eChartData['dataList'][0].data, 6, 0)
      self.$set(self.eChartData['dataList'][0].data, 7, 0)
      self.$set(self.eChartData['dataList'][0].data, 8, 0)
      myChart = echarts.init(document.getElementById('flow-dome'));
      option = {
        title : {
          text: '产值分析汇总情况',
          subtext: '单位(元)'
        },
        tooltip : {
          trigger: 'axis'
        },
        legend: {
          data:self.eChartData.timeList
        },
        toolbox: {
          show : true,
          feature : {
            dataView : {show: true, readOnly: false},
            magicType : {show: true, type: ['line', 'bar']},
            restore : {show: true},
            saveAsImage : {show: true}
          }
        },
        calculable : true,
        xAxis : [
          {
            type : 'category',
            data : ['产值总额','出口额总值','投资额总值','利润总额','税收总额']
          }
        ],
        yAxis : [
          {
            type : 'value'
          }
        ],
        series : self.eChartData.dataList
      };
      myChart.setOption(option);
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
        url: APP + '/getOutputData',
        data: self.query,
      }).then(function (result) {
        self.tableData = result.data;
        self.$nextTick(function () {
          setcity('province', 'city', 'area', self.query.area_id_1, self.query.area_id_2, self.query.area_id_3);
          self.eChart();
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
      }
      self.query = query;
    },
    //导出表格
    exportOutputData: function () {
      var self = this;
      var searchData = '&company_form=' + self.query.company_form + '&is_high_new' + self.query.is_high_new + '&capital_form=' + self.query.capital_form + '&is_hot=' + self.query.is_hot + '&area_id_1=' + self.query.area_id_1 + '&area_id_2=' + self.query.area_id_2 + '&area_id_3=' + self.query.area_id_3 + '&start_time=' + self.query.start_time + '&end_time=' + self.query.end_time;
      window.location.href = APP + '/exportOutputData' + searchData;
    }
  }
})