<?php
/*
 This script performs 4 major payout operations
  1) Check Balance
  2) Add Beneficiary
  3) Make Transfer
  4) Remove Beneficiary
  Comment out the operation(s) you don't wish to execute

  How to get started?
  1) Copy clientId/clientSecret from your Cashfree Merchant Dashboard (Go to Smart Payout -> Access Control -> API Keys)
  2) Whitelist the IP of the system this script is going to be run on (IP Whitelist tab)
  3) Run this by executing : `php execute.php`
*/

include("cfpayout.inc.php");

$clientId = "<your_client_id>";
$clientSecret = "<your_client_secret>";
$stage = "TEST"; //use "PROD" for testing with live credentials

$authParams["clientId"] = $clientId;
$authParams["clientSecret"] = $clientSecret;
$authParams["stage"] = $stage;

try {
  $payout = new CfPayout($authParams);
} catch (Exception $e) {
  echo $e->getMessage();
  echo "\n";  
  die();
}

echo "--------------Fetching Balance---------------\n";
$balance = $payout->getBalance();
echo "Ledger balance is : " .$balance["ledger"];
echo "\n";
echo "Available balance is : " .$balance["available"];
echo "\n";

echo "--------------Adding Beneficiary---------------\n";
$beneficiary = [];
$beneficiary["beneId"] = rand(1, 1000);
$beneficiary["name"] = "My Bene";
$beneficiary["email"] = "mybene@gocashfree.com";
$beneficiary["phone"] = "9876554321";
$beneficiary["bankAccount"] = "4444333322221111";
$beneficiary["ifsc"] = "HDFC0000001";
$beneficiary["address1"] = "820 Iris Avenue, IndiraNagar";
$beneficiary["city"] = "Bangalore";
$beneficiary["state"] = "Karnataka";
$beneficiary["pincode"] = "560008";

$response = $payout->addBeneficiary($beneficiary);
if ($response["status"] == "SUCCESS") {
  echo "Beneficary has been addedd successfully";
}  else {
  echo "Beneficary addition failed. ";
  echo "Reason - ".$response["message"];
}
echo "\n";


echo "--------------Requesting Transfer---------------\n";
$transfer = [];
$transfer["beneId"] = $beneficiary["beneId"];
$transfer["amount"] = 2;
$transfer["transferId"] = time();
$transfer["remarks"] = "Transfer request from Payout kit";
$response = $payout->requestTransfer($transfer);

if ($response["status"] == "SUCCESS") {
  echo "Transfer processed successfully\n";
  echo "Cashfree reference id is ". $response["data"]["referenceId"];
  echo "\n";
  echo "Bank reference number is ". $response["data"]["utr"]; 
} else if ($response["status"] == "PENDING") {
  echo "Transfer request being processed at bank. Check the status after few minutes.\n";
  echo "Cashfree reference id is ". $response["data"]["referenceId"];
} else if ($response["status"] == "ERROR") {
  echo "Transfer request failed\n";
  echo "Reason - ". $response["message"];
}
echo "\n";

echo "--------------Removing Beneficiary---------------\n";
$response = $payout->removeBeneficiary($beneficiary["beneId"]);
if ($response["status"] == "SUCCESS") {
 echo "Beneficiary with id ". $beneficiary["beneId"]. " has been removed";
} else {
  echo "Beneficiary removal failed. Please try again";
}
echo "\n";

?>
