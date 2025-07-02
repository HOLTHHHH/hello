<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use fast\Http;
/**
 * 首页接口
 */
class Index extends Api
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
    
    
    public function music_list()
    {
        
        $name = $this->request->post('name');
        
        if(empty($name)){
             $this->success('请输入歌名');
        }
        $is_shield =  Db::name('shield')->where('name',$name)->find();
        
        $data['vx'] = config('site.vxhao');
        
        if($is_shield){
             $this->error($is_shield['content'],$data);
        }
        
        
         $music_list =  Db::name('music')->where('song','like','%'.$name.'%')->select();
         if(count($music_list) == 0){
                
                 $this->error('暂无歌曲 请点击联系客服更新',$data);
            }
         $this->success('查询成功',$music_list);
    }
    
     public function setsong()
    {
        
        $songlist_id = $this->request->post('songlist_id');
        $carmi = $this->request->post('carmi');
        
         $is_up =  Db::name('carmi')->where('carmi',$carmi)->update(['songlist_id'=>$songlist_id]);
        if($is_up){
             $this->success('选择成功');
        }else{
             $this->error('选择失败');
        }
        
    }
    
    
       
    public function sing_list()
    {
        
        $name = $this->request->post('name');
        $carmi = $this->request->post('carmi');
        
        
        
         $is_karmi =  Db::name('carmi')->where('carmi',$carmi)->find();
        if($is_karmi && ($is_karmi['songlist_id'] == 2 || $is_karmi['songlist_id'] == 1)){
            
            if(empty($name)){
             $this->success('请输入歌手');
            }
            
            $is_shield =  Db::name('shield')->where('name',$name)->find();
            
            $data['vx'] = config('site.vxhao');
            
            if($is_shield){
                 $this->error($is_shield['content'],$data);
            }
        
            
            
            
            
             $music_list =  Db::name('songlist')->where('name','like','%'.$name.'%')->select();
             
             // 遍历查询结果，修改每个记录的 name 字段
             
             $arr = [];
             
            foreach ($music_list as $k => &$song) {
                
              $cou =  Db::name('music')->where('songlist_id',$song['id'])->count();
              
              // 假设要修改 name 字段，比如将它转换为大写
                 $song['name'] =$song['name']. '('.$cou.')'  ;  // 这里是修改 name 字段的逻辑
                 
                 
                if($is_karmi['songlist_id'] == 2 && $cou >10){
                    unset($music_list[$k]);
                }elseif($is_karmi['songlist_id'] == 1 && $cou <20){
                    unset($music_list[$k]);
                }else{
                    $arr[] =$music_list[$k];
                }
                
            }
            
            if(count($music_list) == 0){
                 $this->error('暂无歌曲 请点击联系客服更新',$data);
            }
            
            
        
        
        
         $this->success('查询成功',$arr);
        }
        
    }
    
    // 验证卡密
     public function carmi()
    {
        $carmi = $this->request->post('carmi');
        
        $cookie = $this->request->post('cookie','');
        
        $userId = $this->request->post('userId','');
        
        $nickname = $this->request->post('nickname','');
        if(!$carmi){
            
            $this->error('卡密错误');
        }
        $karmiwhere['carmi'] = $carmi;
        //  $karmiwhere['status'] = ['<>',2];
        
       
         
        $is_karmi =  Db::name('carmi')->where($karmiwhere)->find();
        $is_karmi['ad_list'] =Db::name('ad')->where('switch',1)->select();
        $is_karmi['songlist_text'] = Db::name('songlist')->where('id',$is_karmi['songlist_id'])->value('name');
        if(!$is_karmi){
           $this->error('卡密不可用');
        }
        
        if($is_karmi['status'] ==  '9'){
           $this->error('已被禁用',$is_karmi);
        }
         if($is_karmi['status'] ==  '1'){
          $this->error('上传中',$is_karmi);
        }
        if($is_karmi['status'] ==  '2'){
          $this->error('已完成',$is_karmi);
        }
        
        
         $is_zhicai =  Db::name('zhicai')->where('carmi',$carmi)->find();
         if(!empty($is_zhicai)){
            $this->error('已被限制使用',$is_karmi);
            
        }   
        
        if(!empty($userId)){
            $is_zhicai =  Db::name('zhicai')->where('userId',$userId)->find();
            if(!empty($is_zhicai)){
                 $this->error('已被限制使用',$is_karmi);
            
            }
        }
        
        
        
        
        if(!empty($is_karmi['userId'])){
           if($is_karmi['userId'] != $userId){
              $this->error('当前卡密已绑定其他用户');
           }
        }
        
         if(!empty($userId) ||  !empty($cookie)){
            $updatekarmi['cookie'] = $cookie;
            $updatekarmi['userId'] = $userId;
            $updatekarmi['nickname'] = $nickname;
            
            $updatekarmi['status'] = 1;
            Db::name('carmi')->where($karmiwhere)->update($updatekarmi);
            
            $data = ['carmi' => $carmi, 'time' => time()];
            Db::name('crontab')->insert($data);
            
            
            $this->success('开始执行上传');
             
        }else{
            
            
               $this->success('获取成功',$is_karmi);
        }
              
        
   
              
        
    }
    

  
    
  
  
  
  
//   public function curl($url){
//             $ch = curl_init();
            
//             // 设置请求 URL
//             curl_setopt($ch, CURLOPT_URL, $url);
            
//             // 让 cURL 返回响应，而不是直接输出
//             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
//             // 禁用 SSL 证书验证（开发环境下使用）
//             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//             curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//             // 强制使用 TLS 1.2
//             curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
            
//             // 设置请求头（如需要）
//             curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//                 'Content-Type: application/json',
//                 'Accept: application/json',
//                 'User-Agent: Mozilla/5.0'
//             ));
            
//             // 发送请求
//             $response = curl_exec($ch);
            
//             // 检查 cURL 是否有错误
//             if ($response === false) {
//             return  curl_error($ch);
//             } else {
//             return $response;
//             }
//     }

        
        
//         public function phpRequest($url, $method = 'GET', $data = null) {
//             // 初始化 cURL 会话
//             $ch = curl_init();
//             // 设置 cURL 选项
//             curl_setopt($ch, CURLOPT_URL, 'http://116.62.226.197:3000' . $url);
//             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取响应内容而不是直接输出
//             curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // 设置请求头为 JSON
            
//             if ($method == 'POST' && $data !== null) {
//                 // 如果是 POST 请求，设置请求体
//                 curl_setopt($ch, CURLOPT_POST, true);
//                 curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // 将数据编码为 JSON
//             } elseif ($method == 'PUT' && $data !== null) {
//                 // 如果是 PUT 请求，设置请求体
//                 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
//                 curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
//             } elseif ($method == 'DELETE' && $data !== null) {
//                 // 如果是 DELETE 请求，设置请求体
//                 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
//                 curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
//             }
            
//             // 执行 cURL 请求并获取响应
//             $response = curl_exec($ch);
           
//             // 获取 HTTP 状态码
//             $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//             // 如果发生错误，输出错误信息
//             if(curl_errno($ch)) {
//                 echo 'Error:' . curl_error($ch);
//             }
//             // 关闭 cURL 会话
//             curl_close($ch);
//             // 返回响应内容
//             return $response;
//         }



    
    
    
    
    
    
    
    
    
    
    
    
    
     
       // 歌单列表
     public function createsonglist()
    {
        $playlist = $this->request->post('');
        
       
        $songwhere['songid'] = $playlist['id'] ?? '';
        
        $is_song =  Db::name('songlist')->where($songwhere)->find();
        
        if($is_song){
             $updatedata = [ 'name' => $playlist['name'], 'createtime' => time(), 'updatetime' => time()];
            Db::name('songlist')->where('songid',$playlist['id'])->update($updatedata);
            
        }else{
            $insertdata = ['songid' => $playlist['id'], 'name' => $playlist['name'], 'createtime' => time(), 'updatetime' => time()];
            Db::name('songlist')->insert($insertdata);
        }
        
   
       $this->success('更新歌单成功');   
      
        
    }
    
    
       // 歌单列表
     public function creatmusic()
    {
        $list = $this->request->post('');
       
        $songlist_id  = Db::name('songlist')->where('songid',$list['musiclist'][0]["songlist_id"])->value('id');
        
        
        
       // 使用 array_map 提取 id 和 name
$result = array_map(function($item) use ($songlist_id)  {
    return [
        'bitrate' => $item['bitrate'],
        'fileSize' => $item['fileSize'],
        'fileType'=> $item['fileType'],
        'md5' => $item['md5'],
        'musicid' => $item['musicid'],
        'song' => $item['song'],
        'songlist_id' => $songlist_id
    ];
}, $list['musiclist']);
       
       
       
       
       if($list['type'] != 'add'){
           Db::name('music')->where('songlist_id',$songlist_id)->delete();
       }
      
       
      Db::name('music')->insertAll($result);
   
       $this->success('更新音乐成功');   
      
        
    }
    

    
    
    
    

    
    
    
    
    
    
    
    
    
    
}






















