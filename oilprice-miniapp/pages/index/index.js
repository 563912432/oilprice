// index.js

const { formatTime } = require("../../utils/util")

// 获取应用实例
const app = getApp()

Page({
  data: {
    nowTime: '',
    list: [],
    notice: ''
  },
  onLoad(option) {
    let date = new Date()
    let nowDate = formatTime(date)
    this.setData({
      nowTime: nowDate, 
      list: ['6.17', '6.08', '6.52', '7.24', '5.7' ],
      notice: '1月20日，第3个工作日，预测油价累计上调幅度50元/吨，折算为0.04元、升，调整窗口时间为：2021年1月29日24时'
    })
  }
})
