# News Module

Note: News module for SilverStripe 2.4 is available on the ss24 branch!

## Maintainer Contact

Marcus Nyeholt

<marcus (at) silverstripe (dot) com (dot) au>

## Requirements

SilverStripe 3.0.x

## Overview

The News module provides a straightforward method for creating and publishing
news articles on a website. In some respects it is similar to the Blog module,
however news articles are meant to be focused more around press release style
content - this means a News Article can be represented by normal Content, 
a hosted file (eg a PDF) or a completely remote article on a separate website.
Additionally, News Articles allow authors to specify a separate Summary from
the main content, useful for aggregating content references on your site, and
allows authors to attach a thumbnail for an article.

News Holders can be configured to automatically file contained articles into
a date based hierarchy, generating a hierarchy for archive purposes. 

Unlike the Blog module, the News Module does not support widgets at all, and
does not come with Comments enabled by default. While these things could be
added on by yourself, it is not core to the functionality of the module. 

## Documentation

Extract to the "news" directory in your SilverStripe folder, and run dev/build.
You should now have a "News Holder" page type, and News Item page types to be
created beneath the news holders. 

The News module also provides functionality to automatically file 
articles beneath a hierarchy ordered by Year, Month and day. This will then
automatically provide an "archive" type functionality for news articles. To 
enable this, select the checkbox for "Automatically file contained articles" 
on the News Holder. 

## Known issues

When creating articles with the automatic filing functionality, and its 
eventual parent location isn't visible, the article will initially appear in 
the root of the site tree, even though it has been created underneath the
correct location. Refreshing the tree fixes this problem. 

