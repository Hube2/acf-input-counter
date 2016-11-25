# ACF Input Counter

Supports ACF 5

Adds a counter to all text and textarea fields with character limits.

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
Fields that have one of the classes or ids will include a counter.

**Filter by ID**
```
add_filter('acf-input-counter/ids', 'my_input_counter_filter');
function my_input_counter_filter($ids=array()) {
  // add 1 or more classes to the array
	$ids[] = 'this-is-an-ID';
	return $ids;
}
```

### Filter the Display
To filter the display add a filter wherever you would add a filter.
```
add_filter('acf-input-counter/display', 'my_acf_counter_filter');
function my_acf_counter_filter($display) {
    $display = sprintf(
        __('Characters = %1$s of %2$s', 'acf-counter'),
        '%%len%%',
        '%%max%%'
    );
	return $display;
}
```
In the example string above `%%len%%` represents where you want the current character count shown and `%%max%%`
represents where you want the fields maximum length displayed. You can generate this string any way you want.
For example, you can translate it or use `sprintf()`, as long as it contains the two markers where the values
should be shown. If you do not include these markers then they simply will not be shown. It's up to you to
make sure they are present.

I've puzzled with how to allow this to be altered and this is the best I've been able to come up with.
If you have a better idea, let me know in the issues.

#### Automatic Updates
Install [GitHub Updater](https://github.com/afragen/github-updater) on your site if you want to recieve automatic
updates for this plugin.

#### Remove Nag
You may notice that I've started adding a little nag to my plugins. It's just a box on some pages that lists my
plugins that you're using with a request do consider making a donation for using them. If you want to disable them
add the following filter to your functions.php file.
```
add_filter('remove_hube2_nag', '__return_true');
```

### i18n
The plugin is now also internationalized and it has a .pot file. Also included is a Dutch translation by [Beee][1].

[1]: https://github.com/Beee4life/
