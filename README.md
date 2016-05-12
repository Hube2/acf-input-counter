# ACF Input Counter

Supports ACF 5

Adds a counter to all text and textarea fields with character limits

This is a simple plugin that will add a counter below all ACF text and text area fields to show how many
characters have been added and what the limit is. The display will look something like this:

```
chars: 25 of 55
```

This plugin will not work in ACF 4 for 2 reasons:
* The acf/render_field hook is not supported by ACF 4
* The JavaScript used in this plugin is specific to ACF 5

This plugin also serves as a simple example of how to extend the functionality of fields in ACF 5.
