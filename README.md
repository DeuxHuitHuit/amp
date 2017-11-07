# AMP

> Automagically transform your html ouput into valid amp-html

### SPECS ###

Transforms html into amp-html via a simple wrapper around [Lullabot/amp-library](https://github.com/Lullabot/amp-library)

### REQUIREMENTS ###

- Symphony CMS version 2.7.x and up (as of the day of the last release of this extension)

### INSTALLATION ###

- `git clone` / download and unpack the tarball file
- Put into the extension directory
- Enable/install just like any other extension

You can also install it using the [extension downloader](http://symphonyextensions.com/extensions/extension_downloader/).

For more information, see <http://getsymphony.com/learn/tasks/view/install-an-extension/>

### HOW TO USE ###

- Enable the extension
- Visit your url and append /amp/ at the end
- Add a `<link>` to make your content discoverable

```xslt
<link rel="amphtml" href="{$current-url}/amp/" />
```

#### Options ###

- Disable amp generation on certain pages by adding the `no-amp` page type.
- Use [cachelite](http://symphonyextensions.com/extensions/cachelite/) to cache the result.
- The extension add a `<amp>` node in `/data/params` which contains 'Yes' when the conversion will occur. 'No' otherwise.
- Create alternate xslt templates by creating `_amp.xsl` files.
- Replace content before or after the amp conversion.
This can be done using regular expressions in the `config.php` file.

```php
###### AMP ######
'amp' => array(
    'pre-regexp' => array(
        '/regular expression before conversion/' => 'replacement',
    ),
    'post-regexp' => array(
        '/regular expression after conversion/' => 'replacement',
    ),
),
########
```

### DEBUG ###

The extension provides its own devkit to be able to debug the html to amp conversion.
Simply add `?debug-amp` to your url to enable the devkit.

### LICENSE ###

MIT <http://deuxhuithuit.mit-license.org>

*Voila !*

Come say hi! -> <https://deuxhuithuit.com/>
