# Magento Internationalization

For module developers who want to keep track of locale strings.
MageI18n works by statically scanning for uses of `->__("...")` method in PHP or
`translate="..."` in XML files and adding to/removing from CSV files as necessary.

### Recommended Installation

Make sure `$COMPOSER_HOME/vendor/bin` is in the include path.
Usually this means adding `PATH=$PATH:~/.composer/vendor/bin` to your `.bashrc`
file and logging out then in.
Next just enter:

    composer global require clockworkgeek/magei18n

### First use on an existing project

Begin by testing in your module's project directory.
The following command should list all translatable files in CSV format.

    magei18n scan

If your module doesn't yet have a locale file then it can be generated like this:

    mkdir -p app/locale/en_US
    magei18n scan > app/locale/en_US/Example_Module.csv

If a locale file does exist and needs to be preserved then it is better to use:

    magei18n update

The `update` command will remove any strings it cannot identify and add any
which are missing.  Those strings which are identified are not changed which
will leave existing translations intact.

### Git workflow

MageI18n is intended to work in the background as a pre-commit hook.
Add the command `magei18n git-diff` to any existing hook script.
Alternatively, if one does not exist, enter:

    cat > .git/hooks/pre-commit
    #!/bin/sh
    magei18n git-diff
    
    <Ctrl+D>
    chmod +x .git/hooks/pre-commit

This command will only add/remove strings which have changed with the latest
commit.  This is helpful if you are intentionally leaving some entries out of
your module's locale file.

### Concerning multiple locales

MageI18n will add newly found strings to **all** CSV files it can find.  This
is deliberate so that translators can see which entries still need to be fixed.
