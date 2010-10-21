# News Module

## Maintainer Contact

Marcus Nyeholt

<marcus (at) silverstripe (dot) com (dot) au>

## Requirements

SilverStripe 2.4.x

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

