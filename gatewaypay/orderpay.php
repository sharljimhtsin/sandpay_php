<?php

namespace gatewaypay;

use gatewaypay\common;
use think\Request;

class orderpay extends payinterface
{
    private function getToken($map)
    {
        date_default_timezone_set("Asia/Shanghai");
        $prikey = common::loadPk12Cert(common::PRI_KEY_PATH, common::CERT_PWD);
        $pubkey = common::loadX509Cert(common::PUB_KEY_PATH);

        $data = array(
            'head' => array(
                'version' => '1.0',
                'method' => 'sandpay.trade.pay',
                'productId' => '00000007',
                'accessType' => '1',
                'mid' => $map['mid'],
                'channelType' => '07',
                'reqTime' => date('YmdHis', time())
            ),
            'body' => array(
                'orderCode' => $map['orderCode'],
                'totalAmount' => $map['totalAmount'],
                'subject' => $map['subject'],
                'body' => $map['body'],
                'txnTimeOut' => $map['txnTimeOut'],
                'payMode' => $map['payMode'],
                'payExtra' => json_encode(array('payType' => $map['payType'], 'bankCode' => $map['bankCode'])),
                'clientIp' => $map['clientIp'],
                'notifyUrl' => $map['notifyUrl'],
                'frontUrl' => $map['frontUrl'],
                'extend' => ''
            )
        );

        $sign = common::sign($data, $prikey);
        $post = array(
            'charset' => 'utf-8',
            'signType' => '01',
            'data' => json_encode($data),
            'sign' => $sign
        );
        $result = common::http_post_json(common::API_HOST . '/order/pay', $post);
        parse_str(urldecode($result), $arr);
        $arr['data'] = str_replace(array("  ", "\t", "\n", "\r"), array('', '', '', ''), $arr['data']);
        $data = json_decode($arr['data'], true);
        $credential = json_decode($data['body']['credential'], true);

        if (isset($credential['params']['orig']) && isset($credential['params']['sign'])) {

            $arr['data'] = common::mb_array_chunk($data);
            $arr['data'] = str_replace(array("\\\/", "\\/", "\/"), array("/", "/", "/"), $arr['data']);

        } else {

            $data['body']['credential'] = common::json_encodes($credential);
            //使用第二参数JSON_UNESCAPED_UNICODE,阻止json_encode()转译汉字
            $arr['data'] = str_replace(array("\\\/", "\\/", "\/", " "), array("/", "/", "/", "+"), common::json_encodes($data));
        }

        $arr['sign'] = preg_replace('/\s/', '+', $arr['sign']);
        //验签
        common::verify($arr['data'], $arr['sign'], $pubkey);

        $data = json_decode($arr['data'], 320);

        if ($data['head']['respCode'] == "000000") {
            $credential = str_replace(array('"{', '}"'), array('{', '}'), stripslashes($data['body']['credential']));
        } else {
            $credential = "";
        }
        return $credential;
    }

    public function pay($orderSn, $amt, $bankId, $cardType, $type)
    {
        //约定金额单位为元
        $amt = floatval($amt) * 100;
        $amt = strval($amt);
        $len = strlen($amt);
        for ($i = 0; $i < (12 - $len); $i++) {
            $amt = "0" . $amt;
        }

        $post = array(
            "mid" => common::MID,
            "orderCode" => $orderSn,
            "totalAmount" => $amt,
            "subject" => "biaoti",
            "body" => "neirong",
            "txnTimeOut" => date('YmdHis', time() + 60 * 60 * 1),
            "payMode" => "bank_pc",
            "bankCode" => $bankId,
            "payType" => "1",
            "clientIp" => "47.94.149.36",
            "notifyUrl" => Request::instance()->domain() . '/api/notify/SdpayAlt',
            "frontUrl" => Request::instance()->domain() . '/api/notify/Sdpay',
            "extend" => ""
        );
        $credentialStr = $this->getToken($post);
        $credentialObj = json_decode($credentialStr, JSON_OBJECT_AS_ARRAY);
        $newData = $credentialObj["params"];
        $newData["actionUrl"] = $credentialObj["submitUrl"];
        return $newData;
    }

    public function bankList($type)
    {

        /*
         *
        <option value="01020000" >工商银行</option>
        <option value="01050000" selected="selected">建设银行</option>
        <option value="01030000">农业银行</option>
        <option value="03080000">招商银行</option>
        <option value="03010000">交通银行</option>
        <option value="01040000">中国银行</option>
        <option value="03030000">光大银行</option>
        <option value="03050000">民生银行</option>
        <option value="03090000">兴业银行</option>
        <option value="03020000">中信银行</option>
        <option value="03060000">广发银行</option>
        <option value="03100000">浦发银行</option>
        <option value="03070000">平安银行</option>
        <option value="03040000">华夏银行</option>
        <option value="04083320">宁波银行</option>
        <option value="03200000">东亚银行</option>
        <option value="04012900">上海银行</option>
        <option value="01000000">中国邮储银行</option>
        <option value="04243010">南京银行</option>
        <option value="65012900">上海农商行</option>
        <option value="03170000">渤海银行</option>
        <option value="64296510">成都银行</option>
        <option value="04031000">北京银行</option>
        <option value="64296511">徽商银行</option>
        <option value="04341101">天津银行</option>
        *
         *
         */

        $data = array();
        $data['bankList']['bankRow'] = array(

            array('bankName' => '工商银行',
                'bankID' => '01020000',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '兴业银行',
                'bankID' => '03090000',
                'otherBankID' => '',
                'cardType' => ''
            ),

            array('bankName' => '农业银行',
                'bankID' => '01030000',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '招商银行',
                'bankID' => '03080000',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '建设银行',
                'bankID' => '01050000',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '中国银行',
                'bankID' => '01040000',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '交通银行',
                'bankID' => '03010000',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '浦发银行',
                'bankID' => '03100000',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '平安银行',
                'bankID' => '03070000',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '光大银行',
                'bankID' => '03030000',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '华夏银行',
                'bankID' => '03040000',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '民生银行',
                'bankID' => '03050000',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '邮储银行',
                'bankID' => '01000000',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '广发银行',
                'bankID' => '03060000',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '中信银行',
                'bankID' => '03020000',
                'otherBankID' => '',
                'cardType' => ''
            ),
        );

        return $data;

        //对返回的XML数据进行解析
//        return $data = iconv("GB2312","UTF-8",$responseText);

    }
}

?>