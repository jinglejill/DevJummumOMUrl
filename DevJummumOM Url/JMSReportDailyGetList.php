<?php
    include_once("dbConnect.php");
    setConnectionValue($jummum);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    

    
    
    
    if(isset($_POST["branchID"]) && isset($_POST["monthYear"]))
    {
        $branchID = $_POST["branchID"];
        $monthYear = $_POST["monthYear"];
    }
    
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    $sql = "select a.*, case when transferbalance.TransferBalanceID is null then 0 ELSE 1 END as Status from (SELECT receipt.BranchID, date_format(receiptDate - INTERVAL branch.openingMinute minute,'%Y-%m-%d') ReceiptDate, sum(NetTotal-TransactionFeeValue+JummumPayValue) Balance, sum(TotalAmount) TotalAmount, sum(SpecialPriceDiscount) SpecialPriceDiscount, sum(DiscountValue) DiscountValue, sum(TotalAmount-DiscountValue) AfterDiscount, sum(ServiceChargeValue) ServiceChargeValue, sum(VatValue) VatValue, sum(BeforeVat) BeforeVat, sum(NetTotal) NetTotal, SUM(TransactionFeeValue) TransactionFeeValue, SUM(JummumPayValue) JummumPayValue FROM `receipt` LEFT JOIN $jummumOM.branch ON receipt.BranchID = branch.branchID WHERE receipt.BranchID = '$branchID' and date_format(receiptDate - INTERVAL branch.openingMinute minute,'%Y-%m') = '$monthYear' GROUP BY receipt.BranchID, date_format(receiptDate - INTERVAL branch.openingMinute minute,'%Y-%m-%d'))a LEFT JOIN transferbalance ON a.ReceiptDate = transferbalance.TransferDate and a.branchID = transferbalance.BranchID;";
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQueryArray($sql);
    $response = array('success' => true, 'data' => $jsonEncode, 'error' => null, 'status' => 1);
    echo json_encode($response);


    
    // Close connections
    mysqli_close($con);
    
?>
