<?php

// Load the XML source
$xml = new DOMDocument;
$xml->load('sample.xml');

$xsl = new DOMDocument;
$xsl->load('sample.xsl');

// Configure the transformer
$proc = new XSLTProcessor;
$proc->importStyleSheet($xsl); // attach the xsl rules

echo $proc->transformToXML($xml);

?> 

