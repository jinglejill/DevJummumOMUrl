<?php
    include_once("dbConnect.php");
    setConnectionValue($jummum);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    $dbName = $_POST["dbName"];


    if(isset($_POST["branchID"]) && isset($_POST["page"]) && isset($_POST["perPage"]))
    {
        $branchID = $_POST["branchID"];
        $page = $_POST["page"];
        $perPage = $_POST["perPage"];
    }
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
   
    
    $sql = "select * from (select (@row_number:=@row_number + 1) AS Num, receipt.* from receipt, (SELECT @row_number:=0) AS t WHERE branchID = '$branchID' and hasBuffetMenu = 1 and (TIME_TO_SEC(timediff(now(), ReceiptDate)) < TimeToOrder) and buffetEnded = 0 order by ReceiptDate, ReceiptID)a where Num > $perPage*($page-1) limit $perPage;";
    
   
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQueryArray($sql);
    $response = array('success' => true, 'data' => $jsonEncode, 'error' => null, 'status' => 1);
    echo json_encode($response);
    
    
    
    // Close connections
    mysqli_close($con);
?>
