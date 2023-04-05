# Auto Node Translate

This module provides the ability to add automatic translations to nodes
using external libraries.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/auto_node_translate).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/auto_node_translate).


## Table of contents

- Requirements
- Installation
- Features
- Configuration
- Maintainers


## Requirements

This module requires the the google api client if or don't use composer to 
install the module install it with composer.

- `composer require google/cloud-translate:^1.10`

If using Amazon Translate, install the aws-sdk-php module with composer.

- `composer require aws/aws-sdk-php`


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Features

At the moment the module provides 5 different Translation APIs:
- Amazon Translate (^2.1)
  `https://aws.amazon.com/translate`
- MyMemory 
  `https://mymemory.translated.net/`
- IBM Watson translator
  `https://www.ibm.com/cloud/watson-language-translator`
- Google cloud translator v2
  `https://cloud.google.com/dotnet/docs/reference/Google.Cloud.Translation.V2/latest`
- Google cloud translator v3
  `https://cloud.google.com/translate/docs/reference/rpc/google.cloud.translation.v3`


## Configuration

At the moment the module provides 4 different Translation APIs

- MyMemory 
  No configuration needed 
    
- IBM Watson translator
  - Create an account in `https://cloud.ibm.com/registration.` 
  - Login to your account in `https://cloud.ibm.com/login.`
  - Open the "IBM Cloud" menu and select Watson
  - In Watson menu select `"Browse Services"` and select `"Language Translator"`
  - Create the service
  - Choose the `"Manage"` option from the menu.
  - go to `/admin/config/auto_node_translate/config` and insert your apikey
    and url on the config form. the latest available version can be checked 
    in `https://cloud.ibm.com/apidocs/language-translator#versioning.`

- Google cloud translator v2 and v3
  - Create an account on `https://cloud.google.com`
  - Follow the first step on 
    `https://cloud.google.com/translate/docs/quickstart` to create your 
    project. 
  - On your project Dashboard click on `"Explore and enable APIs"` on the tab 
    `"Credentials"` create an API KEY.
  - go to `/admin/config/auto_node_translate/config` and insert your apikey
    and project id on the config form.
     
- Amazon Translate
  - Create an account on `https://aws.amazon.com`
  - Create an IAM account for the translator, grant TranslateFullAccess
    permission
  - Create an Access Key for the new IAM account
  - go to `/admin/config/auto_node_translate/config` and insert your apikey,
    secret, and AWS region on the config form.


## Maintainers

- Paulo Calado - [kallado](https://www.drupal.org/u/kallado)
- João Mauricio - [jmauricio](https://www.drupal.org/u/jmauricio)

This project has been sponsored by:
- Visit: [Javali](https://www.javali.pt) for more information
