var vm = new Vue({
  el: '#app',
  components: {
    'upload-file': COMPONENT.UPLOAD_FILE_COMPONENT
  },
  data: function () {
    /**region 验证规格 start*/
    //手机
    checkMobile = function (rule, value, callback) {
      if (!value) {
        return callback(new Error('请输入手机'));
      } else {
        var reg = /^1[3|4|5|8][0-9]\d{4,8}$/;
        if (!reg.test(value)) {
          return callback(new Error('请输入有效的手机号码'));
        } else {
          callback();
        }
      }
    };
    //电话
    checkTel = function (rule, value, callback) {
      if (!value) {
        return callback(new Error('请输入电话'));
      } else {
        var reg = /^\d{3,4}-?\d{7,8}$/;
        if (!reg.test(value)) {
          return callback(new Error('请输入有效的电话号码'));
        } else {
          callback();
        }
      }
    };
    //传真
    checkFax = function (rule, value, callback) {
      if (!value) {
        return callback(new Error('请输入传真'));
      } else {
        var reg = /^(\d{3,4}-)?\d{7,8}$/;
        if (!reg.test(value)) {
          return callback(new Error('请输入有效的传真号码'));
        } else {
          callback();
        }
      }
    };
    //QQ
    checkQq = function (rule, value, callback) {
      if (value) {
        var reg = /^[0-9]{0,20}$/;
        if (!reg.test(value)) {
          return callback(new Error('请输入有效的QQ号码'));
        } else {
          callback();
        }
      } else {
        callback();
      }
    };
    // 应用领域
    checkApplyFields = function (rule, value, callback) {
      var reg = /^(\d+,)*\d+$/;
      if (!reg.test(value)) {
        return callback(new Error('请至少选择一个应用领域'));
      } else {
        callback();
      }
    };
    /**endregion 验证规格 end*/
    return {
      rules: {
        lp_mobile: [{validator: checkMobile, trigger: 'blur'}],
        tel: [{validator: checkTel, trigger: 'blur'}],
        contact_mobile: [{validator: checkMobile, trigger: 'blur'}],
        contact_tel: [{validator: checkTel, trigger: 'blur'}],
        contact_fax: [{validator: checkFax, trigger: 'blur'}],
        contact_qq: [{validator: checkQq, trigger: 'blur'}],
        apply_fields: [{validator: checkApplyFields, trigger: 'change'}],
      },
      temp: {
        isSave: +company_id > 0 ? true : false,
      },
      companyQuery: {},
      info: {
        "company_id": "",//string,企业ID。
        "company_name": "",//string,企业名称
        "company_form": "",//string,公司性质。ID
        "area_id_1": "350000",//string,省ID。
        "area_id_2": "350200",//string,市ID。
        "area_id_3": "350203",//string,区ID。
        "is_high_new": "1",//"string,是否是高新企业。0-否 1-是"
        "capital_form": "",//string,所有制形式。ID
        "license_no": "",//"string,企业法人代码。统一社会信用代码"
        "address": "",//string,注册地址。
        "capital": "",//string,注册资本(万元)。
        "establishment_time": "",//string,成立时间。YYYY-MM-DD
        "legal_person": "",//string,法人代表
        "lp_mobile": "",//string,法人手机。
        "tel": "",//string,企业电话。
        "email": "",//string,企业邮箱。
        "introduction": "",//string,企业简介。
        "license_image": "",//string,企业营业执照。图片地址
        "apply_fields": "",//string,应用领域。ID组合，用逗号分隔
        "scope": "",//string,经营范围。
        "contact_name": "",//string,联系人姓名。
        "contact_duty": "",//string,联系人职务。
        "contact_mobile": "",//string,联系人手机。
        "contact_tel": "",//string,联系人电话。
        "contact_fax": "",//"string,联系人传真。",
        "contact_email": "",//"string,联系人邮箱。",
        "contact_qq": "",//"string,联系人QQ。",
        "contact_wechat": "",//"string,联系人微信。",
        "auth_status": "0",//"string,认证状态。0-未认证 1-已认证",
        "audit_status": "0",//"string,审核状态。0-未审核 1-通过 2-拒绝",
        "audit_reason": "",//"string,未认证原因。",
        "company_apply_status": "0",// "string,企业认证申请状态。0-未申请 1-已申请",
        "data_apply_status": "0",//"string,数据申报状态。0-未申报 1-已申报",
        "is_hot": "0",//"string,是否是热门企业。0-不是 1-是"
      },
    }
  },
  computed: {
    //当前企业认证状态
    companyAuthStatus: function(){
      var result = '';
      +this.info.auth_status === 0 ? result += '未认证企业' : result += '已认证企业';
      return result;
    },
    //当前企业审核状态
    companyAuditStatus: function () {
      var self = this;
      var result = '';
      +self.info.company_apply_status === 0 ? result += '未提交审核' :
        (+self.info.audit_status === 0 ? result += '审核中' :
          (+self.info.audit_status === 1 ? result += '审核已通过' :
            result += '<font color="red">审核已拒绝</font><font color="#999">（请重新修改信息并提交）</font>'));
      return result;
    },

    // 应用领域
    applyField: {
      set: function (val) {
        this.info.apply_fields = val.join(',')
      },
      get: function () {
        if (this.info.apply_fields === '') {
          return []
        }
        return this.info.apply_fields.split(',')
      }
    },

    // 注册资本
    registerCapital: {
      set: function (val) {
        this.info.capital = val
      },
      get: function () {
        this.info.capital = +this.info.capital
        return this.info.capital
      }
    },

    // 成立时间
    establishmentTime: {
      set: function (val) {
        this.info.establishment_time = val
      },
      get: function () {
        if (+this.info.establishment_time > 0) {
          var date = new Date(this.info.establishment_time * 1000)
          this.info.establishment_time = publicObj.dateTime_Str(date, 'date');
        }
        return this.info.establishment_time
      }
    },

    // 判断能否保存
    canSave: function () {
      // 如果已经认证了 可以提交
      if (this.info.auth_status == 1) {
        return true;
      }
      // 如果已经提交了申请 不能保存
      if (this.info.company_apply_status == 1) {
        // 如果被拒绝了可以再保存
        if (this.info.audit_status == 2) {
          return true;
        }
        return false;
      }
      return true;
    },

    // 判断能否提交
    canApply: function () {
      // 已经认证了 不能提交
      if (this.info.auth_status == 1) {
        return false;
      }
      // 提交了
      if (this.info.company_apply_status == 1) {
        // 如果被拒绝了可以再提交
        if (this.info.audit_status == 2) {
          return true;
        }
        return false;
      }
      return true;
    }
  },
  mounted: function () {
    this.load();
  },
  methods: {
    load: function () {
      var self = this;
      self.getSelectData().then(function (result) {
        self.companyQuery = result.data;
        if (self.temp.isSave) {
          self.getCompany().then(function (result) {
            self.info = result.data;
            self.$nextTick(function () {
              setcity('province', 'city', 'area', self.info.area_id_1, self.info.area_id_2, self.info.area_id_3);
            })
          })
        } else {
          self.$nextTick(function () {
            setcity('province', 'city', 'area', self.info.area_id_1, self.info.area_id_2, self.info.area_id_3);
          })
        }
      })
    },
    //获取选项值
    getSelectData: function () {
      return AppUtil.ajaxRequest({
        url: APP + '/getSelectData',
      })
    },
    //获取企业信息
    getCompany: function () {
      return AppUtil.ajaxRequest({
        url: APP + '/getCompany',
      })
    },
    //表单验证
    validateForm: function (formName, type) {
      var self = this;
      this.$refs[formName].validate(function (valid) {
        if (valid) {
          if (self.info.license_image === '') {
            layer.msg('请上传营业执照', {icon: 2, anim: 6, time: 1000});
            return false;
          }
          var arr = JSON.stringify(self.info);
          arr = JSON.parse(arr);
          if (+type === 1) {
            self.saveForm(arr);
          } else {
            self.submitForm(arr);
          }
          return false;
        } else {
          layer.msg('有必填项未输入', {icon: 2, anim: 6, time: 1000});
          console.log('error submit!!');
          return false;
        }
      });
    },
    //立即保存
    saveForm: function (queryData) {
      var self = this;
      if (!self.canSave) {
        return;
      }
      AppUtil.ajaxRequest({
        url: APP + '/saveCompany',
        data: JSON.stringify(self.info),
        contentType: AppUtil.CONTENT_TYPE_JSON,
      }).then(function (result) {
        layer.msg('保存成功', {icon: 1, time: 1000});
        self.temp.isSave = true;
        self.load()
      })
    },
    //认证提交
    submitForm: function (queryData) {
      var self = this;
      if (!self.canApply) {
        return;
      }
      AppUtil.ajaxRequest({
        url: APP + '/companyApply',
        data: queryData,
      }).then(function (result) {
        layer.msg('提交成功', {icon: 1, time: 1000});
        self.temp.isSave = true;
        self.load()
      })
    },
    //文件上传
    uploadClick: function () {
      this.$refs.upload_file.$el.click()
    }
  }
})