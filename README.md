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

### Add Counter Filter
By defaults, the counter is added to all text and textarea fields that have a max length. If you would like
to only add the counter on specific fields you can filter them by either the ACF Wrapper Class or the ACF
Wrapper ID of the field. You can allow multiple classes or ids. If classes or ID values are present then only
fields that have one of the classes or ids will include a counter.

**Filter by Class**
```
add_filter('acf-input-counter/classes', 'my_input_counter_filter');
function my_input_counter_filter($classes=array()) {
  // add 1 or more classes to the array
	$classes[] = 'this-is-a-class';
	return $classes;
}
```
fields that have one of the classes or ids will include a counter.

**Filter by ID**
```
add_filter('acf-input-counter/ids', 'my_input_counter_filter');
function my_input_counter_filter($ids=array()) {
  // add 1 or more classes to the array
	$ids[] = 'this-is-an-ID';
	return $ids;
}
```
