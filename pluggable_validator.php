<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <title>Pluggable XML Validator</title>
  </head>
  <style type="text/css">
.result {border-bottom: 1px solid #999;margin-bottom: 1em;padding-bottom:1em;}
.error {color: #660000;}
  </style>
  <body>


<?php 

//$xsdURI = "http://schemas.habariproject.org/Pluggable-0.8.xsd";
$xsdURI = "http://schemas.habariproject.org/Pluggable-0.9.xsd";


if(isset($_REQUEST['uri'])) :

function err_handler($errno, $errstr, $errfile, $errline, $errcontext)
{
	global $result;
	$result .= '<div class="error">' . $errstr . '</div><br>';
}

$result = '';

if((isset($_POST['xml']) && trim($_POST['xml']) != '') && (isset($_REQUEST['itype']) && $_REQUEST['itype'] =='xml')) {
	$doc = new DomDocument;
	$doc->loadXML( stripslashes($_POST['xml']) );
	$xmlout = htmlspecialchars(stripslashes($_POST['xml']));
}

if((!isset($doc)  || (isset($_REQUEST['itype']) && $_REQUEST['itype'] =='url')) && preg_match('%^https?://%', $_REQUEST['uri'] )) {
	$xmlFile = $_REQUEST['uri'];
	$doc = new DomDocument;
	$xmlout = file_get_contents( $xmlFile );
	$doc->loadXML( $xmlout );
	//$doc->load( $xmlFile );
	$xmlout = htmlspecialchars($xmlout);
}

if(isset($doc)) {
	$xsd = file_get_contents( $xsdURI );

	set_error_handler('err_handler');

	if ( $doc->schemaValidateSource( $xsd ) ) {
		$result .= "XML is valid.";
	} else {
		$result .= "XML is <em>NOT</em> valid.";
	}
}
else {
	$result = "XML File specified is not valid.";
}

echo '<div class="result">' . $result . '</div>';

endif;

if (!isset( $xmlout ) ) { $xmlout = ''; }
?>

<div>Schema used for validation is: <a href="<?php echo $xsdURI; ?>"><?php echo $xsdURI; ?></a></div>

<form method="post" action="">
<label><input type="radio" name="itype" value="url" <?php if(!isset($_REQUEST['itype']) || $_REQUEST['itype'] != 'xml') {echo 'checked'; }?> > URL of XML to validate against Plugin Schema:</label><br>
<input type="text" name="uri" style="width:100%;" value="<?php if(isset($_REQUEST['uri'])) { echo htmlspecialchars($_REQUEST['uri']); }?>">
<br>OR<br>
<label><input type="radio" name="itype" value="xml" <?php if(isset($_REQUEST['itype']) && $_REQUEST['itype'] == 'xml') {echo 'checked'; }?> > Text of XML to validate against Plugin Schema:</label><br>
<textarea name="xml" style="width:100%;height:10em;"><?php echo $xmlout; ?></textarea>
<input type="submit" value="Validate">
</form>


  </body>
</html>
