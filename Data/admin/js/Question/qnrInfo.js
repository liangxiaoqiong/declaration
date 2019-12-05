
COMPONENT.QUESTIONNAIRE_SET_INFO = {
  template: '#qnr_set_info',
  components:{},
  props: {
    quesSetData: Object
  },
  data: function () {
    return {
      temp: {
        inputVisible: false,
        inputValue: ''
      },
      qnrData: {
        qnr_name: '',
        is_open: '1',
        is_force: '1',
        questions: []
      }
    }
  },
  methods: {
    loadInit: function(){
      var self = this;
      if(self.quesSetData.dialogData.qnr_id && self.quesSetData.dialogData.qnr_id !== ''){
        var loading = layer.load(2, {shade: [0.2, '#000']});
        self.getQnrData().then(function (result) {
          self.qnrData = result.data;
          layer.close(loading);
        })
      }
    },
    getQnrData: function(){
      var self = this;
      return AppUtil.ajaxRequest({
        url: APP + '/getQnr',
        type: 'get',
        contentType: AppUtil.CONTENT_TYPE_JSON,
        data: {qnr_id: self.quesSetData.dialogData.qnr_id},
      })
    },
    //修改题目
    editTopic: function(item){
      layer.prompt({
        formType: 2,
        value: item.question_title,
        skin: 'layer-confirm',
        title: '修改题目',
        area: ['400px', '200px'] //自定义文本域宽高
      }, function(value, index, elem){
        //alert(value); //得到value
        item.question_title = value;
        layer.close(index);
      });
    },
    //新增题目
    addQuestion: function(){
      var self = this;
      layer.prompt({
        formType: 2,
        value: '',
        skin: 'layer-confirm',
        title: '输入题目',
        area: ['400px', '200px'] //自定义文本域宽高
      }, function(value, index, elem){
        var ques = {
          question_title: value,
          answer_type: '0',
          is_show: '1',
          is_require: '1',
          options: []
        }
        self.qnrData.questions.push(ques);
        layer.close(index);
      });
    },
    //删除题目
    delQuestion: function(key){
      var self = this;
      layer.confirm('', {title: '确定删除该题目？', skin: 'layer-confirm'},function (index) {
        self.qnrData.questions.splice(key, 1);
        layer.msg('删除成功！', {icon: 1, time: 1000});
        layer.close(index);
      })
    },
    //上移排序题目
    moveUpQuestion: function(qnrCurrent, index){
      var self = this;
      var quesArr = self.qnrData.questions;
      var length = quesArr.length;
      console.log(index+1);
      if(index === 0){
        layer.msg('已经处于置顶，无法上移', {icon: 2, anim: 6, time: 1000});
        return false;
      }else{
        self.swapArray(index, index-1, qnrCurrent);
      }
    },
    //下移排序题目
    moveDownQuestion: function(qnrCurrent, index){
      var self = this;
      var quesArr = self.qnrData.questions;
      var length = quesArr.length;
      if(index+1 >= length){
        layer.msg('已经处于置底，无法下移', {icon: 2, anim: 6, time: 1000});
        return false;
      }else{
        self.swapArray(index, index+1, qnrCurrent);
      }
    },
    //替换json值对换
    swapArray: function(indexCurrent, indexNext, qnrCurrent){
      var self = this;
      var quesArr = self.qnrData.questions;
      var qnrNext = self.qnrData.questions[indexNext];
     // quesArr.splice(indexCurrent, 1);
      quesArr.splice(indexNext, 1, qnrCurrent);
      quesArr.splice(indexCurrent, 1, qnrNext);
    },
    //删除选项值
    delOption: function(option, index) {
      option.splice(index, 1);
    },
    //新增选项值-聚焦
    addOption: function(index) {
      var self = this;
      self.temp.inputVisible = true;
      self.$nextTick(function (){
        $('#saveTagInput'+index).focus();
      });
    },
    //新增选项值-完成
    handleInputConfirm: function(option) {
      var inputValue = this.temp.inputValue;
      if (inputValue) {
        option.push({option_value: inputValue});
      }
      this.temp.inputVisible = false;
      this.temp.inputValue = '';
    },
    submitQnr: function () {
      var self = this;
      if(self.qnrData.qnr_name === ''){
        layer.msg('问卷名称不能为空', {icon: 2, anim: 6, time: 1000});
        return false;
      }
      if(self.quesSetData.dialogType === 'edit') {
        self.saveQnr();
      }else{
        self.addQnr();
      }

    },
    saveQnr: function () {
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/updateQnr',
        data: JSON.stringify(self.qnrData),
        contentType: AppUtil.CONTENT_TYPE_JSON,
      }).then(function (result) {
        layer.msg('修改成功', {icon: 1, time: 1000});
        self.quesSetData.showDialog = false;
        self.$emit('page-event');
      })
    },
    addQnr: function () {
      var self = this;
      AppUtil.ajaxRequest({
        url: APP + '/addQnr',
        data: JSON.stringify(self.qnrData),
        contentType: AppUtil.CONTENT_TYPE_JSON,
      }).then(function (result) {
        layer.msg('添加成功', {icon: 1, time: 1000});
        self.quesSetData.showDialog = false;
        self.$emit('page-event');
      })
    }
  }
}