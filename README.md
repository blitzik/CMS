# CMS
(require PHP5.6+ a MySQL5.6+)

Entire system including frontend is responsive and supports all
major browsers. Administration supports IE9+.

Based on <a href="https://nette.org/">Nette framework</a> with <a href="https://github.com/Kdyby/Doctrine">Kdyby\Doctrine</a>

Backend is localized into English and Czech language.

<h2>Description</h2>

Classic CMS where Articles have title, date of publication and text. Actually the text
of the Article is not mandatory. There is an Intro of Article that is mandatory
and is visible on main page in Articles list. When you have only an intro typed
in your Article, it is something like flash post.

Cool thing is that you can create your own URL for each Article or let system to
create the URL of Article from its title. The URL of Article can be easily changed any
times you want. When you change the URL of Article the old URLs are automatically
redirected to the new one.

You don't have to publish your Articles immediately. When you create a half of Article you
can save it as draft. Draft Article is not publicly visible but it has its URL already so
you can see how it looks. If some user without admin privileges purposely
or coincidentally visit our Draft Article he will see 404 page.

Once an Article is published it cannot be set as a Draft anymore because it is
most likely already indexed.

Each Article has comment system at the bottom where your visitors can share
their thoughts. You can close this comment system in each article anytime.

As admin you can administrate comments in each Article. You can suppress a comment which
means that the content of comment won't be visible anymore. The content of suppressed
comment is intact but it's replaced by simple message. And of course you can
remove entire comment.

Articles texts and comments use <a href="https://texy.info/en/">Texy! syntax</a>.

You can also set some Tags. Each tag has name and you can also set a color to the Tag.

When you want to add some images into Articles the system has simple Images management
where you can see all images, filter and remove them.

System has some global settings like Webpage title and subtitle, number of Articles
per page and so on.


Well that's all for now but more is coming.

Below you can see some pictures from administration:


<img src="http://others.alestichava.cz/cms-blitzik/pages_overview.jpg" width="600">
<img src="http://others.alestichava.cz/cms-blitzik/page_editing.jpg" width="600">
<img src="http://others.alestichava.cz/cms-blitzik/page_removal.png" width="600">
<img src="http://others.alestichava.cz/cms-blitzik/images.jpg" width="600">
<img src="http://others.alestichava.cz/cms-blitzik/tags.jpg" width="600">
<img src="http://others.alestichava.cz/cms-blitzik/options.jpg" width="600">
<img src="http://others.alestichava.cz/cms-blitzik/log.jpg" width="600">