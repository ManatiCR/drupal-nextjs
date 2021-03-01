
# Project setup

This guide describes all the steps needed to setup the project. **This steps should be done once**.

## Local setup

Setup `.env` file used by chirripo to run the local environment.

Run `./scripts/dev/local-settings.sh`

Run `composer install --ignore-platform-reqs`

Run `npm install`

In the `scripts/dev/site-install.sh` file, change the `SITE_UUID` variable for a new one ( you can use: `uuidgen` to generate it)

Run `chirripo up`

Run `./scripts/dev/site-install.sh`

Import the [Manatí Base Config](https://packagist.org/packages/manaticr/manati_base_config) by following its steps.

Export the site config:

```bash
chirripo drush cex -- -y
```

Copy the `core.extension` file from `config/dev` to `config/sync` folder, then get ride of `devel`, `stage_file_proxy` and `views_ui` lines.



PANTHEON SITE
1.  Create an empty pantheon site
2.  Upload the required fields (with the vendor folder inside) https://pantheon.io/docs/guides/drupal-8-composer-no-ci
3.  Set the env variables
4.  Set the tokens
