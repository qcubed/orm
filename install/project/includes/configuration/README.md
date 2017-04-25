# About `configuration` directory

This is the central location for all included configuration files.  Feel free to include
any new classes or include files in this directory.

## Files in the configuration directory:

### configuration.inc.php

This contains server-level configuration information (e.g. database connection
information, docroot paths (including subfoldering and virtual directories),
etc.  A sample file is provided for you, and is used by the QCubed startup wizard
to create an initial version. Feel free to make modifications to this file to have it reflect the
configuration of your application, including any global defines that are particular to your application.

See the inline documentation in configuration.sample.inc.php for more information.


### codegen_settings.xml

This file controls overall settings for parts of the code generation. Feel free
to change these as needed.


## Codegen Notes

QCubed is set up to generate a default set of objects and forms to get you started with your application.
This is called “codegen”. The notes below will help you understand the process and how to customize it to your needs.
Ideally, you should customize the codegen process first before starting to write you application code,
but we know that development does not go always as planned, and the whole QCubed system is set up so that you can
separate out your hand written code from the generated code, and continue to tweak the codegen process and re-generate code at any time.

The codegen process starts at the QCubed start screen by clicking on the codegen link.
PHP is executed to generate the files. Therefore, the target directories for codegen will need to be writable by the web server process.

The codegen process works by instantiating a CodeGen object. This object then looks in the template directories and begins
to include the php files there that start with an underscore (_). These templates then include other files, which in turn
may include other template files. This combination will eventually generate the forms, model connectors, and data table
interface classes that you will base your application on.


