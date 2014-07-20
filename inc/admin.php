<?php

@include('admin/qa.php');
@include('admin/check.php');

/* creates a yes/no dropdown item with the right value selected */
function sv_boolean_dropdown($option, $text = "") {
	global $sv_hover_options;

	$value = $sv_hover_options{$option};

	echo $text."<select name=\"$option\" size=\"1\">";

	printf(" <option %s value=\"1\">yes</option>\n",
		$value == 1 ? "selected" : "");
	printf(" <option %s value=\"0\">no</option>\n",
		$value == 0 ? "selected" : "");

	echo "</select>";
}

/* creates a dropdown list */
function sv_multi_dropdown($name, $value, $items) {
	echo "<select name=\"$name\" size=\"1\">";

	foreach ($items as $i) {
		printf(" <option %s value=\"%s\">%s</option>\n",
			$value == $i ? "selected" : "", $i, $i);
	}

	echo "</select>";
}

/* create textareas for our options page for one link */
function sv_hover_display_link($id, $search, $type, $link, $desc,
	$size_s, $size_l, $size_d, $a) 
{
	printf('<tr %s>', $a ? 'class="active"' : '');
	printf('<td align="right" rowspan="2">%d</td>', $id);
	printf("<td><a id=\"id%s\"></a><b>Type: </b>", $id);
	sv_multi_dropdown("type".$id, $type, array("abbr", "acronym", "link"));
	printf("</td>");
	printf('<td rowspan="2" align="center">=&gt;</td>');
	printf('<td rowspan="2" align="right"><textarea name="link%d" %s>%s</textarea>
		</td>', $id, $size_l, $link);
	printf("</tr>");

	printf('<tr %s>', $a ? 'class="active"' : '');
	printf('<td><textarea name="search%d" %s>%s</textarea></td>',
		$id, $size_s, $search);
	printf('</tr>');

	printf('<tr %s>', $a ? 'class="active"' : '');
	printf('<td align="right" valign="top"><b>p<br/>o<br/>p<br/>u<br/>p</b></td>');
	printf('<td colspan="3"><textarea name="description%d" %s>%s</textarea>', $id, $size_d, $desc);
	echo(HOVER_SUBMIT);
	printf("</tr><tr><td>&nbsp;</td></tr>\n");
}

/* convert geometry size style (eg 10x1) into html size style
 * (eg cols="10" rows="1") */
function sv_hover_geometry_to_textarea($option) {
	global $sv_hover_options;

	return preg_replace(
		'|([0-9]*)x([0-9]*)|',
		'cols="$1" rows="$2"',
		$sv_hover_options{$option});
}

/* create a text input field for an option */
function sv_hover_option_to_input($desc, $option, $size = 20) {
	global $sv_hover_options;

	printf('%s: <input type="text" name="%s" value="%s" size="%s"/> ',
		$desc,
		$option,
		$sv_hover_options{$option},
		$size);
}

function sv_hover_panel_head() {
	global $xajax;

	$xajax->printJavascript(HOVER_BASE.'/xajax');

	printf('<link type="text/css" rel="stylesheet" href="%s" />'."\n",
		HOVER_BASE."/admin.css");
	printf("<script type='text/javascript' src='%s'></script>\n",
		HOVER_BASE."/admin.js");
?>
<script type='text/javascript'>
xajax.loadingFunction =
	function(){xajax.$('check').innerHTML='Loading, please wait.';};
</script>
<?php
}

function sv_hover_draw_table($name, $table) {
	$line = '
	<p>
	 <div style="font-weight: bold; background: #eeeeee">'.$name.'</div>
	 <blockquote>
	  <table>';

	$a = false;

	foreach (array_keys($table) as $i) {
		$line .= sprintf("<tr %s><td><b>%s</b></td><td>%s</td></tr>\n",
			$a ? '' : 'class="active"', $i, $table{$i});
			$a = !$a;
	}
	$line .= "
	  </table>
	 </blockquote>
	</p>";

	return $line;
}

function sv_hover_subsubmenu() {
?>
	 <ul id="subsubmenu">
	  <li><a href="<?php echo HOVER_BASE ?>/doc/hover.pdf"><b>Doc</b></a></li>
	  <li>|</li>
	  <li><a href="#Check">Check</a></li>
	  <li><a href="#Fade">Fade</a></li>
	  <li><a href="#Hovers">Hovers</a></li>
	  <li><a href="#Interface">Interface</a></li>
	  <li><a href="#Maxreplace">Maxreplace</a></li>
	  <li><a href="#Maintenance">Maintenance</a></li>
	  <li><a href="#Switches">Switches</a></li>
	  <li><a href="#Titles">Titles</a></li>
	  <li><a href="#Websnapr">Websnapr</a></li>
	  <li><a href="#Images">Images</a></li>
	 </ul>
<?php
}

function sv_hover_update_options() {
	global $sv_hover_options;

	$sv_hover_options = array (
		'usecss' => $_POST['usecss'],
		'usejs' => $_POST['usejs'],
		'usefile' => $_POST['usefile'],
		'blank' => $_POST['blank'],

		'size_search' => $_POST['size_search'],
		'size_link' => $_POST['size_link'],
		'size_desc' => $_POST['size_desc'],

		'fade' => $_POST['fade'],
		'fademax' => $_POST['fademax'],

		'replace' => $_POST['replace'],

		'maxreplace_abbr' => $_POST['maxreplace_abbr'],
		'maxreplace_acronym' => $_POST['maxreplace_acronym'],
		'maxreplace_link' => $_POST['maxreplace_link'],
	);

	update_option('SV_HOVER', $sv_hover_options);
}

# escape description
# FIXME: maybe we need a more general approach, e.g. htmlentities(), utf8, ...
function sv_hover_escape_description($description) {
	$description = preg_replace('|\r\n|', '<br/>', $description);
	$description = preg_replace("|'|", '&#39;', $description);

	return $description;
}

function sv_hover_new_hover() {
	global $wpdb;

	$description = sv_hover_escape_description($_POST['description0']);

	return $wpdb->query(
		"INSERT INTO ".HOVER_TABLE.
		" (search, type, link, description)".
		" VALUES (".
			"'".$_POST['search0']."',".
			"'".$_POST['type0']."',".
			"'".$_POST['link0']."',".
			"'".$description."'".
		")"
	);
}

function sv_hover_new_image() {
	global $wpdb;

	$result = $wpdb->query(
		"INSERT INTO ".HOVER_IMAGES.
		" (src, text)".
		" VALUES (".
			"'".$_POST['src0']."',".
			"'".$_POST['text0']."'".
		")"
	);

	if ($result === FALSE)
		sv_hover_die('insert');
}

function sv_hover_update_hovers() {
	global $wpdb;

	if ($_POST['complete'] != "e96621d9-f72a-4772-aa8b-f16aa3b49f32") {
		wp_die('ERROR: Partial Form content. <pre>'.var_export($_POST, true).'</pre>');
	}

	/* walk through fields, delete empty ones, alter others. We need to
	 * update all existing links, since there is no way to know if
	 * something has changed or not */
	for($id = 1; $id <= $_POST['maxid']; $id++)
		if (empty($_POST['search'.$id])) {
			$result = $wpdb->query(
				"DELETE FROM ".HOVER_TABLE.
				" WHERE id='$id'"
				);
		} else {
			$desc = sv_hover_escape_description(
				$_POST['description'.$id]);

			$result = $wpdb->query(
				"UPDATE ".HOVER_TABLE.
				" SET".
					" search='".$_POST['search'.$id]."',".
					" type='".$_POST['type'.$id]."',".
					" link='".$_POST['link'.$id]."',".
					" description='".$desc."'".
				" WHERE id='$id'"
				);
		}
}

function sv_hover_update_images() {
	global $wpdb;

	for($id = 1; $id <= $_POST['maximages']; $id++)
		if (empty($_POST['src'.$id])) {
			$result = $wpdb->query(
				"DELETE FROM ".HOVER_IMAGES.
				" WHERE id='$id'"
				);
		} else {
			$result = $wpdb->query(
				"UPDATE ".HOVER_IMAGES.
				" SET".
					" src='".$_POST['src'.$id]."',".
					" text='".$_POST['text'.$id]."',".
				" WHERE id='$id'"
				);
		}
}

function sv_hover_handle_file() {
	global $sv_hover_options;

	if ($sv_hover_options{'usefile'})
	{
		sv_hover_create_data();
		sv_hoover_footer_js_writefile();
	} else
	 	@unlink(HOVER_JS_FILE);

	return 0;
}

function sv_hover_hovers() {
	global $wpdb;

	$size_search = sv_hover_geometry_to_textarea('size_search');
	$size_link = sv_hover_geometry_to_textarea('size_link');
	$size_description = sv_hover_geometry_to_textarea('size_desc');

	$categories = $wpdb->get_results(
		"SELECT id, search, type, link, description".
		" FROM ".HOVER_TABLE.
		" ORDER BY search");

	$maxid = $wpdb->get_var(
		"SELECT max(id)".
		" FROM ".HOVER_TABLE);

	echo '<input type="hidden" name="maxid" value="'.$maxid.'"/>';

	sv_fieldset_start("Hovers");

	if (!empty($categories)) {
		foreach ($categories as $cat)
			$shortcuts[$cat->id] = "<a href=\"#id$cat->id\">$cat->search</a>";
		printf("<p>%s</p>\n", implode(", ", $shortcuts));
	}
?>

			<br />

				<table border="0" cellspacing="0">
					<tr align="center">
						<td><b>id</b></td>
						<td><b>search</b></td>
						<td></td>
						<td><b>link</b></td>
<?php
	/* display the special link, the one used to add new entries */
	sv_hover_display_link(0, "", "link", "", "",
		$size_search, $size_link, $size_description, 1);

	/* display all exising links */
	if (!empty($categories)) {
		$a = false;

		foreach ($categories as $cat) {
			$desc = preg_replace('|<br/>|', "\r\n", $cat->description);
			sv_hover_display_link(
				$cat->id, $cat->search, $cat->type, $cat->link,
				$desc, $size_search, $size_link,
				$size_description, $a );

			/* used to alternate the class of each other row, good
			 * for readability */
			$a = !$a;
		}
	}
?>
				</table>
<?php
	sv_fieldset_end();
}

function sv_fieldset_start($name) {
	printf('<a name="%s"></a><fieldset><legend>%s</legend>', $name, $name);
}

function sv_fieldset_end() {
	printf('</fieldset>'."\n");
}

function sv_hover_maintenance_options() {
	if (!is_admin())
		return;

	delete_option('SV_HOVER');

	$response = new xajaxResponse();
	$response->script('alert("Options deleted")');

	return $response;
}

function sv_hover_maintenance_drop() {
	global $wpdb;

	if (!is_admin())
		return;

	$wpdb->query('DROP TABLE '.HOVER_TABLE);

	$response = new xajaxResponse();
	$response->script('alert("Hover table dropped")');

	return $response;
}

function sv_hover_maintenance_drop_images() {
	global $wpdb;

	if (!is_admin())
		return;

	$wpdb->query('DROP TABLE '.HOVER_IMAGES);

	$response = new xajaxResponse();
	$response->script('alert("Images Table dropped")');

	return $response;
}

function sv_hover_images() {
	global $wpdb;

	$size = 'rows="2" cols="80%"';

	$images = $wpdb->get_results(
		"SELECT id, src, text".
		" FROM ".HOVER_IMAGES.
		" ORDER BY src");

	$maxid = $wpdb->get_var(
		"SELECT max(id)".
		" FROM ".HOVER_IMAGES);

	echo '<input type="hidden" name="maximages" value="'.$maxid.'"/>';

	sv_fieldset_start("Images");
?>
	<table border="1" cellspacing="0">
		<tr align="center">
			<td><b>id</b></td>
			<td colspan="2"><b>data</b></td>
		</tr>
<?php

	printf('
		<tr>
		 <td rowspan="2">
		  %d
		 </td>
		 <td align="right"><b>src</b></td>
		 <td><textarea name="src%d" %s>%s</textarea></td>
		</tr>
		<tr>
		 <td><b>text</b></td>
		 <td><textarea name="text%d" %s>%s</textarea></td>
		</tr>
		<tr>
		 <td></td>
		 <td colspan="2">'.HOVER_SUBMIT.'</td>
		</tr>'."\n",
		0, 0, $size, '', 0, $size, '');

	foreach ($images as $i) {
		printf('
			<tr>
			 <td rowspan="2">
			  %d
			 </td>
			 <td align="right"><b>src</b></td>
			 <td><textarea name="src%d" %s>%s</textarea></td>
			</tr>
			<tr>
			 <td><b>text</b></td>
			 <td><textarea name="text%d" %s>%s</textarea></td>
			<tr>
			 <td></td>
			 <td colspan="2">'.HOVER_SUBMIT.'</td>
			</tr>'."\n",
			$i->id, $i->id, $size, $i->src, $i->id, $size, $i->text);
	}

	echo("</table>");

	sv_hover_qa_images();
	sv_fieldset_end();
};

/* our admin function, called from the options page */
function sv_hover_panel () {
	global $wpdb, $sv_hover_options;

	sv_hover_subsubmenu();

	/* did the user press [UPDATE] */
	if (isset($_POST['info_update'])) {
?>
	<div class="updated">
		<p>
			<strong>
<?php

	sv_hover_update_options();

	/* we need to handle the first link specially, since this is where
	 * the user can add new links */
	if (!empty($_POST['search0']))
		sv_hover_new_hover();

	/* update hovers */
	sv_hover_update_hovers();

	if (!empty($_POST['src0']))
		sv_hover_new_image();

	sv_hover_update_images();

	sv_hover_handle_file();
?>
				Updated options.
			</strong>
		</p>
	</div>
<?php
	}

	/* next we create our form */
?>
	<div class=wrap>

		<form action="<?php echo $_SERVER["REQUEST_URI"]?>" method="post">
<?php
	sv_hover_hovers();

	sv_hover_images();

	sv_fieldset_start("Maxreplace");

?>
			<p>Here you can limit the number of times types of
			Hovers are created.</p>
<?php
	sv_hover_option_to_input('Abbr', 'maxreplace_abbr');
	sv_hover_option_to_input('Acronym', 'maxreplace_acronym');
	sv_hover_option_to_input('Link', 'maxreplace_link');

	sv_hover_qa_maxreplace();

	sv_fieldset_end();
	echo(HOVER_SUBMIT);

	sv_fieldset_start("Maintenance");

?>
			<p>Maintenance tools. <strong>WARNING</strong>: Be sure
			to read the Q&A first. Seriously.</p>

		<a onClick="xajax_sv_hover_maintenance_options();" class="ajax">
		Delete options</a><br/>
		<a onClick="xajax_sv_hover_maintenance_drop();" class="ajax">
		Drop Hover Table</a><br/>
		<a onClick="xajax_sv_hover_maintenance_drop_images();" class="ajax">
		Drop Hover Images Table</a><br/>
<?php

	sv_hover_qa_maintenance();

	sv_fieldset_end();
	echo(HOVER_SUBMIT);

	sv_fieldset_start("Switches");

	sv_boolean_dropdown("usecss", " Use internal css: ");
	sv_boolean_dropdown("usejs", " Use javascript: ");
	sv_boolean_dropdown("usefile", " Use File: ");
	sv_boolean_dropdown("blank", " Open in new Window: ");

	sv_hover_qa_switches();

	sv_fieldset_end();
	echo(HOVER_SUBMIT);

	sv_fieldset_start("Fade");
?>
				<p>Configure domTT's fading effects. See
				<a href="http://www.mojavelinux.com/cooker/demos/domTT/">
				here</a> for a demo.</p>

				use fade effect for: 
<?php
	sv_multi_dropdown("fade", $sv_hover_options{'fade'},
		array("in", "out", "neither", "both"));
	
	sv_hover_option_to_input('Max', 'fademax');
	sv_fieldset_end();
	echo(HOVER_SUBMIT);

	sv_fieldset_start("Titles");
?>

				<p>Search the following tags for title
				attributes and replace them with a popup</p>
<?php
	sv_hover_option_to_input('Tags', 'replace');
	sv_hover_qa_titles();

	sv_fieldset_end();
	echo(HOVER_SUBMIT);

	sv_fieldset_start("Websnapr");
?>
				<p><a href="http://websnapr.com/">Websnapr</a>
				support has been disabled for now.</p>

<?php
	sv_hover_qa_websnapr();

	sv_fieldset_end();
	echo(HOVER_SUBMIT);

	sv_fieldset_start("Interface");
	sv_hover_option_to_input('Search', 'size_search');
	sv_hover_option_to_input('Link', 'size_link');
	sv_hover_option_to_input('Description', 'size_desc');
	sv_hover_qa_interface();
	sv_fieldset_end();
	echo(HOVER_SUBMIT);
?>
		<input type="hidden" name="complete" value="e96621d9-f72a-4772-aa8b-f16aa3b49f32"/>
		</form>

<?php sv_fieldset_start("Check"); ?>

		<p>For an overview of hovers options and to perform some
		simple checks click here:
		<a onClick="xajax_sv_hover_check();" class="ajax">
		run checks</a>.
		Please see the Q&amp;A section below for more
		information.</p>

		<p id="check" />
<?php
	sv_hover_qa_check();
	sv_fieldset_end();
?>
	</div>

<?php
}

function sv_hover_xajax() {
	global $xajax;

#	require_once("xajax/xajax_core/xajax.inc.php");


	$xajax = new xajax();
	$xajax->register(XAJAX_FUNCTION, "sv_hover_check");
	$xajax->register(XAJAX_FUNCTION, "sv_hover_maintenance_options");
	$xajax->register(XAJAX_FUNCTION, "sv_hover_maintenance_drop");
	$xajax->register(XAJAX_FUNCTION, "sv_hover_maintenance_drop_images");
	$xajax->processRequest();
}

/* add ourselves to the options page */
function sv_hover_admin() {
	global $sv_hover_options;

	sv_hover_xajax();
	add_plugins_page('Hover', 'Hover', 'activate_plugins', 'hover', 'sv_hover_panel');

}

function sv_hover_die($text, $error = "none") {
	@include('inc/sql.php');

	foreach ($ddl as $sql) {
		$line .= sprintf("<pre>%s</pre>\n", $sql);
	}

	wp_die("
	<p>Oops, hover encountered an error</p>
	<div>context:'$text'</div>
	<div>error:'$error'</div>
	<div>mysql_error():'".mysql_error()."'</div>
	<p>Please check your database for hover tables. The code needed to
	create them is: $line</p>");
}

add_option('SV_HOVER', array(
	version => 6,

	usecss => 1,
	usejs => 1,
	usefile => 0,
	blank => 0,

	size_search => '20x1',
	size_link => '75x3',
	size_desc => '103x4',

	fade => 'neither',
	fademax => 100,

	replace => 'img,span,a',

	maxreplace_abbr => -1,
	maxreplace_acronym => -1,
	maxreplace_link => -1,
	) , '', 'no');

add_action('admin_menu', 'sv_hover_admin');
add_action('admin_head', 'sv_hover_panel_head');

?>
