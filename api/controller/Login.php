<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use fast\Http;
/**
 * 首页接口
 */
class Login extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    
    
    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }
    
    
    // 验证卡密
     public function carmi()
    {
        $carmi = $this->request->post('carmi');
        
        
        if(!$carmi){
            
            $this->error('卡密错误');
        }
        $karmiwhere['carmi'] = $carmi;
         
        $is_karmi =  Db::name('carmi')->where($karmiwhere)->find();
        if(!$is_karmi){
           $this->error('卡密不可用');
        }
        
        if($is_karmi['status'] ==  '9'){
           $this->error('已被禁用');
        }
        
              
         $this->success('验证成功');
   
    }
    



 // 验证卡密
     public function cookie()
    {
        $carmi = $this->request->post('carmi');
        
        $cookie = $this->request->post('cookie','');
        
        $userId = $this->request->post('userId','');
        
        if(!$carmi){
            
            $this->error('卡密错误');
        }
        
         $karmiwhere['carmi'] = $carmi;
         
          $is_karmi =  Db::name('carmi')->where($karmiwhere)->find();
        if(!$is_karmi){
           $this->error('卡密不可用');
        }
        
     $is_zhicai =   Db::name('zhicai')->where('userId',$userId)->find();
        
        if(empty($userId) || $is_zhicai){
            
            $this->error('已被限制');
           
        }
        
        if(!empty($is_karmi['userId'])){
            
            if($is_karmi['userId'] != $userId){
                $this->error('当前卡密已绑定其他用户');
            }
           
        }
        
        
       
        //  $karmiwhere['status'] = ['<>',2];
        $up['userId'] = $userId;
        $up['cookie'] = $cookie;
        $up['status'] = 1;
        
        $is_karmi =  Db::name('carmi')->where($karmiwhere)->update($up);
        
              
        $this->success('成功');
   
        
    }
  
        public function songlist()
    {
        $carmi = $this->request->post('carmi');
        
        if(!$carmi){
            
            $this->error('卡密错误');
        }
         $is_karmi =  Db::name('carmi')->where('carmi',$carmi)->find();
        
        
        $songlist =  Db::name('songlist')->where('id',$is_karmi['songlist_id'])->select();
        
        foreach ($songlist as &$value) {
            // code...
            $value['data'] = Db::name('music')->where('songlist_id',$value['id'])->select();
        }
        
              
        $this->success('成功',$songlist);
   
        
    }
  
  
    
    
    
    
    
    
    
    
    
    
    
    
    
  
    
    

    

    
    
    
    

    
    
    
    
    
    
    
    
    
    
}






















