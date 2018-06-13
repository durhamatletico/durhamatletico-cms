# Atletico theme

## Compiling CSS

Complete these steps to compile SCSS to CSS using gulp:

1. In the theme directory, run `npm install` to install node dependencies.
2. In the theme directory, run `gulp` to compile SCSS to CSS. The compiled CSS
will be placed in the `stylesheets` directory.
3. To watch SCSS files for changes during development, in the theme directory,
run `gulp watch`. This will re-compile CSS whenever a SCSS file changes.

Commit the compiled CSS file.

## Coding standards

Use codeclimate to run scss-lint and eslint.

Configuration for scss-lint and ESLint is located in `.scss-lint.yml` and
`.eslintrc.js`, respectively, in the root of the `omega` repository.

### Ignoring scss-lint rules

There are occasions where it's necessary to break scss-lint rules (e.g. using
an ID selector or `!important` to override a Drupal style). When this happens,
you can tell scss-lint to ignore a line of code like so:

```scss
  // scss-lint:disable ImportantRule
  // Override specific Drupal style.
  margin-bottom: 0 !important;
  // scss-lint:enable ImportantRule
```

Please use this judiciously and always include a comment to explain why the
coding standard violation is necessary.
