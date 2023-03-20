<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
if(isset($_POST['pay_but']) && isset($_SESSION['userId'])) {
    require '../helpers/init_conn_db.php';  
    $flight_id = $_SESSION['flight_id'];
    $sessionUserid = $_SESSION['userId'];
    $price = $_SESSION['price'];
    $passengers = $_SESSION['passengers'];
    $pass_id = $_SESSION['pass_id'];
    $type = $_SESSION['type'];
    $class = $_SESSION['class'];
    $ret_date = $_SESSION['ret_date'];
    $card_no = $_POST['cc-number'];
    $expiry = $_POST['cc-exp'];  
    $sql = "INSERT INTO `payment` (`user_id`,`expire_date`,`amount`,`flight_id`,`card_no`) VALUES ('$sessionUserid','$expiry','$price','$flight_id','$card_no');";            
    //$stmt = mysqli_stmt_init($conn);
    $sqlResult = mysqli_query($conn,$sql);
    $checkSqlResult = $sqlResult;
    //if(!mysqli_stmt_prepare($stmt,$sql)) {
    if(!$checkSqlResult){
        header('Location: ../payment.php?error=sqlerror');
        exit();            
    } else {
        /*
        mysqli_stmt_bind_param($stmt,'isiis',$_SESSION['userId'],
            $expiry,$price,$flight_id,$card_no);          
        mysqli_stmt_execute($stmt);       
        $stmt = mysqli_stmt_init($conn);
        */
        $flag = false;
        for($i=$pass_id;$i<$passengers+$pass_id;$i++) {
            //$sql = 'SELECT * FROM flight WHERE flight_id=?';
            $sql = "SELECT * FROM `flight` WHERE `flight_id`='$flight_id';";
            //$stmt = mysqli_stmt_init($conn);
            $sqlResult = mysqli_query($conn,$sql);
            //if(!mysqli_stmt_prepare($stmt,$sql)) {
            if(!$sqlResult){
                header('Location: ../payment.php?error=sqlerror');
                exit();            
            } else {
                /*
                mysqli_stmt_bind_param($stmt,'i',$flight_id);            
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                */
                if($row = mysqli_fetch_assoc($sqlResult)) {
                    $source = $row['source'];
                    $dest = $row['destination'];
                    if($class === 'B') {
                        if($row['last_bus_seat'] === '') {
                            $new_seat = '1A';
                        } else {
                            $last_seat = $row['last_bus_seat'];
                            $ls_len = strlen($last_seat);
                            $seat_num = (int)substr($last_seat,0,$ls_len-1);
                            $seat_alpha = $last_seat[$ls_len-1];
                            if($seat_alpha === 'F') {
                                $seat_num = $seat_num + 1;
                                $seat_alpha = 'A';
                            } else {
                                $seat_alpha = ord($seat_alpha);
                                $seat_alpha = $seat_alpha + 1;
                                $seat_alpha = chr($seat_alpha);
                            }
                            $new_seat = (string)$seat_num . $seat_alpha;                         
                        }
                    } else if($class === 'E') {
                        if($row['last_seat'] === '') {
                            $new_seat = '21A';
                        } else {
                            $last_seat = $row['last_seat'];
                            $ls_len = strlen($last_seat);
                            $seat_num = (int)substr($last_seat,0,$ls_len-1);
                            $seat_alpha = $last_seat[$ls_len-1];
                            if($seat_alpha === 'F') {
                                $seat_num = $seat_num + 1;
                                $seat_alpha = 'A';
                            } else {
                                $seat_alpha = ord($seat_alpha);
                                $seat_alpha = $seat_alpha + 1;
                                $seat_alpha = chr($seat_alpha);
                            }
                            $new_seat = (string)$seat_num . $seat_alpha;                         
                        }
                    }                    
                    if($class === 'B') {
                        $seats = $row['bus_seats'];                    
                        $seats = $seats - 1;
                        //$stmt = mysqli_stmt_init($conn);
                        /*$sql = "UPDATE Flight SET last_bus_seat=?, bus_seats=?
                            WHERE flight_id=?";
                            */
                        $sql = "UPDATE `flight` SET `last_bus_seat`='$new_seat', `bus_seats`='$seats' WHERE `flight_id`='$flight_id';";
                        $sqlResult = mysqli_query($conn,$sql);
                        $temp='/';
                        //if(!mysqli_stmt_prepare($stmt,$sql)) {
                        if(!$sqlResult){
                            header('Location: ../payment.php?error=sqlerror');
                            exit();            
                        } /*else {
                            mysqli_stmt_bind_param($stmt,'sii',$new_seat,$seats,$flight_id);         
                            mysqli_stmt_execute($stmt);        
                        }
                        */                            
                    } else if($class === 'E') {
                        $seats = $row['seats'];
                        $seats = $seats - 1;
                        //$stmt = mysqli_stmt_init($conn);
                        //$sql = 'UPDATE Flight SET last_seat=?, Seats=? WHERE flight_id=?';
                        $sql = "UPDATE `flight` SET `last_seat`='$new_seat', `seats`='$seats' WHERE `flight_id`='$flight_id';";
                        $sqlResult = mysqli_query($conn,$sql);
                        //if(!mysqli_stmt_prepare($stmt,$sql)) {
                        if(!$sqlResult){
                            header('Location: ../payment.php?error=sqlerror');
                            exit();            
                        }/* else {
                            mysqli_stmt_bind_param($stmt,'sii',$new_seat,$seats,$flight_id);         
                            mysqli_stmt_execute($stmt);        
                        }
                        */                            
                    }    
                    //$stmt = mysqli_stmt_init($conn);
                    /*$sql = 'INSERT INTO Ticket (passenger_id,flight_id
                        ,seat_no,cost,class,user_id
                        ) VALUES (?,?,?,?,?,?)';    
                    */
                    $sql = "INSERT INTO `ticket`(`passenger_id`,`flight_id`,`seat_no`,`cost`,`class`,`user_id`) VALUES ('$pass_id','$flight_id','$new_seat','$price','$class','$sessionUserid')";    
                    //if(!mysqli_stmt_prepare($stmt,$sql)) {
                    if($sqlResult = mysqli_query($conn,$sql)){
                        $flag = true;            
                    } else {
                        /*
                        mysqli_stmt_bind_param($stmt,'iisisi',$i,
                            $flight_id,$new_seat,$price,$class,$_SESSION['userId']);            
                        mysqli_stmt_execute($stmt);  
                        // echo mysqli_stmt_error($stmt), $class   ;    
                        */
                        header('Location: ../payment.php?error=sqlerror');
                        exit();
                    }                                                                       
                  
                }
                else  {
                    header('Location: ../payment.php?error=sqlerror');
                    exit();                     
                }
            }   
        } 
        if($type === 'round' && $flag === true) {
            $flag = false;
            for($i=$pass_id;$i<$passengers+$pass_id;$i++) {
                /*$sql = 'SELECT * FROM Flight WHERE source=? AND Destination=? AND
                    DATE(departure)=?';
                $stmt = mysqli_stmt_init($conn);
                */
                $sql = "SELECT * FROM `flight` WHERE `source`='$dest' AND `destination`='$source' AND DATE(`departure`)='$ret_date';";
                $sqlResult = mysqli_query($conn,$sql);
                //if(!mysqli_stmt_prepare($stmt,$sql)) {
                if(!$sqlResult){
                    header('Location: ../payment.php?error=sqlerror');
                    exit();            
                } else {
                    /*
                    mysqli_stmt_bind_param($stmt,'sss',$dest,$source,$ret_date);            
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    */
                    //if($row = mysqli_fetch_assoc($result)) {
                    if($row = mysqli_fetch_assoc($sqlResult)) {                       
                        $flight_id = $row['flight_id'];
                        if($class === 'B') {
                            if($row['last_bus_seat'] === '') {
                                $new_seat = '1A';
                            } else {
                                $last_seat = $row['last_bus_seat'];
                                $ls_len = strlen($last_seat);
                                $seat_num = (int)substr($last_seat,0,$ls_len-1);
                                $seat_alpha = $last_seat[$ls_len-1];
                                if($seat_alpha === 'F') {
                                    $seat_num = $seat_num + 1;
                                    $seat_alpha = 'A';
                                } else {
                                    $seat_alpha = ord($seat_alpha);
                                    $seat_alpha = $seat_alpha + 1;
                                    $seat_alpha = chr($seat_alpha);
                                }
                                $new_seat = (string)$seat_num . $seat_alpha;                         
                            }
                        } else if($class === 'E') {
                            if($row['last_seat'] === '') {
                                $new_seat = '21A';
                            } else {
                                $last_seat = $row['last_seat'];
                                $ls_len = strlen($last_seat);
                                $seat_num = (int)substr($last_seat,0,$ls_len-1);
                                $seat_alpha = $last_seat[$ls_len-1];
                                if($seat_alpha === 'F') {
                                    $seat_num = $seat_num + 1;
                                    $seat_alpha = 'A';
                                } else {
                                    $seat_alpha = ord($seat_alpha);
                                    $seat_alpha = $seat_alpha + 1;
                                    $seat_alpha = chr($seat_alpha);
                                }
                                $new_seat = (string)$seat_num . $seat_alpha;                         
                            }
                        }                    
                        if($class === 'B') {
                            $seats = $row['bus_seats'];                    
                            $seats = $seats - 1;
                            /*
                            $stmt = mysqli_stmt_init($conn);
                            $sql = "UPDATE Flight SET last_bus_seat=?, bus_seats=?
                                WHERE flight_id=?";
                            */
                            $sql = "UPDATE `flight` SET `last_bus_seat`='$new_seat', `bus_seats`='$seats' WHERE `flight_id`='$flight_id';";
                            $sqlResult = mysqli_query($conn,$sql);
                            $temp='/';
                            //if(!mysqli_stmt_prepare($stmt,$sql)) {
                            if($sqlResult){
                                header('Location: ../payment.php?error=sqlerror');
                                exit();            
                            }/* else {
                                mysqli_stmt_bind_param($stmt,'sii',$new_seat,$seats,$flight_id);         
                                mysqli_stmt_execute($stmt);        
                            } 
                            */                          
                        } else if($class === 'E') {
                            $seats = $row['seats'];
                            $seats = $seats - 1;
                            /*
                            $stmt = mysqli_stmt_init($conn);
                            $sql = 'UPDATE Flight SET last_seat=?, Seats=?
                                WHERE flight_id=?';
                            */
                            $sql = "UPDATE `flight` SET `last_seat`='$new_seat', `seats`='$seats' WHERE `flight_id`='$flight_id';";
                            $sql_f_seat= mysqli_query($conn,$sql);
                            //if(!mysqli_stmt_prepare($stmt,$sql)) {
                            if(!$sql_f_seat){
                                header('Location: ../payment.php?error=sqlerror');
                                exit();            
                            }/* else {
                                mysqli_stmt_bind_param($stmt,'sii',$new_seat,$seats,$flight_id);         
                                mysqli_stmt_execute($stmt);        
                            }
                            */                            
                        } 
                        /*  
                        $stmt = mysqli_stmt_init($conn);
                        $sql = 'INSERT INTO Ticket (passenger_id,flight_id
                            ,seat_no,cost,class,user_id
                            ) VALUES (?,?,?,?,?,?)'; 
                        */
                        $sql = "INSERT INTO `ticket` (`passenger_id`,`flight_id`,`seat_no`,`cost`,`class`,`user_id`) VALUES ('$pass_id','$flight_id','$new_seat','$price','$class','$sessionUserid');";       
                        //if(!mysqli_stmt_prepare($stmt,$sql)) {
                        if($sqlResult = mysqli_query($conn,$sql)){
                            header('Location: ../payment.php?error=sqlerror');
                            exit();            
                        } else {
                            /*
                            mysqli_stmt_bind_param($stmt,'iisisi',$i,
                                $flight_id,$new_seat,$price,$class,$_SESSION['userId']);            
                            mysqli_stmt_execute($stmt);  
                            // echo mysqli_stmt_error($stmt);
                            */          
                            $flag = true;
                        }

                      
                    }
                    else  {
                        header('Location: ../payment.php?error=noret');
                        exit();                     
                    }
                }   
            }             
        }
        if($flag) {
            unset($_SESSION['flight_id']);
            unset($_SESSION['passengers']);
            unset($_SESSION['pass_id']);
            unset($_SESSION['price']);
            unset($_SESSION['class']);
            unset($_SESSION['type']);     
            unset($_SESSION['ret_date']);              
            header('Location: ../pay_success.php');
            exit();    
 
        } else {
            header('Location: ../payment.php?error=sqlerror');
            exit();               
        }
    } 
    /*          
  
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    */     

} else {
    header('Location: ../payment.php');
    exit();  
}    


//ALTER TABLE `ticket` DROP INDEX `flight_id`;
//ALTER TABLE `ticket` DROP INDEX `user_id`;