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


?>
