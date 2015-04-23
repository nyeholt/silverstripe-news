# SilverStripe News Module

SilverStripe news is a standalone module which provides content editor features to have news and blog posts in a SilverStripe website. 

Read more at [SilverStripers.com blog](http://www.silverstripers.com/blog/silverstripe-news-module)

## Installing 

User composer to install

`composer require silverstripers/silverstripe-news dev-master`

## Features 

1. Tags 
2. Categories 
3. Archives 
4. Different Types of news posts for different purposes
5. Use the powerful model admin to manage news
6. robust search form
7. friendly urls for tags and categories
8. Force editing news from the model admin, and doesnt load news pages on to the site tree.

## Configs

All the configuration options are added on to Site Config

go to SiteConfig from the laft navigation panel and to

Settings -> News tab

## Codes 

### Add tags to a page 

add the following code to your Page.ss file or any other template file which renders contents of a Page. 

```
<% if $BlogTags %>
  <ul>
  <% loop $BlogTags %>
    <li><a href='{$Link}'>{$Tag} ({$Count})</a></li>
  <% end_loop %>
  </ul>
<% end_if %>
```

### Add blog archive to a page 

the module allows you to chose that format of archive you want to use, this can be configured in the Site Config. 
the options are 

1. Year
2. Year, Full Month (2015, April)
3. Year, Month Number (2015, 04)
4. Abbreviated Month Name (Jan, Feb, Mar, etc..)

welcome to add more options for these and submit pull requests 

```
<% if $BlogArchives %>
  <ul>
  <% loop $BlogArchives %>
    <li><a href='{$Link}'>{$Archive}</a></li>
  <% end_loop %>
  </ul>
<% end_if %>
```

### Categories 

to show the news categories made in the CMS use 

```
<% if $NewsCategories %>
  <ul>
  <% loop $NewsCategories %>
    <li><a href='{$Link}'>{$Title}</a></li>
  <% end_loop %>
  </ul>
<% end_if %>
```


### Show news on news index

to show news items on your news index use the following code or similar. 

```
<% if $Items %>
    <ul class="news-list">
        <% loop $Items %>
        <li class="news-item">
            <h2>{$Title}</h2>
            <time datetime="{$DateTime.Format('Y-m-d')}">{$DateTime.Format('F d, Y')}</time>
            <% if $Summary %>
                $Summary
            <% else %>
                {$Content.FirstSentence}
            <% end_if %>
            <a class="readMore" href="{$Link}">read more&hellip;</a>
        </li>
        <% end_loop %>
    </ul>

    <% if $Items.MoreThanOnePage %>
        <div class="pagination">
            <ul>
                <% if $Items.NotFirstPage %>
                    <li>
                        <a class="prev" href="{$Items.PrevLink}"><span class="arrow-left"></span></a>
                    </li>
                <% end_if %>
                <% loop $Items.Pages %>
                    <li <% if $CurrentBool %>class="active"<% end_if %>>
                        <% if $Link %>
                            <a href="{$Link}">{$PageNum}</a>
                        <% else %>
                            &hellip;
                        <% end_if %>
                    </li>
                <% end_loop %>
                <% if $Items.NotLastPage %>
                    <li>
                        <a class="next" href="{$Items.NextLink}"><span class="arrow-right"></span></a>
                    </li>
                <% end_if %>
            </ul>
        </div>
    <% end_if %>
<% end_if %>
```

if you want to take the first news articles out of this list, may be on to a hero panel you can do that by passing an offset to the Items functions 

the function call would be `$Items(1)` if you dont want the first item to be returned on to the list. 
