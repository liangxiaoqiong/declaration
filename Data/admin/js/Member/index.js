/**
 * @author   liangxiaoqiong
 * @version  1.0
 * @date 2018-05-04.
 */
'use strict';
var vm = new Vue({
  el: '#app',
  data: function () {
    return {
      query: {
        page: 1,
        limit: 10,
        username: '',
        start_time: '',
        end_time: '',
        member_type: 0//2为普通会员，1为租户,0：全部
      },
      memberData: {},


      temp: {
        /**
         * 点击房间的侧滑页 页面的显示的tab索引
         */
        roomTabIndex: 1,

        /**
         * 侧滑房间详情
         */
        roomDetail: {
          room_id: 0, // 房间ID
          room_name: '', //房间名称
          building_name: '', // 楼宇名称
          province_name: '', //
          city_name: '', //
          area_name: '', //
          address: '', //
          detail_address: '', //
          floor_name: '', // 楼层名称
          room_status: 0, // 0-暂停出租 1-允许出租
          rent_status: 0, // 0-未出租 1-已出租
          room_status_name: '', // 空置 暂停 已租
          room_area: '', // 房间面积
          rent_type: 0, // 租金方式
          rent_value: '', // 租金值
          room_facility: [], // 房间设施ID数组
          facility_name: [], // 房间设施名称
          room_desc: '', // 房间描述
          remark: '', // 装修备注
          room_remark: '', // 房间备注
          create_time_str: '', // 房间创建时间
          contract: {
            contract_id: 0, // 合同ID
            customer_name: '', // 租客名称
            start_time_str: '', // 开始时间
            end_time_str: '', // 结束时间
            contract_sn: '', // 合同编号
            customer_mobile: '', // 手机号码
            customer_id_type: 0, // 证件类型 0-身份证
            customer_id_number: '', // 证件号码
            customer_id_pictures: [], // 证件附件
            contract_pictures: [], // 合同附件
            remark: '', // 合同备注
            month_rental: 0.00, // 月租金额
            deposit: 0.00, // 押金
            pay_type_name: '', // 押1付1
            collect_rental_type: 0, // 0-提前 1-固定
            segments_info: [ // 分段
              {
                "start_time_str": "", // 开始时间
                "end_time_str": "", // 结束时间
                "rent_type": "", // 租金方式 0-按平方出租 1-按固定额
                "rent_value": "", // 租金方式值   元/平方/月   元/月
                "month_rental": "", // 月租金0
                "total_rental": "", // 合计租金
                "total_time": "", // 总共的时间字符串
                "pay_type": "", // 支付周期方式 0-月付 1-季付 2-半年付 3-年付 4-其他
                "pay_period": "", // 周期月数
              }
            ],
            charging_info: [ // 杂费信息
              {
                "item_id": "", // 收费项ID
                "item_name": "", // 收费项名称
                "charge": "", // 收费方式 0-按月首费
                "fee": "", // 费用
              }
            ],
            history: [
              {
                contract_id: 0, // 合同ID
                customer_name: '', // 租客名称
                eviction_time_str: '', // 退租时间
              }
            ],
          },
          bills: {
            wait: [
              {
                bill_id: 0, // 账单ID
                bill_sn: '', // 账单编号
                start_time_str: '', // 账单开始时间
                end_time_str: '', // 账单结束时间
                status_name: '', // 已逾期 待支付
                class_name: '', // 样式名称
                total_fee: '', // 账单金额
                ought_pay_time_str: '', // 应收日期
              }
            ],
            feature: [],
            done: [],
          },
          bill_tips: '提示：当前账单租金总和 < 合同应收总租金（缺：587.09）',
          has_pay_bill: 0, // 是否有交过账单
        },

        /**
         * 房间详情的副本 JSON字符串
         */
        roomDetailBack: '',

        roomDetailEdit: false,

        /**
         * 合同的证件类型
         */
        id_name: ['身份证号', '护照号'],

        /**
         * 合同的收租日期
         */
        collect_name: ['提前支付天数', '固定交租日'],

        /**
         * 提前天数 固定日期
         */
        collect_day_name: ['天', '号'],

        /**
         * 不同的收租方式需要用不同的字段来展示
         */
        collect_field: ['advanced_days', 'fixed_pay_date'],

        /**
         * 账单列表的种类
         */
        bill_name: {
          wait: '待处理账单',
          feature: '未来账单',
          done: '已处理账单'
        },

        /**getRoomDetail
         * 每个账单类型的样式
         */
        bill_class: {
          wait: 'line-left-purple',
          feature: 'line-left-red',
          done: 'line-left-yellow'
        },

        /**
         * 当前查看的账单数据
         */
        checkedBill: {
          bill_id: 0, // 账单ID
          bill_sn: '', // 账单编号
          start_time_str: '', // 账单开始时间
          end_time_str: '', // 账单结束时间
          status_name: '', // 已逾期 待支付
          class_name: '', // 样式名称
          total_fee: '', // 账单金额
          ought_pay_time_str: '', // 应收日期
          fee_items: [
            {
              "item_id": "", // 收费项ID 租金和押金没有ID
              "item_name": "", // 费用名称
              "fee_type": "", // 资金流向 0-收入 1-支出
              "fee": "", // 金额
            }
          ],
          trades: [ // 交易流水
            {
              trade_id: 0, // 交易ID
              transaction_id: '', // 交易编号
              create_time_str: '', // 交易时间
              fee: '', // 交易金额
              pay_type_name: '', // 交易方式名称
              trade_no: '', // 交易流水号
              object_name: '', // 交易账号
            }
          ],
        },

        /**
         * 是否点击了查看账单按钮
         */
        showBill: false,

        /*查看历史合同*/
        historyDetail: {
          contract_id: 0, // 合同ID
          customer_name: '', // 租客名称
          start_time_str: '', // 开始时间
          end_time_str: '', // 结束时间
          contract_sn: '', // 合同编号
          customer_mobile: '', // 手机号码
          customer_id_type: 0, // 证件类型 0-身份证
          customer_id_number: '', // 证件号码
          customer_id_pictures: [], // 证件附件
          contract_pictures: [], // 合同附件
          remark: '', // 合同备注
          month_rental: 0.00, // 月租金额
          deposit: 0.00, // 押金
          pay_type_name: '', // 押1付1
          collect_rental_type: 0, // 0-提前 1-固定
          segments_info: [ // 分段
            /*{
              "start_time_str": "", // 开始时间
              "end_time_str": "", // 结束时间
              "rent_type": "", // 租金方式 0-按平方出租 1-按固定额
              "rent_value": "", // 租金方式值   元/平方/月   元/月
              "month_rental": "", // 月租金0
              "total_rental": "", // 合计租金
              "total_time": "", // 总共的时间字符串
              "pay_type": "", // 支付周期方式 0-月付 1-季付 2-半年付 3-年付 4-其他
              "pay_period": "", // 周期月数
            }*/
          ],
          charging_info: [ // 杂费信息
            /*{
              "item_id": "", // 收费项ID
              "item_name": "", // 收费项名称
              "charge": "", // 收费方式 0-按月首费
              "fee": "", // 费用
            }*/
          ],
        },
        showHistory: false,
      },

      /**
       * 杂项数据
       */
      sundryData: {
        "facility": [
          {
            "facility_id": "", //"string,设备ID",
            "facility_name": "", //"string,设备名称"
          }
        ],
        "charge": [
          {
            "item_id": "", //"string,收费项ID",
            "item_name": "", //"string,收费项名称"
          }
        ],
        "qr_code": [
          {
            "qr_id": "", //"string,二维码ID",
            "qr_name": "", //"string,二维码名称"
          }
        ],
        "contract_tpl": [
          {
            "tpl_id": "", //"string,模版ID",
            "tpl_name": "", //"string,模版名称"
          }
        ]
      },

      rent_type_name: ['元/平方/月', '元/月'],

    }
  },
  mounted: function () {
    var self = this;
    self.getSundryData()
    layui.use(['form', 'upload', 'layer', 'element'], function () {
      var form = layui.form();
      //表格搜索栏的客户状态
      form.on('select(memberSelect)', function (data) {
        self.query.member_type = data.value;
        $("#memberSelect").val(self.query.member_type);
        form.render('select', 'memberSelect');
      })
    });//全局样式
    self.getMember();
  },

  computed: {
    // 计算属性的 getter
    monthRental: {
      get: function () {
        var self = this
        var monthRental = 0.00
        var rentValue = self.temp.roomDetail.rent_value > 0 ? self.temp.roomDetail.rent_value : 0.00
        var roomArea = self.temp.roomDetail.room_area > 0 ? self.temp.roomDetail.room_area : 0.00
        if (self.temp.roomDetail.rent_type == 0) {
          monthRental = rentValue * roomArea
        } else {
          monthRental = rentValue * 1
        }
        return monthRental.toFixed(2)
      }
    }
  },

  methods: {

    /**
     * 获取杂项数据
     */
    getSundryData: function () {
      var self = this
      AppUtil.ajaxRequest({
        url: APP + '/getSundryData'
      }).then(function (result) {
        self.sundryData = result.data
      }).catch(function (result) {

      })
    },

    /*获取房间详情*/
    getRoomDetail: function (room) {
      var self = this
      AppUtil.ajaxRequest({
        url: APP + '/getRoomDetail',
        data: {
          room_id: room.room_id,
        }
      }).then(function (result) {
        self.temp.roomDetail = result.data
        self.saveRoomDetailBack()
      }).catch(function (result) {

      })
    },

    /**点击表格列侧滑显示房间详情弹框*/
    roomInfoLayer: function (room) {
      var self = this
      self.getRoomDetail(room)
      self.temp.roomTabIndex = 1
      self.temp.showBill = false
      publicObj.slideRight('show', '.slide-right-room');
    },

    /**
     * 切换侧边房间详情的tab
     * @param index 索引
     */
    roomTab: function (index) {
      var self = this
      self.temp.roomTabIndex = index
    },

    roomTabClass: function (index) {
      var self = this
      return self.temp.roomTabIndex == index ? 'active' : ''
    },

    /*1、租房合同*/
    /*退租*/
    throwLeaseLayer: function (contract) {
      var self = this
      var roomDetail = self.temp.roomDetail
      var num = roomDetail.bills.wait.length + roomDetail.bills.feature.length
      var desc = ''
      if (num > 0) {
        desc = '您还有' + num + '个应收未收账单未处理，'
      }
      layer.confirm(desc + '是否确认退租?', {title: '退租', skin: 'layer-confirm'}, function (index) {
        layer.close(index)
        publicObj.layerShow(2, '退租', APP + '/Index/throwLease' + '&rid=' + roomDetail.room_id);
      })
    },

    /*删除合同*/
    delContract: function (contract) {
      var self = this
      layer.confirm($('.del-contract-layer'), {type: 1, title: '删除合同', skin: 'layer-confirm'}, function (index) {
        var delPayed = $("#del_payed")[0].checked ? 1 : 0
        AppUtil.ajaxRequest({
          url: APP + '/delContract',
          data: {
            contract_id: contract.contract_id,
            del_payed: delPayed
          },
        }).then(function (result) {
          self.getRightData(self.temp.checkedBuilding, 'update')
          self.getRoomDetail(self.temp.roomDetail)
          publicObj.layerMsg(result.msg, AppUtil.SUCCESS);
          layer.close(index)
        }).catch(function (result) {

        })
      })
    },
    /*删除房间*/
    delRoom: function (room) {
      var self = this
      if (room.rent_status == 1) {
        layer.alert('该房间正在出租中，请先完成退租后再删除！', {title: '删除房间', skin: 'layer-confirm', btn: ['好的']}, function (index) {
          layer.close(index);
        })
      } else {
        var msg = '你是否确认要删除该房间？';
        if (room.contract.history.length > 0) {
          msg = '该房间有历史合同，你是否确认要删除该房间？';
        }
        layer.confirm(msg, {title: '删除房间', skin: 'layer-confirm'}, function (index) {
          AppUtil.ajaxRequest({
            url: APP + '/delRoom',
            data: {
              room_id: room.room_id
            }
          }).then(function (result) {
            self.closeRoomDetail()
            publicObj.layerMsg(result.msg, AppUtil.SUCCESS)
          }).catch(function (result) {

          })
        })
      }
    },

    /*删除园区*/
    delBuilding: function (building) {
      var self = this
      layer.confirm('您确定要删除该园区吗？删除后与该园区相对应的信息将被删除！', {title: '删除园区', skin: 'layer-confirm'}, function (index) {
        AppUtil.ajaxRequest({
          url: APP + '/delBuilding',
          data: {
            building_id: building.building_id
          }
        }).then(function (result) {
          self.getLeftData()
          layer.close(index);
          publicObj.layerMsg(result.msg, 1)
          publicObj.slideRight('hide', '.slide-right-building')
        }).catch(function (result) {

        })
      })
    },

    triggerBuildingDetailEdit: function () {
      var self = this;
      if (self.temp.checkedBuildingDetailEdit) {
        self.backBuildingDetail()
      }
      self.temp.checkedBuildingDetailEdit = !self.temp.checkedBuildingDetailEdit
    },

    saveBuildingDetail: function () {
      var self = this
      AppUtil.ajaxRequest({
        url: APP + '/updateBuilding',
        data: JSON.stringify(self.temp.checkedBuildingDetail),
        contentType: 'application/json;charset=utf-8'
      }).then(function (result) {
        publicObj.layerMsg(result.msg, 1)
        self.temp.checkedBuildingDetailEdit = false
        var newInfo = result.data
        self.temp.checkedBuildingDetail.building_name = newInfo.building_name
        self.temp.checkedBuildingDetail.province_id = newInfo.province_id
        self.temp.checkedBuildingDetail.province_name = newInfo.province_name
        self.temp.checkedBuildingDetail.city_id = newInfo.city_id
        self.temp.checkedBuildingDetail.city_name = newInfo.city_name
        self.temp.checkedBuildingDetail.area_id = newInfo.area_id
        self.temp.checkedBuildingDetail.area_name = newInfo.area_name
        self.temp.checkedBuildingDetail.address = newInfo.address
        self.temp.checkedBuildingDetail.remark = newInfo.remark
        self.saveBuildingDetailBack()
      }).catch(function (result) {

      })
    },

    saveBuildingDetailBack: function () {
      var self = this
      self.temp.checkedBuildingDetailBack = JSON.stringify(self.temp.checkedBuildingDetail)
    },

    backBuildingDetail: function () {
      var self = this
      self.temp.checkedBuildingDetail = JSON.parse(self.temp.checkedBuildingDetailBack)
    },

    /*点击签约*/
    into_contractInfo: function (room) {
      var self = this
      self.getRoomDetail(room)
      if (self.isRented(room)) {
        publicObj.layerMsg('房间已签约', AppUtil.ERROR)
      } else if (!self.canRent(room)) {
        publicObj.layerMsg('房间暂停出租', AppUtil.ERROR)
      } else {
        publicObj.layerShow(2, '签约', APP + '/Index/contractInfo' + '&rid=' + room.room_id, {w_: '100%', h_: '100%'});
      }
    },

    /*删除账单*/
    delBill: function (bill) {
      var self = this
      layer.confirm('您是否确认删除该账单，删除该账单后，将不可恢复。', {title: '删除账单', skin: 'layer-confirm'}, function (index) {
        AppUtil.ajaxRequest({
          url: APP + '/delBill',
          data: {
            bill_id: bill.bill_id
          }
        }).then(function (result) {
          self.getRoomDetail(self.temp.roomDetail)
          self.getRightData(self.temp.checkedBuilding, 'update')
          publicObj.layerMsg(result.msg, AppUtil.SUCCESS);
          layer.close(index);
        }).catch(function (result) {

        })
      })
    },

    /*更新账单*/
    editBill: function (bill) {
      publicObj.layerShow(2, '编辑账单', APP + '/updateBill' + '&bid=' + bill.bill_id)
    },

    /*添加账单*/
    addBill: function (contract) {
      publicObj.layerShow(2, '编辑账单', APP + '/addBill' + '&cid=' + contract.contract_id)
    },

    /*打印账单弹框*/
    billPrintLayer: function () {
      layer.open({
        type: 2,
        area: ['500px', '90%'],
        fix: false, //不固定
        title: ' ',
        content: '/admin.php?s=/billPrint'
      });
    },

    isWait: function (key) {
      return key === 'wait'
    },

    isDone: function (key) {
      return key === 'done'
    },

    isFeature: function (key) {
      return key === 'feature'
    },

    showBill: function (bill) {
      var self = this
      self.temp.checkedBill = bill
      self.temp.showBill = true
    },

    cancelShowBill: function () {
      var self = this
      self.temp.checkedBill = {}
      self.temp.showBill = false
    },

    saveRoom: function () {
      var self = this
      AppUtil.ajaxRequest({
        url: APP + '/updateRoom',
        data: JSON.stringify(self.temp.roomDetail),
        contentType: 'application/json;charset=utf-8'
      }).then(function (result) {
        self.temp.roomDetailEdit = false
        self.getRoomDetail(self.temp.roomDetail)
        publicObj.layerMsg(result.msg, AppUtil.SUCCESS)
      }).catch(function (result) {

      })
    },

    isRented: function (room) {
      return room.rent_status == 1
    },

    canRent: function (room) {
      return room.room_status == 1
    },

    saveRoomDetailBack: function () {
      var self = this
      self.temp.roomDetailBack = JSON.stringify(self.temp.roomDetail)
    },

    backRoomDetail: function () {
      var self = this
      self.temp.roomDetail = JSON.parse(self.temp.roomDetailBack)
    },

    editRoomDetail: function () {
      var self = this
      self.temp.roomDetailEdit = true
    },

    cancelEditRoomDetail: function () {
      var self = this
      self.backRoomDetail()
      self.temp.roomDetailEdit = false
    },

    closeRoomDetail: function () {
      var self = this
      self.getRightData(self.temp.checkedBuilding, 'update')
      publicObj.slideRight('hide', '.slide-right-room')
    },

    /*编辑合同*/
    editContract: function (contract) {
      publicObj.layerShow(2, '编辑合同', APP + '/Index/contractInfo' + '&rid=' + contract.room_id + '&cid=' + contract.contract_id, {
        w_: '100%',
        h_: '100%'
      })
    },

    /*收租*/
    collect: function (bill) {
      publicObj.layerShow(2, '确认收租', APP + '/collectBill' + '&bid=' + bill.bill_id)
    },

    /*判断是否支付过*/
    isPay: function (bill) {
      return bill.pay_status == 1
    },

    /*发送账单弹窗*/
    sendBill: function (bill) {
      publicObj.layerShow(2, '发送账单', APP + '/sendBillMessage' + '&bid=' + bill.bill_id, {w_: '550px', h_: '350px'})
    },

    /*预览*/
    preview: function (contract) {
      publicObj.layerShow(2, ' ', APP + '/previewContract' + '&cid=' + contract.contract_id, {
        w_: '100%',
        h_: '100%'
      })
    },

    /*打印*/
    print: function (contract) {
      window.open(contract.content_url)
      return true;
      publicObj.layerShow(2, ' ', APP + '/contractPrint' + '&contract_id=' + contract.contract_id, {
        w_: '100%',
        h_: '100%'
      })
    },

    printBill: function (bill) {
      publicObj.layerShow(2, ' ', APP + '/billPrint' + '&bid=' + bill.bill_id, {w_: '500px', h_: '90%'})
    },

    /*历史详情*/
    historyDetail: function (contract) {
      var self = this
      AppUtil.ajaxRequest({
        url: APP + '/getContract',
        data: {
          contract_id: contract.contract_id
        }
      }).then(function (result) {
        self.temp.showHistory = true
        self.temp.historyDetail = result.data
      }).catch(function () {

      })
    },

    /*取消查看历史*/
    cancelShowHistory: function () {
      var self = this
      self.temp.showHistory = false
    },

    /*续租*/
    relet: function (contract) {
      var self = this
      var num = self.temp.roomDetail.bills.feature.length + self.temp.roomDetail.bills.wait.length
      if (num > 0) {
        AppUtil.alert('您有未处理完账单,暂时无法续租', AppUtil.ERROR)
        return;
      }
      publicObj.layerShow(2, '续租', APP + '/relet' + '&rid=' + contract.room_id + '&cid=' + contract.contract_id, {
        w_: '100%',
        h_: '100%'
      });
    },

    afterCollectRent: function () {
      var self = this
      self.getRoomDetail(self.temp.roomDetail)
      self.getRightData(self.temp.checkedBuilding, 'update')
    },

    afterEditBill: function () {
      var self = this
      self.getRoomDetail(self.temp.roomDetail)
      self.getRightData(self.temp.checkedBuilding, 'update')
    },

    afterThrowLease: function () {
      var self = this
      self.getRoomDetail(self.temp.roomDetail)
      self.getRightData(self.temp.checkedBuilding, 'update')
    },

    afterEditContract: function () {
      var self = this
      self.getRoomDetail(self.temp.roomDetail)
      self.getRightData(self.temp.checkedBuilding, 'update')
    },


    /**获取会员列表*/
    getMember: function (currentPage, currentLimit) {
      var self = this;
      if(typeof currentPage !== 'undefined'){
        self.query.page = currentPage;
      }
      if(typeof currentLimit !== 'undefined'){
        self.query.limit = currentLimit;
      }
      AppUtil.ajaxRequest({
        url: '/admin.php?s=/getMemberListData',
        data: self.query,
        type: 'POST',
      }).then(function (result) {
        self.memberData = result.data.data;
        layui.use(['laypage'], function () {
          var laypage = layui.laypage;
          laypage({
            cont: 'pageDiv'//ID，不加#
            , pages: Math.ceil(result.data.total / self.query.limit)
            , curr: self.query.page
            , skip: true
            , skin: '#707cd2'
            , jump: function (obj, first) {
              if (!first) {
                self.query.page = obj.curr;
                self.getMember(obj.curr, self.query.limit);
              }
            }
          });
        })
      }).catch(function (reason) {
        publicObj.layerMsg(reason.msg, 0);
      })
    },
    /**重置，清空搜索框*/
    resetQuery: function () {
      var self = this;
      var query_ = {
        page: self.query.page,
        limit: self.query.limit,
        username: '',
        start_time: '',
        end_time: '',
        member_type: 0//2为普通会员，1为租户,0：全部
      }
      self.query = query_;
      layui.use(['form', 'upload', 'layer', 'element'], function () {
        var form = layui.form();
        $("#memberSelect").val(self.query.status);
        form.render('select', 'memberSelect');
      });//全局样式
    },
    /**时间切换*/
    dayToTime: function (type) {
      var self = this;
      var forT,//相隔毫秒数
        startT = 0, endT = 0;
      var myDate = new Date();
      myDate.setHours(0);
      myDate.setMinutes(0);
      myDate.setSeconds(0);
      myDate.setMilliseconds(0);
      var oneDay = 24 * 60 * 60 * 1000 - 1;//当天23:59:59
      switch (+type) {
        case 1:
          startT = myDate.getTime();
          endT = startT + oneDay;
          break;
        case -1:
          forT = 86400000;//昨天
          var t = new Date(myDate - forT);
          startT = t.getTime();
          endT = startT + oneDay;
          break;
        case 3:
          forT = 259200000;//前3天
          var t = new Date(myDate - forT);
          startT = t.getTime();
          endT = startT + oneDay * 3;
          break;
        case 30:
          forT = 2592000000;//前30天
          var t = new Date(myDate - forT);
          startT = t.getTime();
          endT = startT + oneDay * 30;
          break;
        default:
          break;
      }
      var val1 = new Date(startT);
      var val1_ = publicObj.dateTime_Str(val1);
      var val2 = new Date(endT);
      var val2_ = publicObj.dateTime_Str(val2);
      self.query.start_time = val1_;
      self.query.end_time = val2_;
    },

    getRightData: function () {

    },

  },
  directives: {
    // 日期控件
    layDate: {
      bind: function (el, binding) {
        laydate.render({
          elem: el,
          type: 'datetime',
          done: function (value, date, endDate) {
            // 根实例
            var obj = vm;
            var param = binding.value;
            if (binding.value.indexOf('.') !== -1) {
              var arr = binding.value.split('.');
              param = arr[arr.length - 1];
              obj = obj[arr[0]];
              for (var i = 1; i < arr.length - 1; i++) {
                if (typeof obj[arr[i]] !== 'undefined') {
                  obj = obj[arr[i]];
                }
              }
            }
            Vue.set(obj, param, value);
          }
        });
      }
    }
  },
})
