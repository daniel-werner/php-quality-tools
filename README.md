# Php Quality Tools
Basic set up and ruleset for php code quality tools

##Install PhpCS phpmd  phpstan and php-cs-fixer

```sh
composer require --dev squizlabs/php_codesniffer phpstan/phpstan phpmd/phpmd friendsofphp/php-cs-fixer
```

## Composer scripts
```json
{
    "scripts": {
        "inspect": [
            "vendor/bin/phpcs",
            "vendor/bin/phpstan analyze src"
        ],
        "inspect-fix": "vendor/bin/php-cs-fixer fix",
        "insights" : "vendor/bin/phpmd src text phpmd.xml",
    }
}
```