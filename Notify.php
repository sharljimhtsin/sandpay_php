<?php
namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Log;
use app\home\model\OnlinepayModel;
use payment\common;

class Notify extends Controller
{

    public function  __construct(Request $request = null)
    {
        parent::__construct($request);
    }
	
    public function Sdpay()
    {
        /*
         *
        {
            "head": {
                "respTime": "20180402201526",
                "version": "1.0",
                "respCode": "000000"
            },
            "body": {
                "clearDate": "20180402",
                "tradeNo": "20180402152267121688932",
                "payTime": "20180402201354",
                "accNo": "",
                "midFee": "000000000030",
                "mid": "15196167",
                "orderStatus": "1",
                "totalAmount": "000000000012",
                "buyerPayAmount": "000000000012",
                "bankserial": "",
                "orderCode": "20170900001522671212",
                "discAmount": "000000000000"
            }
        }
         *
         */
        Log::mylog('getpay', $_REQUEST, 'Sdpay');
        $sign = $_REQUEST['sign']; //签名
        $data = $_REQUEST['data']; //支付数据
        $mykey = "F5F8590CD58A54E94377E6AE2EDED4D9";
        $param = array();
        $param["sign"] = $sign;
        $param["data"] = $data;
        ksort($param);
        $signStr = $mykey;
        foreach ($param as $key => $value) {
            if (empty($value)) {
                continue;
            }
            if ($key == "sign") {
                continue;
            }
            $signStr .= ($key . $value);
        }
        $signStr .= $mykey;
        $theSign = strtoupper(md5($signStr));

        if ($sign == $theSign) {
            //签名验证成功
            $map = json_decode($data, true); //data数据
            $map = $map["body"];
            $onlinePay = new OnlinepayModel();
            $res = $onlinePay->donePay($map["orderCode"], $map["tradeNo"], $map["orderStatus"]);
            echo "respCode=000000";
        } else {
            //签名验证失败
            echo "respCode=102";
        }
    }

    public function SdpayAlt(){
        Log::mylog('getpay', $_REQUEST, 'SdpayAlt');
        $pubkey = common::loadX509Cert(common::PUB_KEY_PATH);
        $sign = $_REQUEST['sign']; //签名
        $signType = $_REQUEST['signType']; //签名方式
        $data = stripslashes($_REQUEST['data']); //支付数据
        $charset = $_REQUEST['charset']; //支付编码

        if (common::verify($data, $sign,$pubkey)) {
            //签名验证成功
            $result = json_decode($data,true); //data数据
            $map = $result["body"];
            $onlinePay = new OnlinepayModel();
            $res = $onlinePay->donePay($map["orderCode"], $map["tradeNo"], $map["orderStatus"]);
            echo "respCode=000000";
        } else {
            //签名验证失败
            echo "respCode=102";
        }
    }
}