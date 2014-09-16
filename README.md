WebCMS2
=======

[![Total Downloads](https://poser.pugx.org/webcms2/webcms2/downloads.png)](https://packagist.org/packages/webcms2/webcms2)
[![Latest Stable Version](https://poser.pugx.org/webcms2/webcms2/v/stable.png)](https://github.com/ufik/WebCMS2/releases)
[![Latest Unstable Version](https://poser.pugx.org/webcms2/webcms2/v/unstable.png)](https://packagist.org/packages/webcms2/webcms2)
[![License](https://poser.pugx.org/webcms2/webcms2/license.png)](https://packagist.org/packages/webcms2/webcms2)
[![Travis](https://travis-ci.org/ufik/WebCMS2.png?branch=0.6)](https://travis-ci.org/ufik/WebCMS2.png?branch=0.6)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ufik/WebCMS2/badges/quality-score.png?b=0.6)](https://scrutinizer-ci.com/g/ufik/WebCMS2/?branch=0.6)
[![Code Coverage](https://scrutinizer-ci.com/g/ufik/WebCMS2/badges/coverage.png?b=0.6)](https://scrutinizer-ci.com/g/ufik/WebCMS2/?branch=0.6)


Content management system based on Nette Framework with Doctrine2 ORM library.

Contains libraries
------------------

Look into composer.json for all used libraries.

INSTALLATION
------------

Download [https://github.com/nette/sandbox](Nette sandbox), default vendor directory is 'libs'.

Add this line into your composer.json file.

```
"webcms2\webcms2" : "0.*"
```

This command will download all required packages, create DB schema, make all necessary directories and change mode for required files.

```
composer install
```

CONFIG.neon example
--

There is `boxes` section, here defined boxes are shown in page settings.

Another important setting is `cacheNamespace`, which defines project specific namespace. If you use memcached cache storage, there can be a problem if two projects have same namespace.

```
common:
	parameters:
		database:
			driver: pdo_mysql
			host: localhost
			dbname: nameOfDatabase
			user: root
			password:
			charset: utf8
			collation: utf8_czech_ci
		boxes:
			box1: true
			box2: true
			box3: true

		cacheNamespace: 'projectNamespace'

production < common:
	parameters:
		database:
			password: 

development < common:
```


