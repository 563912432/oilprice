// index.js
const { formatTime } = require("../../utils/util")
const { getAreaList } = require("../../utils/arealist")
const QQMapWX = require('../../utils/qqmap-wx-jssdk.min')
let qqmapsdk
// 获取应用实例
const app = getApp()

Page({
  data: {
    nowTime: '',
    list: [],
    notice: '',
    show: false,
    city: '张店区',
    areaList: {}
  },
  onLoad(option) {
    // 取城市列表
    this.getCityList()
    // 定位当前城市
    this.getUserLoaction()
    // 取今日油价
    this.getTodayOilPrice()
    let date = new Date()
    let nowDate = formatTime(date)
    this.setData({
      nowTime: nowDate,
      list: [
        { title: '89', type: 1, price: '6.17', yesterday: '6.17' },
        { title: '92', type: 1, price: '6.14', yesterday: '6.16' },
        { title: '95', type: 1, price: '6.59', yesterday: '6.52' },
        { title: '98', type: 1, price: '7.31', yesterday: '7.24' },
        { title: '0', type: 2, price: '5.76', yesterday: '5.70' }
      ],
      notice: '1月20日，第3个工作日，预测油价累计上调幅度50元/吨，折算为0.04元、升，调整窗口时间为：2021年1月29日24时'
    })
  },
  // 取今日油价
  getTodayOilPrice: function () {
    const _this = this
    wx.showLoading({
      title: '加载中',
    })
    getApp()._get(`Api/OilPrice/oilInfo`).then(res => {
      wx.hideLoading({
        success: () => {
          console.log(res)
        },
      })
    })
  },
  // 取城市列表
  getCityList: function () {
    this.setData({
      areaList: getAreaList()
    })
  },
  showCity: function () {
    this.setData({ show: true })
  },
  onClose: function () {
    this.setData({ show: false })
  },
  confirmChose: function (e) {
    this.setData({ city: e.detail.values[2]['name'], show: false })
  },
  onShareAppMessage: function () {
    return {
      title: '今日油价查询',
      path: '/pages/index/index'
    }
  },
  // 获取用户当前位置
  getUserLoaction: function () {
    let vm = this
    wx.getSetting({
      success: (res) => {
        // console.log(JSON.stringify(res))
        // res.authSetting['scope.userLocation'] == undefined    表示 初始化进入该页面
        // res.authSetting['scope.userLocation'] == false    表示 非初始化进入该页面,且未授权
        // res.authSetting['scope.userLocation'] == true    表示 地理位置授权
        if (res.authSetting['scope.userLocation'] != undefined && res.authSetting['scope.userLocation'] != true) {
          wx.showModal({
            title: '请求授权当前位置',
            content: '需要获取您的地理位置，请确认授权',
            success: function (res) {
              if (res.cancel) {
                wx.showToast({
                  title: '拒绝授权',
                  icon: 'none',
                  duration: 1000
                })
              } else if (res.confirm) {
                wx.openSetting({
                  success: function (dataAu) {
                    if (dataAu.authSetting["scope.userLocation"] == true) {
                      wx.showToast({
                        title: '授权成功',
                        icon: 'success',
                        duration: 1000
                      })
                      //再次授权，调用wx.getLocation的API
                      vm.getLocation();
                    } else {
                      wx.showToast({
                        title: '授权失败',
                        icon: 'none',
                        duration: 1000
                      })
                    }
                  }
                })
              }
            }
          })
        } else if (res.authSetting['scope.userLocation'] == undefined) {
          //调用wx.getLocation的API
          vm.getLocation();
        }
        else {
          //调用wx.getLocation的API
          vm.getLocation();
        }
      }
    })
  },
  getLocation: function () {
    let vm = this;
    wx.getLocation({
      type: 'wgs84',
      success: function (res) {
        // console.log(JSON.stringify(res))
        var latitude = res.latitude
        var longitude = res.longitude
        var speed = res.speed
        var accuracy = res.accuracy;
        vm.getLocal(latitude, longitude)
      },
      fail: function (res) {
        console.log('fail' + JSON.stringify(res))
      }
    })
  },
  getLocal: function (latitude, longitude) {
    qqmapsdk = new QQMapWX({
      key: '3RABZ-YPA3U-SN4V7-BILRL-OWZIQ-A2BPH'
    })
    let vm = this;
    qqmapsdk.reverseGeocoder({
      location: {
        latitude: latitude,
        longitude: longitude
      },
      success: function (res) {
        // console.log(JSON.stringify(res));
        // let province = res.result.ad_info.province
        // let city = res.result.ad_info.city
        let district = res.result.ad_info.district
        console.log(district)
        vm.setData({
          city: district
        })
      },
      fail: function (res) {
        console.log(res);
      },
      complete: function (res) {
        // console.log(res);
      }
    });
  }
})
