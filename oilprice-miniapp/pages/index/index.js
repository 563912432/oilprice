// index.js

const { formatTime } = require("../../utils/util")

// 获取应用实例
const app = getApp()

Page({
  data: {
    nowTime: '',
    list: []
  },
  onLoad(option) {
    let date = new Date()
    let nowDate = formatTime(date)
    this.setData({
      nowTime: nowDate, list: [
        { title: '89#汽油' }
      ]
    })
  }
})
