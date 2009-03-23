<?php
/*
Plugin Name: Hover
Plugin URI: http://bc-bd.org/blog/?page_id=48
Description: Replaces keywords with links and optional onmouseover() popups. Something not working? Send me some <a href="mailto:bd@bc-bd.org">FEEDBACK</a>.
Author: Stefan V&ouml;lkel
Author URI: http://bc-bd.org
Version: v0.6.8

Released under the GPLv2.

inspired by Text Link Ads 1.4 by Wil rushmer, http://www.rushmer.com
Uses domTT: http://www.mojavelinux.com/projects/domtooltip/
Uses behaviour.js: http://bennolan.com/behaviour/
Uses xajax: http://www.xajaxproject.org/

Other URLs:
http://freshmeat.net/projects/hover/
http://wpplugins.org/plugin/hover
http://svn.wp-plugins.org/hover/
http://wp-plugins.net/plugin/hover/#plugin_2211
http://wordpress.softplug.net/component/option,com_mtree/task,viewlink/link_id,231/Itemid,40/

*/

/* since 2.5 we need to define those two global, else
 * sv_hover_install is not able to access the needed values
 * see https://bc-bd.org/trac/hover/ticket/13 */
global $table_prefix, $sv_hover_sql;

/* this is where we store our data */
define('HOVER_TABLE', $table_prefix."hover");
define('HOVER_BASE', get_bloginfo('wpurl')."/wp-content/plugins/hover");

define('HOVER_BEHAVIOUR_URL', HOVER_BASE."/behaviour");
define('HOVER_DOMTT_URL', HOVER_BASE."/domTT");

if (substr(get_option('upload_path'),0 , 1) == "/") {
	define('HOVER_JS_FILE', get_option("upload_path")."/"."hover.js");
} else {
	define('HOVER_JS_FILE', ABSPATH."/".get_option("upload_path")."/hover.js");
}

if (get_option('upload_url_path')) {
	define('HOVER_JS_URL', get_option('upload_url_path'));
} else {
	define('HOVER_JS_URL', get_bloginfo('wpurl')."/wp-content/uploads");
}

$sv_hover_options = array();

/* code needed to create our table */
$sv_hover_sql = "CREATE TABLE ".HOVER_TABLE." (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	   type tinytext NOT NULL,
	   search tinytext NOT NULL,
	   link text NOT NULL,
	   description text not NULL,
	   UNIQUE KEY id (id)
		   );";

/* actual content replacing happens here */
function sv_hover_get($content) {
	global $sv_hover_data;

	// aggregate replace counting data.
	$maxreplace{'abbr'} = get_option('SV_HOVER_MAXREPLACE_ABBR');
	$maxreplace{'acronym'} = get_option('SV_HOVER_MAXREPLACE_ACRONYM');
	$maxreplace{'link'} = get_option('SV_HOVER_MAXREPLACE_LINK');

	/* loop over all links and replace them */

	foreach ($sv_hover_data as $hover)
		$content = preg_replace($hover->search, $hover->replace,
		$content, $maxreplace{$hover->type});

	return $content;
}

/* create needed date here */
function sv_hover_create_data() {
	global $wpdb, $sv_hover_behaviour, $sv_hover_data;

	/* retrieve data from database */
	$sv_hover_links = $wpdb->get_results(
		"SELECT id, search, type, link, description".
		" FROM ".HOVER_TABLE." ORDER BY id"
		);

	/* no need to add script, css or filter if there are no links */
	if (count($sv_hover_links) <= 0)
		return false;

	foreach ($sv_hover_links as $link){
		// pattern matching our name
		$search = "|(?!<[^<>]*?)(?<![?./&])".
			"$link->search".
			"([ \.,\!\?])(?!:)(?![^<>]*?>)|imsU";

		$replace = preg_replace('|^:(.*):$|imsU', '$1',
			$link->search);

		$id = 'hover'.$link->id;

		/* If javascript is disabled, we set the title attribute */
		if (get_option('SV_HOVER_USEJS') == "0")
			$title = " title=\"$link->description\" ";

		if ("" != $link->link)
			$websnapr = sprintf("<br/> <div class=\"hover-websnapr\"><div class=\"hover-websnapr-spinner\"><\/div><img src=http://images.websnapr.com/?size=s&amp;url=%s class=\"hover-websnapr-img\" /><\/div>", urlencode($link->link));
		else
			$websnapr = "";

		$desc = $link->description;

		// and build together our hover
		switch ($link->type) {
			case "abbr":
				$replace = '<abbr '.$title.'>'.
					'<span class="'.$id.'">'.
					$replace.
					'</span></abbr>';
				break;
			case "acronym":
				$replace = '<acronym '.$title.'>'.
					'<span class="'.$id.'">'.
					$replace.
					'</span></acronym>';

				if (get_option('SV_HOVER_WEBSNAPR_ACRONYM'))
					$desc .= $websnapr;

				break;
			case "link":
				$replace = '<a class="hover-link" href="'.$link->link.
					'" '.$title.'><span class="'.$id.'">'.
					$replace.
					'</span></a>';

				if (get_option('SV_HOVER_WEBSNAPR_LINK'))
					$desc .= $websnapr;

				break;
		}

		$replace .= '$1';

		$sv_hover_data[$id]->search = $search;
		$sv_hover_data[$id]->replace = $replace;
		$sv_hover_data[$id]->type = $link->type;

		// reference java script on mouse events
		if (get_option('SV_HOVER_USEJS') != "0")
			$sv_hover_behaviour[$id] = $desc;
	}

	return true;
}

/* initialize our plugin here */
function sv_hover_header () {
	global $sv_hover_behaviour, $wpdb;

	/* create data, skip js inclusion if no data exists */
	if (!sv_hover_create_data())
		return false;

	/* register content filter */
	add_filter('the_content', 'sv_hover_get', 18);

	/* include java script of wanted */
	if (get_option('SV_HOVER_USEJS') != "0") {
		add_action('wp_footer', 'sv_hover_footer');

		echo '<script type="text/javascript" src="'.
			HOVER_BEHAVIOUR_URL.
			'/behaviour.js"></script>'."\n";

		echo '<script type="text/javascript" src="'.
			HOVER_DOMTT_URL.
			'/domLib.js"></script>'."\n";
		echo '<script type="text/javascript" src="'.
			HOVER_DOMTT_URL.
			'/domTT.js"></script>'."\n";

		/* no need to include fading if it is not enabled */
		if ("neither" != get_option('SV_HOVER_FADE')) {
			echo '<script type="text/javascript" src="'.
				HOVER_DOMTT_URL.
				'/fadomatic.js"></script>'."\n";
		}

		/* we need to define or style regardless if we should include
		 * domTT or not since above we already checked for js support */
		echo '<script type="text/javascript">';
		echo 'var domTT_styleClass = \'hover\';';
		echo '</script>'."\n";

		printf('<script type="text/javascript" src="%s"></script>'.
			"\n", HOVER_BASE."/hover.js");
	}

	/* include css if wanted */
	if (get_option('SV_HOVER_USECSS') != "0") {
		echo '<link type="text/css" rel="stylesheet" href="'.
			HOVER_BASE.'/hover.css" />'."\n";
	}
}

function sv_hover_footer_js() {
	global $sv_hover_behaviour;

	/* no need to include fading code if it is not enabled */
	if ("neither" != get_option("SV_HOVER_FADE"))
		$fade .=
			"  ,'fade', '".get_option("SV_HOVER_FADE")."', ".
			"  'fadeMax', ".get_option("SV_HOVER_FADE_MAX");
	else
		$fade = "";

	if ("" != get_option('SV_HOVER_REPLACETITLES')) {
		$line .= sprintf("hover_replaceTitlesByTags('%s', \"%s\");\n\n",
			get_option('SV_HOVER_REPLACETITLES'),
			"'trail', true ".$fade);
	}

        /* do not add javascript on empty replace array */
        if (sizeof($sv_hover_behaviour) == 0)
                return $line;

	$line .= 'var hover = {'."\n";

	foreach (array_keys($sv_hover_behaviour) as $b)
		$sv_hover_behaviour[$b] = 
			"'span.$b' : function(element) {\n".
			" element.onmouseover = function(event) {\n".
			"  domTT_activate(this, event, ".
			"  'content', '$sv_hover_behaviour[$b]', ".
			"  'trail', true ".
			$fade.
			"  );\n".
			" }"."\n".
			"}"."\n";

	$line .= implode(",", $sv_hover_behaviour);

	$line .= '};'."\n\n";
    	$line .= 'Behaviour.register(hover);'."\n";

	return $line;
}

function sv_hoover_footer_js_writefile() {

	if (get_option('SV_HOVER_USEFILE')) {
		$f = fopen(HOVER_JS_FILE, 'w');

		if (!$f) {
			echo "Could not open file: '".HOVER_JS_FILE."'";
			return false;
		}

		fwrite($f, sv_hover_footer_js());
		fclose($f);
	}

	return true;
}

/* create data array for behaviour.js */
function sv_hover_footer($footer) {

	if (get_option('SV_HOVER_USEFILE') && is_file(HOVER_JS_FILE)) {
		echo '<script type="text/javascript" src="'.
			HOVER_JS_URL.'/hover.js'.
			'"></script>'."\n";
	} else {
		/* we need to enclose our javascript code in CDATA tags
		 * since XHTML 1.0 Strict defines the contents of
		 * <script> as PCDATA which would us deny the
		 * possibility to use special html characters */
		echo '<script type="text/javascript">';
		echo '/* <![CDATA[ */';
		echo sv_hover_footer_js();
		echo '/* ]]> */';
		echo '</script>';
	}
}

/* creates a yes/no dropdown item with the right value selected */
function sv_boolean_dropdown($name, $value, $text = "") {
	echo $text."<select name=\"$name\" size=\"1\">";

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
	sv_hover_submit();
	printf("</tr><tr><td>&nbsp;</td></tr>\n");
}

/* convert geometry size style (eg 10x1) into html size style
 * (eg cols="10" rows="1") */
function sv_hover_geometry_to_textarea($option) {
	return preg_replace(
		'|([0-9]*)x([0-9]*)|',
		'cols="$1" rows="$2"',
		get_option($option));
}

/* create a text input field for an option */
function sv_hover_option_to_input($desc, $option, $size = 20) {
	printf('%s: <input type="text" name="%s" value="%s" size="%s"/> ',
		$desc,
		$option,
		get_option($option),
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

"Database",
"This is the current database schema."
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
	." which will allow editors to alter these options too."
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

function sv_hover_submit() {
?>
			<div class="submit">
				<a href="#wphead">Top</a>
				<input type="submit" name="info_update" value="Update options"/>
			</div>
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
	 </ul>
<?php
}

function sv_hover_update_options() {
	update_option('SV_HOVER_USECSS', $_POST['css']);
	update_option('SV_HOVER_USEJS', $_POST['js']);
	update_option('SV_HOVER_MOVE', $_POST['move']);
	update_option('SV_HOVER_USEFILE', $_POST['file']);

	update_option('SV_HOVER_SIZESEARCH', $_POST['SV_HOVER_SIZESEARCH']);
	update_option('SV_HOVER_SIZELINK', $_POST['SV_HOVER_SIZELINK']);
	update_option('SV_HOVER_SIZEDESCRIPTION', $_POST['SV_HOVER_SIZEDESCRIPTION']);

	update_option('SV_HOVER_FADE', $_POST['fade']);
	update_option('SV_HOVER_FADE_MAX', $_POST['SV_HOVER_FADE_MAX']);

	update_option('SV_HOVER_REPLACETITLES',	$_POST['SV_HOVER_REPLACETITLES']);

	update_option('SV_HOVER_WEBSNAPR_ACRONYM', $_POST['SV_HOVER_WEBSNAPR_ACRONYM']);
	update_option('SV_HOVER_WEBSNAPR_LINK', $_POST['SV_HOVER_WEBSNAPR_LINK']);

	update_option('SV_HOVER_MAXREPLACE_ABBR', $_POST['SV_HOVER_MAXREPLACE_ABBR']);
	update_option('SV_HOVER_MAXREPLACE_ACRONYM', $_POST['SV_HOVER_MAXREPLACE_ACRONYM']);
	update_option('SV_HOVER_MAXREPLACE_LINK', $_POST['SV_HOVER_MAXREPLACE_LINK']);
}

function sv_hover_new_hover() {
	global $wpdb;

	# FIXME handle newlines in description
	return $wpdb->query(
		"INSERT INTO ".HOVER_TABLE.
		" (search, type, link, description)".
		" VALUES (".
			"'".$_POST['search0']."',".
			"'".$_POST['type0']."',".
			"'".$_POST['link0']."',".
			"'".$_POST['description0']."'".
		")"
	);
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
			$desc = preg_replace('|\r\n|', '<br/>', $_POST['description'.$id]);
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

function sv_hover_handle_file() {
	if (get_option('SV_HOVER_USEFILE'))
	{
		sv_hover_create_data();
		sv_hoover_footer_js_writefile();
	} else
	 	@unlink(HOVER_JS_FILE);

	return 0;
}

function sv_hover_hovers() {
	global $wpdb;

	$size_search = sv_hover_geometry_to_textarea('SV_HOVER_SIZESEARCH');
	$size_link = sv_hover_geometry_to_textarea('SV_HOVER_SIZELINK');
	$size_description =
		sv_hover_geometry_to_textarea('SV_HOVER_SIZEDESCRIPTION');

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
	if ( !get_option('SV_HOVER_USEFILE') ) {
		$checks{$f} = "disabled";
	}

	return $checks;
}

function sv_hover_check_upload_url() {
	global $wpdb;

	$describe = $wpdb->get_results("DESCRIBE ".HOVER_TABLE);

	if (get_option("SV_HOVER_USEFILE")) {
		$check["SV_HOVER_USEFILE"] = "True, the following checks apply to you.";
	} else {
		$check["SV_HOVER_USEFILE"] = "False, you can ignore the following checks and possible warning messages";
	}

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

function sv_hover_check_database() {
	global $wpdb;

	$describe = $wpdb->get_results("DESCRIBE ".HOVER_TABLE);

	foreach($describe as $d)
		$db[$d->Field] = $d->Type;

	return $db;
}

function sv_hover_check_ticket($id, $reason) {
	return sprintf('<a href="%s/%s">Ticket #%s</a> %s',
		'https://bc-bd.org/trac/hover/ticket',
		$id, $id, $reason);
}

function sv_hover_check() {
	global $sv_hover_options;

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
		"DB" => get_option('SV_HOVER_VERSION'),
		"Version" => 'v0.6.8',
		"Commit" => '4e611b6aa77b7461f84ca06aa84d03cb209cf729'
	);

	$line .= sv_hover_draw_table("Versions", $table);
	
	$javascript = sv_hover_check_javascript();
	$line .= sv_hover_draw_table("Javascript", $javascript);

	$options['TABLE'] = HOVER_TABLE;
	foreach ($sv_hover_options as $opt)
		$options{$opt} = get_option($opt);

	$line .= sv_hover_draw_table("Config", $options);

	$line .= sv_hover_draw_table("Common", $common);

	$url = sv_hover_check_upload_url();
	$line .= sv_hover_draw_table("Upload Checks", $url);

	$db = sv_hover_check_database();
	$line .= sv_hover_draw_table("Database", $db);

	$response = new xajaxResponse();
	$response->addAssign("check", "innerHTML", $line);

	return $response;
}

/* our admin function, called from the options page */
function sv_hover_panel () {
	global $wpdb;

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

	sv_fieldset_start("Maxreplace");

?>
			<p>Here you can limit the number of times types of
			Hovers are created.</p>
<?php
	sv_hover_option_to_input('Abbr', 'SV_HOVER_MAXREPLACE_ABBR');
	sv_hover_option_to_input('Acronym', 'SV_HOVER_MAXREPLACE_ACRONYM');
	sv_hover_option_to_input('Link', 'SV_HOVER_MAXREPLACE_LINK');

	sv_hover_qa_maxreplace();

	sv_fieldset_end();
	sv_hover_submit();

	sv_fieldset_start("Switches");

	sv_boolean_dropdown("css", get_option('SV_HOVER_USECSS'), " Use internal css: ");
	sv_boolean_dropdown("js", get_option('SV_HOVER_USEJS'), " Use javascript: ");
	sv_boolean_dropdown("file", get_option('SV_HOVER_USEFILE'), " Use File: ");
	sv_boolean_dropdown("move", get_option('SV_HOVER_MOVE'), " Move to Plugins: ");

	sv_hover_qa_switches();

	sv_fieldset_end();
	sv_hover_submit();

	sv_fieldset_start("Fade");
?>
				<p>Configure domTT's fading effects. See
				<a href="http://www.mojavelinux.com/cooker/demos/domTT/">
				here</a> for a demo.</p>

				use fade effect for: 
<?php
	sv_multi_dropdown("fade", get_option('SV_HOVER_FADE'),
		array("in", "out", "neither", "both"));
	
	sv_hover_option_to_input('Max', 'SV_HOVER_FADE_MAX');
	sv_fieldset_end();
	sv_hover_submit();

	sv_fieldset_start("Titles");
?>

				<p>Search the following tags for title
				attributes and replace them with a popup</p>
<?php
	sv_hover_option_to_input('Tags', 'SV_HOVER_REPLACETITLES');
	sv_hover_qa_titles();

	sv_fieldset_end();
	sv_hover_submit();

	sv_fieldset_start("Websnapr");
?>
				<p>Enable
				<a href="http://websnapr.com/">Websnapr</a>
				for these items</p>

<?php
	sv_boolean_dropdown("SV_HOVER_WEBSNAPR_ACRONYM",
		get_option('SV_HOVER_WEBSNAPR_ACRONYM'), " Acronym:");
	sv_boolean_dropdown("SV_HOVER_WEBSNAPR_LINK",
		get_option('SV_HOVER_WEBSNAPR_LINK'), " Links:");

	sv_hover_qa_links();

	sv_fieldset_end();
	sv_hover_submit();

	sv_fieldset_start("Interface");
	sv_hover_option_to_input('Search', 'SV_HOVER_SIZESEARCH');
	sv_hover_option_to_input('Link', 'SV_HOVER_SIZELINK');
	sv_hover_option_to_input('Popup', 'SV_HOVER_SIZEDESCRIPTION');
	sv_hover_qa_interface();
	sv_fieldset_end();
	sv_hover_submit();
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

	require_once("xajax/xajax.inc.php");

	$xajax = new xajax();
	$xajax->registerfunction("sv_hover_check");
	$xajax->processRequests();
}

/* add ourselves to the options page */
function sv_hover_admin() {
	sv_hover_xajax();
	if (get_option('SV_HOVER_MOVE')) {
		if (function_exists('add_submenu_page'))
			add_submenu_page('plugins.php', 'Hover', 'Hover', 1,
				basename(__FILE__), 'sv_hover_panel');
	} else
		if (function_exists('add_options_page'))
			add_options_page('Hover', 'Hover', 8, basename(__FILE__), 'sv_hover_panel');
}

function sv_hover_die($text, $sql = "none") {
	global $sv_hover_sql;

	die("
	<p>Oops, hover encountered an error</p>
	<div>context:'$text'</div>
	<div>sql:'$sql'</div>
	<div>mysql_error():'".mysql_error()."'</div>
	<p>Please check your database for a valid hover table. The code needed to
	create it is: <pre>'".$sv_hover_sql."'</pre></p>".sv_hover_check());
}

/* called on plugin activation */
function sv_hover_install () {
	global $wpdb, $sv_hover_sql;

	/* does or table exist */
	if ($wpdb->get_var("show tables like '".HOVER_TABLE."'") != HOVER_TABLE) {
		$wpdb->query($sv_hover_sql);
		update_option('SV_HOVER_VERSION', 1);
	}

	if (0 == get_option('SV_HOVER_VERSION')) {
		$alter = "ALTER table ".HOVER_TABLE." add type tinytext NOT NULL";
		$wpdb->query($alter) or sv_hover_die("Could not update table",
			$alter);

		$update = "UPDATE ".HOVER_TABLE." set type='link'";
		$wpdb->query($update) or sv_hover_die("Could not update table",
			$update);

		update_option('SV_HOVER_VERSION', 1);
	}

	if (1 == get_option('SV_HOVER_VERSION')) {
		delete_option('SV_HOVER_PATH_BEHAVIOUR');
		delete_option('SV_HOVER_PATH_DOMTT');

		update_option('SV_HOVER_VERSION', 2);
	}
}

function sv_hover_add_option($name, $default, $desc, $load) {
	global $sv_hover_options;

	add_option($name, $default, $desc, $load);

	array_push($sv_hover_options, $name);
}

/* register activation and options hooks */
add_action('activate_hover/hover.php','sv_hover_install');
add_action('admin_menu', 'sv_hover_admin');
add_action('admin_head', 'sv_hover_panel_head');

/* register head hook */
add_action('wp_head', 'sv_hover_header');

/* internal options */
sv_hover_add_option('SV_HOVER_VERSION', '1', 'Database schema version', 'no');

/* generic functional options */
sv_hover_add_option('SV_HOVER_USECSS', '1', "Use internal css", 'no');
sv_hover_add_option('SV_HOVER_USEJS', '1', "Use javascript", 'no');
sv_hover_add_option('SV_HOVER_USEFILE', '0', "Create Javascript file", 'no');
sv_hover_add_option('SV_HOVER_MOVE', '1', "Move Page to Plugins", 'yes');

/* layout options */
sv_hover_add_option('SV_HOVER_SIZESEARCH', '20x1', "Size of Search Input Field", 'no');
sv_hover_add_option('SV_HOVER_SIZELINK', '75x3', "Size of Link Input Field", 'no');
sv_hover_add_option('SV_HOVER_SIZEDESCRIPTION', '103x4',
	"Size of Desription Input Field", 'no');

/* behavioural options */
sv_hover_add_option('SV_HOVER_FADE', 'neither', "domTT fading effect", 'no');
sv_hover_add_option('SV_HOVER_FADE_MAX', '100', "domTT fading effect alpha value", 'no');

sv_hover_add_option('SV_HOVER_REPLACETITLES', 'img,span,a', "Replace title attribute", 'no');

sv_hover_add_option('SV_HOVER_WEBSNAPR_LINK', '1', "Use Websnapr on links", 'no');
sv_hover_add_option('SV_HOVER_WEBSNAPR_ACRONYM', '1', "Use Websnapr on acronyms", 'no');

sv_hover_add_option('SV_HOVER_MAXREPLACE_ABBR', '-1', "Maximum number an abbr is replaced", 'no');
sv_hover_add_option('SV_HOVER_MAXREPLACE_ACRONYM', '-1', "Maximum number an acronym is replaced", 'no');
sv_hover_add_option('SV_HOVER_MAXREPLACE_LINK', '-1', "Maximum number a link is replaced", 'no');
?>
