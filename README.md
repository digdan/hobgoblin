Hobgoblin
=========

Hobgoblin is a PHP framework.
The foundation of the framework is a facade/hooking/scope engine to incorporate singleton classes and dynamic controllers.

Highlights
-----------
* Able to facade any singleton, or function
* Dynamic autoloading/routing/hooking modular controllers
* Closure ready Routing engine
* Hooking engine

Classes
-----------
* HG - [HogGoblin Hook/Facade Base]
* Rb - [RedBeans ORM]
* Session - Custom Session Management
* Cache - Custom Caching engine
* AltoRouter - Routing Engine
* Uses PHP scoping as templating engine


Flavors included
-----------
* Bootstrap
* ReadBeans ORM
* FontAwesome
* jQuery.WYSIWYG

Example of Use in controller : 

> HG::map( 'GET /my-account/[i:id]', function($params) {
>   HG::active('my-account')->
>   v('account',R::load('accounts',$params['id']))->
>   display('my-account.php');
> }
