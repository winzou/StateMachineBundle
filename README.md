A very lightweight yet powerful PHP state machine bundle
========================================================

Define your states, define your transitions and your callbacks: we do the rest.
The era of hard-coded states is over!

[![Build Status](https://travis-ci.org/winzou/StateMachineBundle.svg?branch=master)](https://travis-ci.org/winzou/StateMachineBundle)

Installation
---------------

### Installation (via composer)
```sh
composer require winzou/state-machine-bundle
```

### Register the bundle
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


### Configure a state machine graph

In order to use the state machine of this bundle, you first need to define a graph. A graph is a definition of states, transitions and optionally callbacks ; all attached on an object from your domain. Multiple graphs can be attached to the same object.

Let's define a graph called *simple* for our `Article` object:

```yaml
# app/config/config.yml

winzou_state_machine:
    my_bundle_article:
        class: My\Bundle\Entity\Article # class of your domain object
        property_path: state            # property of your object holding the actual state (default is "state")
        graph: simple                   # name of the graph (default is "default")
        # list of all possible states:
        states:
            - new
            - pending_review
            - awaiting_changes
            - accepted
            - published
            - rejected
        # list of all possible transitions:
        transitions:
            create:
                from: [new]
                to: pending_review
            ask_for_changes:
                from: [pending_review, accepted]
                to: awaiting_changes
            submit_changes:
                from: [awaiting_changes]
                to: pending_review
            approve:
                from: [pending_review, rejected]
                to: accepted
            publish:
                from: [accepted]
                to: published
        # list of all callbacks
        callbacks:
            # will be called when testing a transition
            guard:
                guard_on_submitting:
                    on:   'submit_changes'                        # call the callback on a specific transition
                    do:   ['@my.awesome.service', 'isSubmittable']  # will call the method of this Symfony service
                    args: ['object']                              # arguments for the callback
            # will be called before applying a transition
            before:
                update_reviewer:
                    on:   'create'
                    do:   ['@my.awesome.service', 'update']
                    args: ['object']
            # will be called after applying a transition
            after:
                email_on_publish:
                    on:   'publish'
                    do:   ['@my.awesome.service', 'sendEmail']
                    args: ['object', '"Email title"']
```

So, in the previous example, the object `Article` has 6 possible states, and those can be achieved by applying some transitions to the entity. For example, when creating a new `Article`, you would apply the 'create' transition to the entity, and after that the state of it would become *pending_review*. 

Let's imagine now that, after an exhaustive review, someone decides the `Article` was not good enough, so it would like to ask you for some changes. Therefore, they would apply the *ask_for_changes* transition, and now the state would be *awaiting_changes*.


### Using the state machine

#### Definitions

The state machine is the object actually manipulating your object. By using the state machine you can test if a transition can be applied, actually apply a transition, retrieve the current state, etc. *A state machine is specific to a couple object + graph.* It means that if you want to manipulate another object, or the same object with another graph, *you need another state machine*.

The factory helps you to get the state machine for these couples object + graph. You give an object and a graph name to it, and it will return you the state machine for this couple. The factory is a service named `SM\Factory\Factory`.

#### Usage


``` php

public function myAwesomeAction($id, \SM\Factory\Factory $factory)
{
    // Get your domain object
    $article = $this->getRepository('MyAwesomeBundle:Article')->find($id);
    
    // Get the state machine for this object, and graph called "simple"
    $articleSM = $factory->get($article, 'simple');
}
```

Now, the `$articleSM` has a bunch of methods that will allow you to check if the desired transitions are possible, given the state of the object we have passed to it (`$article` in our case). For example, we can:

``` php
// Check if a transition can be applied: returns true or false
$articleSM->can('a_transition_name');

// Apply a transition
$articleSM->apply('a_transition_name');

// Get the actual state of the object
$articleSM->getState();

// Get all available transitions
$articleSM->getPossibleTransitions();
```

### Callbacks

Callbacks are used to guard transitions or execute some code before or after applying transitions. This bundle adds the ability to use Symfony2 services in the callbacks.
