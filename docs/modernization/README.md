# Modernization Project

### flat is being modernized

Development on the flat framework largely halted in 2020. There are various issues including bugs that need to be addressed including updating the PHP version used. This is a bit of an undertaking.

This modernization is taking place in multiple deliberate steps to avoid consumer disruption.
The branch **modernize-26** contains the latest release phase of the modernized flat.

The phases are going to correspond with the major versions of PHP starting at PHP 7.2 up to present released version.
Each phase will have it's own branch, i.e. **php-7.2** for php-7.2 (the last PHP version flat was using when development stalled in 2020). Once the phases are caught up to the latest stable release of PHP, **modernize-26** branch will be merged with the **master** branch and the modernization project will be considered complete.

### Progress
 * php-7.2: [CHANGELOG.md](./php-7.2/CHANGELOG.md)
