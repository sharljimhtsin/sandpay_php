<?php
namespace quickpay;

use Curl\Curl;
use quickpay\common;
use think\Request;

class quickpay extends payinterface
{
    private function getToken($_POST)
    {
        date_default_timezone_set("Asia/Shanghai");
        $prikey = common::loadPk12Cert(common::PRI_KEY_PATH, common::CERT_PWD);
        $pubkey = common::loadX509Cert(common::PUB_KEY_PATH);

        $data = array(
            'head' => array(
                'version' => '1.0',
                'method' => 'sandPay.fastPay.quickPay.index',
                'productId' => '00000016',
                'accessType' => '1',
                'mid' => $_POST['mid'],
                'channelType' => '07',
                'reqTime' => date('YmdHis', time())
            ),
            'body' => array(
                'userId' => $_POST['userId'],
                'orderCode' => $_POST['orderCode'],
                'orderTime' => $_POST['orderTime'],
                'totalAmount' => $_POST['totalAmount'],
                'subject' => $_POST['subject'],
                'body' => $_POST['body'],
                'currencyCode' => $_POST['currencyCode'],
                'notifyUrl' => $_POST['notifyUrl'],
                'frontUrl' => $_POST['frontUrl'],
                'clearCycle' => $_POST['clearCycle'],
                'extend' => ''
            )
        );

        $sign = common::sign($data, $prikey);
        $post = array(
            'charset' => 'utf-8',
            'signType' => '01',
            'data' => json_encode($data, 320),
            'sign' => urlencode($sign)
        );

        file_put_contents('temp/log.txt', date('Y-m-d H:i:s', time()) . " 请求报文:" . json_encode($post, 320) . "\r\n", FILE_APPEND);
        return $post;
    }

    public function pay($orderSn, $amt, $bankId, $cardType, $type)
    {
        //$mid, $userId, $orderCode, $orderTime, $totalAmount, $subject, $body, $currencyCode, $notifyUrl, $frontUrl, $clearCycle, $extend
        $post = array(
            "mid" => common::MID,
            "userId" => "xzx",
            "orderCode" => $orderSn,
            "orderTime" => date('YmdHis', time()),
            "totalAmount" => $amt,
            "subject" => "biaoti",
            "body" => "neirong",
            "currencyCode" => "156",
            "notifyUrl" => Request::instance()->domain() . '/api/notify/Sdpay',
            "frontUrl" => "",
            "clearCycle" => "0",
            "extend" => ""
        );
        $token = $this->getToken($post);
        $payUrl = "http://61.129.71.103:8003/fastPay/quickPay/index";
        $curl = new Curl();
        $res = $curl->post($payUrl, $token)->response;
        return $res;
    }

    public function bankList($type)
    {

        $data = array();
        $data['bankList']['bankRow'] = array(

            array('bankName' => '工商银行',
                'bankID' => 'ICBC',
                'otherBankID' => '',
                'cardType' => ''
            ),
//            array('bankName' => '兴业银行',
//                'bankID'   => '1103',
//                'otherBankID' => '',
//                'cardType'    => ''
//            ),

            array('bankName' => '农业银行',
                'bankID' => 'ABC',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '招商银行',
                'bankID' => 'CMB',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '建设银行',
                'bankID' => 'CCB',
                'otherBankID' => '',
                'cardType' => ''
            ),

            array('bankName' => '中国银行',
                'bankID' => 'BOC',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '交通银行',
                'bankID' => 'COMM',
                'otherBankID' => '',
                'cardType' => ''
            ),

//            array('bankName' => '浦发银行',
//                'bankID'   => '1109',
//                'otherBankID' => '',
//                'cardType'    => ''
//            ),
//            array('bankName' => '平安银行',
//                'bankID'   => '1121',
//                'otherBankID' => '',
//                'cardType'    => ''
//            ),
            array('bankName' => '光大银行',
                'bankID' => 'CEB',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '华夏银行',
                'bankID' => 'HXB',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '民生银行',
                'bankID' => 'CMBC',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '邮储银行',
                'bankID' => 'PSBC',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '广发银行',
                'bankID' => 'GDB',
                'otherBankID' => '',
                'cardType' => ''
            ),
            array('bankName' => '中信银行',
                'bankID' => 'CITIC',
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
