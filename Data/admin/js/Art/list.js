
var vm = new Vue({
  el: '#app',
  components: {
    'pagination': COMPONENT.PAGINATION,
    'datetime-range': COMPONENT.DATETIME_RANGE,
    'art-info': COMPONENT.ART_INFO,
  },
  data: function () {
    return {
      loadComplete: false,
      queryData: {
        navList: [],
      },
      query: {
        page: 1,
        limit: 20,
        nav_id: '',//所属导航ID
        article_title: '',
        is_show: '',
        min_create_time: '',
        max_create_time: '',
        search_time: '',
      },
      temp: {
        artDialog: {
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
  computed: {
    navName: function () {
      var self = this;
      return function (item) {
        var name = '无';
        if(+item.nav_id !== 0){
          self.queryData.navList.forEach(function (value) {
            if(+value.nav_id === +item.nav_id){
              name = value.nav_name;
            }
          })
        }
        return name;
      }
    }
  },
  mounted: function () {
    this.loadInit();
  },
  methods: {
    loadInit: function () {
      var self = this;
      self.getSelectData().then(function (result) {
        self.queryData.navList = result.data.nav_list
        self.getTableData()
      })
    },
    //获取选项值
    getSelectData: function () {
      return AppUtil.ajaxRequest({
        url: APP + '/getSelectData',
      })
    },
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
        url: APP + '/getArtList',
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
        nav_id: '',//所属导航ID
        article_title: '',
        is_show: '',
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
    changeArtSort: function(item){
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/changeArtSort',
        data: {article_id: item.article_id, sort: item.sort},
      }).then(function (result) {
        self.getTableData();
        layer.msg('修改成功！', {icon: 1, time: 1000});
      }).catch(function () {
        self.getTableData();
      })
    },
    //修改显示||不显示
    changeArtShow: function (item) {
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/changeArtShow',
        data: {article_id: item.article_id, is_show: item.is_show},
      }).then(function (result) {
        self.getTableData();
        layer.msg('修改成功！', {icon: 1, time: 1000});
      }).catch(function () {
        self.getTableData();
      })
    },
    //添加||修改弹框
    showArtInfoLayer: function (type, item) {
      var self = this;
      if(type === 'add'){
        item = {
          "article_title": "",
          "nav_id": "",
          "member_id": "",
          "article_desc": "",
          "article_img": "",
          "sort": "1",
          "content": "",
          "is_show": "1"
        }
      }
      var data = JSON.stringify(item);
      self.temp.artDialog.dialogData = JSON.parse(data);
      self.temp.artDialog.dialogType = type;
      self.temp.artDialog.showDialog = true;
      self.$nextTick(function () {
        self.$refs.artInfoLayer.loadInit()
      })
    },
    //删除文章
    delArt: function (item) {
      var self = this;
      layer.confirm('', {title: '确定删除该篇文章？', skin: 'layer-confirm'}, function (index) {
        AppUtil.ajaxRequest({
          url: APP + '/deleteArt',
          data: {article_id: item.article_id},
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