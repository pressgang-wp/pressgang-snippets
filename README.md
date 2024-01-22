# PressGang Snippets

## Overview

`PressGang Snippets` is a curated collection of reusable code snippets designed for WordPress themes, specifically designed for integration with the PressGang parent theme framework. These snippets provide a streamlined way to enhance your WordPress theme development with PressGang, offering a range of functionalities that are commonly needed in WordPress themes.

## Installation

To incorporate `PressGang Snippets` into your PressGang child theme, use Composer:

```bash
composer require pressgang-wp/pressgang-snippets
```

## Configuring Snippets in Your Theme

To utilize the PressGang Snippets in your WordPress theme, you need to configure them in your theme's `config/snippets.php` file. This file acts as a central place to manage which snippets are active in your theme and to pass any necessary arguments to them.

### Step-by-Step Instructions

1. **Locate or Create `snippets.php`**: 
   - Find the `snippets.php` file in your theme’s root directory. 
   - If it doesn't exist, create a new PHP file named `snippets.php` in the root of your theme.

2. **Add Snippets to the Config File**: 
   - Open `snippets.php` in your code editor.
   - To activate a snippet, add it to the return array in `snippets.php`. Use the snippet's class name as the key, and an associative array of arguments as the value.

3. **Example Configuration**:
   - Here's an example of what your `snippets.php` might look like after adding a couple of snippets:

```php
<?php
// snippets.php
return [
    'PressGang\\Snippets\\SomeSnippet' => ['arg1' => 'value1'],
    'PressGang\\Snippets\\AnotherSnippet' => ['arg2' => 'value2'],
    // Add more snippets here...
];
