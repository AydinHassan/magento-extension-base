# Base Magento Extension

Pre-configured repository for building a Magento extension. Provides `Composer` configuration and common
`Composer` dependencies. 

## Setup

First, create a repository for your extension, then run the following commands:

    $ git clone https://github.com/AydinHassan/magento-extension-base your-ext-name
    $ cd your-ext-name
    $ php build.php
    
Follow the instructions and that's it!, an initial commit will be made for your and your remote added. You can then
run `git push` whenever you are ready.

## Unit Tests

We are using [Aspect Mock](https://github.com/Codeception/AspectMock) which uses [Go! AOP](https://github.com/lisachenko/go-aop-php) 
to be able to create test doubles for static method calls. This is required due to Magento relying heavily on static 
calls to the `Mage` class. 

Unit tests should be a core part of any project so it's recommended to read up on [Aspect Mock](https://github.com/Codeception/AspectMock) 
and keep your project up to date as it is still in Alpha!