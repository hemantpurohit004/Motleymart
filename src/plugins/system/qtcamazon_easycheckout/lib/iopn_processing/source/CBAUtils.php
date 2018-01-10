<?php
/**
* Copyright 2009 Amazon.com, Inc. or its affiliates. All Rights Reserved.
*
* Licensed under the Apache License, Version 2.0 (the "License").
* You may not use this file except in compliance with the License.
* A copy of the License is located at
*
*    http://aws.amazon.com/apache2.0/
*
* or in the "license" file accompanying this file.
* This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
* either express or implied. See the License for the specific language governing permissions
* and limitations under the License.
*
*
* @brief Utility Functions for logging the request.
* @catagory Checkout By Amazon  - Utility Functions file
*
*/

/* generic function to write the log */
function WriteLog($content){
  if(!file_exists(LOG_FILE)){
    $handle = fopen(LOG_FILE,"w");
  }else{
    $handle = fopen(LOG_FILE,"a+");
  }

  if (is_writable(LOG_FILE) && $handle) {
    $somecontent = date("D M j G:i:s T Y") ." :- " . $content . "\n";
    $somecontent .= "-----------------------------------------------------\n";

    if (fwrite($handle, $somecontent) === FALSE) {
      exit;
    }
    fclose($handle);
  }else{  	
  	exit("Please check if the log file exists and it has write permissions.");
  }
}

/* Logs the POST request */
function LogRequest(){
  if($_POST){
    foreach ($_POST as $k => $v) {
      $logContent = "$k = ".str_replace("\\\"","\"",$v)."\n";
      WriteLog($logContent);
    }
  }
}
?>
