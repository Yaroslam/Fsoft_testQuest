<?php

require "vendor/autoload.php";
use PHPHtmlParser\Dom;


function main(string $url){
    $urlObj = getUrlObj($url);
    $dataForUrl = getDataFromClass($urlObj, "draftArrow", "/[0-9]+/");
    $allData = [];
    $keys = ["Type", "Organizational and legal form", "Full name", "INN", "CRR", "Postal address"];
    preg_match_all("/[0-9]+/",$url,$regNumber);
    foreach ($dataForUrl as $data){
        $url = "https://zakupki.gov.ru/epz/order/notice/ea44/view/protocol/protocol-bid-review.html?regNumber=".$regNumber[0][1]."&protocolLotId=".$data[0]."&protocolBidId=".$data[1];
        $subUrlObj = getUrlObj($url);
        $class_data = getDataFromClass($subUrlObj,"section__info");
        array_pop($class_data);
        array_pop($class_data);
        array_push($allData, $class_data);
    }
    $myJson = makeJson($allData, $keys);
    file_put_contents('./zakupki'.$regNumber[0][1].'.json', $myJson);

}


function makeJson($values, $keys){
    $JsonVal = [];
    foreach ($values as $val){
     $JsonSubValue = [];
     foreach ($val as $v){
         $v = trim($v);
         $v = str_replace ("&#034;", "\"", $v);
         array_push($JsonSubValue, $v);
     }
     $SubJson = array_combine($keys,$JsonSubValue);
     array_push($JsonVal, $SubJson);
    }
    $json = json_encode($JsonVal, JSON_UNESCAPED_UNICODE);
    return $json;
}


function getUrlObj(string $url){
    $dom = new Dom;
    $dom->loadFromUrl($url);
    return $dom;
}


function getDataFromClass(Dom $dom, string $className, string $regex=""){
    $matches = [];
    $classData = $dom->find('.'.$className);
    if ($regex ==   ""){
        foreach($classData as $data){
            array_push($matches, $data->text);
        }
    }
    else{
        foreach ($classData as $data) {
            $data = $data->__toString();
            preg_match_all($regex, $data, $match);
            array_push($matches, $match[0]);
        }
    }
    return $matches;
}


main("https://zakupki.gov.ru/epz/order/notice/ea44/view/protocol/protocol-bid-list.html?regNumber=0329200062221006202&protocolId=35530565");

