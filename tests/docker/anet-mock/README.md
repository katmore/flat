# Authorize.Net mock (WireMock)

Stubs for the Authorize.Net tests in `tests/Sandbox`, run against a WireMock server with fake credentials. It is a
test service like MongoDB and RabbitMQ: `docker/bin/test all`, `services` and `anet` start it. The answers follow Authorize.Net's documentation (message shapes from
the XML schema, codes `I00001`, `E00007`, `E00040`). They are not recorded from the real sandbox, so a pass
proves only that flat agrees with this reading of the documentation.

Limits:

- One customer profile (id `1001`), with memory through a WireMock scenario: created, updated, deleted.
- Fixed run id `flat-mock0001`, fixed fake login and key (`docker/docker-compose.anet-mock.yml`).
- Customer transactions: `authcapture` and `authonly` are approved (direct response of the real 55-field length,
  `|`-encapsulated as the SDK asks); a payment method made with the billing ZIP code 46282 is declined with `E00027`
  (confirmed on the sandbox, 2026-11-23).
- Only the messages the sandbox tests send. Anything else gets WireMock's 404.
- Real responses start with a byte-order mark; these do not. The root element carries the `xsi` and `xsd`
  namespaces first, as the real ones do: the old SDK's XPath handling depends on that order.

A stub is one JSON file in `mappings/`; a request matches by the XML element names it contains.

Known differences from the real sandbox (found by running both):

- The real subscription list lags behind a new subscription by some seconds; the mock lists it at once.
- The mock does not model every rule. What it does model was taken from real answers: an empty profile is `E00041`;
  a validation mode without payment profiles is `E00013`.

