function sv_hover_hide(id)
{
	var e = document.getElementById(id);
	var p = e.parentNode.firstChild.nextSibling;
	
	e.className = "hidden";
	p.onclick = function(in_event) { sv_hover_show(id); };
	p.innerHTML = "Questions & Answers (click to show)";
}

function sv_hover_show(id)
{
	var e = document.getElementById(id);
	var p = e.parentNode.firstChild.nextSibling;
	
	e.className = "visible";
	p.onclick = function(in_event) { sv_hover_hide(id); };
	p.innerHTML = "Questions & Answers (click to hide)";
	Fat.fade_element(id, '30', '600', '#00FF00');
}
