<?php


/*
$to_email = "scvinodkumar.php@gmail.com";
$subject = "Simple Email Test via PHP";
$body = "Hi,nn This is test email send by PHP Script";
$headers = "From: sender\'s email";
 
if (mail($to_email, $subject, $body, $headers)) {
    echo "Email successfully sent to $to_email...";
} else {
    echo "Email sending failed...";
}
*/


checkAvailability(571);

function checkAvailability($userDistrictID) {

  $dates = date('d-m-Y');

  $urls = 'https://cdn-api.co-vin.in/api/v2/appointment/sessions/public/calendarByDistrict';
  $datas = array(
      "district_id" => $userDistrictID,
      "date" => $dates
  );

  $getCowinUrl = CallAPI('GET', $urls, $datas);
  $getCowinSessions = json_decode($getCowinUrl, true);

  //print '<pre>';print_r($getCowinSessions);die;

  /* Form array by center and date wise */
  $details = [];
  $address = '';
  foreach ($getCowinSessions['centers'] as $data) {
    foreach ($data['sessions'] as $key => $session) {
      // Save in array only if available capacity is more than 0 in order to save space in the db.
      if ($session['available_capacity'] > 0 && $session['available_capacity_dose1'] > 0) {
        $details[] = $data;
        if ($session['vaccine'] == 'COVISHIELD')
          $address .= "<div style='background-color:#cfcfcf; width: 35%'>";
        else
          $address .= "<div style='background-color:#c3c3c3; width: 35%'>";
        $address .= "<b>Hospital Name: ". $data['name']."</b><br>";
        $address .= "Address: ". $data['address']."<br>";
        $address .= "Area Name: ". $data['block_name']."<br>";
        $address .= "Vaccine Name: ". $session['vaccine']."<br>";
        $address .= "Dose 1 available: ". $session['available_capacity_dose1']."<br>";
        $address .= "Dose 1 available Date: ". $session['date']."<br>";
        $address .= "Min Age: ". $session['min_age_limit']."<br>";
        $address .= "***************************************************************<br>";
        $address .= "</div>";

        /*
        $details[$data['center_id']]['date'.($key+1)]['date'] =  $session['date'];
        $details[$data['center_id']]['date'.($key+1)]['name'] = $data['name'];
        $details[$data['center_id']]['date'.($key+1)]['available_capacity'] = $session['available_capacity'];
        $details[$data['center_id']]['date'.($key+1)]['min_age_limit'] = $session['min_age_limit'];
        $details[$data['center_id']]['date'.($key+1)]['vaccine'] = $session['vaccine'];
        $details[$data['center_id']]['date'.($key+1)]['available_capacity_dose1'] = $session['available_capacity_dose1'];
        $details[$data['center_id']]['date'.($key+1)]['available_capacity_dose2'] = $session['available_capacity_dose2'];
        $details[$data['center_id']]['date'.($key+1)]['slots'] = $session['slots'];
        */
      }
    }
  }
  echo $address;
  print '<pre>';print_r($details);die;

  /* To find total slots available in the given district */
  $sessions = array_column($getCowinSessions['centers'], 'sessions');

  foreach ($sessions as $arr)
    foreach ($arr as $val)
      $result[] = $val;

  $available_capacity = array_column($result, 'available_capacity');
  $totalCapacity = array_sum($available_capacity);
 // $serializedData = serialize($details);
 // print '<pre>';print_r($serializedData);die;

  if (!$hospitalList) {
    $serializedData = serialize($details);
    updateData($conn, $userDistrictID, $serializedData, $totalCapacity);
  } else {
    //echo $hospitalList;
    $unSerializedData = unserialize($hospitalList);
    /*
    print '<pre>';print_r(multi_diff(
        array(
          "A"=>array(
            "A1"=>array('A1-0','A1-1','A1-2','A1-3'),
            "A2"=>array('A2-0','A2-1','A2-2','A2-3'),
            "A3"=>array('A3-0','A3-1','A3-2','A3-3')
          ),
          "B"=>array(
            "B1"=>array('B1-0','B1-1','B1-2','B1-3'),
            "B2"=>array('B2-0','B2-1','B2-2','B2-3'),
            "B3"=>array('B3-0','B3-1','B3-2','B3-3')
          ),
          "C"=>array(
            "C1"=>array('C1-0','C1-1','C1-2','C1-3'),
            "C2"=>array('C2-0','C2-1','C2-2','C2-3'),
            "C3"=>array('C3-0','C3-1','C3-2','C3-3')
          ),
          "D"=>array(
            "D1"=>array('D1-0','D1-1','D1-2','D1-3'),
            "D2"=>array('D2-0','D2-1','D2-2','D2-3'),
            "D3"=>array('D3-0','D3-1','D3-2','D3-3')
          )
        ),
        
        array(
          "A"=>array(
            "A1"=>array('A1-0','A1-1','A1-2','A1-3'),
            "A2"=>array('A2-0','A2-1','A2-2','A2-3'),
            "A3"=>array('A3-0','A3-1','A3-2')
          ),
          "B"=>array(
            "B1"=>array('B1-0','B1-2','B1-3'),
            "B2"=>array('B2-0','B2-1','B2-2','B2-3'),
            "B3"=>array('B3-0','B3-1','B3-3')
          ),
          "C"=>array(
            "C1"=>array('C1-0','C1-1','C1-2','C1-3'),
            "C3"=>array('C3-0','C3-1')
          ),
          "D"=>array(
            "D1"=>array('D1-0','D1-1','D1-2','D1-3'),
            "D2"=>array('D2-0','D2-1','D2-2','D2-3'),
            "D3"=>array('D3-0','D3-1','D3-2','D3-3')
          )
        )
        
        ));
    */
   $oldData = getAvailabilityCount($unSerializedData);
  // $oldData['335077'] = 60;
   $newData = getAvailabilityCount($details);
   $diff = array_intersect($oldData, $newData);
  
  // $serializedData = serialize($details);
   //updateData($conn, $userDistrictID, $serializedData, $totalCapacity);
    print '<pre>';
    //print_r($oldData);print_r($newData);
    print_r($diff);
   // $ids = array_column($details, 'available_capacity');
    //$compare = multi_diff($unSerializedData, $details);
    //print '<pre>';
    //print_r($ids);die;
   // print_r(multidimenssional_array_diff($unSerializedData, $details));
   // die;
  }
}

function getAvailabilityCount($details) {
  $newArray = [];
  foreach ($details as $key => $value) {
    foreach ($value as $sub_key => $sub_val) {
      $newArray[$key] = $sub_val['available_capacity'];
      //$newArray[$key][] = $sub_val['date'];
    }
  }
  return $newArray;
}

function updateData($conn, $userDistrictID, $serializedData, $totalCapacity) {
  //echo "UPDATE location_list SET hospital_list = '".$serializedData."', available_slot = '".$totalCapacity."' WHERE district_id = '".$userDistrictID."'";die;
   mysqli_query($conn, "UPDATE location_list SET hospital_list = '".$serializedData."', available_slot = '".$totalCapacity."' WHERE district_id = '".$userDistrictID."'");
}

function multi_diff($arr1,$arr2){
    $result = array();
    foreach ($arr1 as $k=>$v){
      if(!isset($arr2[$k])){
        $result[$k] = $v;
      } else {
        if(is_array($v) && is_array($arr2[$k])){
          $diff = multi_diff($v, $arr2[$k]);
          if(!empty($diff))
            $result[$k] = $diff;
        }
      }
    }
    return $result;
  }

//results for array1 (when it is in more, it is in array1 and not in array2. same for less)
//Values in array1 not in array2 (more)
//Values in array2 not in array1 (less)
//Values in array1 and in array2 but different (diff)
function compare_multi_Arrays($array1, $array2){
    $result = array("more"=>array(),"less"=>array(),"diff"=>array());
    foreach($array1 as $k => $v) {
      if(is_array($v) && isset($array2[$k]) && is_array($array2[$k])){
        $sub_result = compare_multi_Arrays($v, $array2[$k]);
        //merge results
        foreach(array_keys($sub_result) as $key){
          if(!empty($sub_result[$key])){
            $result[$key] = array_merge_recursive($result[$key],array($k => $sub_result[$key]));
          }
        }
      }else{
        if(isset($array2[$k])){
          if($v !== $array2[$k]){
            $result["diff"][$k] = array("from"=>$v,"to"=>$array2[$k]);
          }
        }else{
          $result["more"][$k] = $v;
        }
      }
    }
    foreach($array2 as $k => $v) {
        if(!isset($array1[$k])){
            $result["less"][$k] = $v;
        }
    }
    return $result;
}

function key_compare($products,$excel_items)
{
    foreach ($products as $k1 => $v1) {
        if (array_diff($excel_items[$k1], $products[$k1])){
            $update_items[$k1] = array_diff($excel_items[$k1], $products[$k1]);
        }
    }
}
function multidimenssional_array_diff($arr1, $arr2)
{
    return array_diff_uassoc($arr1, $arr2,"key_compare");
}

die("Don't execute below things as of now...");

$mobile_number = urldecode($_GET['mobile_number']);
$state_id = urldecode($_GET['state_id']);
$state_name = urldecode($_GET['state_name']);
$district_id = urldecode($_GET['district_id']);
$district_name = urldecode($_GET['district_name']);
$pincode = urldecode($_GET['pincode']);
$age_group = urldecode($_GET['age_group']);
$token = urldecode($_GET['token']);

if ($conn)
{
    if ($mobile_number != null && $age_group != null && ($pincode != null || $district_id != null))
    {
        $sqlCheckDeviceId = "SELECT * FROM users WHERE mobile_number LIKE '$mobile_number'";
        $check = mysqli_fetch_assoc(mysqli_query($conn, $sqlCheckDeviceId));

        if (isset($check))
        {
            $existingToken = $check['token'];
            $existingPincode = $check['pincode'];
            $existingDistrictID = $check['district_id'];

            $sqlNotificationUpdate = "UPDATE users SET token = '$token', state_id = '$state_id', state_name = '$state_name', district_id = '$district_id', district_name = '$district_name', pincode = '$pincode', age_group = '$age_group' WHERE mobile_number = '$mobile_number' ";
            $update = mysqli_query($conn, $sqlNotificationUpdate);

            $sqlCheckDeviceId = "SELECT * FROM users WHERE mobile_number LIKE '$mobile_number'";
            $checks = mysqli_fetch_assoc(mysqli_query($conn, $sqlCheckDeviceId));
            //print_r($checks["pincode"]);
            $usersUpdatePincode = $checks["pincode"];
            $usersUpdatedDistrictId = $checks["district_id"];
            $usersUpdateToken = $checks["token"];
            //print_r($usersUpdatePincode);
            //print_r($usersUpdatedDistrictId);
            //exit;
            $status = "1";
            $message = "Already Registered";
            $response = $checks;
			
			ob_start();

                // Send your response.
                $output = array(
                    'status' => $status,
                    'message' => $message,
                    'response' => $response
                );
                echo json_encode($output);

                // Get the size of the output.
                $size = ob_get_length();

                // Disable compression (in case content length is compressed).
                header("Content-Encoding: none");

                // Set the content length of the response.
                header("Content-Length: {$size}");

                // Close the connection.
                header("Connection: close");

                // Flush all output.
                ob_end_flush();
                ob_flush();
                flush();

            $getExisitngLocationList = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM location_list WHERE pincode ='$existingPincode' AND district_id = '$existingDistrictID' "));

            $oldtokenArray = explode(",", $getExisitngLocationList["tokens"]);

            $OldTokenCount = count($oldtokenArray);

            $newArray = "";
            for ($i = 0;$i < $OldTokenCount;$i++)
            {
                if ($oldtokenArray[$i] != $existingToken)
                {
                    if ($newArray == "")
                    {
                        $newArray = $oldtokenArray[$i];
                    }
                    else
                    {
                        $newArray = $newArray . "," . $oldtokenArray[$i];
                    }
                }
            }
            print_r($newArray);

            $removeExistingToken = mysqli_query($conn, "UPDATE location_list SET tokens = '$newArray' WHERE pincode ='$existingPincode' AND district_id = '$existingDistrictID'");

            $sql_locationdetailsRegister = "SELECT * FROM location_list WHERE pincode ='$usersUpdatePincode' AND district_id = '$usersUpdatedDistrictId' ";

            $locationresultRegister = mysqli_query($conn, $sql_locationdetailsRegister);

            echo "Affected rows: " . mysqli_affected_rows($conn);

            if (mysqli_affected_rows($conn) > 0)
            {
                $update_locationRegister = mysqli_query($conn, "UPDATE location_list SET tokens = CONCAT(tokens, ',' '$usersUpdateToken') WHERE pincode ='$usersUpdatePincode' AND district_id = '$usersUpdatedDistrictId' ");

                if (mysqli_affected_rows($conn) > 0)
                {
                    //$variable = "12345" .',';
                    echo "token updated";
                    //$removeExistingToken = "UPDATE location_list SET tokens = REPLACE(tokens, '$variable', '') WHERE"
                    
                }
            }
            else
            {
                $new_locationRegister = "INSERT INTO location_list (pincode, district_id, tokens, available_slot, users_count, hospital_list) VALUES ('$usersUpdatePincode','$usersUpdatedDistrictId','$usersUpdateToken', '0', '1', '')";
                $locationInsertRegister = mysqli_query($conn, $new_locationRegister);
                if ($locationInsertRegister)
                {
                    echo "new location added";
                    $currentDate = date("d-m-y");

                    //$output = array('status' => $status , 'message'=> $message, 'response'=> $response);
                    //echo json_encode($output);
                    setInterval($userspincode, $usersdestictid,60000);
                    
                }

            }
            //echo $pincode;
            //callSlot("1", $pincode, $district_id, $age_group, $district_name);
            //$user_id = $checks['id'];
            //mysqli_query($conn, "DELETE FROM user_slot WHERE user_id = '$user_id'");
            //exit;
            //callSlot($user_id, $pincode, $district_id, $age_group, $district_name);
            
        }
        else
        {
            $sql_register = "INSERT INTO users (mobile_number,state_id,state_name,district_id,district_name,pincode,age_group,is_notification,token) VALUES ('$mobile_number','$state_id','$state_name','$district_id','$district_name','$pincode','$age_group','0', '$token')";
            $register = mysqli_query($conn, $sql_register);
            if ($register)
            {
                $userid["id"] = mysqli_insert_id($conn);
                $userid = mysqli_insert_id($conn);
                //echo $userid["id"];
                //print_r($register["pincode"]);
                //exit;
                $status = "1";
                $message = "Successfully registered";
                $response = $userid;

                $sql_usersdetails = "SELECT * FROM users WHERE id = '$userid' ";
                //echo $sql_getdetails;
                if ($usersresult = mysqli_query($conn, $sql_usersdetails))
                {
                    while ($obj = mysqli_fetch_object($usersresult))
                    {
                        $userspincode = ($obj->pincode);
                        $usersdestictid = ($obj->district_id);
                        $userstoken = ($obj->token);
                        //echo($userspincode);
                        //echo($usersdestictid);
                        
                    }
                    mysqli_free_result($usersresult);
                }

                ob_start();

                // Send your response.
                $output = array(
                    'status' => $status,
                    'message' => $message,
                    'response' => $response
                );
                echo json_encode($output);

                // Get the size of the output.
                $size = ob_get_length();

                // Disable compression (in case content length is compressed).
                header("Content-Encoding: none");

                // Set the content length of the response.
                header("Content-Length: {$size}");

                // Close the connection.
                header("Connection: close");

                // Flush all output.
                ob_end_flush();
                ob_flush();
                flush();

                // Close current session (if it exists).
                //	if(session_id()) session_write_close();
                

                $sql_locationdetails = "SELECT * FROM location_list WHERE pincode ='$userspincode' AND district_id = '$usersdestictid' ";

                $locationresult = mysqli_query($conn, $sql_locationdetails);

                echo "Affected rows: " . mysqli_affected_rows($conn);

                if (mysqli_affected_rows($conn) > 0)
                {
                    $update_location = mysqli_query($conn, "UPDATE location_list SET tokens = CONCAT(tokens, ',' '$userstoken') WHERE pincode ='$userspincode' AND district_id = '$usersdestictid' ");

                    if (mysqli_affected_rows($conn) > 0)
                    {
                        echo "token updated";
                    }
                }
                else
                {
                    $new_location = "INSERT INTO location_list (pincode, district_id, tokens, available_slot) VALUES ('$userspincode','$usersdestictid','$token', '0')";
                    $locationInsert = mysqli_query($conn, $new_location);
                    if ($locationInsert)
                    {
                        echo "new location added";
                        $currentDate = date("d-m-y");

                        //$output = array('status' => $status , 'message'=> $message, 'response'=> $response);
                        //echo json_encode($output);
                        setInterval($userspincode, $usersdestictid,60000);
                        
                    }

                }

            }
            else
            {
                $status = "0";
                $message = "Register Failed";
                $response = "";
            }
        }
    }
    else
    {
        $status = "0";
        $message = "Register Failed";
        $response = "";
    }
}
else
{
    $status = "0";
    $message = "Connection Error";
    $response = "";
}

function setInterval($userspincode, $usersdestictid, $milliseconds)
{
    $seconds = (int)$milliseconds / 1000;
	
    while (true)
    {
        callSlot($userspincode, $usersdestictid);
        sleep($seconds);
    }
}



function callSlot($usersdestictid)
{
    //echo "date";
    //echo $currentDate;
    //require "config.php";
    //echo "asd1";
    $yesteDayCount = 0;
    $currentAvailableCount = 0;
    for ($i = - 1;$i <= 10;$i++)
    {

        $str = strval($i);
        $dates = "";

        if ($i == - 1)
        {
            $dates = date('d-m-Y', strtotime("-1 days"));
        }
        else
        {
            $dates = date('d-m-Y', strtotime('+' . $str . ' day'));
        }

        $methods = "GET";

        if ($userspincode != null)
        {
            $urls = 'https://cdn-api.co-vin.in/api/v2/appointment/sessions/public/findByPin';
            $datas = array(
                "pincode" => $userspincode,
                "date" => $dates
            );
            $usersdestictid = "";

            //echo $urls;
            
        }
        else
        {
            //https://cdn-api.co-vin.in/api/v2/appointment/sessions/public/findByDistrict?district_id=512&date=31-03-2021
            $urls = 'https://cdn-api.co-vin.in/api/v2/appointment/sessions/public/findByDistrict';
            $datas = array(
                "district_id" => $usersdestictid,
                "date" => $dates
            );
            $userspincode = "";
        }

        $getCowinUrl = CallAPI($methods, $urls, $datas);
        $getCowinSessions = json_decode($getCowinUrl);

        print '<pre>';
        print_r($getCowinSessions);die;

        //$is_available_slot = "0";
        $getAvailableSlotCapacity = 0;

        if (sizeof($getCowinSessions->sessions) > 0)
        {
            foreach ($getCowinSessions->sessions as $getCowinArrays)
            {
                //echo $pincode;
                $getAvailableCapacity = $getCowinArrays->available_capacity;
                $min_age_limit = $getCowinArrays->min_age_limit;

                //if($min_age_limit == $age_group) {
                //$is_available_slot = "1";
                $getAvailableSlotCapacity = $getAvailableSlotCapacity + $getAvailableCapacity;

                //echo $userspincode;
                //echo $usersdestictid;
                //exit;
                

                //}
                
            }
        }
        if ($i == - 1)
        {

            $yesteDayCount = $getAvailableSlotCapacity;
            echo "Yesterday count:";
            echo $yesteDayCount;
        }

        $currentAvailableCount = $currentAvailableCount + $getAvailableSlotCapacity;
        echo "Data Adding:";
        echo $currentAvailableCount;
        //echo $currentAvailableCount;
        if ($i == 10)
        {
            echo "If called";
            $currentAvailableCount = $currentAvailableCount - $yesteDayCount;
            //echo $currentAvailableCount;
            $get_slot = "SELECT * FROM location_list WHERE pincode ='$userspincode' AND district_id = '$usersdestictid'";
            if ($getAvailableSlot = mysqli_query($conn, $get_slot))
            {
                while ($obj = mysqli_fetch_object($getAvailableSlot))
                {
                    $getOldSlot = ($obj->available_slot);
                    $getToken = ($obj->tokens);
                    $tokenArray = explode(",", $getToken);
                    //echo "0th index value";
                    //print_r($tokenArray[0]);
                    //echo "<br>";
                    //echo "Tokens";
                    //print_r($getToken);
                    //echo "<br>";
                    
                }
                mysqli_free_result($getAvailableSlot);
            }
            echo "old count = ";
            echo $getOldSlot;
            if ($getOldSlot < $currentAvailableCount)
            {
                echo "updated data available";
                //echo $currentAvailableCount;
                $insert_slot = mysqli_query($conn, "UPDATE location_list SET available_slot = '$currentAvailableCount' WHERE pincode ='$userspincode' AND district_id = '$usersdestictid'");
                //print_r($insert_slot);
                $length = count($tokenArray);
                $body = "Hey Hurry Up.!! " . $currentAvailableCount . " Slots Available in Your Location";
                for ($i = 0;$i < $length;$i++)
                {
                    //print $tokenArray[$i];
                    $pushNotificationCall = pushNotification($currentAvailableCount, $tokenArray[$i], $body);
                }
                $currentAvailableCount = 0;
            }
            else
            {
                echo "There is not updated data.";
                $currentAvailableCount = 0;
            }
            //echo $getOldSlot;
            

            
        }

        //echo "asd2";
        //echo $dates;
        //$getExistAvailableSlot = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user_slot WHERE user_id = '$userId' AND select_date = '$dates'"));
        //$getExistAvailableSlot = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM location_list WHERE user_id = '$userId' AND select_date = '$dates'"));
        //foreach ($getExistAvailableSlots as $getExistAvailableSlot) {
        /*if(isset($getExistAvailableSlot)) {
        
        if($getExistAvailableSlots['send_push'] == 0) {
        if($getAvailableSlotCapacity > ((int)$getExistAvailableSlot['available_slot'])) {
        //if(500 > ((int)$getExistAvailableSlot['available_slot'])) {
        //echo "asd4";
        $getToken = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = '$userId'"));
        //echo $getToken['token'];
        //exit;
        //{"multicast_id":6482756314838366521,"success":1,"failure":0,"canonical_ids":0,"results":[{"message_id":"0:1620923991782676%5f8f472af9fd7ecd"}]}
        
        
        $pushNotificationCall = pushNotification($getExistAvailableSlot, $getToken['token']);
        $pushResult = json_decode($pushNotificationCall);
        //echo "<br>";
        //echo $pushNotificationCall;
        //echo $pushResult;
        //echo "<br>";
        //echo $pushResult->success;
        //echo "<br>";
        //echo "<br>";
        if($pushResult->success == '1') {
        //echo "Push Sent success";
        //echo "<br>";
        //echo $userId;
        //echo $dates;
        $sendPushValue = "1";
        $sql_update_slot = "UPDATE user_slot SET district_id = '$district_id', district_name = '$district_name', pincode = '$pincode', is_available_slot = '$is_available_slot', available_slot = '$getAvailableSlotCapacity', send_push = '$sendPushValue' WHERE select_date = '$dates' AND user_id = '$userId'";
        
        $sql_update_slots = mysqli_query($conn, $sql_update_slot);
        if (mysqli_affected_rows($conn)) {
        
        }
        } else {
        //echo "Push Sent failed";
        }
        
        //$sql_register = "INSERT INTO user_slot (user_id, select_date, district_id, pincode, is_available_slot, available_slot, send_push) VALUES ('$userId','$dates','$district_id','$pincode','$is_available_slot','$getAvailableSlotCapacity','0')";
        
        //$register = mysqli_query($conn, $sql_register);
        }
        }
        } else {
        
        $sql_register = "INSERT INTO user_slot (user_id, select_date, district_id, district_name, pincode, is_available_slot, available_slot, send_push) VALUES ('$userId','$dates','$district_id','$district_name','$pincode','$is_available_slot','$getAvailableSlotCapacity','0')";
        
        
        $register = mysqli_query($conn, $sql_register);
        }*/
        //}
        //$getExistAvailableSlots = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM user_slot WHERE user_id = '$user_id'"), MYSQLI_ASSOC);
        

        //$sql_register = "INSERT INTO user_slot (user_id, select_date, district_id, pincode, is_available_slot, available_slot, send_push) VALUES ('$userId','$dates','$district_id','$pincode','$is_available_slot','$getAvailableSlotCapacity','0')";
        //$register = mysqli_query($conn, $sql_register);
        //print_r($getCowinUrl);
        //echo $getCowinSessions;
        
    }

    /*$strs = strval(1);
    $datess = date('d-m-Y', strtotime('-'.$strs.' day'));
    mysqli_query($conn, "DELETE FROM user_slot WHERE user_id = '$userId' AND select_date = '$datess'");*/
    //echo $datess;
    
}

function CallAPI($method, $url, $data)
{
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:
        //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //curl_setopt($curl, CURLOPT_USERPWD, "username:password");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    function pushNotification($currentAvailableCount, $target, $body)
    {

        //$target= "fDWGsf3aQMCOrmy0YnqgJx:APA91bFFWebluz1T07H3gucqeWkQbUojM0JH9seQ8GDp6I2F-kE_b2YOKKWy0yBaPxLHBkWg7Zc9wbPZ3cypN6Qb-j8fwKUxZcLn-suiv2RAGq9zyohxa0Om1YGEsQPoqIdHyuU7fqu4";
        $serverKey = "AAAAMV0CHws:APA91bEhCrolUzUh2Y8sS80Uyx_fc2uTyLi5CPprHvoodOUCli5ZQ0EkIN5H09nPGSFYJKu3ZJim1sfrgkP3XDbNHQSIP8z3baDg4MsqiMX0pes7-_W_nZSxqAj9AH-5KnR9xyEtSB84";

        define('API_ACCESS_KEY', $serverKey);
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $notification = ['notification_response' => $currentAvailableCount, 'body' => $body, 'icon' => 'data', 'sound' => 'data'];
        $extraNotificationData = ["message" => $notification, "moredata" => 'dd'];

        $fcmNotification = [
        //'registration_ids' => $tokenList, //multple token array
        'to' => $target, //single token
        'notification' => $notification, 'data' => $extraNotificationData];

        $headers = ['Authorization: key=' . API_ACCESS_KEY, 'Content-Type: application/json'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        curl_close($ch);

        //echo $result;
        return $result;

    }
?>
