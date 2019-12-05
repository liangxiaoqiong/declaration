
var vm = new Vue({
  el: '#app',
  components: {
    'pagination': COMPONENT.PAGINATION,
    'datetime-range': COMPONENT.DATETIME_RANGE,
    'member-info': COMPONENT.MEMBER_INFO,
  },
  data: function () {
    return {
      loadComplete: false,
      memberTitleList: [],
      query: {
        page: 1,
        limit: 20,
        title_id: '',//职务ID
        member_name: '',//会员名称
        short_name: '',//会员简称
        is_show: '0',
        is_recommend: '0',
        min_create_time: '',
        max_create_time: '',
        search_time: '',
      },
      temp: {
        memberDialog: {
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
    memberTitleName: function () {
      var self = this;
      return function (item) {
        var name = '无';
        self.memberTitleList.forEach(function (value) {
          if(+value.title_id === +item.title_id){
            name = value.title_name
          }
        })
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
        self.memberTitleList = result.data.member_title_list;
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
        url: APP + '/getMemberList',
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
        title_id: '0',//职务ID
        member_name: '',//会员名称
        short_name: '',//会员简称
        is_show: '0',
        is_recommend: '0',
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
    changeMemberSort: function(item){
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/changeMemberSort',
        data: {member_id: item.member_id, sort: item.sort},
      }).then(function (result) {
        self.getTableData();
        layer.msg('修改成功！', {icon: 1, time: 1000});
      }).catch(function () {
        self.getTableData();
      })
    },
    //修改显示||不显示
    changeMemberShow: function (item) {
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/changeMemberShow',
        data: {member_id: item.member_id, is_show: item.is_show},
      }).then(function (result) {
        self.getTableData();
        layer.msg('修改成功！', {icon: 1, time: 1000});
      }).catch(function () {
        self.getTableData();
      })
    },
    //修改推荐||不推荐
    changeMemberRecommend: function (item) {
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/changeMemberRecommend',
        data: {member_id: item.member_id, is_recommend: item.is_recommend},
      }).then(function (result) {
        self.getTableData();
        layer.msg('修改成功！', {icon: 1, time: 1000});
      }).catch(function () {
        self.getTableData();
      })
    },
    //添加||修改弹框
    showMemberInfoLayer: function (type, item) {
      var self = this;
      if(type === 'add'){
        item = {
          "member_name": "",
          "short_name": "",
          "logo": "",
          "sort": "1",
          "is_recommend": "1",
          "content": "",
          "is_show": "1",
          "title_id": "",
          "article_id": "",
        }
      }
      var data = JSON.stringify(item);
      self.temp.memberDialog.dialogData = JSON.parse(data);
      self.temp.memberDialog.dialogType = type;
      self.temp.memberDialog.showDialog = true;
      self.$nextTick(function () {
        self.$refs.memberInfoLayer.loadInit()
      })
    },
    //删除
    delMember: function (item) {
      var self = this;
      layer.confirm('', {title: '确定删除该会员？', skin: 'layer-confirm'}, function (index) {
        AppUtil.ajaxRequest({
          url: APP + '/deleteMember',
          data: {member_id: item.member_id},
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