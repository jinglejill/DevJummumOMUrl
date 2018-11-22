<?php
    include_once("dbConnect.php");
    setConnectionValue($jummum);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    

    if(isset($_POST["branchID"]) && isset($_POST["receiptID"]) && isset($_POST["buffetEnded"]) && isset($_POST["buffetEndedDate"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $branchID = $_POST["branchID"];
        $receiptID = $_POST["receiptID"];
        $buffetEnded = $_POST["buffetEnded"];
        $buffetEndedDate = $_POST["buffetEndedDate"];
        $modifiedUser = $_POST["modifiedUser"];
        $modifiedDate = $_POST["modifiedDate"];
        
        
        $modifiedDeviceToken = $_POST["modifiedDeviceToken"];
        
    }

    
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    

    
    $warning = "";
    if($buffetEnded == 1)
    {
        $sql = "select * from receipt where receiptID = '$receiptID' and buffetEnded = 1";
        $selectedRow = getSelectedRow($sql);
        $receiptNo = $selectedRow[0]["ReceiptNoID"];
        if(sizeof($selectedRow) > 0)
        {
            $warning = "Receipt no: $receiptNo เคลียร์โต๊ะไปแล้วค่ะ";
        }
        $eachMsg = "Buffet ended";
    }
    else
    {
        //คืนโต๊ะ
        $sql = "select * from receipt where receiptID = '$receiptID' and buffetEnded = 0";
        $selectedRow = getSelectedRow($sql);
        if(sizeof($selectedRow) > 0)
        {
            $warning = "Receipt no: $receiptNo คืนโต๊ะไปแล้วค่ะ";
        }
        else
        {
            //หมดเวลาสั่งแล้ว คืนไม่ได้
            $sql = "select * from receipt where receiptID = '$receiptID' and (TIME_TO_SEC(timediff(now(), ReceiptDate)) >= TimeToOrder)";
            $selectedRow = getSelectedRow($sql);
            if(sizeof($selectedRow) > 0)
            {
                $warning = "ไม่สามารถคืนโต๊ะได้ บุฟเฟ่ต์หมดเวลาแล้ว";
            }
        }
        
        
        $eachMsg = "Buffet return";
    }
    
    writeToLog("warning: " . $warning);
    if($warning == "")
    {
        $sql = "update receipt set buffetEnded = '$buffetEnded', buffetEndedDate = '$buffetEndedDate', modifiedUser = '$modifiedUser', modifiedDate = '$modifiedDate' where receiptID = '$receiptID'";
        
        
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            //        putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    mysqli_commit($con);
    
    
    
    
    if($warning == "")
    {
        //push sync to other device
        $sql = "select * from Receipt where receiptID = '$receiptID';";
        $selectedRow = getSelectedRow($sql);
        $memberID = $selectedRow[0]["MemberID"];
        $orderNo = $selectedRow[0]["ReceiptNoID"];
        
        
        $pushSyncDeviceTokenReceiveOrder = array();
        $sql = "select * from $jummumOM.device left join $jummumOM.Branch on $jummumOM.device.DbName = $jummumOM.Branch.DbName where branchID = '$branchID';";
        $selectedRow = getSelectedRow($sql);
        for($i=0; $i<sizeof($selectedRow); $i++)
        {
            $deviceToken = $selectedRow[$i]["DeviceToken"];
            $modifiedDeviceToken = $_POST["modifiedDeviceToken"];
            if($deviceToken != $modifiedDeviceToken)
            {
                array_push($pushSyncDeviceTokenReceiveOrder,$deviceToken);
            }
        }
        
        $category = "buffetEnded";
        $msg = "Order no.$orderNo: $eachMsg";
        $contentAvailable = 1;
        $data = array("receiptID" => $receiptID);
        sendPushNotificationJummumOM($pushSyncDeviceTokenReceiveOrder,$title,$msg,$category,$contentAvailable,$data);
        
        
        
        
        
        //send noti to customer
        $sql = "select login.DeviceToken,login.ModifiedDate,login.Username from useraccount left join login on useraccount.username = login.username where useraccount.UserAccountID = '$memberID' and login.status = '1' order by login.modifiedDate desc;";
        $selectedRow = getSelectedRow($sql);
        $customerDeviceToken = $selectedRow[0]["DeviceToken"];
        $logInModifiedDate = $selectedRow[0]["ModifiedDate"];
        $logInUsername = $selectedRow[0]["Username"];
        $sql = "select * from login where DeviceToken = '$customerDeviceToken' and Username != '$logInUsername' and status = 1 and modifiedDate > '$logInModifiedDate';";
        $selectedRow = getSelectedRow($sql);
        if(sizeof($selectedRow) == 0)
        {
            $arrCustomerDeviceToken = array();
            array_push($arrCustomerDeviceToken,$customerDeviceToken);
            $category = "buffetEnded";
            $msg = "Order no.$orderNo: $eachMsg";
    //        $category = "buffetEnded";
            $contentAvailable = 1;
            $data = array("receiptID" => $receiptID);
            sendPushNotificationJummum($arrCustomerDeviceToken,$title,$msg,$category,$contentAvailable,$data);
        }
    }
    

    
    
    //dataJson
    $sql = "select '$warning' as Text;";
    $sql .= "select * from receipt where receiptID = '$receiptID';";
    $dataJson = executeMultiQueryArray($sql);
    
    
    
    
    
    //do script successful
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql, 'tableName' => 'ReceiptSendToKitchen', 'dataJson' => $dataJson);
    echo json_encode($response);
    exit();
?>
