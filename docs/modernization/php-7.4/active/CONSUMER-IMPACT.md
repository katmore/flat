# Consumer impact, php-7.4 phase

Behavior changes of this phase that an application using flat may notice, in the format of
[../../php-7.2/active/CONSUMER-IMPACT.md](../../php-7.2/active/CONSUMER-IMPACT.md): what consumers saw
before, what they see now, and what to search for in their code.

## Behavior changes

### MOD-292 (Q51) — flat requires PHP 7.4 or newer

- Before: the root `composer.json` required `php >=7.3.0`.
- After: it requires `php >=7.4.0`. A PHP 7.3 host keeps installing `v1.73.x`; Composer skips `v1.74.0` for it.
- Search consumer code for: `katmore/flat`, the `php` constraint and `config.platform.php` of your `composer.json`.
- Verified: a resolution under a simulated PHP 7.4.33 host succeeds.

### MOD-292 (Q54) — flat requires `doctrine/annotations`

- Before: flat did not require it. `jms/serializer` 3.29.1 (pulled in by the Authorize.Net SDK) required it, so
  a lock made earlier contains it. `jms/serializer` 3.32.9 does not require it, and on PHP below 8.0 it throws
  as soon as the SDK builds its serializer ("either ... the doctrine/annotations package installed, or use
  PHP 8.0 or later"): a fresh install on PHP 7.4 had a broken Authorize.Net SDK.
- After: `composer.json` requires `doctrine/annotations ^2.0`, so a fresh install works on 7.4. The package is
  marked abandoned on Packagist; Composer says so on install. On PHP 8 it is installed but not needed; the
  php-8.0 phase can drop the requirement.
- Search consumer code for: `doctrine/annotations`, `jms/serializer`, `authorizenet/authorizenet`.
- Verified: yes. A fresh resolution under a simulated PHP 7.4.33 host locks `doctrine/annotations` 2.0.2 with
  `jms/serializer` 3.32.9.
