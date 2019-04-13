<?php
function ifsetor(&$variable, $default = null)
{
    if (isset($variable))
    {
        $tmp = $variable;
    }
    else
    {
        $tmp = $default;
    }
    
    return $tmp;
}

function googleapi_search($isbn)
{
    $query=GOOGLE_BOOKS_EP;
    $query.=$isbn;
    
    $jsonResult=  file_get_contents($query);
    $decodedOutput= json_decode($jsonResult,true);
    
    if($decodedOutput['totalItems']!=0)
    {
        return $decodedOutput;
    }
    else
    {
        return NULL;
    }
}

function googleapi_getisbn10($searchResult)
{
    $numIds = count($searchResult['items'][0]['volumeInfo']['industryIdentifiers']);
    for($x=0;$x<$numIds;$x++)
    {   
        if($searchResult['items'][0]['volumeInfo']['industryIdentifiers'][$x]['type'] == 'ISBN_10')
        {
            return $searchResult['items'][0]['volumeInfo']['industryIdentifiers'][$x]['identifier'];
        }
    }
    return NULL;
}

function googleapi_getisbn13($searchResult)
{
    $numIds = count($searchResult['items'][0]['volumeInfo']['industryIdentifiers']);
    
    for($x=0;$x<$numIds;$x++)
    {
        if($searchResult['items'][0]['volumeInfo']['industryIdentifiers'][$x]['type'] == 'ISBN_13')
        {
            return $searchResult['items'][0]['volumeInfo']['industryIdentifiers'][$x]['identifier'];
        }
    }
    return NULL;
}
