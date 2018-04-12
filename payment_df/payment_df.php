<?php

namespace payment;

use payment\common;

class payment_df extends payinterface
{
    private function getToken($map)
    {
        $prikey = common::loadPk12Cert(common::PRI_KEY_PATH, common::CERT_PWD);
        $pubkey = common::loadX509Cert(common::PUB_KEY_PATH);
        $AESKey = common::generate(16);

        $AgentPay = array(
            'transCode' => 'RTPM',//实时代付
            'merId' => $map['mid'],
            'url' => common::DF_HOST . '/agentpay',
            'pt' => array(
                'orderCode' => $map['orderCode'],
                'version' => '01',
                'productId' => '00000004',
                'tranTime' => date('YmdHis', time()),
                'timeOut' => $map['txnTimeOut'],
                'tranAmt' => $map['totalAmount'],
                'currencyCode' => '156',
                'accAttr' => '0',
                'accNo' => $map["accNo"],
                'accType' => '4',
                'accName' => '全渠道',
//        'provNo' => 'sh',
//        'cityNo' => 'sh',
                'bankName' => 'cbc',//收款账户开户行名称
                'bankType' => '1',//收款人账户联行号
                'remark' => 'pay',
                'payMode' => '2'
            )
        );

        //入参json格式化
        $AgentPay['pt'] = json_encode($AgentPay['pt']);
        //AESKey 加密
        $encryptKey = common::RSAEncryptByPub($AESKey, $pubkey);
        //报文加密
        $encryptData = common::AESEncrypt($AgentPay['pt'], $AESKey);
        //签名
        $sign = common::sign($AgentPay['pt'], $prikey);
        //post数据
        $returnData = array();
        $returnData['transCode'] = $AgentPay['transCode'];
        $returnData['merId'] = $AgentPay['merId'];
        $returnData['encryptKey'] = $encryptKey;
        $returnData['encryptData'] = $encryptData;
        $returnData['sign'] = $sign;
        //发送加密数据
        $res = common::http_post_json($AgentPay["url"], $returnData);
        //解密返回数据并验签
        parse_str($res, $arr);

        if (empty($arr['encryptKey']) || empty($arr['encryptData']) || empty($arr['sign'])) {
            throw new \Exception('数据返回格式有误');
        }

        //AESKey 解密
        $decryptAESKey = common::RSADecryptByPri($arr['encryptKey'], $prikey);
        //报文解密
        $decryptPlainText = common::AESDecrypt($arr['encryptData'], $decryptAESKey);
        //验签
        $verify = common::verify($decryptPlainText, $arr['sign'], $pubkey);
        if ($verify) {
            return $decryptPlainText;
        }
        return "";
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
            'orderCode' => $orderSn,
            "totalAmount" => $amt,
            "txnTimeOut" => date('YmdHis', time() + 60 * 60 * 1),
            'version' => '01',
            'productId' => '00000004',
            'currencyCode' => '156',
            'accAttr' => '0',//账户属性
            'accNo' => '6216261000000000018',//收款人账户号
            'accName' => '全渠道',//收款人账户名
            'accType' => '4',//账号类型
            "bankCode" => $bankId,
        );
        $credentialStr = $this->getToken($post);
        //TODO
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