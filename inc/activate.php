<?php

@include('sql.php');

foreach ($ddl as $name => $sql) {
	if ($wpdb->get_var("show tables like '$name'") == $name)
		continue;

	$result = $wpdb->query($sql);
	if ($result === FALSE)
		sv_hover_die("Could not create table $name", $sql);
}

/* we need to pass a default option here, in case the user is not
 * upgrading but freshly installing. we would then end up running
 * version 0 -> 1 upgrade code which would fail */
$version = get_option('SV_HOVER_VERSION', HOVER_VERSION);
if (FALSE === $version)
	sv_hover_die("Could not determine Database version");

if (0 == $version) {
	$alter = "ALTER table ".HOVER_TABLE." add type tinytext NOT NULL";
	$result = $wpdb->query($alter);

	if ($result === FALSE)
		sv_hover_die("Could not update table", $alter);

	$update = "UPDATE ".HOVER_TABLE." set type='link'";
	$result = $wpdb->query($update);

	if ($result === FALSE)
		sv_hover_die("Could not update table", $update);

	update_option('SV_HOVER_VERSION', 1);
}

if (1 == $version) {
	delete_option('SV_HOVER_PATH_BEHAVIOUR');
	delete_option('SV_HOVER_PATH_DOMTT');

	update_option('SV_HOVER_VERSION', 2);
}

if (2 == $version) {
	update_option('SV_HOVER_VERSION', 3);
}

if (3 == $version) {
	$sv_hover_options = array(
		version => 4,
		usecss => get_option('SV_HOVER_USECSS'),
		usejs => get_option('SV_HOVER_USEJS'),
		usefile => get_option('SV_HOVER_USEFILE'),
		move => get_option('SV_HOVER_MOVE'),
		blank => get_option('SV_HOVER_BLANK'),

		size_search => get_option('SV_HOVER_SIZESEARCH'),
		size_link => get_option('SV_HOVER_SIZELINK'),
		size_desc => get_option('SV_HOVER_SIZEDESCRIPTION'),

		fade => get_option('SV_HOVER_FADE'),
		fademax => get_option('SV_HOVER_FADEMAX'),

		replace => get_option('SV_HOVER_REPLACETITLES'),

		websnapr_link => get_option('SV_HOVER_WEBSNAPR_LINK'),
		websnapr_acronym => get_option('SV_HOVER_WEBSNAPR_ACRONYM'),

		maxreplace_abbr => get_option('SV_HOVER_MAXREPLACE_ABBR'),
		maxreplace_acronym => get_option('SV_HOVER_MAXREPLACE_ACRONYM'),
		maxreplace_link => get_option('SV_HOVER_MAXREPLACE_LINK'),
	);

	update_option('SV_HOVER', $sv_hover_options, "Hover's options", 'no');

	foreach (array('SV_HOVER_VERSION', 'SV_HOVER_USECSS',
		'SV_HOVER_USEJS', 'SV_HOVER_USEFILE', 'SV_HOVER_MOVE',
		'SV_HOVER_BLANK', 'SV_HOVER_SIZESEARCH',
		'SV_HOVER_SIZELINK', 'SV_HOVER_SIZEDESCRIPTION',
		'SV_HOVER_FADE', 'SV_HOVER_FADE_MAX',
		'SV_HOVER_REPLACETITLES', 'SV_HOVER_WEBSNAPR_LINK',
		'SV_HOVER_WEBSNAPR_ACRONYM', 'SV_HOVER_MAXREPLACE_ABBR',
		'SV_HOVER_MAXREPLACE_ACRONYM',
		'SV_HOVER_MAXREPLACE_LINK') as $option)
		delete_option($option);
} // 3 == $version

if (4 == $version) {
	$sv_hover_options = get_option('SV_HOVER');

	unset($sv_hover_options{move});
	$sv_hover_options{version} = 5;

	update_option('SV_HOVER', $sv_hover_options, "Hover's options", 'no');
}

if (5 == $version) {
	$sv_hover_options = get_option('SV_HOVER');

	unset($sv_hover_options{websnapr_link});
	unset($sv_hover_options{websnapr_acronym});
	$sv_hover_options{version} = 6;

	update_option('SV_HOVER', $sv_hover_options, "Hover's options", 'no');
}
?>
