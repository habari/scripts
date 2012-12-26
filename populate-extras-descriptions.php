<?php

  // skip the first x repos?
	$skip = 0;

	$current_page = 1;

	$api_url = 'https://api.github.com/orgs/habari-extras/repos';

	// UPDATE YOUR USERNAME AND PASSWORD HERE
	$api_update_url = 'https://username:password@api.github.com/repos/habari-extras/';

	// make sure our repos directory exists
	if ( !is_dir( 'habari-extras' ) ) {
		mkdir( 'habari-extras' );
	}

	// move into the directory
	chdir( 'habari-extras' );

	// set the total number of pages to 1 for now - it'll get updated in the loop
	$pages = 1;
	$i = 0;
	for ( $page = 1; $page <= $pages; $page++ ) {

		echo 'Getting page ' . $page . "\n";

		$result = get_page( $api_url . '?page=' . $page );

		echo 'Got ' . count( $result['items'] ) . ' repos' . "\n";

		// make sure we go all the way
		$pages = $result['last_page'];

		foreach ( $result['items'] as $repo ) {

			$i++;

			if ( $i < $skip ) {
				continue;
			}


			echo 'Processing ' . $repo->name . "\n";

			if ( strpos( $repo->description, 'A mirror of the ' ) === 0 ) {
				// they already have a non-default description, so let's just skip over it now that we've made one pass thru
			}

			if ( !is_dir( $repo->name ) ) {
				echo 'Cloning ' . $repo->clone_url . "\n";
				// clone the repo locally
				exec( 'git clone ' . $repo->clone_url );
			}
			else {
				echo 'Pulling' . "\n";
				chdir( $repo->name );
				// update it
				exec( 'git pull' );

				chdir( '../' );
			}

			// look for a plugin xml file in the new cloned copy
			$xml_files = glob( $repo->name . '/{*.plugin.xml,theme.xml}', GLOB_BRACE );

			if ( count( $xml_files ) == 0 ) {
				echo 'No XML file found for ' . $repo->name . "\n";
				continue;
			}

			// get the xml contents
			$xml = file_get_contents( $xml_files[0] );

			// read it in with SimpleXML, which is horrible, but gets the job done
			$s = new SimpleXMLElement( $xml );

			// make sure it has a description
			if ( isset( $s->description ) && !empty( $s->description ) ) {
				$description = (string)$s->description;

				// prefix it
				$description = strtoupper( (string)$s['type'] ) . ': ' . $description;
			}
			else {
				echo 'No description in XML for ' . $repo->name . "\n";
				continue;
			}

			// does the repo lack a URL or have a generic one?
			if ( !isset( $repo->homepage ) || empty( $repo->homepage ) || $repo->homepage == 'http://habariproject.org' ) {

				// then look for that in the XML, too
				if ( isset( $s->url ) && !empty( $s->url ) ) {
					$url = (string)$s->url;
				}
				else {
					$url = null;
					echo 'No URL for ' . $repo->name . "\n";
					// we don't continue for this one
				}

			}
			else {
				$url = null;
			}

			// is there actually anything to change?
			if ( ( $description != $repo->description ) || ( $url != null && $url != $repo->homepage ) ) {
				// update the repo's description and (maybe) URL
				update_repo( $repo->name, $description, $url );
			}

		}

	}


	function get_page ( $url ) {

		$content = file_get_contents( $url );

		if ( $content === false ) {
			die('Unable to get repo list' );
		}

		$items = json_decode( $content );

		// parse out the next and last page links
		foreach ( $http_response_header as $header ) {
			if ( strpos( $header, 'Link: ' ) === 0 ) {

				preg_match_all( '/page=(\d+)/', $header, $matches );

				$next_page = $matches[1][0];
				$last_page = $matches[1][1];

				echo $last_page . ' total pages' . "\n";

			}
		}

		if ( !isset( $next_page ) || !isset( $last_page ) ) {
			echo 'No pages!';
			print_r($http_response_header);
			echo $items;
			die();
		}

		return array(
			'items' => $items,
			'next_page' => $next_page,
			'last_page' => $last_page,
		);

	}

	function update_repo ( $name, $description, $homepage = null ) {

		global $api_update_url;

		$url = $api_update_url . $name;

		$fields = array(
			'name' => $name,
			'description' => $description,
		);

		if ( $homepage != null ) {
			$fields['homepage'] = $homepage;
		}

		$options = array(
			'http' => array(
				'method' => 'PATCH',
				'content' => json_encode( $fields ),
				'header' => array(
					'Content-Type: application/x-www-form-urlencoded ',
				),
			),
		);

		$context = stream_context_create( $options );

		$result = file_get_contents( $url, false, $context );

		$json = json_decode( $result );

		// did it fail?
		if ( $json->description != $description || ( $homepage != null && $json->homepage != $homepage ) ) {
			echo 'Failed updating ' . $name . "\n";
		}
		else {
			echo 'Updated ' . $name . "\n";
		}

	}

?>
