---
title:  "Blog Setup with Jekyll"
date:   2019-02-17 9:00:46 +0200
categories: Projects
tags: [Project Finish, Website]
toc: true
---
I thought the first project description should be my automatic jekyll page generation.

## Why tho?
At least **20%** of the popular internet runs on WordPress. {%cite trendcms%} Some years ago I hosted a WordPress blog on my server. It is really comfortable and configurable, the need for PHP and a DBS is also not a problem for anyone, you can set those up in minutes or just rent an inexpensive instance.

The main opensource WordPress platform has been become quite hardened over time, but vulnerabilities in one of the over **50.000** plugins (Feb19){%cite wordpressplugins%} get discovered almost monthly.  The only _dynamic_ part of a website I would be interested in, is a comment system, otherwise my site consists of static content.

## Jekyll
![Jekyll logo](/assets/images/jekylllogo.png){: .align-right} While working on a pipeline to analyse methylome data I needed to document a lot of decisions and also wanted reports on the processed datasets. At the time I had already used Markdown (Cheatsheat {%cite markdown%}) with [Showdown](https://github.com/showdownjs/showdown) and [Rmarkdown](https://rmarkdown.rstudio.com) to generate HTML pages and came acrosse the Jekyll Project. Jekyll is a Ruby program, which allows the creation of static blog-like-pages with the help of Markdown and [Liquid](https://shopify.github.io/liquid/).
Another really nice option is to use jekyll within a [Github Pages](https://pages.github.com) page, then they take of automatically building your page, but they limit the plugins you can use to the ones in this [gem](https://github.com/github/pages-gem), this is the reason I use a different configuration.

### Installation 
The  [documentation](https://jekyllrb.com/docs/) is quite useful, it is also a jekyll page. To install jekyll you will need [Ruby](https://www.ruby-lang.org/en/documentation/installation/) and [Bundler](https://bundler.io/#getting-started). Bundler can be installed after Ruby using:
``` sh
gem install bundler
```

### CI friendly Setup 
After looking at [some themes](http://jekyllthemes.org) I decided to use the MinimalMistakes Theme {%cite mmistakes%}. I have been seeing it or a fork of it on some websites of researchers and liked the *minimal* design, it is also very functional and well documented. To install it, make a new directory and create a file in it with the name `Gemfile` and the following content:

``` sh
source "https://rubygems.org"
# To upgrade, run `bundle update`.
gem "jgd" # Automatic build and git deployment
gem "jekyll" # Site Generation
gem "minimal-mistakes-jekyll" # Theme
# The following plugins are automatically loaded by the theme-gem:
#   gem "jekyll-paginate"
#   gem "jekyll-sitemap"
#   gem "jekyll-gist"
#   gem "jekyll-feed"
#   gem "jemoji"
#   gem "jekyll-data"

gem "unicode"

group :jekyll_plugins do
    gem "jekyll-archives" # Tag and category pages
    gem "jekyll-scholar" # Bibliography and citations
end
```

You will also need the default jekyll/minimalmistakes `_config.yaml`file, which you can download from the repository:
```
wget https://raw.githubusercontent.com/mmistakes/minimal-mistakes/master/_config.yml
```

In this file add:
``` yml
theme: "minimal-mistakes-jekyll"
source: src
destination: _site
```

This means that jekyll will read the input file from the `src` directory and store the resulting html pages in the `_site` directory.
To create the directory structure in the `src` directory:
``` sh
bundle exec jekyll new --skip-bundle --blank src
```

### Example post and page
Now you can create posts and pages.

`src/index.html`
```` md
---
layout: home
author_profile: true 
---
Hi!
````


`src/_pages/about.md`
```` md
---
title: About me
layout: single
permalink: about
author_profile: true 
---
## Headline
 You can create a 404 page in app/_pages/404.md
````

`src/_posts/2019-02-017-namestartshere.md`
```` md
---
title:  "This is the post title"
# The post will only be build into the page, 
# if the date of the build reached this:
date:   2019-02-17 9:00:46 +0200 
# You can also use the --future option
categories: Category
tags: [Tag 1, Tag2]
toc: true
#toc: table of contents
published: true 
# published false overwrites the date, true doesn't
---
[Link to the page](/about)

## You can write blog stuff here
Check the minimal mistakes website for more layout options.

### Links
* [Wiki](https://wikipedia.com)
* [Google](https://google.com)
````

[Here](https://mmistakes.github.io/minimal-mistakes/docs/navigation/) you can read how to create a navigation bar. Now you can build your site with:
``` sh
# Build the html files
bundle exec jekyll build
# Start a small webserver on localhost:4000
bundle exec jekyll serve
```
![Example post](/assets/images/jekyllresult.png){: .align-center}

## Travis CI Autobuild
![Jekyll logo](/assets/images/TravisCI-Mascot-1.png){: .align-right}You can host your version control on [GitHub](https://github.com), that allows you to use [Travis CI](https://travis-ci.com) to automatically build and publish it. Thanks to the cron function of Travis CI, it is also possible to have a working planned publish system with the `date: ....` option. Travis CI has two websites travis-ci.com and .org, in the future, .org will be merged with .com, so use [.com](https://travis-ci.com). {% cite travis %}

If you plan to make it your personal site, don't use your `<username>.github.io` repositories master branch, as you have to use this branch for the html files, but you can use a new branch.

I use the jekyll-github-deploy Ruby script, look at the project README for all the options.{%cite jgd%} You could replace it with a build script.

The build is defined in the `.travis.yml` file in the root of the repo: {%cite jekylltravis%}
``` yml
language: ruby
rvm:
- 2.4.1
script: bundle exec jgd -b TARGET_BRANCH -r SRC_BRANCH -u https://${GH_TOKEN}@github.com/USERNAME/REPO.git
branches:
  only:
  - SRC_BRANCH
env:
  global:
  - NOKOGIRI_USE_SYSTEM_LIBRARIES=true
  - secure: ENCRYPTEDGHTOKEN
addons:
  apt:
    packages:
    - libcurl4-openssl-dev
sudo: false
cache: bundler
```

The used placeholders:

| Placeholder|Description |
|--------------|-----------|
| USERNAME | Your GitHub username |
| REPO | Your repo, for your personal website USERNAME.github.io |
| SRC_BRANCH | The branch which holds the Jekyll files and the .travis.yml |
| TARGET_BRANCH | The branch which holds the HTML files, master for USERNAME.github.io|
| ENCRYPTEDGHTOKEN | The encrypted GitHub access token with the name GH_TOKEN. See below |

You can create a GitHub access token [here](https://github.com/settings/tokens). It only needs the `public_repo` permission.
![Github Token Creation](/assets/images/ghtoken.png){: .align-center}
Never publish credentials in public visible repositories, you need to [encrypt those for Travis CI](https://docs.travis-ci.com/user/encryption-keys/). 

After pushing the file to the repo, you should be able to enable in TravisCI on the website and enable Github Pages on the TARGET_BRANCH.  In the settings for builds it is possible to define a cron job for periodic builds:
![Travis CI Cron Jobs](/assets/images/traviscron.png){: .align-center}
## End
In later posts I will cover citations and the archive pages (tags, categories). If something isn't working, feel free to contact me.


Something nice for you to check out:

[The AdventureZone Podcast](https://www.maximumfun.org/shows/adventure-zone). The McElroy family (My Brother, My Brother and Me) playing RPG tabletop games.

## References
{% bibliography --cited %}
