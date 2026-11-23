# Authorize.Net mock (WireMock)

Stubs for `docker/bin/test sandbox-mock`: the sandbox tests in `tests/Sandbox`, run offline against a
WireMock server with fake credentials. The answers follow Authorize.Net's documentation (message shapes from
the XML schema, codes `I00001`, `E00007`, `E00040`). They are not recorded from the real sandbox, so a pass
proves only that flat agrees with this reading of the documentation.

Limits:

- One customer profile (id `1001`), with memory through a WireMock scenario: created, updated, deleted.
- Fixed run id `flat-mock0001`, fixed fake login and key (`docker/docker-compose.anet-mock.yml`).
- Only the messages the sandbox tests send. Anything else gets WireMock's 404.
- Real responses start with a byte-order mark; these do not.

A stub is one JSON file in `mappings/`; a request matches by the XML element names it contains.
