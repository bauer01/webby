# Webby

## ROADMAP 1.0

---
### Refactoring
- media support
- composer support for plugins
- implement model access
- moltin - use own pages
---
### Features
- HTTPs
- 404
- layout themes (Christmas, Summer events etc.)
- caching
- translations
- core plugins (menu etc.)
- better error handling for end users
- CLI commands via REST API (?)
- E-shop - based on Unimapper adapters - interface for supported eshop functions - disabled/enabled by target platform - Moltin, Flexibee, Snipcart
- inherit structures & layouts
- drafts - plugin in standalone dir?

```yaml
sections:
  0:
    structure: name
    element:
      background:
      class: uk-navbar-container
      attributes:
        uk-navbar: "dropbar: true"
    wrap:
    rows:
      0:
        element:
          class: uk-navbar-container
          attributes:
            uk-navbar: "dropbar: true"
        columns:
          0:
            element:
              class: uk-navbar-left
            particles:
              0:
                particle: navbar-mobile-menu
                element:
                  class: uk-hidden@l
                options:
                  menu: navbar
              1:
                particle: navbar-nav
                element:
                  class: uk-visible@l
                options:
                  menu: navbar
                  count: 3
                  offset:
```