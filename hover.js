/*
 * Copyright 2007,2010 Stefan Völkel <bd@bc-bd.org>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
/*
 * based on domTT v0.7.3 by Dan Allen
 */

/* look for all tags in 'in_tags' and hover'ify their titles */
function hover_replaceTitlesByTags(in_tags, in_options, in_decorator)
{
	var elements = domLib_getElementsByTagNames(in_tags.split(","), true);
	for (var i = 0; i < elements.length; i++)
	{
		if (elements[i].title)
		{
			var content;
			if (typeof(in_decorator) == 'function')
			{
				content = in_decorator(elements[i]);
			}
			else
			{
				content = elements[i].title;
			}

			content = content.replace(new RegExp("'", 'g'), "&rsquo;");
			elements[i].onmouseover = new Function('in_event', "domTT_activate(this, in_event, 'content', '" + content + "', " + in_options + ")");
			elements[i].title = '';
		}
	}
}

/* lookup src attribute of all img tags in a hash, and replace their titles with
 * the value of said hash */
function hover_images(in_options)
{
	var elements = domLib_getElementsByTagNames(['img'], true);
	for (var i = 0; i < elements.length; i++)
	{
		var title = hover_image_map[elements[i].src];

		if (undefined != title)
		{
			elements[i].title = title;
		}
	}
}
