# Semantic Versioning policy

Mythos OS follows Semantic Versioning: `MAJOR.MINOR.PATCH`.

## PATCH

Backward-compatible defect fixes, performance improvements, documentation corrections and security patches that preserve supported APIs.

## MINOR

Backward-compatible capabilities, optional manifest additions, new contracts and deprecations. Existing integrations continue to work without mandatory code changes.

## MAJOR

Incompatible public-contract, manifest, runtime or persistence changes. A MAJOR release includes an upgrade guide, migration strategy, deprecation removals and an explicit compatibility boundary.

Stable public APIs cannot change incompatibly in PATCH or MINOR releases. Experimental APIs may change in MINOR releases with release notes. Internal APIs have no compatibility guarantee.

The canonical version is stored in `VERSION`; release artifacts, application compatibility and changelog entries must agree.
