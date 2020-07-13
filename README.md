# PartsBin

I recently saw MyPartsBin on https://www.mypartsbin.com/. There were some changes I wanted to make. Authentication, notes, project tracking, etc.  I forked the open source project and put it on GitHub to learn how to collaborate.

* [System requirements](#system-requirements)
* [Install Parts Bin](#install-partsbin)
* [Contributing](#contributing)
* [Thank you](#thank-you)

## System requirements

- PHP 7.0.0+
- MySQL 5.0.3+ (version 5.5+ recommended).
- Apache 1.3+ (version 2.4+ with mod_rewrite recommended) or Nginx (version 1.13+).
- PHP extensions (included by default in many PHP installations):
  - mysqli
  - XML with SimpleXML
  - JSON
  - Optional (recommended) PHP extensions:
    - mbstring
    - zip
    - zlib
- A valid PHP date.timezone setting.
- A Unix/Linux server OS with locale support is recommended.
- You first need to setup the database, use phpMyadmin to create a database and the database user, and allocate the user to the database, enter this information in the config.php file.

## Install PartsBin

I have an SQL file included which will create the required tables for you, just go into phpmyadmin and import the file.

From there you should be good to go!

If you need to change the site structure, for example putting it in a subdirectory, you may need to make some changes to the config file so it nows where it is, you may also need to change the "$path" setting at the top of some of the pages.

## Contributing

Do you want to help with the development of PartsBin? Submit a pull request.

## Thank You

I am grateful to DefPom, the original creator of the software.  As well as anyone who shares suggestions, issues, or pull requests.

**Termlimit