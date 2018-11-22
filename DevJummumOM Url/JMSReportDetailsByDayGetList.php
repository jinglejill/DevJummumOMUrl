<?php
    include_once("dbConnect.php");
    setConnectionValue($jummum);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    

    
    
    
    if(isset($_POST["branchID"]) && isset($_POST["receiptDate"]))
    {
        $branchID = $_POST["branchID"];
        $receiptDate = $_POST["receiptDate"];
    }
    
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    $sql = "select * from $jummumOM.branch where branchID = '$branchID'";
    $selectedRow = getSelectedRow($sql);
    $dbName = $selectedRow[0]["DbName"];
    
    $sql = "SELECT receipt.BranchID, date_format(receiptDate - INTERVAL branch.openingMinute minute,'%Y-%m-%d') ReceiptDate, ReceiptID, ReceiptNoID, NetTotal NetTotal, TotalAmount, SpecialPriceDiscount, DiscountValue, (TotalAmount-DiscountValue) AfterDiscount, ServiceChargeValue, VatValue, BeforeVat, TransactionFeeValue, JummumPayValue FROM `receipt` LEFT JOIN $jummumOM.branch ON receipt.BranchID = branch.BranchID WHERE receipt.BranchID = '$branchID' and date_format(receiptDate - INTERVAL branch.openingMinute minute,'%Y-%m-%d') = '$receiptDate';";
    $sql .= "SELECT receipt.ReceiptID,orderTaking.menuID,Menu.TitleThai,sum(orderTaking.SpecialPrice+takeAway) Total FROM `receipt` LEFT JOIN $jummumOM.branch ON receipt.BranchID = branch.BranchID left join orderTaking on receipt.receiptID = orderTaking.receiptID left join $dbName.Menu on orderTaking.menuID = Menu.menuID WHERE receipt.BranchID = '$branchID' and date_format(receiptDate - INTERVAL branch.openingMinute minute,'%Y-%m-%d') = '$receiptDate' group by receipt.receiptID,orderTaking.menuID";
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQueryArray($sql);
    $response = array('success' => true, 'data' => $jsonEncode, 'error' => null, 'status' => 1);
    echo json_encode($response);


    
    // Close connections
    mysqli_close($con);
    
?>
