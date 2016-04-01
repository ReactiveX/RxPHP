# 1.2.0

### Bug Fixes

- Fixed uninitialized disposable in `skipUntil` ([2b5ea0b](https://github.com/ReactiveX/RxPHP/commit/2b5ea0b5fc8ae6f6cda20eb0fb0a3512ea3ef217))
- Fixed `asObservable` operator was not passing through the scheduler ([d805cfd](https://github.com/ReactiveX/RxPHP/commit/d805cfdf620334ab977ef9532f1c2e29f79c4cf7))
- Fixed uninitialized disposable in `retry` ([ca4da7e](https://github.com/ReactiveX/RxPHP/commit/ca4da7e24527ffcb7d206865e1e8c2caee3ea583))
- Fixed #23 - changing yield behavior in hhvm ([da2737c](https://github.com/ReactiveX/RxPHP/commit/da2737ccd19fa3faed3cedc734130c8e6866d66f))
- Fixed #33 - For Rx\React\Promise::toObservable, wrap non-exceptions on reject ([ed852de](https://github.com/ReactiveX/RxPHP/commit/ed852de1596652466d72ef0a67b491a3b0242927))

### Features

- Added ability to record and validate output of demo files ([883ad4b](https://github.com/ReactiveX/RxPHP/commit/883ad4ba8bfeb3e2192d7501039b99eb8a641229))
- Added `catchError` operator ([cd4fc03](https://github.com/ReactiveX/RxPHP/commit/cd4fc034644e7d30c697597d9cec4d4995080dcd))
- Added `takeWhile` and `takeWhileWithIndex` operators ([177835e](https://github.com/ReactiveX/RxPHP/commit/177835e8dbfd593f1f62cefb88012bd9ed306d77))
- Added `startWith` and `startWithArray` operators ([585f893](https://github.com/ReactiveX/RxPHP/commit/585f8933dd859c720aacee385f3ac0c62f07f0f0))
- Added `retryWhen` operator ([19b36fc](https://github.com/ReactiveX/RxPHP/commit/19b36fc5f232ad1f4d8a7da953e59d09affc7bb1))
- Added utility to generate documentation for reactivex.io ([a62d46b](https://github.com/ReactiveX/RxPHP/commit/a62d46b50a24bc88adca5da64215f00c5ce2b350))
- Added `concatAll` and `concatMap` operators ([c794cf3](https://github.com/ReactiveX/RxPHP/commit/c794cf31cf577fe374f7ab120c6a0b20afeb3957))
- Added `skipWhile` and `skipWhileWithIndex` operators ([80c997f](https://github.com/ReactiveX/RxPHP/commit/80c997ff1cc2ed852ca2db4f60872fc32cab2daf))
- Added `max` operator ([1a841fa](https://github.com/ReactiveX/RxPHP/commit/1a841fa25e7b17c70fd00084a3688fb5d432ce15))

# 1.1.0

### Bug fixes

- Fixed bug where the `map` operator called `onNext` after it was disposed ([8a1d68c](https://github.com/ReactiveX/RxPHP/commit/8a1d68c))
- Minor fixes to `groupByUntil` ([f6f56e3](https://github.com/ReactiveX/RxPHP/commit/f6f56e3))
- Minor fixes to `delay` ([2613f36](https://github.com/ReactiveX/RxPHP/commit/2613f36))

### Features

- Added `range` observable ([1684522](https://github.com/ReactiveX/RxPHP/commit/1684522)) ([0ceab90](https://github.com/ReactiveX/RxPHP/commit/0ceab90)) ([121806c](https://github.com/ReactiveX/RxPHP/commit/121806c))
- Added index to `map` selector ([8a1d68c](https://github.com/ReactiveX/RxPHP/commit/8a1d68c))
- Added `mapTo` operator ([8a1d68c](https://github.com/ReactiveX/RxPHP/commit/8a1d68c))
- Added `timer` observable ([304bc0c](https://github.com/ReactiveX/RxPHP/commit/304bc0c))
- Added `distinct` and `distinctKey` ([e9575f1](https://github.com/ReactiveX/RxPHP/commit/e9575f1))
- Split `distinctUntilChanged` into `distinctUntilChanged` and `distinctUntilKeyChanged` ([e9575f1](https://github.com/ReactiveX/RxPHP/commit/e9575f1))
