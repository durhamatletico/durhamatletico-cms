[![CircleCI](https://circleci.com/gh/durhamatletico/durhamatletico-cms.svg?style=svg)](https://circleci.com/gh/durhamatletico/durhamatletico-cms) [![Maintainability](https://api.codeclimate.com/v1/badges/5048ed1b6c858832c1a7/maintainability)](https://codeclimate.com/github/durhamatletico/durhamatletico-cms/maintainability)

# Durham Atletico

This is the Drupal 8 codebase for `https://www.durhamatletico.com`.

## Running locally

Use Docker. Set `127.0.0.1 local.durhamatletico.com` in your `/etc/hosts` file.

``` sh
export PANTHEON_TOKEN=your_token
./tests/scripts/build.sh
```

You should be able to view the site at https://local.durhamatletico.com.

## Hosting

The site is hosted on Pantheon.

## Email

Email is handled via Mailgun.

## Tests

Automated testing is run through CircleCI.

## Contributing

Contributions are very welcome! Please post issues or pull requests.
