COMPONENT.QUESTIONNAIRE_INFO = {
  template: '#questionnaire_info',
  components: {
    'question': COMPONENT.QUESTION_COMPONENTS,
  },
  props: {
    propData: Object
  },
  data: function () {
    return {
      myQnrData: {
        questions: []
      },
      myAnswerData: {
        answer_id: 0, // 答案ID
        qnr_id: 0, // 问卷ID
        detail: [], // 答案详情
      },
    }
  },
  computed: {
    //是否必填true.不可关闭弹框
    isForce: function(){
      if(+this.myQnrData.is_force === 1){
        return true;
      }else{
        return false;
      }
    }
  },
  methods: {
    loadInit: function () {
      var self = this;
      var loading = layer.load(2, {shade: [0.2, '#000']});
      // 1. 我的问卷列表页用这个组件时,能获取到answer_id，如果有answer_id，调用的接口不同。同时父组件传递的数据是quesData，这里都没有定义
      // 2. 首页使用这个组件的时候，只能获取到qnr_id。 而且首页使用这个组件的时候,父组件传递的数据是propData。
      if(typeof (self.propData.isIndex) && self.propData.isIndex){
        //首页
        self.myQnrData = self.propData.dialogData
        self.myAnswerData.qnr_id = self.myQnrData.qnr_id
        layer.close(loading);
      }else{
        self.getMyQnr().then(function (result) {
          self.myQnrData = result.data.qnr_data;
          self.myAnswerData = result.data.answer_data;
          layer.close(loading);
        })
      }
    },
    getMyQnr: function () {
      var self = this;
      return AppUtil.ajaxRequest({
        url: APP + '/getMyQnr',
        type: 'get',
        data: {
          answer_id: self.propData.dialogData.answer_id,
        },
      })
    },

    syncQnr: function () {
      var self = this
      AppUtil.ajaxRequest({
        url: APP + '/syncQnr',
        type: 'get',
        data: {
          qnr_id: self.myQnrData.qnr_id,
        },
      }).then(function (result) {
        self.myQnrData = result.data
        layer.msg('问卷同步成功！', {icon: 1, anim: 6, time: 1000});
      }).catch(function () {

      })
    },

    //提交问卷
    submitMyQnr: function () {
      var self = this;
      var action = self.myAnswerData.answer_id > 0 ? '/updateAnswerQnr' : '/addAnswerQnr'
      AppUtil.ajaxRequest({
        url: APP + action,
        data: JSON.stringify(self.myAnswerData),
        contentType: AppUtil.CONTENT_TYPE_JSON,
      }).then(function (result) {
        layer.msg('问卷提交成功', {icon: 1, anim: 6, time: 1000});
        self.propData.showDialog = false;
      }).catch(function () {

      })
    },
  }
}