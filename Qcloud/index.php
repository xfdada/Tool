<?php

/**
 * 例子如下
 */

require 'SendSmS.class.php';

  $send = new SendSmS();

  $phone = "15347974139";
  $params = [522522,10];

  $result = $send->Send($phone,$params);

  var_dump($result);