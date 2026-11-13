# Deferred testing findings

These findings are recorded for later review. They do not take priority over
running the PHP 7.2 unit suite and increasing coverage of uncovered framework code.
No behavior changes are authorized by this list.

## Assertions beyond line coverage

- UUID: `testGenerateUsesConstructorState()` tests only default construction and
  UUID format. Add a configured v3/v5 state assertion when reviewing test strength.
- Random strings: the invocation test checks length but not preservation of an
  object's character pool. The implementation delegates invocation to static
  `load()` with new arguments; determine whether that distinction is intentional.
- Random numbers: the existing suite does not characterize invocation without
  arguments after construction with custom bounds.
- Consider targeted mutation checks after uncovered-code work, rather than
  treating 100% line coverage as proof that every assertion is strong.

## Boundary behavior observed on PHP 7.2.34

Probed independently of PHPUnit during coverage setup; not yet added as tests:

- `uuid::get(2)` without a namespace/name returns a version 4 UUID.
- `rand::get(1, 0)` accepts the input because a zero maximum becomes the default
  maximum, rather than triggering the inverted-bounds exception.
- `input\map` treats `0`, `false`, and `"0"` as empty input and creates no data
  property.
- `csv::fields2csv([])` returns a newline.

Record these as characterization cases before considering any behavior change.

## Debug, data, and templates discovered in MOD-031/032

- Debug data2str uses the object template for arrays, truncate template for small
  non-string scalars, and length as the truncated type name; string "0" is empty.
- Debug kill options overwrite the options array with the option value and later
  reference an undefined fh_options; termination-path coverage still needed.
- Data webview ignores the returned HTML-encoded clone; dump htmlencode similarly
  ignores the clone. field_list(instance) does not initialize its reflection.
- Template absolute file resolution converts null $file rather than the design;
  tmpl/display declares a class extending the output interface. Do not load that
  declaration casually in a shared PHP 7.2 process: it can abort the suite.
- Template silent-fail/generated-Markdown exception paths may leave buffers open.
  Tests restore buffer depth; this does not repair application buffer behavior.
- Shutdown equal-priority ordering and SQL fetch option intent are awaiting owner
  answers. Continue independent coverage while those questions are pending.

## API findings from MOD-036

- Status class formatter indexes the class-name string with type/description
  instead of looking up CODE_CLASSES; convenience getters use unmatched tokens.
- TRACE exports dynamic input through the existing mapper path that loses fields.
- API request-method getter has an unassigned backing field; HEAD flag is sticky.
- Constructor error callbacks reference undefined message/data/exception context
  on several paths. Distinguish returned errors from thrown errors before repair.
- Response exception's unknown-error fallback calls nonexistent getClassName.
- Empty header names reference a missing exception class. Header getters' array
  return defects were fixed; this does not establish actual HTTP header delivery.

## ORM, AWS, and image findings (MOD-043 through MOD-045)

- ORM delete checks read_done and skips deletion after reading; crud_error's op
  field stores details. Nested argument mapping has an always-false boolean
  is_array check, retained as disabled original code; intended nesting needs input.
- S3 multipart code uses removed SDK v2 classes although Composer specifies v3.
  Its constant is 10GiB despite a 10MB comment. No cloud requests were sent.
- Image constructor sizing policy awaits owner response: minimum bounds versus
  fixed targets. Static single-axis proportional methods and minimum-width guard
  are fixed with real Imagick geometry assertions; both-axis constructor remains.

## Findings from payment, alert and provider tests (MOD-054–061)

- Authorize.Net profile_Customer drops null-initialized SDK fields via isset;
  legacy customer methods contain undefined IDs, abstract payment construction
  and incomplete transaction methods. Support/retirement question is pending.
- Exception print_r wrapping includes trace arguments and can exhaust memory on
  large object graphs; the characterization test isolates its runtime. Review
  diagnostic formatting separately before changing externally visible messages.
- Alert trigger is unfinished (undefined meta/driver); Thread listener depends
  on pthreads and contains unfinished shutdown coordination and a typo.
- data/validate_failure ignores message; event/resolver set_data is a no-op.
- data/orm has an undefined default_handler constant, incompatible driver typing
  and undefined param locals. Session driver namespaces do not match their paths;
  native handler calls absent config::get_or_default and throws class names as
  functions. Their intended compatibility surface needs clarification.
- Legacy SSH authentication conditions are reversed and references still use
  sf/module namespaces; missing exec_output and incomplete kill validation are
  documented in the pending support/retirement question.

## Additional legacy declarations and mapping findings (MOD-065–069)

- View resolution references undefined resolved instead of its searched route.
- Validation controller reads member::$data despite its private visibility;
  its field-present body is otherwise empty. No validation policy was invented.
- from_object mapping omits the required value argument. core/data references
  a create_properties interface not included in this repository.
- Event stub reads listeners, but the base class stores listener. Listener
  factory extends absent core/event/loader; hookable references undefined
  event_data and does not implement hooking.
- Report metadata interface extends a concrete class, which PHP cannot load.
  Database load_read uses self as a value, so it cannot test an instance.
- Certificate helpers declare old namespaces that do not match their paths;
  the file adapter calls __get_cafile_path instead of its declared hook.
- The Mongo collection setter function-call defect is resolved by MOD-067;
  retry/lazy-connection questions remain pending.

## Resolved declarations and current boundaries (MOD-080–087)

- Header exception legacy namespaces now autoload through forwarding files.
- Certificate file adapter hook typo, session reason accessors, scalar error
  initialization, event listener-factory parent, report meta declaration and
  core/data dynamic-field marker are repaired; earlier findings above remain as
  historical evidence, not current failures.
- Validation controller now unwraps members, but its actual validation body is
  still unfinished. Hookable still drops event data. Legacy not_found still pairs
  500 with Not Found; changing that behavior needs an explicit routing decision.
- S3 multipart now uses the required v3 API. The 10 GiB threshold/comment mismatch
  and MIME propagation for large files remain deferred; no policy changed.


SSH follow-ups (deferred): verify whether generated key files must be encrypted at rest (the current OpenSSL command does not specify a cipher), and review temporary-file cleanup when process/auto-connect construction fails. Native session destroy calls regeneration after session_destroy and emits a warning; do not choose restart-versus-end semantics without intent. These are separate from tests of the preserved behavior.


Subscription follow-ups: create documents a string identifier but currently returns null; get_info builds SDK paging offset 1 but never advances it. Passing the existing list object and guarding missing total index zero are fixed; paging advancement and create return contract need intent before changing their public behavior. Response-error's scalar constructor still ignores its second argument, as earlier tests characterize.


Holiday findings (real Yasumi 1.8): valid date/year fields remain unset, provider cache hits are not attached, locale is ignored, and native providers reject the raw timestamp. Getter representation remains an owner question; no calendar/date policy was invented. Legacy root ORM default storage now works, but get drops its result and constructor-array selection remains malformed.


Resolved owner decisions (MOD-105/106/107): SFTP now restarts its idle timeout after each successful read; eager Mongo clients actively ping/retry; unfinished alert publishing/pthread scaffolding is retired while preserving working subscriptions/synchronous listeners. Native SSH server integration, transport-specific blocking/read timeouts and write-failure policy remain separate follow-ups; unit coverage uses an explicit SSH transport boundary with native local streams. Other previously recorded behavior questions remain unchanged.


Deferred pre-existing Mongo detail: load_client's optional NULL options argument still passes through the legacy foreach-on-NULL warning path. All implemented eager/lazy paths use explicit arrays in the tests; normalizing that optional argument can be handled separately without changing the owner-approved connection policy.
