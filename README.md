# SwipeStripe - Ecommerce module for SilverStripe
[![Build Status](https://travis-ci.org/littlegiant/silverstripe-swipestripe.svg?branch=5.0)](https://travis-ci.org/littlegiant/silverstripe-swipestripe)
[![Coverage Status](https://coveralls.io/repos/github/littlegiant/silverstripe-swipestripe/badge.svg?branch=5.0)](https://coveralls.io/github/littlegiant/silverstripe-swipestripe?branch=5.0)

## Requirements
* SilverStripe 4.2
* Omnipay gateway(s) for your application's supported payment method(s)

## Version
5.0

## Installation Instructions
1. `composer require [provider of php-http/client-implementation]` - e.g. `php-http/guzzle6-adapter` 
2. `composer require swipestripe/swipestripe:5.0.x-dev`
3. Add composer dependencies for any relevant Omnipay gateways for your application
4. Configure your gateway parameters and allowed gateways as per the [silverstripe-omnipay documentation](https://github.com/silverstripe/silverstripe-omnipay/blob/master/docs/en/Configuration.md)
5. Configure your supported currency (or currencies)
6. Run a dev/build?flush=1

## License
	Copyright (c) 2011 - 2018, [Frank Mullenger](http://nz.linkedin.com/in/frankmullenger)
	All rights reserved.

	Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

	    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
	    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the 
	      documentation and/or other materials provided with the distribution.
	    * Neither the name of SilverStripe nor the names of its contributors may be used to endorse or promote products derived from this software 
	      without specific prior written permission.

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
	LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
	GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, 
	STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY 
	OF SUCH DAMAGE.

## Attribution
Big thanks to:

* [SilverStripe](http://http://www.silverstripe.com/)
