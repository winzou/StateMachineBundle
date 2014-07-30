A very lightweight yet powerful PHP state machine bundle
========================================================

Define your states, define your transitions and your callbacks: we do the rest.
The era of hard-coded states is over!

[![Build Status](https://travis-ci.org/winzou/StateMachineBundle.svg?branch=master)](https://travis-ci.org/winzou/StateMachineBundle)

Installation
---------------

### Installation (via composer)
```js
{
    "require": {
        "winzou/state-machine-bundle": "~0.1"
    }
}
```

Register the bundle in `app/AppKernel.php`:

```php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new winzou\Bundle\StateMachineBundle\winzouStateMachineBundle(),
    );
}
```

Usage
-----

### Configure the bundle

In order to use this bundle, you first need to set the states for each entity you want to manage with the state machin, and the possible transitions between them. This is done in the `app/config/config.yml` file.

So, let's imagine you have an *Article* entity, and you would like to use the state machine with it. Configure the bundle like the following:

```yaml
# app/config/config.yml
winzou_state_machine:
    my_bundle_article:
        class: My\AwesomeBundle\Entity\Article # the class you want to use with the state machine
        property_path: state # the property from the entity that holds the state
        graph: article # the name of the transition's graph
        states: # you will define all possible states here
            new: ~
            pending_review: ~
            awaiting_changes: ~
            accepted: ~
            published: ~
            rejected: ~
        transitions:
            create:
                from: [new]
                to: pending_review
            ask_for_changes:
                from: [pending_rewiew, accepted]
                to: awaiting_changes
            submit_changes:
                from: [awaiting_changes, approve, pending_review]
                to: pending_review
            approve:
                from: [pending_review, rejected]
                to: accepted
            publish:
                from: [accepted]
                to: published
```

So, in the previous example, the entity *Article* has 6 possible states, and those can be achived by applying some transitions to the entity. For example, when creating a new Article, you would apply the 'create' transition to the entity, and after that the state of it would become *pending_review*. 

Let's imagine now that, after an exhaustive review, someone decides the Article was not good enough, so it would like to ask you for some changes. Therefore, they would apply the *ask_for_changes* transition, and now the state would be *awaiting_changes*.


### Using the state machine from a controller

In order to apply the transitions from a Controller, you need to request for the `sm.factory` service. Once you have the SM factory, you need to get the transitions that are available for a given entity, under a give graph name. In our example, we have defined a grap called `article`, that will be applied to the *Article* entity. So, from a controller, we would do:

``` php

    public function myAwesomeAction(Request $request)
    {
        $article = $this->getRepository('MyAwesomeBundle:Article')->find($request->get('id'));
        $smFactory = $this->get('sm.factory');
        $articleSM = $smFactory->get($article, 'article');
    }
```

Now, the `$articleSM` has a bunch of methods that will allow us to check if the desired transitions are possible, given the state of the object we have passed to the factory (`$article` in our case). For example, we can:

- Check the available transitions: `$articleSM->getPossibleTransitions();`
- Get the actual state of the object: `$articleSM->getState();`
- Check if a transition can be made: `$articleSM->can('transition_name');`
- Apply a transition: `$articleSM->apply('transition_name');`


### Using the state machine from a service

You can also embbed the state machine as a dependecy in your services. Let's say you have an `ArticleListener` that listen on some Article events, and you would like to apply some transitions after the Article is updated (for example, you would like the state to be *pending_review*), you would define the listener in the following way:

``` yaml
services:
     my_awesome_bundle.listener.article:
        class: My\AwesomeBundle\EventListener\ArticleListener
        tags:
          - { name: kernel.event_listener, event: my_awesome_article_event.on_update, method: onUpdate }
        arguments: [@sm.factory]
```

And after that, you would use the state machine in your listener:

``` php
<?php
namespace My\AwesomeBundle\EventListener;

use SM\Factory\Factory;
use Symfony\Component\EventDispatcher\GenericEvent;

class ArticleListener
{
    private $transitionsFactory;

    public function __construct(Factory $stateFactory)
    {
        $this->transitionsFactory = $stateFactory;
    }
    
    public function onUpdate(GenericEvent $event)
    {
        $article = $event->getSubject();
        $transitions = $this->transitionsFactory->get($article, 'article'); # notice that 'article' is the graph name
        // we chack that we can apply the transition
        if ($transitions->can('submit_changes')) {
            // if transition is possible, we apply it
            $transitions->apply('submit_changes');
        }
        // we need to flush the changes, you can do it here or back in your controller
    }
}
