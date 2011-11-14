<?php

$api_endpoint = 'https://api.github.com/';

$url = $api_endpoint . "orgs/habari-extras";

$result = file_get_contents( $url );

$result = json_decode ( $result );

if ( isset( $result->public_repos ) and $result->public_repos > 0 ) {
	$url = $api_endpoint . "orgs/habari-extras/repos";

//	$url .= "?per_page=100"; // this does not seem to actually be paginating currently, so this is unnecessary

	$result = file_get_contents( $url );

	$repos = json_decode ( $result );

	$count = 1;
	foreach( $repos as $repo ) {
		echo $count++ . ". {$repo->name}\n";

	}
}
?>
