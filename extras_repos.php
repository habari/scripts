<?php

$api_endpoint = 'https://api.github.com/';

$url = $api_endpoint . "orgs/habari-extras/repos";
$result = file_get_contents( $url );
$repos = json_decode ( $result );

$extras_repositories = array();

foreach( $repos as $repo ) {
	$extras_repositories[] = $repo->name;
}
?>
