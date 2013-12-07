MyLightMvc
==========

This is an application for which I developped a **light MVC framework**, inspired by *Zend Framework*

Specs
-----

* It uses a **front controller** for dispatching the *HTTP request*
* all the controllers inherits from an **Action Controller**
* Views are simply partials with embeded PHP, the whole is wrapped in a **layout**
* It's possible to create **view helpers**
* The view can **combine scripts** on demand for limiting the number of HTTP requests
* All the **application variables** are stored in a *singleton* class and defined in a *bootstrap* file
* I used the pattern **VO/DAO** to separate my *models* from the database layer

