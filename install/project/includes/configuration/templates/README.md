# About the `templates` directory

This directory contains files that point to the templates in vendor directory in the
various repositories. Its a mechanism that allows repositories to inject their own
templates into the code generation engine at install time. Feel free to change
or remove.

Templates will be loaded in alphabetic order, with later templates overriding
earlier templates. Templates starting with numbers are reserved for the QCubed system and will
be loaded first.

Templates in the project\codegen\templates library will be loaded last and
will override all previous templates.