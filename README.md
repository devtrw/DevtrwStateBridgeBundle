DevtrwStateBridgeBundle
=======================
[![Build Status](http://img.shields.io/travis/devtrw/DevtrwStateBridgeBundle.svg?style=flat)](http://travis-ci.org/devtrw/DevtrwStateBridgeBundle)
[![Code Quality](http://img.shields.io/scrutinizer/g/devtrw/DevtrwStateBridgeBundle.svg?style=flat)](https://scrutinizer-ci.com/g/devtrw/DevtrwStateBridgeBundle/?branch=master)
[![Code Coverage](http://img.shields.io/scrutinizer/coverage/g/devtrw/DevtrwStateBridgeBundle.svg?style=flat)](https://scrutinizer-ci.com/g/devtrw/DevtrwStateBridgeBundle/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/20dac64f-4e8e-4825-830e-08069094b497/mini.png)](https://insight.sensiolabs.com/projects/20dac64f-4e8e-4825-830e-08069094b497)

This bundle provides a basic framework for dynamically creating 
[ui-router](https://github.com/angular-ui/ui-router) states from configuration in a symfony bundle. The main drive 
behind this is to allow for selective activation of parts of an AngularJS frontend based on a users roles within
a symfony context.

Installation
------------

### Install Bundle
__TODO__

### Register the "jsonp" Request Format

[How to register a new Request Format and Mime Type](http://symfony.com/doc/current/cookbook/request/mime_type.html)

Usage
-----

### NOTE: Theses Docs are incomplete. I'll make an effort to fill them out once the API has solidified

**1. Define your states under the `devtrw_state_bridge.states` configuration key.**

See the [configuration]() section below.

**2. Export the `devtrw_state_bridge_get_entity_state` and `devtrw_state_bridge_get_state` routes to your angular 
application.**
 
The [FOSJsRoutingBundle](https://github.com/FriendsOfSymfony/FOSJsRoutingBundle) can handle dumping the routes
 along with the symfony router implemented in javascript. Simply wrap it with an angular module and you'll have 
 easy access to any exported routes from within your angular app.

**3. Create an abstract state in angular that loads in the needed states**

 You can get a general idea of how to do this by skimming over 
 [this blog post](http://alexfeinberg.wordpress.com/2014/04/26/delay-load-anything-angular/) by Alex Feinberg.
 
 
Configuration
-------------
__TODO__

TODO
----

[ ] Wrap/Extend the FOSJsRoutingBundle to provide a more seamless integration with angular
[ ] Finish initial documentation
