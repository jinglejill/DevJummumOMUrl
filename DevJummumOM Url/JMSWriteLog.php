<?php
    include_once("dbConnect.php");
    setConnectionValue($jummumOM);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    if (isset ($_POST["stackTrace"]))
    {
        $stackTrace = $_POST["stackTrace"];
    }
    else
    {
        $stackTrace = "-";
    }
    
    
    writeToLog("fail with exception: " . $stackTrace);
    writeToErrorLog("fail with exception: " . $stackTrace);
    $response = array('status' => '1', 'sql' => "");
    
    
    
    
    
    //send push to jummum admin
    $sql = "select Value from $jummum.Setting where keyName = 'DeviceTokenAdmin'";
    $selectedRow = getSelectedRow($sql);
    $pushSyncDeviceTokenAdmin = $selectedRow[0]["Value"];
    $arrPushSyncDeviceTokenAdmin = array();
    array_push($arrPushSyncDeviceTokenAdmin,$pushSyncDeviceTokenAdmin);
    
    
    
    $msg = "Error occur" . ', time:' . date("Y/m/d H:i:s");
    $category = "admin";
    $contentType = 1;
    $data = null;
    sendPushNotificationAdmin($arrPushSyncDeviceTokenAdmin,$title,$msg,$category,$contentType,$data);
//    sendPushNotificationToDeviceWithMsg($pushSyncDeviceTokenAdmin,$msg);
    
    
    
    
    
    echo json_encode($response);
    exit();
?>
