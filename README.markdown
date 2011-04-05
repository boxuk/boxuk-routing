
# Box UK Routing

This library provides an easy way to add highly configurable routing support to your Front Controller based application.  It comes with an input router to pass control to your application classes, and an output filter that transforms HTML to use these routes automatically.  All of this is then configured through one easy to read routing file.

## Requirements

* PHP 5.3

## The Routing File

The routing file is designed to be as easy to read as possible, and simply maps URL's on the left side, so the controller to handle them on the right.  Here's a simple example...

<pre>
/user/:num = user:show( id )
</pre>

The format for route definitions is as follows:

<pre>
METHOD URL = CONTROLLER:ACTION( PARAMS )
</pre>

The method allows you to limit certain routes to certain HTTP request methods.  If the action is not specified then it defaults to _'index'_.

### URL's

The left hand side specifies the URL for the route.  You specify your dynamic parameters with a colon (eg. :num), by default these are...

* :num
* :word
* :file
* :any

#### Additional Types

You can specify additional types using regular expressions.

<pre>
:num = ID\d\d\w+
</pre>

### Controller Blocks

As a shorthand (and a neat way of keeping related specs together) you can use controller blocks to eliminate the need to specify the controller in your routes.

<pre>
[foo]
/this/:word = show( name )
/some/:num = ( id )
[*]
</pre>

The above example shows a two routes defined for a controller called 'foo'.  As you can see it's no longer required to specify *foo* for each route. The controller block is then ended with a star (indicating any controller can now be used in routes)

### Base Paths

You can also specify a base path for a controller block which means all routes have that as their prefix.  This is useful when all of a controllers routes have the same prefix, for example:

<pre>
[admin:/private/admin]
/:word = admin( action )
/ = admin()
[*]
</pre>

This would then send URLs like /private/admin/home to the _admin_ controller, with the action _home_.

## The Helper

You are free to instantiate and set upp all the objects manually if you want, but the easiest way to do it is through the helper class provided.  This is used in all the examples.

## The Router

The router forms the input part of the system.  You initialise the router with your routes, then pass it the requested URL along with a request object to populate with the data extracted from the matched route.

<pre>
$req = new BoxUK\Routing\Input\StandardRequest();
$url = ‘/some/url’;

$helper = new BoxUK\Routing\Helper();
$helper->setRoutesFile( ‘/path/to/routes.txt’ );

$router = $helper->getRouter();
$router->process( $req, $url )
</pre>

You can see we need to use a parser class to extract routing information from our routes file.  When the process method has completed the request object will contain the information from the route (eg. controller = 'foo')

The example above uses a default request object provided with this library, but you will most likely want to provide your own if you already have a request object abstraction in your application (just implement BoxUK\Routing\Input\Request)

## The Filter

The output filter allows us to transform our output using the routing information provided in our routes file.  This is done dynamically so the URLs in your output should be the raw versions pointing to your front controller script.

<pre>
$html = ‘&lt;a href=”server.php?controller=cars&action=show&brand=ford”&gt;Show Ford&lt;/a&gt;’;

$filter = $helper->getFilter();
$filter->process( $html );
</pre>

Given the route _/cars/:word = cars:show( brand )_, the *$html* variable will be transformed to...

<pre>
&lt;a href="/cars/ford"&gt;Show Ford&lt;/a&gt;
</pre>

This provides a flexible, unobtrusive way to handle generating your clean URLs.

### Forms

Forms actions can also be transformed by using hidden input fields to specify your routing parameters.

<pre>
&lt;form action="server.php"&gt;
    &lt;input type="hidden" name="controller" value="cars" /&gt;
    &lt;input type="hidden" name="action" value="show" /&gt;
    etc...
&lt;/form&gt;
</pre>

## The Rewriter

The rewriting object needs to be created and provided for the filter demonstrated above, but you can use this alone if you would just like to rewrite URLs (when redirecting the user for example).

## Unit Tests

The library is fully unit tested.  To run these tests just use phpunit...

<pre>
phpunit tests/php
</pre>
