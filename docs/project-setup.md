
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

Setup the git prject:

```bash
git init
git checkout -b develop
git add -A
git commit -m "Initial commit"
git remote add origin REMOTE_GIT_REPO
git push origin develop
```

## Panthon setup

Create a Drupal 8 in [Pantheon](https://pantheon.io/), you should initialize **test** and **live** environments, also you need to create a backup from live in order to use CircleCI.

Go to the [CircleCI](https://circleci.com/) and start building the site.

Setup the CircleCI env environments listed at the top of the `./circleci/config.yml` file.

Add to the CircleCI the **ssh-keys**
