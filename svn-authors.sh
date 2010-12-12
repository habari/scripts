#!/usr/bin/env php
<?php
// generates SVN authors list

// grab wiki text:
$contributors = array();
$dom = new DOMDocument;
$dom->loadHTMLFile('http://wiki.habariproject.org/en/Contributors');
$xpath = new DOMXpath($dom);
$rows = $xpath->query('//table/tr');
$firstRow = true;
foreach ($rows as $row) {
	if ($firstRow) {
		$firstRow = false;
		continue;
	}
	$svnNames = explode(', ', trim($row->childNodes->item(1)->nodeValue));
	$first = array_shift($svnNames);
	$contributors[$first] = trim($row->childNodes->item(0)->nodeValue);

	foreach ($svnNames as $svnName) {
		$contributors[$svnName] = '@' . $first; // @ = link
	}
}

// get authors from svn log
$rawAuthors = `svn log -q`;
if (!$rawAuthors) {
	echo "No authors.\n";
	exit(1);
}

// grab unique authors into $authors
$authors = array();
$rawAuthors = explode("\n", $rawAuthors);
foreach ($rawAuthors as $authorLine) {
	if (!isset($authorLine[0]) || $authorLine[0] !== 'r') {
		// not a revision line;
		continue;
	}
	list(,$author) = explode('|', $authorLine, 3);
	$author = trim($author);
	if (!isset($authors[$author]) && $author != '(no author)') {
		if (isset($contributors[$author])) {
			$authors[$author] = $contributors[$author];
		} else {
			$authors[$author] = '';
		}
	}
}

// output
foreach ($authors as $user => $name) {
	if (!$name) {
		$name = "(no name)";
	}
	$userLower = strtolower($user);
	if ($name[0] == '@') { // check for link
		$userLower = substr($name, 1);
		if (isset($contributors[$userLower])) {
			$name = $contributors[$userLower];
		}
		$userLower = strtolower($userLower);
	}
	echo "{$user} = {$name} <{$userLower}@contrib.habariproject.org>\n";
}



