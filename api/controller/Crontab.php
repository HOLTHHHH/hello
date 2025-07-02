<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use fast\Http;
use think\Log;
/**
 * 首页接口
 */
class Crontab extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    
    
    
    
     public function index()
    {
       
        ignore_user_abort(true);//关掉浏览器，PHP脚本也可以继续执行.
        set_time_limit(0);// 通过set_time_limit(0)可以让程序无限制的执行下去
       $crontabInfo = Db::name('crontab')->where('state',0)->find();
       if($crontabInfo){
        //   Log::write('查询到一条任务'); 
          Db::name('crontab')->where('id',$crontabInfo['id'])->update(['state'=>1]);
       $carmi = $crontabInfo['carmi'];
       
       $carmiInfo = Db::name('carmi')->where('carmi',$carmi)->find();
      
        // 查询用户数据并联表查询相关数据
        $songInfo = Db::name('songlist')->where('id',$carmiInfo['songlist_id'])->find();
        
          // 遍历查询结果，追加相关数据到 `data` 字段
        $musiclist  = Db::name('music')->where('songlist_id',$songInfo['id'])->select();
         
         $host = $_SERVER['HTTP_HOST'];
        // 使用正则表达式匹配所有汉字字符
        preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $songInfo['name'], $matches);
        // 获取前三个汉字
        $songInfo['name'] = implode('', array_slice($matches[0], 0, 3));
      
      
      
     
        $response1 = Http::get('http://127.0.0.1:3000/playlist/create?hosts='.$host.'&name='.urlencode($songInfo['name']).'&time='.time().'&cookie='.urlencode($carmiInfo['cookie']));
        $response1 = json_decode($response1,true);
        // 	  完成创建歌单
        if(!empty($response1['playlist']["id"])){
        //   Log::write('创建歌单ID：'.$response1['playlist']["id"]); 
           Db::name('crontab')->where('id',$crontabInfo['id'])->update(['createsong'=>1]);
           
           Db::name('carmi')->where('carmi',$carmi)->update(['singId' => $response1['playlist']["id"]]);
            // 				// 循环导入歌曲
            if(count($musiclist) > 0){
                for($i = 0; $i < count($musiclist); $i++) {
                
                $music = $musiclist[$i];
                
                	$response2 = Http::get('http://127.0.0.1:3000/cloud/import?hosts='.$host.'&song='.urlencode($music['song']).'&artist='.urlencode($music['artist']).'&album='.urlencode($music['album']).'&fileType='.$music['fileType'].'&fileSize='.$music['fileSize'].'&bitrate='.$music['bitrate'].'&md5='.$music['md5'].'&time='.time().'&cookie='.urlencode($carmiInfo['cookie']));
                	
                	 $response2 = json_decode($response2,true);
                	  Log::write('上传歌曲：'.json_encode($response2['data'])); 
                	if(empty($response2['data']['successSongs'])){
                	    unset($musiclist[$i]);
                	}else{
                	    Db::name('crontab')->where('id',$crontabInfo['id'])->setInc('music');
                	}
                
                }
                 Db::name('crontab')->where('id',$crontabInfo['id'])->update(['musics'=>count($musiclist)]);
                
                
                        // 提取所有 id 值
                $ids = array_column($musiclist, 'musicid');
                
                // 将 id 值用逗号连接成字符串
                $ids = implode(',', $ids);
                
                Log::write('音乐ids：'.$ids); 
                $response3 = Http::get('http://127.0.0.1:3000/playlist/tracks?hosts='.$host.'&op=add&pid='.$response1['playlist']["id"].'&time='.time().'&tracks='.$ids.'&time='.time().'&cookie='.urlencode($carmiInfo['cookie']));
                
                Db::name('carmi')->where('carmi',$carmi)->update(['status' => 2]);
            }
            
            	
             Db::name('crontab')->where('id',$crontabInfo['id'])->update(['state'=>2]);
             
             
             $names ="歌单里没有请到网盘查看";
              Http::get('http://127.0.0.1:3000/playlist/create?hosts='.$host.'&name='.urlencode($names).'&time='.time().'&cookie='.urlencode($carmiInfo['cookie']));
            $this->success('上传完成');
        }
            
       }
         
       
    }
    
    
   
   
}






















