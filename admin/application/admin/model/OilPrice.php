<?php

namespace app\admin\model;

use think\Model;


class OilPrice extends Model
{





    // 表名
    protected $name = 'oil_price';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];


    public function oil()
    {
        return $this->belongsTo('Oil', 'oil_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function city()
    {
        return $this->belongsTo('City', 'city_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
