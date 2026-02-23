<?
  include_once("common.php");
  //include_once('include_taxi_webservices.php');
	//include_once(TPATH_CLASS.'configuration.php');
  $systemTimeZone = date_default_timezone_get();
  //$Address_Array = array("23.020222","72.505296");
  //$Address_Array = array("23.0123273810923","72.5030366331339");
  $Address_Array = array("23.012327","72.503036");
  FetchVehicleTypeFromGeoLocation($Address_Array);
  function FetchVehicleTypeFromGeoLocation($Address_Array){
		global $obj;
		$Vehicle_Str = "-1";
    
    if(!empty($Address_Array)){
			$sqlaa = "SELECT * FROM location_master WHERE eStatus='Active' AND eFor = 'VehicleType'";
			$allowed_data = $obj->MySQLSelect($sqlaa);
			if(!empty($allowed_data)){
				$polygon = array();
				foreach($allowed_data as $key => $val) {
					$latitude = explode(",",$val['tLatitude']);
					$longitude = explode(",",$val['tLongitude']);
					for ($x = 0; $x < scount($latitude); $x++) {
						if(!empty($latitude[$x]) || !empty($longitude[$x])) {
							$polygon[$key][] = array($latitude[$x],$longitude[$x]);
						}
					}
		
					if($polygon[$key]){ 
						$address = contains($Address_Array,$polygon[$key]) ? 'IN' : 'OUT';
						if($address == 'IN'){
							$Vehicle_Str .= ",".$val['iLocationId'];
              //break;
						}
					}
				}
			} 
		}
    echo $Vehicle_Str;
		return $Vehicle_Str;
	}
  
  function contains($point, $polygon)
	{            
	    if($polygon[0] != $polygon[scount($polygon)-1])
	        $polygon[scount($polygon)] = $polygon[0];
	    $j = 0;
	    $oddNodes = false;
	    $x = $point[1];
	    $y = $point[0];
	    $n = scount($polygon);
	    for ($i = 0; $i < $n; $i++)
	    {
	        $j++;
	        if ($j == $n)
	        {
	            $j = 0;
	        }
	        if ((($polygon[$i][0] < $y) && ($polygon[$j][0] >= $y)) || (($polygon[$j][0] < $y) && ($polygon[$i][0] >= $y)))
	        {
	            if ($polygon[$i][1] + ($y - $polygon[$i][0]) / ($polygon[$j][0] - $polygon[$i][0]) * ($polygon[$j][1] - $polygon[$i][1]) < $x)
	            {
	                $oddNodes = !$oddNodes;
	            }
	        }
	    }    
	    return $oddNodes;
	}
?>