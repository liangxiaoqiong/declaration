/**
 * @author   liangxiaoqiong
 * @version  1.0
 * @date 2018-03-27.
 */


var publicObj = new Object({
  /*点击复制文字*/
  copyContent: function (text, id, doMsg) {
    if (navigator.userAgent.match(/(iPhone|iPod|iPad);?/i)) {
      //ios
      var copyDOM = document.querySelector('#' + id);  //要复制文字的节点
      var range = document.createRange();
      // 选中需要复制的节点
      range.selectNode(copyDOM);
      // 执行选中元素
      window.getSelection().addRange(range);
      // 执行 copy 操作
      var successful = document.execCommand('copy');
      try {
        var msg = successful ? 'successful' : 'unsuccessful';
        console.log('copy is' + msg);
      } catch (err) {
        console.log('Oops, unable to copy');
      }
      // 移除选中的元素
      window.getSelection().removeAllRanges();
    } else {
      // 创建元素用于复制
      var aux = document.createElement("input");
      // 设置元素内容
      aux.setAttribute("value", text);
      // 将元素插入页面进行调用
      document.body.appendChild(aux);
      // 复制内容
      aux.select();
      // 将内容复制到剪贴板
      document.execCommand("copy");
      // 删除创建元素
      document.body.removeChild(aux);
    }
    doMsg = !(doMsg === 0)
    if (doMsg){
      layer.msg('已复制内容到剪贴板');
    }
  },
  /**
   * 质朴长存法 =>不足位步0 by lifesinger
   * @param value
   */
  padNum: function (num, n) {
    var len = num.toString().length;
    while (len < n) {
      num = "0" + num;
      len++;
    }
    return num;
  },
  /**
   * 正则，只允许正整数
   * @param value
   * limitVal{
   *  "maxVal":"限制最大数"，"maxMsg":"超过最大数限制说明",
   *  "minVal":"限制最小数"，"minMsg":"超过最小数限制说明"}
   * @returns {number}
   */
  numInt: function (obj, limitVal) {
    if (obj.value.length == 1) {
      obj.value = obj.value.replace(/[^0-9]/g, '')
    } else {
      obj.value = obj.value.replace(/\D/g, '')
    }
    if (typeof (limitVal) !== 'undefined') {
      if (obj.value > +limitVal.maxVal) {
        layer.msg(limitVal.maxMsg);//'该商品最大售量9999件！'
        obj.value = +limitVal.maxVal;
      }
    }
    return obj.value;
  },
  /**
   * 浮点小数(最多精确到2位)
   * @param value
   * limitVal{
   *  "maxVal":"限制最大数"，"maxMsg":"超过最大数限制说明",
   *  "minVal":"限制最小数"，"minMsg":"超过最小数限制说明"}
   * @returns {number}
   */
  numPoint2: function (obj, limitVal) {
    obj.value = obj.value.match(/\d+(\.\d{0,2})?/) ? obj.value.match(/\d+(\.\d{0,2})?/)[0] : '';
    if (typeof (limitVal) !== 'undefined') {
      if (obj.value > +limitVal.maxVal) {
        layer.msg(limitVal.maxMsg);
        obj.value = +limitVal.maxVal;
      }
    }
    return obj.value;
  },
  /**
   * 格式化时间
   * @param {} date
   * @param {} format
   */
  formatDate: function (date, format) {
    var paddNum = function (num) {
      num += "";
      return num.replace(/^(\d)$/, "0$1");
    }
    //指定格式字符
    var cfg = {
      yyyy: date.getFullYear() //年 : 4位
      , yy: date.getFullYear().toString().substring(2)//年 : 2位
      , M: date.getMonth() + 1  //月 : 如果1位的时候不补0
      , MM: paddNum(date.getMonth() + 1) //月 : 如果1位的时候补0
      , d: date.getDate()   //日 : 如果1位的时候不补0
      , dd: paddNum(date.getDate())//日 : 如果1位的时候补0
      , hh: paddNum(date.getHours())  //时
      , mm: paddNum(date.getMinutes()) //分
      , ss: paddNum(date.getSeconds()) //秒
    }
    format || (format = "yyyy-MM-dd hh:mm:ss");
    return format.replace(/([a-z])(\1)*/ig, function (m) {
      return cfg[m];
    });
  },
  formatDate2: function (date, format) {
    var paddNum = function (num) {
      num += "";
      return num.replace(/^(\d)$/, "0$1");
    }
    //指定格式字符
    var cfg = {
      yyyy: date.getFullYear() //年 : 4位
      , yy: date.getFullYear().toString().substring(2)//年 : 2位
      , M: date.getMonth() + 1  //月 : 如果1位的时候不补0
      , MM: paddNum(date.getMonth() + 1) //月 : 如果1位的时候补0
      , d: date.getDate()   //日 : 如果1位的时候不补0
      , dd: paddNum(date.getDate())//日 : 如果1位的时候补0
      , hh: paddNum(date.getHours())  //时
      , mm: paddNum(date.getMinutes()) //分
      , ss: paddNum(date.getSeconds()) //秒
    }
    format || (format = "yyyy.MM.dd");
    return format.replace(/([a-z])(\1)*/ig, function (m) {
      return cfg[m];
    });
  },
  /**
   * 判断是否是手机
   * @param value
   * @returns {boolean}
   */
  isPhone: function (value) {
    var reg = /^1[3|4|5|8][0-9]\d{4,8}$/;
    return reg.test(value);
  },

  /**选择时间切换*/
  dayToTime: function (type) {
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
      case 7:
        forT = 604800000;//前7天
        var t = new Date(myDate - forT);
        startT = t.getTime();
        endT = startT + oneDay * 7;
        break;
      case 15:
        forT = 1296000000;//前15天
        var t = new Date(myDate - forT);
        startT = t.getTime();
        endT = startT + oneDay * 15;
        break
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
    if(+type === 0){
      val1_ = '';val2_ = '';
    }
    var arr = [val1_, val2_];
    return arr;
  },
  /**
   * //毫秒转时间戳2017-08-20 12:12:12*/
  dateTime_Str: function (time_, type) {
    var Y = time_.getFullYear();    //获取完整的年份(4位,1970-????)
    var M = publicObj.padNum(time_.getMonth() + 1, 2);       //获取当前月份(0-11,0代表1月)
    var D = publicObj.padNum(time_.getDate(), 2);        //获取当前日(1-31)
    if(typeof type !== 'undefined'){
      switch (type) {
        case 'date':
          var dataTime = Y + '-' + M + '-' + D;
          break;
        case 'mouth':
          var dataTime = Y + '-' + M;
          break;
        default:
          break;
      }
    }else{
      var H = publicObj.padNum(time_.getHours(), 2);       //获取当前小时数(0-23)
      var Min = publicObj.padNum(time_.getMinutes(), 2);     //获取当前分钟数(0-59)
      var S = publicObj.padNum(time_.getSeconds(), 2);     //获取当前秒数(0-59)
      var dataTime = Y + '-' + M + '-' + D + ' ' + H + ':' + Min + ':' + S;
    }
    return dataTime;
  },
  //获取当前上个月
  getPreMonth: function () {
    var date = new Date();
    var year = date.getFullYear();
    var month = publicObj.padNum(date.getMonth()+1, 2);
    var year2 = year;
    var month2 = parseInt(month) - 1;
    if (+month2 === 0) {
      year2 = parseInt(year2) - 1;
      month2 = 12;
    }
    return year2+'-'+month2;
  }
})