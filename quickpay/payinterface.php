<?php
/**
 * Created by PhpStorm.
 * User: wuhuiqing
 * Date: 2018/3/9
 * Time: 上午11:07
 */

namespace quickpay;


abstract class payinterface
{
    public abstract function pay($orderSn, $amt, $bankId, $cardType, $type);

    public abstract function bankList($type);
}