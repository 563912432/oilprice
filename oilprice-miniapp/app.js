// app.js
App({
  onLaunch() {
    // 统一处理样式
    wx.getSystemInfo({
      success: res => {
        //导航高度
        this.globalData.navHeight = res.statusBarHeight;
        let modelmes = res.model;
        if (modelmes.search('iPhone X') != -1) {
          this.globalData.bottomHeight = 30
        } else {
          this.globalData.bottomHeight = 0
        }
      }
    })
  },
  globalData: {
    url: 'https://www.yuzipaopao.cn/',
    navHeight: '',
    userInfo: null
  },
  _post: function (url, postData) {
    var promise = new Promise((resolve, reject) => {
      wx.request({
        url: this.globalData.url + url,
        data: postData,
        method: 'POST',
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
          resolve(res.data)
        },
        error: function (e) {
          console.log(e)
          reject('网络出错')
        }
      })
    });
    return promise;
  },
  _get: function (url, getData) {
    var promise = new Promise((resolve, reject) => {
      wx.request({
        url: this.globalData.url + url,
        data: getData,
        method: 'GET',
        header: {
          'content-type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
          resolve(res.data)
        },
        error: function (e) {
          reject('网络出错')
        }
      })
    });
    return promise;
  }
})
