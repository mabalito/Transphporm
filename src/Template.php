<?php
namespace Transphporm;
/** Loads an XML string into a DomDocument and allows searching for specific elements using xpath based hooks */
class Template {
	private $hooks = [];
	private $document;
	private $xpath;

	public function __construct($doc) {
		$this->document = new \DomDocument;
		$this->document->loadXML($doc);
	
		$this->xpath = new \DomXPath($this->document);
	}
	
	public function addHook($xpath, $hook) {
		$this->hooks[] = [$xpath, $hook];
	}

	private function processHooks($query, $hook) {
		foreach ($this->hooks as list($query, $hook)) {
			foreach ($this->xpath->query($query) as $element) $hook->run($element);
		}
	}

	private function printDocument(\DocumentElement $doc) {
		$output = '';
		foreach ($doc->documentElement->childNodes as $node) $output .= $doc->saveXML($node, LIBXML_NOEMPTYTAG);
		return $output;
	}

	public function output($document = false) {
		//Process all hooks
		 $this->processHooks();

		//Generate the document by taking only the childnodes of the template, ignoring the <template> and </template> tags
		//TODO: Is there a faster way of doing this without string manipulation on the output or this loop through childnodes?
		if ($document) return $this->document;


		$output = ($this->document->doctype) ? $this->document->saveXml($this->document->doctype) . "\n" : '';

		if ($this->document->documentElement->tagName !== 'template') $output .= $this->document->saveXml($this->document->documentElement, LIBXML_NOEMPTYTAG);
		else $output = $this->printDocument($this->document);

		//repair empty tags. Browsers break on <script /> and <div /> so can't avoid LIBXML_NOEMPTYTAG but they also break on <base></base> so repair them
		$output = str_replace(['></img>', '></br>', '></meta>', '></base>', '></link>', '></hr>', '></input>'], ' />', $output);
		return trim($output);
	}
}

