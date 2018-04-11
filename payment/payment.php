<?php

namespace payment;

use Curl\Curl;

class payment extends payinterface
{

    private $name;
    private $mid;
    private $mykey;
    private $orderpay_url;
    private $quickpay_url;

    /**
     * payment constructor.
     */
    public function __construct()
    {
        $this->name = "江苏苏信通企业管理咨询有限公司";
        $this->mid = "97496044626";
        $this->mykey = "F5F8590CD58A54E94377E6AE2EDED4D9";
        $this->orderpay_url = "http://www.sdshopping.cn/api/orderpay.php";
        $this->quickpay_url = "http://www.sdshopping.cn/api/quickpay.php";
    }

    private function sign($map)
    {
        ksort($map);
        $signStr = $this->mykey;
        foreach ($map as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $signStr .= ($key . $value);
        }
        $signStr .= $this->mykey;
        return strtoupper(md5($signStr));
    }

    public function pay($orderSn, $amt, $bankId, $cardType, $type)
    {
        $data = array(
            'mid' => $this->mid,
            'orderCode' => $orderSn,
            'totalAmount' => $amt,
            'subject' => "biaoti",
            'body' => "neirong",
            'txnTimeOut' => date('YmdHis', time() + 60 * 60 * 1),
            "payMode" => "bank_pc",
            "bankCode" => $bankId,
            "payType" => "1",
            "extend" => ""
        );

        $sign = $this->sign($data);
        $data["sign"] = $sign;

        $curl = new Curl();
        $result = $curl->post($this->orderpay_url, $data)->response;
        $data = json_decode($result, true);
        $newData = $data["params"];
        $newData["actionUrl"] = $data["submitUrl"];
        $credential = $newData;
        return $credential;
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