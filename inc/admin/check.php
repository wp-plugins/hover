<?php

function sv_hover_check_url($url) {

	require_once( ABSPATH . 'wp-includes/class-snoopy.php');

	$snoopy = New Snoopy;
	$snoopy->fetch($url);

	list($version, $code, $text) = split(' ', $snoopy->response_code);

	if (200 != $code) {
		$ret  = "<p class=bad>ERROR: $snoopy->response_code</p>";
		$ret .= "<p><b>Url</b>:<pre>".$url."</pre></p>";
		$ret .= "<p><b>Response</b>:<pre>".
			join("", $snoopy->headers)."</pre></p>";
	} else
		$ret = "OK";

	return $ret;
}

function sv_hover_check_javascript() {
	global $sv_hover_options;

	$files = array(
		"behaviour.js" => HOVER_BEHAVIOUR_URL,
		"domTT.js" => HOVER_DOMTT_URL,
		"domLib.js" => HOVER_DOMTT_URL,
		"fadomatic.js" => HOVER_DOMTT_URL,
		"hover.js" => HOVER_JS_URL
	);

	$checks = array();

	foreach (array_keys($files) as $f) {
		$checks{$f} = sv_hover_check_url($files{$f}.'/'.$f);
	}

	# this is kind of evil since we first perform the check and then
	# hide the error if USEFILE is disabled.
	if ( !$sv_hover_options{'usefile'} ) {
		$checks{$f} = "disabled";
	}

	return $checks;
}

function sv_hover_check_database($name) {
	global $wpdb;

	if ($wpdb->get_var("show tables like '$name'") != $name) {
		$db['ERROR'] = "Table $name missing?!";
		return $db;
	}

	$describe = $wpdb->get_results("DESCRIBE $name");

	foreach($describe as $d)
		$db[$d->Field] = $d->Type;

	return $db;
}

function sv_hover_check_upgrade() {
	global $wpdb;

	if ($wpdb->get_var("show tables like '".HOVER_TABLE."'") != HOVER_TABLE) {
		$db['ERROR'] = "Table ".HOVER_TABLE." missing?!";
		return $db;
	}

	$colons = $wpdb->get_results(
		"SELECT search".
		" FROM ".HOVER_TABLE." WHERE search LIKE ':%:'"
		);

	$db = array();
	foreach ($colons as $c) {
		$db["Colons ".$c->search] = "Found, please remove training colon";
	}

	if (!is_array($db)) {
		$db["Colons"] = "No trailing colons found, good";
	}

	return $db;
}

function sv_hover_check_ticket($id, $reason) {
	return sprintf('<a href="%s/%s">Ticket #%s</a> %s',
		'https://bc-bd.org/trac/hover/ticket',
		$id, $id, $reason);
}

function sv_hover_check() {
	global $sv_hover_options;

	@include(HOVER_PATH.'/inc/sql.php');

	$common = array(
		"HOVER_BASE" => HOVER_BASE,
		"HOVER_BEHAVIOUR_URL" => HOVER_BEHAVIOUR_URL,
		"HOVER_DOMTT_URL" => HOVER_DOMTT_URL,
		"HOVER_JS_FILE" => HOVER_JS_FILE,
		"HOVER_JS_URL" => HOVER_JS_URL,
		"wpurl" => get_bloginfo('wpurl'),
		"url" => get_bloginfo('url'),
	);

	$table = array(
		"DB" => $sv_hover_options{'version'},
		"Version" => '0.7.3',
		"Commit" => '513bf3759140c702b71a99e5aa7dac04b00ef080'
	);

	$line = sv_hover_draw_table("Versions", $table);

	$javascript = sv_hover_check_javascript();
	$line .= sv_hover_draw_table("Javascript", $javascript);

	$options['TABLE'] = HOVER_TABLE;
	$options['IMAGES'] = HOVER_IMAGES;

	$line .= sv_hover_draw_table("Options", $sv_hover_options);

	$line .= sv_hover_draw_table("Common", $common);

	foreach ($ddl as $name => $sql) {
		$db = sv_hover_check_database($name);
		$line .= sv_hover_draw_table($name, $db);
	}

	$db = sv_hover_check_upgrade();
	$line .= sv_hover_draw_table("Upgrade", $db);

	$response = new xajaxResponse();
	$response->assign("check", "innerHTML", $line);

	return $response;
}

?>
