<?php
    include_once('dbConnect.php');
    setConnectionValue("JUMMUM_OM");
    $arrBody = array(
                     'alert' => 'test'//ข้อความ
                      ,'sound' => 'default'//,//เสียงแจ้งเตือน
//                      ,'badge' => 3 //ขึ้นข้อความตัวเลขที่ไม่ได้อ่าน
                     ,'category' => 'Print'
                      );
    sendTestApplePushNotification('f808ebc97fff8d2d54209aab7d4cf5e8fe10f43f978346c1c02066bfc1408da0',$arrBody);
    
//    sleep(5);
//
//
//    $arrBody = array(
//                     'alert' => 'เทสจิ๋ว 2
//                     เอ'//ข้อความ
//                     ,'sound' => 'default'//,//เสียงแจ้งเตือน
//                     //                      ,'badge' => 3 //ขึ้นข้อความตัวเลขที่ไม่ได้อ่าน
//                     );
//    sendTestApplePushNotification('1877301d04f677b7fcc415b7f0bcbd799bf679013b14f76ce746d778087a22f6',$arrBody);
?>

//<table><tr><td style="text-align: center;border: 1px solid black; padding-left: 10px;padding-right: 10px; border-radius: 15px;">x</td></tr></table>
