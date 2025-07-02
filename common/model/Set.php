<?php

namespace app\common\model;

use think\Model;


class Set extends Model
{

    

    

    // 表名
    protected $name = 'set';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







    public function songlist()
    {
        return $this->belongsTo('Songlist', 'songlist_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
