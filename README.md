mf4php-beanstalk
================

This is a Beanstalk binding for [mf4php](https://github.com/szjani/mf4php)

Attention
---------

You have to create your own long running script to reserve messages from beanstalk and forward jobs to the dispatcher.

The reason why it is not implemented in the dispatcher is it should watch several queues, which is impossible in one process.
Another reason is that it is often necessary to create a lightweight long running script without any open resources (database, etc.),
and it executes a CLI program to pass the message to the dispatcher.

Configuration
-------------

```php
<?php
$dispatcher = new BeanstalkMessageDispatcher($pheanstalk);
$queue = new DefaultQueue('queue');

/* @var $listener MessageListener */
$dispatcher->addEventListener($queue, $listener);
```

Send events
-----------
```php
<?php
/* @var $object Serializable */
$message = new BeanstalkMessage($object);
$dispatcher->send($queue, $message);
// onMessage method in $listener is called
```
