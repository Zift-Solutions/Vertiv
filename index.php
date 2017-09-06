<?php
header("Cache-Control: no-store, no-cache");  
header('Content-Type: text/html; charset=utf-8');
$files = glob('C:/xampp/htdocs/FTP/*_en_*', GLOB_BRACE);
$RES = fopen('resources.csv', 'w');
$CAT = fopen('categories.csv', 'w');
$PRO = fopen('products.csv', 'w');
$GLOBALS["URLs"] =  array();
$GLOBALS["IDs"] =  array();
$GLOBALS["RESIDs"] =  array();
$GLOBALS["documentTitle"] =  array();
$GLOBALS["GroupName"] =  array();
fputcsv($RES, array("Content Type Name","Name","Source","Link","Additional Sources","Author","Content Date","Activation Date","Description","External ID","File","Thumbnail","Resource Type","Resource URL","Resource Name","Resource Page","Import"),',','"');
fputcsv($CAT, array("Content Type Name","Name","Source","Link","Additional Sources","Author","Content Date","Activation Date","Description","External ID","File","Thumbnail","Page Type","Category Page Selection","Category Display Name","Category Page","Sub Category Types","Category Summary","Category Description","Category Features","Category Benefits","Status","Image URL","Image Type","Import","Showcase Region"),',','"');
fputcsv($PRO, array("Content Type Name","Name","Source","Link","Additional Sources","Author","Content Date","Activation Date","Description","External ID","File","Thumbnail","Page Type","Product Page Selection","Product Display Name","Product Page","Category Id","Sub Category Types","ProductSummary","ProductDescription","ProductFeatures","ProductBenefits","Status","ImageURL","Image Type","Import","Showcase Region"),',','"');
foreach($files as $file) {
	$XMLfile = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
	createCsv($XMLfile, $RES, $CAT, $PRO);
}
// Functions
function createCsv($xml,$RES,$CAT, $PRO)
{
	$put_arr = json_decode(json_encode((array)$xml), TRUE);
	foreach($put_arr["Product"] as $MasterProduct){
		$ImageURL = '';
		$ImageType = '';
		if(isset($MasterProduct["@attributes"]["ProductVariantType"])){
			// If ProductVariantType = Product Family
			$ShowcaseTypes = array("Product Family","Product Variant","Individual SKU");
			foreach($ShowcaseTypes as $Types){
			if($MasterProduct["@attributes"]["ProductVariantType"]==$Types){
				foreach($MasterProduct as $Products){
					if(isset($Products["ProductId"])){
						if (strpos($Products["RegionsSoldIn"], 'North America NA') !== false) {
						$ProductFeatures = $Products["ProductFeatures"];
						$ProductBenefits = $Products["ProductBenefits"];
						// RESOURCE VIDEOS
						
						if(isset($Products["ProductAssets"]["ProductVideos"]["ProductVideos"])){
							$ProductVideosFinal = [];
							foreach($Products["ProductAssets"]["ProductVideos"]["ProductVideos"] as $ProductVideos){
								if((isset($ProductVideos["fullStreamingUrl"]))&&($ProductVideos["fullStreamingUrl"]!="")){
									$ProductFront = array("Resources",$ProductName,"","","","Emerson",$Products["PublishedDate"],$Products["PublishedDate"],"",("VIDEO-" . $Products["ProductId"]),"","","Video",$ProductVideos["fullStreamingUrl"],$Products["ProductName"],$Products["ProductId"],"N16");
									fputcsv($RES, $ProductFront,',','"');
								}else{
									foreach($ProductVideos as $Videos){
								if((isset($Videos["fullStreamingUrl"]))&&($Videos["fullStreamingUrl"]!="")){
									$ProductFront = array("Resources",$ProductName,"","","","Emerson",$Products["PublishedDate"],$Products["PublishedDate"],"",("VIDEO-" . $Products["ProductId"]),"","","Video",$Videos["fullStreamingUrl"],$Products["ProductName"],$Products["ProductId"],"N16");
									fputcsv($RES, $ProductFront,',','"');
										}
									}
								}
							}
						}
						// RESOURCES
						if(isset($Products["ProductAssets"]["ProductDocuments"]["Document"])){
							$R = 0;
							foreach($Products["ProductAssets"]["ProductDocuments"]["Document"] as $AssetBlock){
								if(isset($AssetBlock["url"])){
									array_push($GLOBALS['URLs'], $AssetBlock["url"]);
									array_push($GLOBALS["IDs"], $Products["ProductId"]);
									array_push($GLOBALS["RESIDs"], $R . "-RES-" . $Products["ProductId"]);
									array_push($GLOBALS["documentTitle"], $AssetBlock["documentTitle"]);
									array_push($GLOBALS["GroupName"], $AssetBlock["GroupName"]);
									$R++;
								}else{
									foreach($AssetBlock as $Asset){
										if(isset($Asset["url"])){
											array_push($GLOBALS['URLs'], $Asset["url"]);
											array_push($GLOBALS["IDs"], $Products["ProductId"]);
											array_push($GLOBALS["RESIDs"], $R . "-RES-" . $Products["ProductId"]);
											array_push($GLOBALS["documentTitle"], $Asset["documentTitle"]);
											array_push($GLOBALS["GroupName"], $Asset["GroupName"]);
											$R++;
										}
									}
								}
							}
						}
						$countingImage = 0;
						if(isset($Products["ProductAssets"]["ProductImages"]["ProductImages"])){
							foreach($Products["ProductAssets"]["ProductImages"]["ProductImages"] as $ProductImages){
								if(isset($ProductImages["url"])&&($countingImage==0)){
									$ImageURL = $ProductImages["url"];
									$ImageType = $ProductImages["groupName"];
									$countingImage++;
								}else if($countingImage==0){
									foreach($ProductImages as $Image){
										if(isset($Image["url"])){
											$ImageURL = $Image["url"];
											$ImageType = $Image["groupName"];
											$countingImage++;
										}
									}
								}
							}
						}
					
						
						if(empty($ProductBenefits)){ $ProductBenefits = ''; }else{ $ProductBenefits = trim(preg_replace('/\s+/', ' ',mb_convert_encoding($ProductBenefits ,"HTML-ENTITIES","UTF-8"))); }
						$ProductBenefits = str_replace("&bull;", '<span class="bulletHERE">&nbsp;</span>', $ProductBenefits);
						if(empty($ProductFeatures)){ $ProductFeatures = ''; }else{ $ProductFeatures = trim(preg_replace('/\s+/', ' ',mb_convert_encoding($ProductFeatures ,"HTML-ENTITIES","UTF-8"))); }
						$ProductFeatures = str_replace('&bull;', '<span class="bulletHERE">&nbsp;</span>', $ProductFeatures);
						if(empty($Products["ProductDescription"])){ $ProductDescription = ''; }else{ $ProductDescription = trim(preg_replace('/\s+/', ' ',mb_convert_encoding($Products["ProductDescription"],"HTML-ENTITIES","UTF-8"))); }
						if(empty($Products["ProductSummary"])){ $ProductSummary = ''; }else{ $ProductSummary = trim(preg_replace('/\s+/', ' ',mb_convert_encoding($Products["ProductSummary"],"HTML-ENTITIES","UTF-8"))); }
						if(empty($Products["ProductName"])){ $ProductName = ''; }else{ $ProductName = trim(preg_replace('/\s+/', ' ', mb_convert_encoding($Products["ProductName"],"HTML-ENTITIES","UTF-8"))); }
						if(empty($Products["ParentProductId"])){ $ParentProductId = ''; }else{ $ParentProductId = trim(preg_replace('/\s+/', ' ', mb_convert_encoding($Products["ParentProductId"],"HTML-ENTITIES","UTF-8"))); }
						if(($Types=="Product Family")||($Types=="Individual SKU")){
							$ProductArray = array("Product Family",$ProductName,"","","","Emerson",$Products["PublishedDate"],$Products["PublishedDate"],"",$Products["ProductId"] . "21","","",$Types,$Products["ProductId"],$ProductName,$Products["ProductGroup"],$Products["ProductCategory"],$ProductSummary,$ProductDescription,$ProductFeatures,$ProductBenefits,$Products["Status"],$ImageURL,$ImageType,"N16","North America NA");
						}else{
						if($ProductName!=""){
							$ProductArray = array("Products",$ProductName,"","","","Emerson",$Products["PublishedDate"],$Products["PublishedDate"],"",$Products["ProductId"],"","",$Types,$Products["ProductId"],$ProductName,$ParentProductId,$Products["ProductGroup"],$Products["ProductCategory"],$ProductSummary,$ProductDescription,$ProductFeatures,$ProductBenefits,$Products["Status"],$ImageURL,$ImageType,"N16","North America NA");
						}
						}
						if(($Types=="Product Family")||($Types=="Individual SKU")){
							fputcsv($CAT, $ProductArray,',','"');
						}else{
							fputcsv($PRO, $ProductArray,',','"');
						}
					}
				}}
				}
			}
		}
		}
	}
// GLOBAL: Getting only unique resources
$i = 0;
foreach($URLs as $URL){
	if(array_search($URL, $URLs) < $i){
		$GLOBALS["IDs"][array_search($URL, $URLs)] = $GLOBALS["IDs"][array_search($URL, $URLs)] . "," . $GLOBALS["IDs"][$i];
		unset($GLOBALS["IDs"][$i]);
		unset($GLOBALS["URLs"][$i]);
		unset($GLOBALS["documentTitle"][$i]);
		unset($GLOBALS["GroupName"][$i]);
		unset($GLOBALS["RESIDs"][$i]);
	}
$i++;
}
$GLOBALS["IDs"] = array_values($GLOBALS["IDs"]);
$GLOBALS["URLs"] = array_values($GLOBALS["URLs"]);
$GLOBALS["documentTitle"] = array_values($GLOBALS["documentTitle"]);
$GLOBALS["GroupName"] = array_values($GLOBALS["GroupName"]);
$GLOBALS["RESIDs"] = array_values($GLOBALS["RESIDs"]);
$LoopNumber = count($GLOBALS["IDs"]);
for($x = 0; $x < $LoopNumber; $x++){
	$ArrayNew = array("Resources",str_replace('&#65533;','',$GLOBALS["documentTitle"][$x]),"","","","Emerson","2016-11-24T00:49:18.0000000+00:00","2016-11-24T00:49:18.0000000+00:00","",$GLOBALS["RESIDs"][$x],"","",$GLOBALS["GroupName"][$x],$GLOBALS["URLs"][$x],str_replace('&#65533;','',str_replace(".pdf","",mb_convert_encoding($GLOBALS["documentTitle"][$x],"HTML-ENTITIES","UTF-8"))),$GLOBALS["IDs"][$x],"N16");
	fputcsv($RES, $ArrayNew,',','"');
}
fclose($RES);fclose($CAT);fclose($PRO);
?>