# Themeisle SDK

ThemeIsle SDK used to register common features for products in the portfolio. 

Can be installed using composer: 
    `composer require codeinwp/themeisle-sdk`
and manually autoloading the load.php file  in the composer.json file of your project:

```

  "autoload": {
    "files": [
      "vendor/codeinwp/themeisle-sdk/load.php"
    ]
  }
  
```
  
  
### Features
  
  * Loads the most recent version of the library across all the products on the same wordpress instance. For instance if there is a theme which bundles v2.0.0 of the SDK and one plugin which bundles the v1.9.1, it will load on the most recent one, v2.0.0 for both products. 
  * If there are two products using the same version, it will load the first one that register the SDK, unless it's explicitly overwritten. 
  * Each functionality is bundled into modules, which are loaded based on the product type. Free/Pro, is available on wordpress or not. 

### How to register product

  * The library works out of the box by simply loading the autoloader into the plugin/theme files. 
  * Some modules are loaded only if the product is not available on WordPress.org ( licenser/review ). You can define if the product is available on wordpress.org by adding this file header `WordPress Available:  <yes|no>` where `<yes|no>` will be replaced with the proper status. 
  * If the product requires is a premium one and requires a licesing mechanism, we can use  `Requires License: <yes|no>` to specifically tell that the product requires license.
  
  
