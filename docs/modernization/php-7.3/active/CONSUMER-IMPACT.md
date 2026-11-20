# Consumer impact, php-7.3 phase

Behavior changes of this phase that an application using flat may notice, in the format of
[../../php-7.2/active/CONSUMER-IMPACT.md](../../php-7.2/active/CONSUMER-IMPACT.md): what
consumers saw before, what they see now, and what to search for in their code.

## So far: none

- MOD-189 (environment) and MOD-190 (JShrink `continue` written as `break`) change no
  behavior a caller can see.
- One thing consumers can notice, which is not a flat change: the root `composer.json` now
  requires `php >=7.3.0` (it was `>=7.2.1`). A PHP 7.2 host keeps installing `v1.72.x`; Composer
  skips `v1.73.0` for it.
