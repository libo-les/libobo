<?php

/** api接口返回数据
 *
 * @param string $errcode
 * @param string $errmsg
 * @param array $result
 * @param integer $http_code
 * @return void
 */
function apiReturn($errcode = 0, $errmsg = "", $result = [], $http_code = 200)
{
    if (empty($errmsg)) {
        $errmsg = config('errcode.' . $errcode) ?? '失败';
    }
    $data = [
        'errcode' => $errcode,
        'errmsg'  => $errmsg,
        'result'  => $result,
    ];
    return response()->json($data, $http_code);
}

if (!function_exists('http_request')) {
    /**
     * 获取网络请求
     * @param  [type] $method   [description]
     * @param  [type] $uri      [description]
     * @param  [type] $options  [description]
     * @param  string $dataType [description]
     * @return [type]           [description]
     */
    function http_request($method, $uri, $options, $dataType = 'text')
    {
        $httpClient = new \GuzzleHttp\Client([
            'verify' => false,
        ]);

        try {
            $res      = $httpClient->request($method, $uri, $options);
            $response = $res->getBody()->getContents();
        } catch (GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse()->getBody()->getContents();
        }

        if ($dataType === 'json') {
            $response = json_decode($response, true);
        } else if ($dataType === 'jsonp') {
            if (!preg_match('/\((.*?)\)/i', $response, $matches)) {
                return false;
            }
            $response = json_decode($matches[1], true);
        }

        return $response;
    }
}

if (!function_exists('mx_ssl_public_encrypt')) {
    /**
     * 萌信公钥加密
     * @param  [type] $originalData [description]
     * @param  [type] $rsaPublicKey [description]
     * @return [type]               [description]
     */
    function mx_ssl_public_encrypt($originalData, $rsaPublicKey)
    {
        $crypto = '';
        foreach (str_split($originalData, 117) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $rsaPublicKey);
            $crypto .= $encryptData;
        }
        return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($crypto));
    }
}

if (!function_exists('mx_ssl_private_encrypt')) {
    /**
     * 萌信私钥加密
     * @param  [type] $originalData  [description]
     * @param  [type] $rsaPrivateKey [description]
     * @return [type]                [description]
     */
    function mx_ssl_private_encrypt($originalData, $rsaPrivateKey)
    {
        $crypto = '';
        foreach (str_split($originalData, 117) as $chunk) {
            openssl_private_encrypt($chunk, $encryptData, $rsaPrivateKey);
            $crypto .= $encryptData;
        }
        return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($crypto));
    }
}

if (!function_exists('mx_ssl_public_decrypt')) {
    /**
     * 萌信公钥解密
     * @param  [type] $encryptData  [description]
     * @param  [type] $rsaPublicKey [description]
     * @return [type]               [description]
     */
    function mx_ssl_public_decrypt($encryptData, $rsaPublicKey)
    {
        $encryptData = str_replace(array('-', '_'), array('+', '/'), $encryptData);
        if ($mod4 = strlen($encryptData) % 4) {
            $encryptData .= substr('====', $mod4);
        }

        $crypto = '';
        foreach (str_split(base64_decode($encryptData), 128) as $chunk) {
            openssl_public_decrypt($chunk, $decryptData, $rsaPublicKey);
            $crypto .= $decryptData;
        }
        return $crypto;
    }
}

if (!function_exists('mx_ssl_private_decrypt')) {
    /**
     * 萌信私钥解密
     * @param  [type] $encryptData   [description]
     * @param  [type] $rsaPrivateKey [description]
     * @return [type]                [description]
     */
    function mx_ssl_private_decrypt($encryptData, $rsaPrivateKey)
    {
        $encryptData = str_replace(array('-', '_'), array('+', '/'), $encryptData);
        if ($mod4 = strlen($encryptData) % 4) {
            $encryptData .= substr('====', $mod4);
        }

        $crypto = '';
        foreach (str_split(base64_decode($encryptData), 128) as $chunk) {
            openssl_private_decrypt($chunk, $decryptData, $rsaPrivateKey);
            $crypto .= $decryptData;
        }
        return $crypto;
    }
}

if (!function_exists('send_code')) {
    /**
     * 发送短信
     */
    function send_code($param)
    {
        \AlibabaCloud\Client\AlibabaCloud::accessKeyClient('LTAISPD0dTrMEvwZ', 'fhwTiyzalghZhQEC4ouisBjT4b5c1R')
            ->regionId('cn-hangzhou') // replace regionId as you need
            ->asDefaultClient();

            try {
                $result = \AlibabaCloud\Client\AlibabaCloud::rpc()
                ->product('Dysmsapi')
            // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId'      => "default",
                        'PhoneNumbers'  => $param['phone'],
                        'SignName'      => $param['sign'],
                        'TemplateCode'  => $param['template'],
                        'TemplateParam' => json_encode($param['message']),
                    ],
                ])
                ->request();
                return $result->Code;
            } catch (\AlibabaCloud\Client\Exception\ClientException $e) {
                return $e->getErrorMessage();
            } catch (\AlibabaCloud\Client\Exception\ServerException $e) {
                return $e->getErrorMessage();
            }
        }
    }

    if (!function_exists('checkVerify')) {
    /**
     * 校验验证码
     * @param  [type] $user_id 用户id
     * @param  [type] $sms_type    记录类型 1添加银行卡addcard 2绑定手机bindtel
     * @return [type]          [description]
     */
    function checkVerify($user_ap_id, $telephone, $verify, $sms_type)
    {
        if (empty($telephone)) {
           return false;
       }
       $user_key = $user_ap_id . '_' . $sms_type . '_' . $telephone . '_' . $verify;
       if (cache("$user_key") == $verify) {

        \Illuminate\Support\Facades\Cache::forget("$user_key");
        return true;
    }
    return false;
}
}

function func_is_base64($str)
{
    // return $str == base64_encode(base64_decode($str)) ? true : false;
    $aa = strstr($str, 'storage');
    if (empty($aa)) {
        return true;
    } else {
        return false;
    }

}
 
function getmenuList($act_lists){
    if ($act_lists!='all') {
         $act_lists = explode(',', $act_lists);
        
         $list =DB::table('sys_module')->whereIn('id',$act_lists)->where('is_menu',1)->select('id','pid','module_name','controller','method','is_menu','level','route')->get()->toArray();
         $tab = array();
         foreach ($list as $key => $value) {
            $tab[]=$value['controller'].'@'.$value['method'];
        }

        //去重
        $tab = array_values(array_unique($tab));

        $getAllMenu=DB::table('sys_module')->where('is_menu',1)->get()->toArray();
         $items = array();
                foreach($getAllMenu as $value){
                    $items[$value['id']] = $value;
                }
                $tree = array();
                foreach($items as $key => $value){
        //如果pid这个节点存在
                    if(isset($items[$value['pid']])){
                //把当前的$value放到pid节点的son中
                      $items[$value['pid']]['sub_menu'][] = &$items[$key];
                  }else{
                      $tree[] = &$items[$key];
                  }
              }
        $menu_list = $tree;
        foreach ($menu_list as $key => $value) {
            if (in_array('sub_menu',$value)&&is_array($value['sub_menu'])&&$value['level']==1) {  //判断是否有下级
                foreach ($value['sub_menu'] as $k => $v) {

                   if (in_array('sub_menu',$v)&&!empty($v['sub_menu'])) {
                        if (!in_array($v['controller'] . '@' . $v['method'], $tab)) {
                                unset($menu_list[$key]['sub_menu'][$k]);//过滤菜单
                        }else{
                            foreach ($v['sub_menu'] as $i => $j) {     
                               if (!in_array($j['controller'] . '@' . $v['method'],$tab)) {
                                  unset($menu_list[$key]['sub_menu'][$k]['sub_menu'][$i]);
                              }
                             }
                        }
                       

                    }else{
                        if (!in_array($v['controller'] . '@' . $v['method'], $tab)) {
                                unset($menu_list[$key]['sub_menu'][$k]);//过滤菜单
                        }
                       
                    }
                }        

            }else{
               unset($menu_list[$key]);
               
            }

        }
           
        foreach ($menu_list as $key => $value) {
            if (empty($value['sub_menu'])) {
                unset($menu_list[$key]);
            }
            foreach ($value['sub_menu'] as $k => $v) {
              if (in_array('sub_menu',$v)&&!empty($v['sub_menu'])) {
                    foreach ($v['sub_menu'] as $i => $j) {
                          array_multisort(array_column($menu_list[$key]['sub_menu'][$k]['sub_menu'],'sort'),SORT_ASC,SORT_NUMERIC,$menu_list[$key]['sub_menu'][$k]['sub_menu']);   
                    }
              }
            }
        }
        $data=array();
        foreach ($menu_list as $key => $value) {  
              $data[$key]['name']=$value['module_name'];
              $data[$key]['path'] = $value['route'];
              $data[$key]['sort'] = $value['sort'];
             foreach ($value['sub_menu'] as $k => $v) {
                    $data[$key]['sub_menu'][$k]['name'] = $v['module_name'];
                    $data[$key]['sub_menu'][$k]['path'] = $v['route'];
                    $data[$key]['sub_menu'][$k]['sort'] = $v['sort'];
                    if (in_array('sub_menu',$v)&&!empty($v['sub_menu'])) {
                        foreach ($v['sub_menu'] as $i => $j) {
                            $data[$key]['sub_menu'][$k]['sub_menu'][$i]['name'] = $j['module_name'];
                            $data[$key]['sub_menu'][$k]['sub_menu'][$i]['path'] = $j['route'];
                            $data[$key]['sub_menu'][$k]['sub_menu'][$i]['sort'] = $j['sort'];
                            
                        }
                    }

             }
             array_multisort(array_column($data[$key]['sub_menu'],'sort'),SORT_ASC,SORT_NUMERIC,$data[$key]['sub_menu']);
        }
         return $data;

    }else{  
      
                 $getAllMenu=DB::table('sys_module')->where('is_menu',1)->get()->toArray();
                 $items = array();
                 foreach($getAllMenu as $value){
                    $items[$value['id']] = $value;
                }
                $tree = array();
                foreach($items as $key => $value){
                            //如果pid这个节点存在
                    if(isset($items[$value['pid']])){
                                    //把当前的$value放到pid节点的son中
                      $items[$value['pid']]['sub_menu'][] = &$items[$key];
                  }else{
                      $tree[] = &$items[$key];
                  }
              }
              //排序
        foreach ($tree as $key => $value) {
         array_multisort(array_column($tree[$key]['sub_menu'],'sort'),SORT_ASC,SORT_NUMERIC,$tree[$key]['sub_menu']);
         foreach ($value['sub_menu'] as $k => $v) {
          if (in_array('sub_menu',$v)&&!empty($v['sub_menu'])) {
            foreach ($v['sub_menu'] as $i => $j) {
              array_multisort(array_column($tree[$key]['sub_menu'][$k]['sub_menu'],'sort'),SORT_ASC,SORT_NUMERIC,$tree[$key]['sub_menu'][$k]['sub_menu']);   
            }
          }
        }
      }

          $menu_list = $tree;
          $data=array();
        foreach ($menu_list as $key => $value) {  
              $data[$key]['name']=$value['module_name'];
              $data[$key]['path'] = $value['route'];
              $data[$key]['sort'] = $value['sort'];
             foreach ($value['sub_menu'] as $k => $v) {
                    $data[$key]['sub_menu'][$k]['name'] = $v['module_name'];
                    $data[$key]['sub_menu'][$k]['path'] = $v['route'];
                    $data[$key]['sub_menu'][$k]['sort'] = $v['sort'];
                    if (in_array('sub_menu',$v)&&!empty($v['sub_menu'])) {
                        foreach ($v['sub_menu'] as $i => $j) {
                            $data[$key]['sub_menu'][$k]['sub_menu'][$i]['name'] = $j['module_name'];
                            $data[$key]['sub_menu'][$k]['sub_menu'][$i]['path'] = $j['route'];
                            $data[$key]['sub_menu'][$k]['sub_menu'][$i]['sort'] = $j['sort'];
                            
                        }
                    }

             }
             array_multisort(array_column($data[$key]['sub_menu'],'sort'),SORT_ASC,SORT_NUMERIC,$data[$key]['sub_menu']);
        }

         return $data;

    }    
  
    
}

 