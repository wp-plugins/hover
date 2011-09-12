<?php

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

function sv_hover_qa_make_list($list) {
	global $sv_hover_qa_id;

	$sv_hover_qa_id += 1;
	$id = "qa".$sv_hover_qa_id;
?>
 <div class="qa">
  <p onclick="sv_hover_show('<?php echo $id?>')" class="ajax">
  Questions &amp; Answers</p>
  <ol class="visible" id="<?php echo $id?>">
<?php

	for ($i =0; $i <count($list); $i += 2)
		printf(
'<li>
  <ul>
   <li class="question">%s</li>
   <li class="answer">%s</li>
  </ul>
 </li>'."\n", $list[$i], $list[$i+1]);

?>
  </ol>
 </div>
 <script type="text/javascript">
  sv_hover_hide('<?php echo $id?>');
 </script>
<?php
}

function sv_hover_qa_websnapr() {
	sv_hover_qa_make_list(array(
"This does not seem to work",
"Websnapr support has been disabled in 0.7.0, since you now need to register
with their site."
	));
}

function sv_hover_qa_titles() {
	sv_hover_qa_make_list(array(
"How do I disable this feature?",
"Delete the content of the input field.",

"What are sensible tags?",
"At the moment I am using: 'img,span,a'.",

"It is not working!",
'Be sure to seperate each tag with a comma and do NOT include whitespaces:
<br/><br/>
<table>
<tr><td align="right" class="bad">BAD</td><td class="pre">img span a</td></tr>
<tr><td align="right" class="bad">BAD</td><td class="pre">img, span, a</td></tr>
<tr><td align="right" class="good">GOOD</td><td class="pre">img,span,a</td></tr>
</table>'
	));
}

function sv_hover_qa_links() {
	sv_hover_qa_make_list(array(
"Where does the link for acronyms come from?",
"From the 'link' input field.",

"How can I disable this feature for a specific acronym?",
"Leave the link field empty."
	));
}

function sv_hover_qa_check() {
	sv_hover_qa_make_list(array(
"How do I run the checks?",
"Simply click on 'run checks' above. The tests will then be run through AJAX on
demand to save CPU and bandwidth. Find below a short explanation of each check.",

"Versions",
"These are some internal versioning information. Please include them in every
BUG report.",

"Known Problems",
"This is a list of known problems with links to their ticket numbers",

"Javascript",
"These checks test the availability of all needed javascript files. Access is
tested via HTTP, thus resembling the behaviour of a normal browser. Errors here
mostly indicate a wrong setting in 'Paths'.",

"Config",
"An overview of all of Hover's settings including their value. Please include
them in every BUG report.",

"*_hover/*_hover_images",
"These are the current database schemata."
));
}

function sv_hover_qa_images() {
	sv_hover_qa_make_list(array(
"What data do I need to enter?",
"Put the <i>absolut</i> path of your image into the <b>src</b> textarea and the
text you'd want to popup into the <b>text</b> textare",

"How is this different from including 'img' in the Switches option?",
"The latter only takes the title attribute and displays it as a popup. With
this, you can define the title attribute once, and it will be used for every
occurance of that image on your blog.",

"This overwrites title attributes I set with the WYSIWYG editor",
"Yes, that would be a feature.",

"Image popups are not working properly, only a browser based popup is shown",
"Make sure you have 'img' (without the quotes) included in the <i>Titles</i>
setting"
));

}

function sv_hover_qa_maxreplace() {
	sv_hover_qa_make_list(array(
"I am not sure I understand what this option actually does?",
"With maxreplace you are able to limit the number of times a specific hover is
created. For example if you define an acronym Hover for 'FTP' and use this in
your posting 'FTP is a simple protocol. FTP may use two Ports.' and a maxreplace
limit of 1 for acronyms only the first occurance of FTP will be decorated with a
popup.",

"I used a maxreplace of 1, but still more than one Hover is created for the same
term?",
"Due to technical limitations maxreplace can only be applied to certain blocks
of your content. On your Frontpage maxreplace limitation only works per posting,
thus a Hover found in two postings will be replaced one time per posting.",

"I want to replace all occurances!",
"Just enter a value of -1.",

"I entered 0 and no hovers are created?!",
"Hover did just as it was told and replaced 0 occurances (see previous Q&amp;A)."
));

}

function sv_hover_qa_interface() {
	sv_hover_qa_make_list(array(
"What are these settings for?",
"You can alter the size of all textfields used to configure hovers. This
enables you to adapt it to your screen size. Changing these settings does not
alter your blog, only this configuration page.",

"What format are these settings in?",
"It is <i>Width</i><b>x</b><i>Height</i>. To set a size of 3 rows and
20 columns use (without the quotes): '20x3'",

"Setting height to 1 still results in a textbox two lines high?",
"That would be a feature."
));
}

function sv_hover_qa_switches() {
	sv_hover_qa_make_list(array(
"Use internal css:",
"Hover comes with it's preconfigured CSS file defining the needed classes."
	." You can disable the inclusion of this file e.g. if you'd like"
	." to define your one style.",

"Use javascript:",
"You can disable the use of javascript alltogether with the downside of"
	." losing some features like popups and title replacement. ",

"Use file:",
"Through activating this option Hover will create a file containing all needed
javascript in your uploads directory and include this into your blog via link.
This will save CPU cycles (only create needed code once) and bandwidth (clients
and proxies can cache this file).",

"Move to Plugins:",
"This will move the configuration page from Options/Hover to Plugins/Hover"
	." which will allow editors to alter these options too.",

"Open in new Window:",
"Opens links in a new window"
));

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
		usecss => $_POST['usecss'],
		usejs => $_POST['usejs'],
		usefile => $_POST['usefile'],
		move => $_POST['move'],
		blank => $_POST['blank'],

		size_search => $_POST['size_search'],
		size_link => $_POST['size_link'],
		size_desc => $_POST['size_desc'],

		fade => $_POST['fade'],
		fademax => $_POST['fademax'],

		replace => $_POST['replace'],

		websnapr_link => $_POST['websnapr_link'],
		websnapr_acronym => $_POST['websnapr_acronym'],

		maxreplace_abbr => $_POST['maxreplace_abbr'],
		maxreplace_acronym => $_POST['maxreplace_acronym'],
		maxreplace_link => $_POST['maxreplace_link'],
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

	if ($sv_hover_options{usefile})
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
	if (!empty($categories)) 
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
	if ( !$sv_hover_options{usefile} ) {
		$checks{$f} = "disabled";
	}

	return $checks;
}

function sv_hover_check_upload_url() {
	global $wpdb, $sv_hover_options;

	$describe = $wpdb->get_results("DESCRIBE ".HOVER_TABLE);

	if ($sv_hover_options{usefile})
		$check["usefile"] = "True, the following checks apply to you.";
	else
		$check["usefile"] = "False, you can ignore the following checks and possible warning messages";

	if (substr(get_option('upload_path'),0 , 1) == "/") {
		$check["upload_path"] = "Is an absolute path, checking for upload_url_path";
		if (get_option('upload_url_path')) {
			$check["upload_url_path"] = "Is set, good.";
		} else {
			$check["upload_url_path"] = "Is unset, <span class=bad>BAD</span>. YOU WILL NEED TO CONFIGURE THIS OR USE A RELATIVE PATH FOR upload_url. Hover wil fail to work if you don't.";
		}
	} else {
		$check["upload_path"] = "Is an relative path, good. Skiping unneeded tests";
	}

	return $check;
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

	$colons = $wpdb->get_results(
		"SELECT search".
		" FROM ".HOVER_TABLE." WHERE search LIKE ':%:'"
		);

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

	@include('sql.php');

	$common = array(
		"HOVER_BASE" => HOVER_BASE,
		"HOVER_BEHAVIOUR_URL" => HOVER_BEHAVIOUR_URL,
		"HOVER_DOMTT_URL" => HOVER_DOMTT_URL,
		"HOVER_JS_FILE" => HOVER_JS_FILE,
		"HOVER_JS_URL" => HOVER_JS_URL,
		"wpurl" => get_bloginfo('wpurl'),
		"url" => get_bloginfo('url'),
		"upload_path" => get_option('upload_path'),
		"upload_url_path" => get_option('upload_url_path')
	);

	$table = array(
		"DB" => $sv_hover_options{version},
		"Version" => '0.7.0',
		"Commit" => 'a81041ec15fe48c991bc631a73c0d834c8d90ec6'
	);

	$line .= sv_hover_draw_table("Versions", $table);

	$javascript = sv_hover_check_javascript();
	$line .= sv_hover_draw_table("Javascript", $javascript);

	$options['TABLE'] = HOVER_TABLE;
	$options['IMAGES'] = HOVER_IMAGES;

	$line .= sv_hover_draw_table("Options", $sv_hover_options);

	$line .= sv_hover_draw_table("Common", $common);

	$url = sv_hover_check_upload_url();
	$line .= sv_hover_draw_table("Upload Checks", $url);

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

	sv_fieldset_start("Switches");

	sv_boolean_dropdown("usecss", " Use internal css: ");
	sv_boolean_dropdown("usejs", " Use javascript: ");
	sv_boolean_dropdown("usefile", " Use File: ");
	sv_boolean_dropdown("move", " Move to Plugins: ");
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
	sv_multi_dropdown("fade", $sv_hover_options{fade},
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
		</form>

	</div>

<?php
}

function sv_hover_xajax() {
	global $xajax;

#	require_once("xajax/xajax_core/xajax.inc.php");


	$xajax = new xajax();
	$xajax->register(XAJAX_FUNCTION, "sv_hover_check");
	$xajax->processRequest();
}

/* add ourselves to the options page */
function sv_hover_admin() {
	global $sv_hover_options;

	sv_hover_xajax();
	if ($sv_hover_options{move}) {
		if (function_exists('add_submenu_page'))
			add_submenu_page('plugins.php', 'Hover', 'Hover', 1,
				basename(__FILE__), 'sv_hover_panel');
	} else
		if (function_exists('add_options_page'))
			add_options_page('Hover', 'Hover', 8, basename(__FILE__), 'sv_hover_panel');
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

/* called on plugin activation */
function sv_hover_install () {
	global $wpdb;

	@include('inc/activate.php');
}

add_option('SV_HOVER', array(
	version => 4,

	usecss => 1,
	usejs => 1,
	usefile => 0,
	move => 1,
	blank => 0,

	size_search => '20x1',
	size_link => '75x3',
	size_desc => '103x4',

	fade => 'neither',
	fademax => 100,

	replace => 'img,span,a',

	websnapr_link => 1,
	websnapr_acronym => 1,

	maxreplace_abbr => -1,
	maxreplace_acronym => -1,
	maxreplace_link => -1,
	) , '', 'no');

/* register activation and options hooks */
add_action('activate_hover/hover.php','sv_hover_install');
add_action('admin_menu', 'sv_hover_admin');
add_action('admin_head', 'sv_hover_panel_head');

?>
