<?php

namespace payment;

class common
{
    //--------------------------------------------1、基础参数配置------------------------------------------------
    /*
     *
     * 生产：URL=https://cashier.sandpay.com.cn/gateway/api
       测试：URL=http://61.129.71.103:8003/gateway/api
     *
     */
    const API_HOST_TEST = 'http://61.129.71.103:8003/gateway/api';//测试环境地址
    const API_HOST = 'https://cashier.sandpay.com.cn/gateway/api';//测试环境地址
    const DF_HOST_TEST = 'http://61.129.71.103:7970/agent-main/openapi';//测试环境地址
    const DF_HOST = 'https://caspay.sandpay.com.cn/agent-main/openapi/';//测试环境地址
    const PUB_KEY_PATH = '../extend/payment/a.cer';//测试环境公钥
    const PRI_KEY_PATH = '../extend/payment/b.pfx';//测试环境私钥
    const CERT_PWD = '123456'; //私钥证书密码
    const MID = '17123285'; //商户号

//--------------------------------------------end基础参数配置------------------------------------------------
    /**
     *获取公钥
     * @param  [$path]
     * @return [mixed]
     * @throws [\Exception]
     */
    static function loadX509Cert($path)
    {
        try {
            $file = file_get_contents($path);
            if (!$file) {
                throw new \Exception('loadx509Cert::file_get_contents ERROR');
            }

            $cert = chunk_split(base64_encode($file), 64, "\n");
            $cert = "-----BEGIN CERTIFICATE-----\n" . $cert . "-----END CERTIFICATE-----\n";

            $res = openssl_pkey_get_public($cert);
            $detail = openssl_pkey_get_details($res);
            openssl_free_key($res);

            if (!$detail) {
                throw new \Exception('loadX509Cert::openssl_pkey_get_details ERROR');
            }

            return $detail['key'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 获取私钥
     * @param  [$path]
     * @param  [$pwd]
     * @return [mixed]
     * @throws [\Exception]
     */
    static function loadPk12Cert($path, $pwd)
    {
        try {
            $file = file_get_contents($path);
            if (!$file) {
                throw new \Exception('loadPk12Cert::file
					_get_contents');
            }

            if (!openssl_pkcs12_read($file, $cert, $pwd)) {
                throw new \Exception('loadPk12Cert::openssl_pkcs12_read ERROR');
            }
            return $cert['pkey'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 私钥签名
     * @param [$plainText]
     * @param [$path]
     * @return [string]
     * @throws [\Exception]
     */
    static function sign($plainText, $path)
    {
        $plainText = json_encode($plainText);
        try {
            $resource = openssl_pkey_get_private($path);
            $result = openssl_sign($plainText, $sign, $resource);
            openssl_free_key($resource);
            if (!$result) {
                throw new \Exception('签名出错' . $plainText);
            }
            return base64_encode($sign);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 发送post请求
     * @param string $url 请求地址
     * @param array $post_data post键值对数据
     * @return string
     */
    function send_post($url, $post_data)
    {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }


    /**
     * PHP发送Json对象数据
     *
     * @param $url 请求url
     * @param $jsonStr 发送的json字符串
     * @return string
     */
    static function http_post_json($url, $param)
    {
        if (empty($url) || empty($param)) {
            return false;
        }
        $param = http_build_query($param);
        try {
            $ch = curl_init();//初始化curl
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //正式环境时解开注释
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $data = curl_exec($ch);//运行curl
            curl_close($ch);
            if (!$data) {
                throw new \Exception('请求出错');
            }
            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * 公钥验签
     * @param  [$plainText[]
     * @param  [$sign[]
     * @return [int]
     * @throws [\Exception]
     */
    static function verify($plainText, $sign, $path)
    {
        $resource = openssl_pkey_get_public($path);
        $result = openssl_verify($plainText, base64_decode($sign), $resource);
        openssl_free_key($resource);
        if (!$result) {
            throw new \Exception('签名验证未通过,plainText:' . $plainText . '。sign:' . $sign, '02002');
        }
        return $result;
    }

    /**
     * 对数组变量进行JSON编码，为了（本系统的PHP版本为5.3.0）解决PHP5.4.0以上才支持的JSON_UNESCAPED_UNICODE参数
     * @param mixed array 待编码的 array （除了resource 类型之外，可以为任何数据类型，改函数只能接受 UTF-8 编码的数据）
     * @return  string （返回 array 值的 JSON 形式）
     * @author
     * @d/t     2017-07-17
     */
    static function json_encodes($array)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $str = json_encode($array);
            $str = preg_replace_callback("#\\\u([0-9a-f]{4})#i", function ($matchs) {
                return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
            }, $str);
            return $str;
        } else {
            return json_encode($array, 320);
        }
    }

    /**
     * 分割字符串
     * @param String $str 要分割的字符串
     * @param int $length 指定的长度
     * @param String $end 在分割后的字符串块追加的内容
     * @return string
     */
    static function mb_chunk_split($string, $length, $end, $once = false)
    {
        $string = iconv('gb2312', 'utf-8//ignore', $string);
        $array = array();
        $strlen = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string, 0, $length, "utf-8");
            if ($once)
                return $array[0] . $end;
            $string = mb_substr($string, $length, $strlen, "utf-8");
            $strlen = mb_strlen($string);
        }
        $str = implode($end, $array);
        return $str . '%0A';
    }


    static function mb_array_chunk($arr)
    {
        $credential = json_decode($arr['body']['credential'], true);
        $credential['params']['orig'] = self::mb_chunk_split($credential['params']['orig'], 76, '%0A');
        $credential['params']['sign'] = self::mb_chunk_split($credential['params']['sign'], 76, '%0A');
        $arr['body']['credential'] = str_replace(array('==', '+', '='), array('%3D%3D', '%2B', '%3D'), self::json_encodes($credential));
        return self::json_encodes($arr);
    }

    static function generate($size)
    {
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $arr = array();
        for ($i = 0; $i < $size; $i++) {
            $arr[] = $str[mt_rand(0, 61)];
        }
        return implode('', $arr);
    }

    /**
     * AES解密
     * @param $cipherText
     * @param $key
     * @return string
     * @throws \Exception
     */
    static function AESDecrypt($cipherText, $key)
    {
        $result = openssl_decrypt(base64_decode($cipherText), 'AES-128-ECB', $key, 1);

        if (!$result) {
            throw new \Exception('报文解密错误', 2003);
        }
        return $result;
    }

    /**
     * AES加密
     * @param $plainText
     * @param $key
     * @return string
     * @throws \Exception
     */
    static function AESEncrypt($plainText, $key)
    {
        try {
            $result = openssl_encrypt($plainText, 'AES-128-ECB', $key, 1);

            if (!$result) {
                throw new \Exception('报文加密错误');
            }
            return base64_encode($result);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * RSA私钥解密
     * @param $cipherText
     * @param $prk
     * @return string
     * @throws \Exception
     */
    static function RSADecryptByPri($cipherText, $prk)
    {
        if (!openssl_private_decrypt(base64_decode($cipherText), $plainText, $prk, OPENSSL_PKCS1_PADDING)) {
            throw new \Exception('AESKey 解密错误');
        }
        return (string)$plainText;
    }

    /**
     * RSA公钥加密
     * @param $plainText
     * @param $puk
     * @return string
     * @throws \Exception
     */
    static function RSAEncryptByPub($plainText, $puk)
    {
        try {
            if (!openssl_public_encrypt($plainText, $cipherText, $puk, OPENSSL_PKCS1_PADDING)) {
                throw new \Exception('AESKey 加密错误');
            }
            return base64_encode($cipherText);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}