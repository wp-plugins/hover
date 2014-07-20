<?php

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

function sv_hover_qa_maintenance() {
	sv_hover_qa_make_list(array(
"What does 'delete options' do?",
"This will delete all options for hover. Your links, etc will not be touched,
but everything else will be unset. You will need to deactivate/activate hover,
before it will work again",

"What does 'Drop Hover Table' do?",
"This will DELETE all your configured hovers. No recovery possible.",

"What does 'Drop Hover Images Table' do?",
"This will DELETE all your configured images. No recovery possible.",
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

"Open in new Window:",
"Opens links in a new window"
));

}

?>
